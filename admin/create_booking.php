<?php
// Include session configuration
require_once '../config/session_config.php';

// Debug function
function debug_to_file($data, $label = '') {
    $debug_file = '../admin_booking_debug.log';
    $output = date('[Y-m-d H:i:s] ') . ($label ? "[$label] " : '') . (is_array($data) || is_object($data) ? json_encode($data) : $data) . "\n";
    file_put_contents($debug_file, $output, FILE_APPEND);
}

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

// Log session data for debugging
debug_to_file($_SESSION, 'SESSION_DATA_START');

// Include functions
require_once '../includes/functions.php';

// Set page title
$page_title = "Create Booking";

// Include database connection
$conn = require_once '../config/database.php';

// Ensure admin user exists
$sql = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows == 0) {
    // Create default admin user if none exists
    $adminPassword = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
    $sql = "INSERT INTO users (first_name, last_name, email, phone, password, role)
            VALUES ('Admin', 'User', 'admin@isioloraha.com', '0700000000', ?, 'admin')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $adminPassword);
    $stmt->execute();
    $stmt->close();

    logActivity("System", "Created default admin user", "info");
}

// Initialize variables
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : "";
$selected_seats = $passenger_name = $passenger_phone = $passenger_id_number = $payment_method = "";
$booking_created = false;
$booking_reference = "";
$error = "";
$schedules = [];
$booked_seats = [];
$schedule = null;

// Start output buffering to capture content for the template
ob_start();

// Get all active schedules for the dropdown
$sql = "SELECT s.id, s.departure_time, s.fare,
        r.origin, r.destination,
        b.name AS bus_name, b.registration_number
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.status = 'scheduled' AND s.departure_time > NOW()
        ORDER BY s.departure_time ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log form submission
    debug_to_file($_POST, 'FORM_DATA');
    debug_to_file($_SESSION, 'SESSION_DATA');
    // Validate schedule_id
    if (empty($_POST["schedule_id"])) {
        $error = "Please select a schedule.";
    } else {
        $schedule_id = intval($_POST["schedule_id"]);
    }

    // Validate selected seats
    if (empty($_POST["selected_seats"])) {
        $error = "Please select at least one seat.";
    } else {
        $selected_seats = $_POST["selected_seats"];
    }

    // Validate passenger details
    if (empty($_POST["passenger_name"])) {
        $error = "Please enter passenger name.";
    } else {
        $passenger_name = $_POST["passenger_name"];
    }

    if (empty($_POST["passenger_phone"])) {
        $error = "Please enter passenger phone number.";
    } else {
        $passenger_phone = $_POST["passenger_phone"];
    }

    $passenger_id_number = $_POST["passenger_id_number"] ?? "";

    // Validate payment method
    if (empty($_POST["payment_method"])) {
        $error = "Please select a payment method.";
    } else {
        $payment_method = $_POST["payment_method"];
    }

    // Validate passenger ID number (optional)
    if (isset($_POST["passenger_id_number"])) {
        $passenger_id_number = $_POST["passenger_id_number"];
    }

    // If no errors, process the booking
    if (empty($error)) {
        // Get schedule details
        $sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
                r.origin, r.destination, r.distance,
                b.name AS bus_name, b.type AS bus_type, b.capacity
                FROM schedules s
                JOIN routes r ON s.route_id = r.id
                JOIN buses b ON s.bus_id = b.id
                WHERE s.id = ? AND s.status = 'scheduled'";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $schedule_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedule = $result->fetch_assoc();
            $stmt->close();
        }

        if (!$schedule) {
            $error = "Invalid schedule selected.";
        } else {
            // Generate booking reference
            $booking_reference = 'IR' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

            // Double-check that user_id is set
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                $error = "User ID is not set. Please log in again.";
                debug_to_file("User ID is not set in session", 'ERROR');
                logActivity("Admin", "Booking failed - User ID not set", "error");
                return;
            }

            // Log the user ID for debugging
            debug_to_file("User ID from session: " . $_SESSION['user_id'], 'USER');

            // Begin transaction
            debug_to_file("Starting database transaction", 'DB');
            $conn->begin_transaction();
            debug_to_file("Transaction started", 'DB');

            try {
                // Log the data we're about to insert
                logActivity("Admin", "Attempting to create booking with data: " .
                    "Reference: " . $booking_reference .
                    ", User ID: " . $_SESSION['user_id'] .
                    ", Schedule ID: " . $schedule_id .
                    ", Seat: " . $selected_seats .
                    ", Passenger: " . $passenger_name);

                // Insert booking
                $sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, passenger_id_number, amount, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // Get the user ID from session or use admin ID (1) as fallback
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
                debug_to_file("Using user_id: " . $user_id . " for booking insertion", 'DB');

                $stmt->bind_param("siissssd", $booking_reference, $user_id, $schedule_id, $selected_seats, $passenger_name, $passenger_phone, $passenger_id_number, $schedule['fare']);

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $booking_id = $stmt->insert_id;
                $stmt->close();

                // Insert payment record
                $sql = "INSERT INTO payments (booking_id, transaction_reference, amount, payment_method, status, payment_date)
                        VALUES (?, ?, ?, ?, 'successful', NOW())";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare payment record failed: " . $conn->error);
                }

                $transaction_reference = ($payment_method == 'cash') ? 'CASH-' . $booking_reference : 'PAYSTACK-' . $booking_reference;
                $stmt->bind_param("isds", $booking_id, $transaction_reference, $schedule['fare'], $payment_method);

                if (!$stmt->execute()) {
                    throw new Exception("Execute payment record failed: " . $stmt->error);
                }

                $stmt->close();

                // Log successful insertion
                logActivity("Admin", "Successfully inserted booking and payment records. Booking ID: " . $booking_id);

                // Log activity
                logActivity("Admin", "Created booking for " . $passenger_name . " with reference: " . $booking_reference);

                // Commit transaction
                debug_to_file("About to commit transaction", 'DB');
                $conn->commit();
                debug_to_file("Transaction committed successfully", 'DB');

                // Set success flag
                $booking_created = true;
                debug_to_file("Booking created flag set to true", 'DB');

                // Log the booking ID for debugging
                logActivity("Admin", "Created booking ID: " . $booking_id . " with reference: " . $booking_reference);

                // Reset form fields
                $schedule_id = $selected_seats = $passenger_name = $passenger_phone = $passenger_id_number = $payment_method = "";
            } catch (Exception $e) {
                // Rollback transaction
                debug_to_file("Error occurred, rolling back transaction: " . $e->getMessage(), 'DB_ERROR');
                $conn->rollback();
                debug_to_file("Transaction rolled back", 'DB_ERROR');

                // Log the error
                logActivity("Admin", "Error creating booking: " . $e->getMessage(), "error");
                debug_to_file($e->getTraceAsString(), 'DB_ERROR_TRACE');

                // Check if it's a foreign key constraint error
                if (strpos($e->getMessage(), "foreign key constraint fails") !== false) {
                    $error = "Database constraint error. Please check that all required data is valid.";
                    debug_to_file("Foreign key constraint error detected", 'DB_ERROR');
                } else {
                    $error = "Error creating booking: " . $e->getMessage();
                }

                // Set booking_created flag to false explicitly
                $booking_created = false;
                debug_to_file("Booking created flag set to false", 'DB_ERROR');
            }
        }
    }
}

// If schedule_id is set, get booked seats
if (!empty($schedule_id) && !$booking_created) {
    // Get schedule details
    $sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
            r.origin, r.destination, r.distance,
            b.name AS bus_name, b.type AS bus_type, b.capacity
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            JOIN buses b ON s.bus_id = b.id
            WHERE s.id = ? AND s.status = 'scheduled'";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_assoc();
        $stmt->close();
    }

    // Get booked seats
    $sql = "SELECT seat_number FROM bookings WHERE schedule_id = ? AND status != 'cancelled'";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $booked_seats[] = $row['seat_number'];
        }

        $stmt->close();
    }
}
?>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <!-- Admin Navigation Breadcrumb -->
    <div class="text-sm breadcrumbs mb-4">
        <ul class="flex items-center space-x-2 text-gray-600">
            <li><a href="index.php" class="hover:text-primary-600 transition-colors"><i class="fas fa-home mr-1"></i> Dashboard</a></li>
            <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
            <li class="text-primary-700 font-medium">Create Booking</li>
        </ul>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-ticket-alt text-primary-600 mr-3"></i> Create Booking
        </h1>
        <a href="index.php" class="btn-secondary flex items-center transition-transform hover:-translate-x-1">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Display Flash Messages -->
    <?php displayFlashMessages(); ?>

    <!-- Display Form Errors -->
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                </div>
                <div>
                    <p><?php echo $error; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-calendar-alt text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Available Schedules</p>
                <p class="text-xl font-bold"><?php echo count($schedules); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-ticket-alt text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Office Bookings Today</p>
                <p class="text-xl font-bold">
                    <?php
                        // Get count of bookings made by admin today
                        $today = date('Y-m-d');
                        $sql = "SELECT COUNT(*) as count FROM bookings b
                                JOIN payments p ON b.id = p.booking_id
                                WHERE b.user_id = ? AND DATE(b.created_at) = ?
                                AND p.payment_method = 'cash'";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $stmt->bind_param("is", $_SESSION['user_id'], $today);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            echo $row['count'] ?? 0;
                            $stmt->close();
                        } else {
                            echo "0";
                        }
                    ?>
                </p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Cash Revenue Today</p>
                <p class="text-xl font-bold">
                    <?php
                        // Get sum of cash payments made today
                        $sql = "SELECT SUM(b.amount) as total FROM bookings b
                                JOIN payments p ON b.id = p.booking_id
                                WHERE b.user_id = ? AND DATE(b.created_at) = ?
                                AND p.payment_method = 'cash'";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) {
                            $stmt->bind_param("is", $_SESSION['user_id'], $today);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            echo formatCurrency($row['total'] ?? 0);
                            $stmt->close();
                        } else {
                            echo formatCurrency(0);
                        }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <?php
    // Debug the booking_created flag
    debug_to_file("Before success message, booking_created = " . ($booking_created ? 'true' : 'false'), 'RENDER');

    if ($booking_created):
        // Debug the booking reference
        debug_to_file("Showing success message with booking reference: " . $booking_reference, 'RENDER');
    ?>
        <!-- Booking Success Message -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 p-4 rounded-full text-green-600">
                    <i class="fas fa-check-circle text-3xl"></i>
                </div>
                <div class="ml-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Booking Created Successfully!</h2>
                    <p class="text-gray-600 mb-4">The booking has been created with reference: <strong class="text-primary-600 font-semibold"><?php echo $booking_reference; ?></strong></p>
                    <div class="flex flex-wrap gap-3">
                        <a href="booking_details.php?reference=<?php echo $booking_reference; ?>" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-sm transition-colors flex items-center">
                            <i class="fas fa-eye mr-2"></i> View Booking
                        </a>
                        <a href="../print_ticket.php?reference=<?php echo $booking_reference; ?>" target="_blank" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-sm transition-colors flex items-center">
                            <i class="fas fa-print mr-2"></i> Print Ticket
                        </a>
                        <a href="create_booking.php" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Create Another Booking
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Booking Form -->
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
            <div class="flex items-center mb-4">
                <div class="p-2 rounded-full bg-primary-100 text-primary-600 mr-3">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Create New Booking</h2>
            </div>

            <p class="mb-6 text-gray-600 border-l-4 border-primary-200 pl-3 py-2 bg-primary-50 rounded-r-md">
                Use this form to create a booking for a customer who visits the office. Select a schedule, choose a seat, enter passenger details, and select a payment method.
            </p>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $error; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="booking-form">
                <!-- Schedule Selection -->
                <div class="mb-6">
                    <label for="schedule_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Select Schedule <span class="text-red-500">*</span>
                    </label>
                    <select name="schedule_id" id="schedule_id"
                            class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                            required>
                        <option value="">-- Select a Schedule --</option>
                        <?php foreach ($schedules as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($schedule_id == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo $s['origin']; ?> to <?php echo $s['destination']; ?> -
                                <?php echo date('d M Y, H:i', strtotime($s['departure_time'])); ?> -
                                <?php echo $s['bus_name']; ?> (<?php echo $s['registration_number']; ?>) -
                                <?php echo formatCurrency($s['fare']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($schedule): ?>
                    <!-- Seat Selection -->
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex items-center mb-3">
                            <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-2">
                                <i class="fas fa-chair"></i>
                            </div>
                            <label class="block text-sm font-medium text-gray-700">
                                Select Seat <span class="text-red-500">*</span>
                            </label>
                        </div>

                        <div class="bg-white p-4 rounded-lg border border-gray-200 mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-bus text-primary-600 mr-1"></i>
                                    <?php echo $schedule['bus_name']; ?> (<?php echo $schedule['registration_number']; ?>)
                                </div>
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-money-bill-wave text-green-600 mr-1"></i>
                                    <?php echo formatCurrency($schedule['fare']); ?> per seat
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-5 gap-3 max-w-md mx-auto">
                                <?php
                                $capacity = $schedule['capacity'];
                                for ($i = 1; $i <= $capacity; $i++) {
                                    $seatNumber = $i;
                                    $isBooked = in_array($seatNumber, $booked_seats);
                                    $isSelected = ($selected_seats == $seatNumber);

                                    // Determine seat class
                                    $seatClass = 'seat ';
                                    if ($isBooked) {
                                        $seatClass .= 'booked';
                                    } elseif ($isSelected) {
                                        $seatClass .= 'selected';
                                    } else {
                                        $seatClass .= 'available';
                                    }

                                    // Add aisle for better visualization (adjust as needed)
                                    $isAisle = ($i % 5 == 3);
                                    if ($isAisle) {
                                        echo '<div class="seat aisle"></div>';
                                    }

                                    echo '<div class="' . $seatClass . '" data-seat="' . $seatNumber . '">' . $seatNumber . '</div>';
                                }
                                ?>
                            </div>
                        </div>

                        <input type="hidden" name="selected_seats" id="selected_seats" value="<?php echo $selected_seats; ?>">
                        <div class="flex justify-center gap-4 mt-3">
                            <span class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">
                                <span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span> Selected
                            </span>
                            <span class="inline-flex items-center bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs">
                                <span class="inline-block w-3 h-3 bg-gray-300 rounded-full mr-1"></span> Booked
                            </span>
                            <span class="inline-flex items-center bg-white text-gray-800 px-3 py-1 rounded-full text-xs border border-gray-200">
                                <span class="inline-block w-3 h-3 bg-white border border-gray-300 rounded-full mr-1"></span> Available
                            </span>
                        </div>
                    </div>

                    <!-- Passenger Details -->
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex items-center mb-4">
                            <div class="p-2 rounded-full bg-green-100 text-green-600 mr-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800">Passenger Details</h3>
                        </div>

                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <!-- Passenger Name -->
                                <div>
                                    <label for="passenger_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user-tag text-gray-400 mr-1"></i> Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="passenger_name" id="passenger_name"
                                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                           value="<?php echo $passenger_name; ?>" required>
                                </div>

                                <!-- Passenger Phone -->
                                <div>
                                    <label for="passenger_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone text-gray-400 mr-1"></i> Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="passenger_phone" id="passenger_phone"
                                           class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                           value="<?php echo $passenger_phone; ?>" required>
                                </div>
                            </div>

                            <!-- ID Number -->
                            <div>
                                <label for="passenger_id_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-id-card text-gray-400 mr-1"></i> ID Number
                                </label>
                                <input type="text" name="passenger_id_number" id="passenger_id_number"
                                       class="form-input w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                       value="<?php echo $passenger_id_number; ?>">
                                <p class="text-xs text-gray-500 mt-1 ml-1">National ID, Passport, or other identification</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex items-center mb-4">
                            <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-2">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800">Payment Method</h3>
                        </div>

                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Cash Option -->
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-green-500 hover:bg-green-50 transition-colors cursor-pointer border-green-500 bg-green-50">
                                    <div class="flex items-center">
                                        <input type="radio" name="payment_method" id="payment_cash" value="cash"
                                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300"
                                               checked required>
                                        <label for="payment_cash" class="flex items-center cursor-pointer ml-3">
                                            <div class="p-2 rounded-full bg-green-100 text-green-600 mr-2">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div>
                                                <span class="font-medium block">Cash Payment</span>
                                                <span class="text-sm text-gray-600">Customer pays with cash at the office</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Note about payment -->
                                <div class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg border border-blue-100">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <i class="fas fa-info-circle text-blue-500"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p>Cash is the only payment method available for office bookings. Collect payment from the customer before confirming the booking.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end mt-8">
                        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-sm transition-colors flex items-center">
                            <i class="fas fa-ticket-alt mr-2"></i> Create Booking
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle schedule selection
    const scheduleSelect = document.getElementById('schedule_id');
    if (scheduleSelect) {
        scheduleSelect.addEventListener('change', function() {
            if (this.value) {
                // Show loading indicator
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50';
                loadingDiv.id = 'loading-overlay';
                loadingDiv.innerHTML = `
                    <div class="bg-white p-5 rounded-lg shadow-lg flex items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mr-3"></div>
                        <p class="text-gray-700">Loading schedule details...</p>
                    </div>
                `;
                document.body.appendChild(loadingDiv);

                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = 'create_booking.php?schedule_id=' + this.value;
                }, 300);
            }
        });
    }

    // Handle seat selection
    const seats = document.querySelectorAll('.seat.available');
    const selectedSeatsInput = document.getElementById('selected_seats');

    seats.forEach(seat => {
        seat.addEventListener('click', function() {
            const seatNumber = this.getAttribute('data-seat');

            // Deselect all seats first
            document.querySelectorAll('.seat.selected').forEach(s => {
                s.classList.remove('selected');
                s.classList.add('available');
            });

            // Select this seat
            this.classList.remove('available');
            this.classList.add('selected');

            // Update hidden input
            selectedSeatsInput.value = seatNumber;

            // Add a subtle animation
            this.animate([
                { transform: 'scale(1)' },
                { transform: 'scale(1.1)' },
                { transform: 'scale(1)' }
            ], {
                duration: 300,
                easing: 'ease-in-out'
            });
        });
    });

    // Cash payment is the only option, so no need for complex selection logic
    const cashOption = document.getElementById('payment_cash');
    if (cashOption) {
        cashOption.checked = true;
    }
});
</script>

<?php
// Get the buffered content
$admin_content = ob_get_clean();

// Include the admin template
require_once '../includes/templates/admin_template.php';
?>
