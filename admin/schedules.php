<?php
// Include session configuration
require_once '../config/session_config.php';

// Include functions
require_once '../includes/functions.php';

// Set page title
$page_title = "Manage Schedules";

// Include database connection
$conn = require_once '../config/database.php';

// Initialize variables
$id = $route_id = $bus_id = $departure_time = $arrival_time = $fare = $status = "";
$errors = [];

// Process form submission for adding/editing schedule
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_schedule'])) {
    // Get form data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $route_id = intval($_POST['route_id']);
    $bus_id = intval($_POST['bus_id']);
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $fare = floatval($_POST['fare']);
    $status = $_POST['status'];

    // Validate form data
    if ($route_id <= 0) {
        $errors[] = "Please select a valid route.";
    }

    if ($bus_id <= 0) {
        $errors[] = "Please select a valid bus.";
    }

    if (empty($departure_time)) {
        $errors[] = "Departure time is required.";
    }

    if (empty($arrival_time)) {
        $errors[] = "Arrival time is required.";
    }

    if ($fare <= 0) {
        $errors[] = "Fare must be greater than 0.";
    }

    // Check if departure time is before arrival time
    if (!empty($departure_time) && !empty($arrival_time)) {
        $departure = new DateTime($departure_time);
        $arrival = new DateTime($arrival_time);

        if ($departure >= $arrival) {
            $errors[] = "Departure time must be before arrival time.";
        }
    }

    // Check if bus is available for the selected time
    if (empty($errors) && $bus_id > 0) {
        $check_sql = "SELECT id FROM schedules
                     WHERE bus_id = ?
                     AND id != ?
                     AND status != 'cancelled'
                     AND ((departure_time <= ? AND arrival_time >= ?)
                          OR (departure_time <= ? AND arrival_time >= ?)
                          OR (departure_time >= ? AND arrival_time <= ?))";

        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iissssss", $bus_id, $id, $arrival_time, $departure_time, $departure_time, $departure_time, $departure_time, $arrival_time);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $errors[] = "The selected bus is not available for the specified time period.";
        }

        $check_stmt->close();
    }

    // If no errors, proceed with database operation
    if (empty($errors)) {
        // Begin transaction
        $conn->begin_transaction();

        try {
            if ($id > 0) {
                // Update existing schedule
                $sql = "UPDATE schedules SET
                        route_id = ?,
                        bus_id = ?,
                        departure_time = ?,
                        arrival_time = ?,
                        fare = ?,
                        status = ?
                        WHERE id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissdsi", $route_id, $bus_id, $departure_time, $arrival_time, $fare, $status, $id);
            } else {
                // Add new schedule
                $sql = "INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status)
                        VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissds", $route_id, $bus_id, $departure_time, $arrival_time, $fare, $status);
            }

            $stmt->execute();
            $stmt->close();

            // Get route and bus details for logging
            $details_sql = "SELECT r.origin, r.destination, b.name AS bus_name
                           FROM routes r, buses b
                           WHERE r.id = ? AND b.id = ?";
            $details_stmt = $conn->prepare($details_sql);
            $details_stmt->bind_param("ii", $route_id, $bus_id);
            $details_stmt->execute();
            $details_result = $details_stmt->get_result();
            $details = $details_result->fetch_assoc();
            $details_stmt->close();

            // Log activity
            $action = ($id > 0) ? "Updated" : "Added";
            logActivity("Admin", $action . " schedule: " . $details['origin'] . " to " . $details['destination'] . " on " . date('Y-m-d', strtotime($departure_time)) . " with " . $details['bus_name']);

            // Commit transaction
            $conn->commit();

            // Set success message
            setFlashMessage("success", "Schedule " . strtolower($action) . " successfully.");

            // Reset form
            $id = $route_id = $bus_id = $departure_time = $arrival_time = $fare = $status = "";

            // Redirect to avoid form resubmission
            header("Location: schedules.php");
            exit();
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Set error message
            setFlashMessage("error", "Error " . strtolower(($id > 0) ? "updating" : "adding") . " schedule: " . $e->getMessage());
        }
    }
}

// Process delete request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    // Check if schedule has bookings
    $check_sql = "SELECT COUNT(*) as count FROM bookings WHERE schedule_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($check_row['count'] > 0) {
        setFlashMessage("error", "Cannot delete schedule because it has bookings. Consider cancelling it instead.");
    } else {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Get schedule details for logging
            $get_sql = "SELECT s.departure_time, r.origin, r.destination, b.name AS bus_name
                       FROM schedules s
                       JOIN routes r ON s.route_id = r.id
                       JOIN buses b ON s.bus_id = b.id
                       WHERE s.id = ?";
            $get_stmt = $conn->prepare($get_sql);
            $get_stmt->bind_param("i", $delete_id);
            $get_stmt->execute();
            $get_result = $get_stmt->get_result();
            $schedule = $get_result->fetch_assoc();
            $get_stmt->close();

            // Delete schedule
            $sql = "DELETE FROM schedules WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $stmt->close();

            // Log activity
            logActivity("Admin", "Deleted schedule: " . $schedule['origin'] . " to " . $schedule['destination'] . " on " . date('Y-m-d', strtotime($schedule['departure_time'])) . " with " . $schedule['bus_name']);

            // Commit transaction
            $conn->commit();

            // Set success message
            setFlashMessage("success", "Schedule deleted successfully.");
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Set error message
            setFlashMessage("error", "Error deleting schedule: " . $e->getMessage());
        }
    }

    // Redirect to avoid resubmission
    header("Location: schedules.php");
    exit();
}

// Process cancel request
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $cancel_id = intval($_GET['id']);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Get schedule details for logging
        $get_sql = "SELECT s.departure_time, r.origin, r.destination, b.name AS bus_name
                   FROM schedules s
                   JOIN routes r ON s.route_id = r.id
                   JOIN buses b ON s.bus_id = b.id
                   WHERE s.id = ?";
        $get_stmt = $conn->prepare($get_sql);
        $get_stmt->bind_param("i", $cancel_id);
        $get_stmt->execute();
        $get_result = $get_stmt->get_result();
        $schedule = $get_result->fetch_assoc();
        $get_stmt->close();

        // Update schedule status to cancelled
        $sql = "UPDATE schedules SET status = 'cancelled' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cancel_id);
        $stmt->execute();
        $stmt->close();

        // Log activity
        logActivity("Admin", "Cancelled schedule: " . $schedule['origin'] . " to " . $schedule['destination'] . " on " . date('Y-m-d', strtotime($schedule['departure_time'])) . " with " . $schedule['bus_name']);

        // Commit transaction
        $conn->commit();

        // Set success message
        setFlashMessage("success", "Schedule cancelled successfully.");
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();

        // Set error message
        setFlashMessage("error", "Error cancelling schedule: " . $e->getMessage());
    }

    // Redirect to avoid resubmission
    header("Location: schedules.php");
    exit();
}

// Process edit request
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);

    // Get schedule details
    $sql = "SELECT * FROM schedules WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $schedule = $result->fetch_assoc();
        $id = $schedule['id'];
        $route_id = $schedule['route_id'];
        $bus_id = $schedule['bus_id'];
        $departure_time = $schedule['departure_time'];
        $arrival_time = $schedule['arrival_time'];
        $fare = $schedule['fare'];
        $status = $schedule['status'];
    }

    $stmt->close();
}

// Get all routes for dropdown
$routes_sql = "SELECT id, origin, destination FROM routes ORDER BY origin, destination";
$routes_result = $conn->query($routes_sql);
$routes = [];

if ($routes_result && $routes_result->num_rows > 0) {
    while ($row = $routes_result->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Get all buses for dropdown
$buses_sql = "SELECT id, name, registration_number, capacity, type FROM buses WHERE status = 'active' ORDER BY name";
$buses_result = $conn->query($buses_sql);
$buses = [];

if ($buses_result && $buses_result->num_rows > 0) {
    while ($row = $buses_result->fetch_assoc()) {
        $buses[] = $row;
    }
}

// Get all schedules with route and bus details
$sql = "SELECT s.*,
        r.origin, r.destination,
        b.name AS bus_name, b.registration_number, b.capacity,
        (SELECT COUNT(*) FROM bookings WHERE schedule_id = s.id AND status != 'cancelled') as booked_seats
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        ORDER BY s.departure_time DESC";
$result = $conn->query($sql);
$schedules = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
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
            <li class="text-primary-700 font-medium">Manage Schedules</li>
        </ul>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-calendar-alt text-primary-600 mr-3"></i> Manage Schedules
        </h1>
        <a href="index.php" class="btn-secondary flex items-center transition-transform hover:-translate-x-1">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Display Flash Messages -->
    <?php displayFlashMessages(); ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-calendar-alt text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Schedules</p>
                <p class="text-xl font-bold"><?php echo count($schedules); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Active Schedules</p>
                <p class="text-xl font-bold">
                    <?php
                        $active_count = 0;
                        foreach ($schedules as $schedule) {
                            if ($schedule['status'] == 'scheduled' || $schedule['status'] == 'departed') $active_count++;
                        }
                        echo $active_count;
                    ?>
                </p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Bookings</p>
                <p class="text-xl font-bold">
                    <?php
                        $total_bookings = 0;
                        foreach ($schedules as $schedule) {
                            $total_bookings += $schedule['booked_seats'];
                        }
                        echo $total_bookings;
                    ?>
                </p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                <i class="fas fa-ban text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Cancelled</p>
                <p class="text-xl font-bold">
                    <?php
                        $cancelled_count = 0;
                        foreach ($schedules as $schedule) {
                            if ($schedule['status'] == 'cancelled') $cancelled_count++;
                        }
                        echo $cancelled_count;
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Schedule Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 border border-gray-100">
        <div class="flex items-center mb-4">
            <div class="p-2 rounded-full bg-primary-100 text-primary-600 mr-3">
                <i class="fas fa-<?php echo ($id) ? 'edit' : 'plus'; ?>"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800"><?php echo ($id) ? 'Edit' : 'Add New'; ?> Schedule</h2>
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
                    <label for="route_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-route text-gray-400 mr-1"></i> Route
                    </label>
                    <select id="route_id" name="route_id" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                        <option value="">Select Route</option>
                        <?php foreach ($routes as $route): ?>
                            <option value="<?php echo $route['id']; ?>" <?php echo ($route_id == $route['id']) ? 'selected' : ''; ?>>
                                <?php echo $route['origin']; ?> to <?php echo $route['destination']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="bus_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-bus text-gray-400 mr-1"></i> Bus
                    </label>
                    <select id="bus_id" name="bus_id" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                        <option value="">Select Bus</option>
                        <?php foreach ($buses as $bus): ?>
                            <option value="<?php echo $bus['id']; ?>" <?php echo ($bus_id == $bus['id']) ? 'selected' : ''; ?>>
                                <?php echo $bus['name']; ?> (<?php echo $bus['registration_number']; ?>, <?php echo $bus['capacity']; ?> seats)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="departure_time" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-plane-departure text-gray-400 mr-1"></i> Departure Time
                    </label>
                    <input type="datetime-local" id="departure_time" name="departure_time"
                           value="<?php echo $departure_time ? date('Y-m-d\TH:i', strtotime($departure_time)) : ''; ?>"
                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                </div>

                <div>
                    <label for="arrival_time" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-plane-arrival text-gray-400 mr-1"></i> Arrival Time
                    </label>
                    <input type="datetime-local" id="arrival_time" name="arrival_time"
                           value="<?php echo $arrival_time ? date('Y-m-d\TH:i', strtotime($arrival_time)) : ''; ?>"
                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                </div>

                <div>
                    <label for="fare" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave text-gray-400 mr-1"></i> Fare (KES)
                    </label>
                    <input type="number" id="fare" name="fare" value="<?php echo $fare; ?>" min="1" step="0.01"
                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                </div>
            </div>

            <div class="mb-6">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-toggle-on text-gray-400 mr-1"></i> Status
                </label>
                <select id="status" name="status" class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                    <option value="scheduled" <?php echo ($status == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="departed" <?php echo ($status == 'departed') ? 'selected' : ''; ?>>Departed</option>
                    <option value="arrived" <?php echo ($status == 'arrived') ? 'selected' : ''; ?>>Arrived</option>
                    <option value="cancelled" <?php echo ($status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>

            <div class="flex justify-end space-x-3">
                <?php if ($id): ?>
                    <a href="schedules.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                <?php endif; ?>
                <button type="submit" name="save_schedule" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-sm transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i> <?php echo ($id) ? 'Update' : 'Add'; ?> Schedule
                </button>
            </div>
        </form>
    </div>

    <!-- Schedules List -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-primary-100 text-primary-600 mr-3">
                    <i class="fas fa-list"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">All Schedules</h2>
            </div>

            <!-- Search and Filter Controls -->
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative">
                    <input type="text" id="schedule-search" placeholder="Search schedules..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <div class="flex gap-2">
                    <select id="filter-status" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="all">All Status</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="departed">Departed</option>
                        <option value="arrived">Arrived</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <select id="filter-date" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="all">All Dates</option>
                        <option value="today">Today</option>
                        <option value="tomorrow">Tomorrow</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($schedules)): ?>
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <div class="text-gray-400 text-5xl mb-3">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <p class="text-gray-500 mb-2">No schedules found in the system.</p>
                <p class="text-gray-500 text-sm">Add your first schedule using the form above.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border-b border-gray-200 searchable-table">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-route mr-2 text-gray-400"></i> Route
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-bus mr-2 text-gray-400"></i> Bus
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-2 text-gray-400"></i> Times
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave mr-2 text-gray-400"></i> Fare
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-users mr-2 text-gray-400"></i> Bookings
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-gray-400"></i> Status
                                </div>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-end">
                                    <i class="fas fa-cog mr-2 text-gray-400"></i> Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="schedule-table-body">
                        <?php foreach ($schedules as $schedule): ?>
                            <tr class="schedule-row hover:bg-gray-50"
                                data-route="<?php echo strtolower($schedule['origin'] . ' ' . $schedule['destination']); ?>"
                                data-bus="<?php echo strtolower($schedule['bus_name']); ?>"
                                data-status="<?php echo $schedule['status']; ?>"
                                data-date="<?php echo date('Y-m-d', strtotime($schedule['departure_time'])); ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                            <i class="fas fa-route"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $schedule['origin']; ?> to <?php echo $schedule['destination']; ?></div>
                                            <div class="text-xs text-gray-500">ID: <?php echo $schedule['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $schedule['bus_name']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $schedule['registration_number']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-plane-departure text-gray-400 mr-1"></i> <?php echo date('d M Y, H:i', strtotime($schedule['departure_time'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-900 mt-1">
                                        <i class="fas fa-plane-arrival text-gray-400 mr-1"></i> <?php echo date('d M Y, H:i', strtotime($schedule['arrival_time'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($schedule['fare']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $occupancy_percentage = ($schedule['booked_seats'] / $schedule['capacity']) * 100;
                                    $badge_class = $occupancy_percentage < 50 ? 'badge-warning' : ($occupancy_percentage < 80 ? 'badge-info' : 'badge-success');
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $badge_class; ?>">
                                        <?php echo $schedule['booked_seats']; ?>/<?php echo $schedule['capacity']; ?> seats
                                    </span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="<?php echo $occupancy_percentage < 50 ? 'bg-yellow-400' : ($occupancy_percentage < 80 ? 'bg-blue-500' : 'bg-green-500'); ?> h-2 rounded-full" style="width: <?php echo $occupancy_percentage; ?>%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php
                                        echo ($schedule['status'] == 'scheduled') ? 'bg-blue-100 text-blue-800' :
                                            (($schedule['status'] == 'departed') ? 'bg-yellow-100 text-yellow-800' :
                                            (($schedule['status'] == 'arrived') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'));
                                        ?>">
                                        <?php
                                        $icon = ($schedule['status'] == 'scheduled') ? 'calendar-check' :
                                            (($schedule['status'] == 'departed') ? 'bus' :
                                            (($schedule['status'] == 'arrived') ? 'flag-checkered' : 'ban'));
                                        ?>
                                        <i class="fas fa-<?php echo $icon; ?> mr-1"></i>
                                        <?php echo ucfirst($schedule['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="schedules.php?action=edit&id=<?php echo $schedule['id']; ?>" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded-md mr-1 inline-flex items-center">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <?php if ($schedule['booked_seats'] == 0): ?>
                                        <a href="schedules.php?action=delete&id=<?php echo $schedule['id']; ?>" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-md inline-flex items-center" onclick="return confirm('Are you sure you want to delete this schedule?');">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add JavaScript for search and filter functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('schedule-search');
            const statusFilter = document.getElementById('filter-status');
            const dateFilter = document.getElementById('filter-date');
            const scheduleRows = document.querySelectorAll('.schedule-row');

            function filterSchedules() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);

                const nextWeek = new Date(today);
                nextWeek.setDate(nextWeek.getDate() + 7);

                const nextMonth = new Date(today);
                nextMonth.setMonth(nextMonth.getMonth() + 1);

                scheduleRows.forEach(row => {
                    const routeText = row.getAttribute('data-route');
                    const busText = row.getAttribute('data-bus');
                    const status = row.getAttribute('data-status');
                    const dateStr = row.getAttribute('data-date');
                    const rowDate = new Date(dateStr);
                    rowDate.setHours(0, 0, 0, 0);

                    // Text search match
                    const textMatch = routeText.includes(searchTerm) ||
                                     busText.includes(searchTerm);

                    // Status filter match
                    const statusMatch = statusValue === 'all' || status === statusValue;

                    // Date filter match
                    let dateMatch = true;
                    if (dateValue === 'today') {
                        dateMatch = rowDate.getTime() === today.getTime();
                    } else if (dateValue === 'tomorrow') {
                        dateMatch = rowDate.getTime() === tomorrow.getTime();
                    } else if (dateValue === 'week') {
                        dateMatch = rowDate >= today && rowDate < nextWeek;
                    } else if (dateValue === 'month') {
                        dateMatch = rowDate >= today && rowDate < nextMonth;
                    }

                    // Show/hide row based on all filters
                    if (textMatch && statusMatch && dateMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Add event listeners
            if (searchInput) {
                searchInput.addEventListener('keyup', filterSchedules);
            }

            if (statusFilter) {
                statusFilter.addEventListener('change', filterSchedules);
            }

            if (dateFilter) {
                dateFilter.addEventListener('change', filterSchedules);
            }
        });
    </script>
</div>

<?php
// Get the buffered content
$admin_content = ob_get_clean();

// Include the admin template
require_once '../includes/templates/admin_template.php';
?>
