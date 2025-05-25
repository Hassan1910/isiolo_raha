<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Set message
    setFlashMessage("error", "You do not have permission to access the admin dashboard.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Manage Routes";

// Include header
require_once '../includes/templates/admin_header.php';

// Include functions
require_once '../includes/functions.php';

// Include database connection
$conn = require_once '../config/database.php';

// Initialize variables
$id = $origin = $destination = $distance = $duration = "";
$errors = [];

// Process form submission for adding/editing route
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_route'])) {
    // Get form data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $distance = floatval($_POST['distance']);
    $duration = intval($_POST['duration']);

    // Validate form data
    if (empty($origin)) {
        $errors[] = "Origin is required.";
    }

    if (empty($destination)) {
        $errors[] = "Destination is required.";
    }

    if ($origin == $destination) {
        $errors[] = "Origin and destination cannot be the same.";
    }

    if ($distance <= 0) {
        $errors[] = "Distance must be greater than 0.";
    }

    if ($duration <= 0) {
        $errors[] = "Duration must be greater than 0.";
    }

    // Check if route already exists
    if (empty($errors)) {
        $check_sql = "SELECT id FROM routes WHERE origin = ? AND destination = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ssi", $origin, $destination, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $errors[] = "A route from $origin to $destination already exists.";
        }

        $check_stmt->close();
    }

    // If no errors, proceed with database operation
    if (empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();

        try {
            if ($id > 0) {
                // Update existing route
                $sql = "UPDATE routes SET
                        origin = ?,
                        destination = ?,
                        distance = ?,
                        duration = ?
                        WHERE id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdii", $origin, $destination, $distance, $duration, $id);
            } else {
                // Add new route
                $sql = "INSERT INTO routes (origin, destination, distance, duration)
                        VALUES (?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdi", $origin, $destination, $distance, $duration);
            }

            $stmt->execute();
            $stmt->close();

            // Log activity
            $action = ($id > 0) ? "Updated" : "Added";
            logActivity("Admin", $action . " route: " . $origin . " to " . $destination);

            // Commit transaction
            $conn->commit();

            // Set success message
            setFlashMessage("success", "Route " . strtolower($action) . " successfully.");

            // Reset form
            $id = $origin = $destination = $distance = $duration = "";

            // Redirect to avoid form resubmission
            header("Location: routes.php");
            exit();
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Set error message
            setFlashMessage("error", "Error " . strtolower(($id > 0) ? "updating" : "adding") . " route: " . $e->getMessage());
        }
    }
}

// Process delete request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    // Check if route is used in any schedules
    $check_sql = "SELECT COUNT(*) as count FROM schedules WHERE route_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($check_row['count'] > 0) {
        setFlashMessage("error", "Cannot delete route because it is used in schedules.");
    } else {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Get route details for logging
            $get_sql = "SELECT origin, destination FROM routes WHERE id = ?";
            $get_stmt = $conn->prepare($get_sql);
            $get_stmt->bind_param("i", $delete_id);
            $get_stmt->execute();
            $get_result = $get_stmt->get_result();
            $route = $get_result->fetch_assoc();
            $get_stmt->close();

            // Delete route
            $sql = "DELETE FROM routes WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            // Log activity
            logActivity("Admin", "Deleted route: " . $route['origin'] . " to " . $route['destination']);

            // Commit transaction
            $conn->commit();

            // Set success message
            setFlashMessage("success", "Route deleted successfully.");
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Set error message
            setFlashMessage("error", "Error deleting route: " . $e->getMessage());
        }
    }

    // Redirect to avoid resubmission
    header("Location: routes.php");
    exit();
}

// Process edit request
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);

    // Get route details
    $sql = "SELECT * FROM routes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $route = $result->fetch_assoc();
        $id = $route['id'];
        $origin = $route['origin'];
        $destination = $route['destination'];
        $distance = $route['distance'];
        $duration = $route['duration'];
    }

    $stmt->close();
}

// Get all routes
$sql = "SELECT r.*,
        (SELECT COUNT(*) FROM schedules s WHERE s.route_id = r.id) as schedule_count
        FROM routes r
        ORDER BY r.origin, r.destination";
$result = $conn->query($sql);
$routes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Get list of cities for dropdown
$cities_sql = "SELECT DISTINCT origin as city FROM routes UNION SELECT DISTINCT destination as city FROM routes ORDER BY city";
$cities_result = $conn->query($cities_sql);
$cities = [];

if ($cities_result && $cities_result->num_rows > 0) {
    while ($row = $cities_result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}
?>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6">
    <!-- Sidebar -->
    <div class="md:col-span-1">
        <?php include_once '../includes/templates/admin_sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="md:col-span-4">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Manage Routes</h1>
                <a href="index.php" class="btn-secondary text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>

            <!-- Display Flash Messages -->
            <?php displayFlashMessages(); ?>

            <!-- Route Form Card -->
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold"><?php echo ($id) ? 'Edit Route' : 'Add New Route'; ?></h2>
                    <?php if ($id): ?>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Editing ID: <?php echo $id; ?></span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                        <div class="flex items-center mb-1">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span class="font-medium">Please fix the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside pl-4">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="" id="routeForm">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="origin" class="block text-sm font-medium text-gray-700 mb-2">Origin City</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <input type="text" id="origin" name="origin" value="<?php echo $origin; ?>"
                                       class="form-input pl-10 w-full" list="cities" required
                                       placeholder="Enter origin city">
                                <datalist id="cities">
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?php echo $city; ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">City where the route starts</p>
                        </div>

                        <div>
                            <label for="destination" class="block text-sm font-medium text-gray-700 mb-2">Destination City</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-map-pin text-gray-400"></i>
                                </div>
                                <input type="text" id="destination" name="destination" value="<?php echo $destination; ?>"
                                       class="form-input pl-10 w-full" list="cities" required
                                       placeholder="Enter destination city">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">City where the route ends</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="distance" class="block text-sm font-medium text-gray-700 mb-2">Distance (km)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-road text-gray-400"></i>
                                </div>
                                <input type="number" id="distance" name="distance" value="<?php echo $distance; ?>"
                                       min="1" step="0.1" class="form-input pl-10 w-full" required
                                       placeholder="Enter distance in kilometers">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Total distance between cities in kilometers</p>
                        </div>

                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                                <input type="number" id="duration" name="duration" value="<?php echo $duration; ?>"
                                       min="1" class="form-input pl-10 w-full" required
                                       placeholder="Enter travel time in minutes">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Estimated travel time in minutes</p>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <?php if ($id): ?>
                            <a href="routes.php" class="btn-secondary">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </a>
                        <?php endif; ?>
                        <button type="submit" name="save_route" class="btn-primary">
                            <i class="fas fa-save mr-2"></i> <?php echo ($id) ? 'Update Route' : 'Add Route'; ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Routes List Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">All Routes</h2>

                    <!-- Search Box -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="table-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-80 pl-10 p-2.5" placeholder="Search for routes...">
                    </div>
                </div>

                <?php if (empty($routes)): ?>
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-3">
                            <i class="fas fa-route text-5xl"></i>
                        </div>
                        <p class="text-gray-500">No routes found. Add your first route above.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 searchable-table">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Origin</th>
                                    <th scope="col" class="px-6 py-3">Destination</th>
                                    <th scope="col" class="px-6 py-3">Distance</th>
                                    <th scope="col" class="px-6 py-3">Duration</th>
                                    <th scope="col" class="px-6 py-3">Schedules</th>
                                    <th scope="col" class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($routes as $route): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium"><?php echo $route['origin']; ?></td>
                                        <td class="px-6 py-4 font-medium"><?php echo $route['destination']; ?></td>
                                        <td class="px-6 py-4"><?php echo $route['distance']; ?> km</td>
                                        <td class="px-6 py-4"><?php echo formatDuration($route['duration']); ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($route['schedule_count'] > 0): ?>
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                    <?php echo $route['schedule_count']; ?> schedule<?php echo $route['schedule_count'] > 1 ? 's' : ''; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">No schedules</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="routes.php?action=edit&id=<?php echo $route['id']; ?>" class="text-primary-600 hover:text-primary-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="#" class="text-red-600 hover:text-red-900"
                                               onclick="confirmDelete(<?php echo $route['id']; ?>, '<?php echo $route['origin']; ?>', '<?php echo $route['destination']; ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Confirm Delete</h3>
            <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mb-6">
            <p class="text-gray-700 mb-3">Are you sure you want to delete the route from <span id="deleteOrigin" class="font-semibold"></span> to <span id="deleteDestination" class="font-semibold"></span>?</p>
            <p class="text-red-600 text-sm">This action cannot be undone.</p>
        </div>
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md">
                Cancel
            </button>
            <a id="confirmDeleteBtn" href="#" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                Delete Route
            </a>
        </div>
    </div>
</div>

<script>
    // Delete confirmation modal
    function confirmDelete(id, origin, destination) {
        document.getElementById('deleteOrigin').textContent = origin;
        document.getElementById('deleteDestination').textContent = destination;
        document.getElementById('confirmDeleteBtn').href = 'routes.php?action=delete&id=' + id;
        document.getElementById('deleteModal').classList.remove('hidden');
        return false;
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeDeleteModal();
        }
    });

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('routeForm');
        const origin = document.getElementById('origin');
        const destination = document.getElementById('destination');

        form.addEventListener('submit', function(event) {
            if (origin.value === destination.value) {
                event.preventDefault();
                alert('Origin and destination cannot be the same.');
            }
        });
    });
</script>

<?php
// Include admin footer
require_once '../includes/templates/admin_footer.php';
?>
