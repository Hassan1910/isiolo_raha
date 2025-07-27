<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include database connection
$conn = require_once 'config/database.php';

// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to book tickets.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Check if booking data exists in session
if (!isset($_SESSION['booking_data'])) {
    // Set message
    setFlashMessage("error", "Please select seats first.");

    // Redirect to home page
    header("Location: index.php");
    exit();
}

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$total_amount = $booking_data['total_amount'];
$journey_type = isset($booking_data['journey_type']) ? $booking_data['journey_type'] : 'outbound';

// Get schedule details
$sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
        r.origin, r.destination, r.distance,
        b.name AS bus_name, b.type AS bus_type
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $schedule_id);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $schedule = $result->fetch_assoc();
        } else {
            setFlashMessage("error", "Schedule not found.");
            header("Location: index.php");
            exit();
        }
    } else {
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: index.php");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Initialize user variable with default values
$user = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => ''
];

// Get user details
$sql = "SELECT first_name, last_name, email, phone FROM users WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $_SESSION['user_id']);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
        } else {
            // Log that user was not found
            error_log("User with ID {$_SESSION['user_id']} not found in database.");
        }
    } else {
        // Log database error
        error_log("Database error when fetching user details: " . $stmt->error);
    }

    // Close statement
    $stmt->close();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize passenger details array
    $passenger_details = [];
    $valid = true;

    // Validate passenger details
    foreach ($selected_seats as $index => $seat) {
        $name = trim($_POST["passenger_name_" . $index]);
        $phone = trim($_POST["passenger_phone_" . $index]);
        $id_number = trim($_POST["passenger_id_number_" . $index]);

        if (empty($name) || empty($phone)) {
            $valid = false;
            break;
        }

        $passenger_details[] = [
            'seat' => $seat,
            'name' => $name,
            'phone' => $phone,
            'id_number' => $id_number
        ];
    }

    if ($valid) {
        // Store passenger details in session
        $_SESSION['booking_data']['passenger_details'] = $passenger_details;

        // Generate unique booking reference
        $_SESSION['booking_data']['booking_reference'] = generateUniqueBookingReference($conn);

        // Redirect to payment page
        header("Location: payment.php");
        exit();
    } else {
        setFlashMessage("error", "Please fill in all required passenger details.");
    }
}

// Set page title
$page_title = "Passenger Details";

// Include header
require_once 'includes/templates/header.php';

// Include booking progress component
require_once 'includes/components/booking_progress.php';
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6">Passenger Details</h1>

        <!-- Booking Progress -->
        <?php renderBookingProgress('details'); ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Trip Details -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">Trip Details</h2>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Route</p>
                            <p class="font-bold"><?php echo $schedule['origin']; ?> to <?php echo $schedule['destination']; ?></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Bus</p>
                            <p class="font-bold"><?php echo $schedule['bus_name']; ?> (<?php echo ucfirst($schedule['bus_type']); ?>)</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Departure</p>
                            <p class="font-bold"><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?>, <?php echo date('d M, Y', strtotime($schedule['departure_time'])); ?></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Selected Seats</p>
                            <p class="font-bold"><?php echo implode(', ', $selected_seats); ?></p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="font-bold text-primary-600 text-xl"><?php echo formatCurrency($total_amount); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passenger Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">Enter Passenger Details</h2>
                    <p class="mb-6 text-gray-600">Please provide details for each passenger.</p>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" data-validate="true">
                        <?php foreach ($selected_seats as $index => $seat): ?>
                            <div class="mb-6 p-4 border border-gray-200 rounded-md">
                                <h3 class="text-lg font-bold mb-3">Passenger <?php echo $index + 1; ?> (Seat <?php echo $seat; ?>)</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <!-- Passenger Name -->
                                    <div>
                                        <label for="passenger_name_<?php echo $index; ?>" class="form-label">Full Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="passenger_name_<?php echo $index; ?>" id="passenger_name_<?php echo $index; ?>" class="form-input" value="<?php echo ($index === 0 && isset($user['first_name']) && isset($user['last_name'])) ? htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) : ''; ?>" required>
                                    </div>

                                    <!-- Passenger Phone -->
                                    <div>
                                        <label for="passenger_phone_<?php echo $index; ?>" class="form-label">Phone Number <span class="text-red-500">*</span></label>
                                        <input type="tel" name="passenger_phone_<?php echo $index; ?>" id="passenger_phone_<?php echo $index; ?>" class="form-input" value="<?php echo ($index === 0 && isset($user['phone'])) ? htmlspecialchars($user['phone']) : ''; ?>" required>
                                    </div>
                                </div>

                                <!-- ID Number -->
                                <div>
                                    <label for="passenger_id_number_<?php echo $index; ?>" class="form-label">ID Number</label>
                                    <input type="text" name="passenger_id_number_<?php echo $index; ?>" id="passenger_id_number_<?php echo $index; ?>" class="form-input">
                                    <p class="text-xs text-gray-500 mt-1">National ID, Passport, or other identification</p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="text-center">
                            <button type="submit" class="btn-primary">
                                Continue to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
