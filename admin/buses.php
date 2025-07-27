<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Set message
    setFlashMessage("error", "You do not have permission to access the admin dashboard.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Manage Buses";

// Include database connection
$conn = require_once '../config/database.php';

// Initialize variables
$id = $name = $registration_number = $capacity = $type = $amenities = $status = "";
$errors = [];

// Process form submission for adding/editing bus
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_bus'])) {
    // Get form data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = trim($_POST['name']);
    $registration_number = trim($_POST['registration_number']);
    $capacity = intval($_POST['capacity']);
    $type = $_POST['type'];
    $status = $_POST['status'];

    // Process amenities from checkboxes
    if (isset($_POST['amenity_list']) && is_array($_POST['amenity_list'])) {
        $amenities = implode(', ', $_POST['amenity_list']);
    } else {
        $amenities = '';
    }

    // Validate form data
    if (empty($name)) {
        $errors[] = "Bus name is required.";
    }

    if (empty($registration_number)) {
        $errors[] = "Registration number is required.";
    }

    if ($capacity <= 0) {
        $errors[] = "Capacity must be greater than 0.";
    }

    // If no errors, proceed with database operation
    if (empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();

        try {
            if ($id > 0) {
                // Update existing bus
                $sql = "UPDATE buses SET
                        name = ?,
                        registration_number = ?,
                        capacity = ?,
                        type = ?,
                        amenities = ?,
                        status = ?
                        WHERE id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssisssi", $name, $registration_number, $capacity, $type, $amenities, $status, $id);
            } else {
                // Add new bus
                $sql = "INSERT INTO buses (name, registration_number, capacity, type, amenities, status)
                        VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssisss", $name, $registration_number, $capacity, $type, $amenities, $status);
            }

            $stmt->execute();
            $stmt->close();

            // Log activity
            $action = ($id > 0) ? "Updated" : "Added";
            logActivity("Admin", $action . " bus: " . $name);

            // Commit transaction
            $conn->commit();

            // Set success message
            setFlashMessage("success", "Bus " . strtolower($action) . " successfully.");

            // Reset form
            $id = $name = $registration_number = $capacity = $type = $amenities = $status = "";

            // Redirect to avoid form resubmission
            header("Location: buses.php");
            exit();
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Set error message
            setFlashMessage("error", "Error " . strtolower(($id > 0) ? "updating" : "adding") . " bus: " . $e->getMessage());
        }
    }
}

// Process delete request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    // Check if bus is used in any schedules
    $check_sql = "SELECT COUNT(*) as count FROM schedules WHERE bus_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($check_row['count'] > 0) {
        // Option to force delete by updating schedules to use a different bus or mark them as cancelled
        if (isset($_GET['force']) && $_GET['force'] == 'true') {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Get bus details for logging
                $get_sql = "SELECT name FROM buses WHERE id = ?";
                $get_stmt = $conn->prepare($get_sql);
                $get_stmt->bind_param("i", $delete_id);
                $get_stmt->execute();
                $get_result = $get_stmt->get_result();
                $bus = $get_result->fetch_assoc();
                $get_stmt->close();
                
                // Mark affected schedules as cancelled
                $update_sql = "UPDATE schedules SET status = 'cancelled' WHERE bus_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $delete_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Delete bus
                $sql = "DELETE FROM buses WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $delete_id);
                $stmt->execute();
                $stmt->close();
                
                // Log activity
                logActivity("Admin", "Force deleted bus: " . $bus['name'] . " and cancelled related schedules");
                
                // Commit transaction
                $conn->commit();
                
                // Set success message
                setFlashMessage("success", "Bus deleted successfully and related schedules marked as cancelled.");
            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                
                // Set error message
                setFlashMessage("error", "Error deleting bus: " . $e->getMessage());
            }
        } else {
            // Show error with option to force delete
            setFlashMessage("error", "Cannot delete bus because it is used in schedules. <a href='buses.php?action=delete&id=" . $delete_id . "&force=true' class='underline text-primary-600 hover:text-primary-800'>Click here</a> to delete anyway and mark related schedules as cancelled.");
        }
    } else {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Get bus details for logging
            $get_sql = "SELECT name FROM buses WHERE id = ?";
            $get_stmt = $conn->prepare($get_sql);
            $get_stmt->bind_param("i", $delete_id);
            $get_stmt->execute();
            $get_result = $get_stmt->get_result();
            $bus = $get_result->fetch_assoc();
            $get_stmt->close();

            // Delete bus
            $sql = "DELETE FROM buses WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            // Log activity
            logActivity("Admin", "Deleted bus: " . $bus['name']);

            // Commit transaction
            $conn->commit();

            // Set success message
            setFlashMessage("success", "Bus deleted successfully.");
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Set error message
            setFlashMessage("error", "Error deleting bus: " . $e->getMessage());
        }
    }

    // Redirect to avoid resubmission
    header("Location: buses.php");
    exit();
}

// Process edit request
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);

    // Get bus details
    $sql = "SELECT * FROM buses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bus = $result->fetch_assoc();
        $id = $bus['id'];
        $name = $bus['name'];
        $registration_number = $bus['registration_number'];
        $capacity = $bus['capacity'];
        $type = $bus['type'];
        $amenities = $bus['amenities'];
        $status = $bus['status'];
    }

    $stmt->close();
}

// Get all buses
$sql = "SELECT * FROM buses ORDER BY name";
$result = $conn->query($sql);
$buses = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buses[] = $row;
    }
}
?>

<?php
// Start output buffering to capture content for the template
ob_start();
?>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <!-- Admin Navigation Breadcrumb -->
    <div class="text-sm breadcrumbs mb-4">
        <ul class="flex items-center space-x-2 text-gray-600">
            <li><a href="index.php" class="hover:text-primary-600 transition-colors"><i class="fas fa-home mr-1"></i> Dashboard</a></li>
            <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
            <li class="text-primary-700 font-medium">Manage Buses</li>
        </ul>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-bus text-primary-600 mr-3"></i> Manage Buses
        </h1>
        <a href="index.php" class="btn-secondary flex items-center transition-transform hover:-translate-x-1">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Display Flash Messages -->
    <?php displayFlashMessages(); ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-bus text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Buses</p>
                <p class="text-xl font-bold"><?php echo count($buses); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active Buses</p>
                <p class="text-xl font-bold">
                    <?php
                        $active_count = 0;
                        foreach ($buses as $bus) {
                            if ($bus['status'] == 'active') $active_count++;
                        }
                        echo $active_count;
                    ?>
                </p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <i class="fas fa-wrench text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">In Maintenance</p>
                <p class="text-xl font-bold">
                    <?php
                        $maintenance_count = 0;
                        foreach ($buses as $bus) {
                            if ($bus['status'] == 'maintenance') $maintenance_count++;
                        }
                        echo $maintenance_count;
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Bus Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 border border-gray-100">
        <div class="flex items-center mb-4">
            <div class="p-2 rounded-full bg-primary-100 text-primary-600 mr-3">
                <i class="fas fa-<?php echo ($id) ? 'edit' : 'plus'; ?>"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800"><?php echo ($id) ? 'Edit' : 'Add New'; ?> Bus</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <div class="flex items-center mb-1">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span class="font-semibold">Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside pl-4">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-gray-400 mr-1"></i> Bus Name
                    </label>
                    <input type="text" id="name" name="name" value="<?php echo $name; ?>"
                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                           placeholder="Enter bus name" required>
                </div>

                <div>
                    <label for="registration_number" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card text-gray-400 mr-1"></i> Registration Number
                    </label>
                    <input type="text" id="registration_number" name="registration_number" value="<?php echo $registration_number; ?>"
                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                           placeholder="e.g. KBZ 123A" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-users text-gray-400 mr-1"></i> Capacity (Seats)
                    </label>
                    <input type="number" id="capacity" name="capacity" value="<?php echo $capacity; ?>" min="1" max="100"
                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                           placeholder="e.g. 44" required>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-bus-alt text-gray-400 mr-1"></i> Bus Type
                    </label>
                    <select id="type" name="type"
                            class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                            required>
                        <option value="standard" <?php echo ($type == 'standard') ? 'selected' : ''; ?>>Standard</option>
                        <option value="executive" <?php echo ($type == 'executive') ? 'selected' : ''; ?>>Executive</option>
                        <option value="luxury" <?php echo ($type == 'luxury') ? 'selected' : ''; ?>>Luxury</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on text-gray-400 mr-1"></i> Status
                    </label>
                    <select id="status" name="status"
                            class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                            required>
                        <option value="active" <?php echo ($status == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="maintenance" <?php echo ($status == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="inactive" <?php echo ($status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-list-ul text-gray-400 mr-1"></i> Amenities
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mt-2" id="amenities-container">
                    <?php
                    // Define common amenities
                    $common_amenities = [
                        'Air Conditioning',
                        'Reclining Seats',
                        'WiFi',
                        'USB Charging',
                        'Refreshments',
                        'Onboard Entertainment',
                        'Reading Lights',
                        'Spacious Legroom',
                        'Toilet',
                        'Power Outlets',
                        'Blankets',
                        'Pillows'
                    ];

                    // Get current amenities as array
                    $selected_amenities = !empty($amenities) ? explode(', ', $amenities) : [];

                    // Display amenity checkboxes
                    foreach ($common_amenities as $amenity) {
                        $is_checked = in_array($amenity, $selected_amenities) ? 'checked' : '';
                        echo '<div class="flex items-center bg-white p-2 rounded-lg border border-gray-200 hover:border-primary-300 transition-colors">';
                        echo '<input type="checkbox" id="amenity_' . sanitize_id($amenity) . '" name="amenity_list[]" value="' . htmlspecialchars($amenity) . '" ' . $is_checked . ' class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">';
                        echo '<label for="amenity_' . sanitize_id($amenity) . '" class="ml-2 block text-sm text-gray-700 cursor-pointer flex-grow">' . $amenity . '</label>';
                        echo '</div>';
                    }

                    // Add custom amenities that aren't in the common list
                    foreach ($selected_amenities as $amenity) {
                        if (!in_array($amenity, $common_amenities)) {
                            echo '<div class="flex items-center bg-white p-2 rounded-lg border border-gray-200 hover:border-primary-300 transition-colors">';
                            echo '<input type="checkbox" id="amenity_' . sanitize_id($amenity) . '" name="amenity_list[]" value="' . htmlspecialchars($amenity) . '" checked class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">';
                            echo '<label for="amenity_' . sanitize_id($amenity) . '" class="ml-2 block text-sm text-gray-700 cursor-pointer flex-grow">' . $amenity . '</label>';
                            echo '</div>';
                        }
                    }

                    // Helper function to create valid IDs from amenity names
                    function sanitize_id($string) {
                        return strtolower(str_replace(' ', '_', $string));
                    }
                    ?>
                </div>
                <div class="mt-3">
                    <label for="custom_amenity" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-plus-circle text-gray-400 mr-1"></i> Add Custom Amenity
                    </label>
                    <div class="flex">
                        <input type="text" id="custom_amenity" class="form-input rounded-l-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 flex-grow" placeholder="Enter custom amenity">
                        <button type="button" id="add_amenity" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-r-lg shadow-sm transition-colors">
                            Add
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <?php if ($id): ?>
                    <a href="buses.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                <?php endif; ?>
                <button type="submit" name="save_bus" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-sm transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i> <?php echo ($id) ? 'Update' : 'Add'; ?> Bus
                </button>
            </div>
        </form>
    </div>

    <!-- Buses List -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-primary-100 text-primary-600 mr-3">
                    <i class="fas fa-list"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">All Buses</h2>
            </div>

            <!-- Search and Filter Controls -->
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative">
                    <input type="text" id="bus-search" placeholder="Search buses..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <div class="flex gap-2">
                    <select id="filter-type" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="all">All Types</option>
                        <option value="standard">Standard</option>
                        <option value="executive">Executive</option>
                        <option value="luxury">Luxury</option>
                    </select>

                    <select id="filter-status" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($buses)): ?>
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <div class="text-gray-400 text-5xl mb-3">
                    <i class="fas fa-bus"></i>
                </div>
                <p class="text-gray-500 mb-2">No buses found in the system.</p>
                <p class="text-gray-500 text-sm">Add your first bus using the form above.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border-b border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-bus-alt mr-2 text-gray-400"></i> Bus Details
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-id-card mr-2 text-gray-400"></i> Registration
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-users mr-2 text-gray-400"></i> Capacity
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag mr-2 text-gray-400"></i> Type
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-toggle-on mr-2 text-gray-400"></i> Status
                                </div>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-end">
                                    <i class="fas fa-cog mr-2 text-gray-400"></i> Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="bus-table-body">
                        <?php foreach ($buses as $bus): ?>
                            <tr class="bus-row hover:bg-gray-50"
                                data-name="<?php echo strtolower($bus['name']); ?>"
                                data-type="<?php echo $bus['type']; ?>"
                                data-status="<?php echo $bus['status']; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full
                                            <?php echo ($bus['type'] == 'standard') ? 'bg-blue-100 text-blue-600' :
                                                (($bus['type'] == 'executive') ? 'bg-purple-100 text-purple-600' : 'bg-green-100 text-green-600'); ?>">
                                            <i class="fas fa-bus"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $bus['name']; ?></div>
                                            <div class="text-xs text-gray-500">Added: <?php echo date('M d, Y', strtotime($bus['created_at'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $bus['registration_number']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $bus['capacity']; ?> seats</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        echo ($bus['type'] == 'standard') ? 'bg-blue-100 text-blue-800' :
                                            (($bus['type'] == 'executive') ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800');
                                        ?>">
                                        <?php
                                        $icon = ($bus['type'] == 'standard') ? 'bus' :
                                            (($bus['type'] == 'executive') ? 'star-half-alt' : 'star');
                                        ?>
                                        <i class="fas fa-<?php echo $icon; ?> mr-1"></i>
                                        <?php echo ucfirst($bus['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        echo ($bus['status'] == 'active') ? 'bg-green-100 text-green-800' :
                                            (($bus['status'] == 'maintenance') ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                        ?>">
                                        <?php
                                        $icon = ($bus['status'] == 'active') ? 'check-circle' :
                                            (($bus['status'] == 'maintenance') ? 'wrench' : 'times-circle');
                                        ?>
                                        <i class="fas fa-<?php echo $icon; ?> mr-1"></i>
                                        <?php echo ucfirst($bus['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="buses.php?action=edit&id=<?php echo $bus['id']; ?>"
                                       class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors mr-2">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <a href="buses.php?action=delete&id=<?php echo $bus['id']; ?>"
                                       class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
                                       onclick="return confirm('Are you sure you want to delete this bus? This action cannot be undone.');">
                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (for future implementation) -->
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium"><?php echo count($buses); ?></span> buses
                </div>

                <div class="hidden md:flex">
                    <!-- Pagination controls would go here -->
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for Search and Filter -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('bus-search');
        const typeFilter = document.getElementById('filter-type');
        const statusFilter = document.getElementById('filter-status');
        const busRows = document.querySelectorAll('.bus-row');

        // Function to filter the table
        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const typeValue = typeFilter.value;
            const statusValue = statusFilter.value;

            busRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const type = row.getAttribute('data-type');
                const status = row.getAttribute('data-status');

                // Check if row matches all filters
                const matchesSearch = name.includes(searchTerm);
                const matchesType = typeValue === 'all' || type === typeValue;
                const matchesStatus = statusValue === 'all' || status === statusValue;

                // Show/hide row based on filter results
                if (matchesSearch && matchesType && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Add event listeners
        searchInput.addEventListener('input', filterTable);
        typeFilter.addEventListener('change', filterTable);
        statusFilter.addEventListener('change', filterTable);

        // Amenities Checkbox Functionality
        const amenitiesContainer = document.getElementById('amenities-container');
        const addAmenityBtn = document.getElementById('add_amenity');
        const customAmenityInput = document.getElementById('custom_amenity');

        // Function to add custom amenity
        function addCustomAmenity() {
            const customValue = customAmenityInput.value.trim();

            if (customValue === '') {
                return; // Don't add empty amenities
            }

            // Check if this amenity already exists
            const existingCheckboxes = document.querySelectorAll('input[name="amenity_list[]"]');
            let alreadyExists = false;

            existingCheckboxes.forEach(checkbox => {
                if (checkbox.value.toLowerCase() === customValue.toLowerCase()) {
                    checkbox.checked = true;
                    // Highlight the existing checkbox briefly
                    const parentDiv = checkbox.closest('div');
                    parentDiv.classList.add('ring-2', 'ring-primary-500');
                    setTimeout(() => {
                        parentDiv.classList.remove('ring-2', 'ring-primary-500');
                    }, 1500);
                    alreadyExists = true;
                }
            });

            if (alreadyExists) {
                customAmenityInput.value = '';
                return;
            }

            // Create a sanitized ID
            const sanitizedId = 'amenity_' + customValue.toLowerCase().replace(/\s+/g, '_');

            // Create a new checkbox for the custom amenity
            const newCheckboxDiv = document.createElement('div');
            newCheckboxDiv.className = 'flex items-center bg-white p-2 rounded-lg border border-gray-200 hover:border-primary-300 transition-colors animate-fadeIn';

            // Create the HTML for the new checkbox
            newCheckboxDiv.innerHTML = `
                <input type="checkbox" id="${sanitizedId}" name="amenity_list[]" value="${customValue}" checked
                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                <label for="${sanitizedId}" class="ml-2 block text-sm text-gray-700 cursor-pointer flex-grow">${customValue}</label>
            `;

            // Add the new checkbox to the container
            amenitiesContainer.appendChild(newCheckboxDiv);

            // Clear the custom input
            customAmenityInput.value = '';

            // Add a brief highlight effect
            newCheckboxDiv.classList.add('ring-2', 'ring-primary-500');
            setTimeout(() => {
                newCheckboxDiv.classList.remove('ring-2', 'ring-primary-500');
            }, 1500);
        }

        // Add event listener to the add button
        if (addAmenityBtn) {
            addAmenityBtn.addEventListener('click', addCustomAmenity);
        }

        // Allow pressing Enter in the custom amenity input
        if (customAmenityInput) {
            customAmenityInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Prevent form submission
                    addCustomAmenity();
                }
            });
        }
    });
</script>

<?php
// Get the buffered content
$admin_content = ob_get_clean();

// Include the admin template
require_once '../includes/templates/admin_template.php';
?>
