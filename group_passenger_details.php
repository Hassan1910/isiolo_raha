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

// Check if group booking data exists in session
if (!isset($_SESSION['group_booking_data'])) {
    // Set message
    setFlashMessage("error", "Please complete the group booking form first.");

    // Redirect to group booking page
    header("Location: group_booking.php");
    exit();
}

// Get group booking data from session
$booking_data = $_SESSION['group_booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$total_amount = $booking_data['total_amount'];
$group_name = $booking_data['group_name'];
$contact_person = $booking_data['contact_person'];
$contact_phone = $booking_data['contact_phone'];
$contact_email = $booking_data['contact_email'] ?? '';
$total_passengers = $booking_data['total_passengers'];
$notes = $booking_data['notes'] ?? '';

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
            header("Location: group_booking.php");
            exit();
        }
    } else {
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: group_booking.php");
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

        if (empty($name)) {
            $valid = false;
            break;
        }

        $passenger_details[] = [
            'seat' => $seat,
            'name' => $name,
            'phone' => $phone ?: $contact_phone, // Use contact phone if not provided
            'id_number' => $id_number
        ];
    }

    if ($valid) {
        // Store passenger details in session
        $_SESSION['group_booking_data']['passenger_details'] = $passenger_details;

        // Generate booking reference
        $_SESSION['group_booking_data']['booking_reference'] = 'GRP' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        // Redirect to payment page
        header("Location: group_payment.php");
        exit();
    } else {
        setFlashMessage("error", "Please fill in all required passenger details.");
    }
}

// Set page title
$page_title = "Group Passenger Details";

// Include header
require_once 'includes/templates/header.php';
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6">Group Passenger Details</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Group and Trip Details -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Group Details</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Group Name</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_name); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Contact Person</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($contact_person); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Contact Phone</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($contact_phone); ?></p>
                        </div>
                        <?php if (!empty($contact_email)): ?>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Contact Email</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($contact_email); ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Total Passengers</p>
                            <p class="font-semibold"><?php echo $total_passengers; ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Selected Seats</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($booking_data['selected_seats']); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="font-bold text-primary-600 text-xl"><?php echo formatCurrency($total_amount); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">Trip Details</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Route</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($schedule['origin'] . ' to ' . $schedule['destination']); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Departure</p>
                            <p class="font-semibold"><?php echo date('d M Y, h:i A', strtotime($schedule['departure_time'])); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Arrival</p>
                            <p class="font-semibold"><?php echo date('d M Y, h:i A', strtotime($schedule['arrival_time'])); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Bus</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($schedule['bus_name'] . ' (' . $schedule['bus_type'] . ')'); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Fare per Seat</p>
                            <p class="font-semibold"><?php echo formatCurrency($schedule['fare']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passenger Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">Enter Passenger Details</h2>
                    <p class="mb-6 text-gray-600">Please provide details for each passenger in your group.</p>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" data-validate="true">
                        <?php foreach ($selected_seats as $index => $seat): ?>
                            <div class="mb-6 p-4 border border-gray-200 rounded-md">
                                <h3 class="text-lg font-bold mb-3">Passenger <?php echo $index + 1; ?> (Seat <?php echo $seat; ?>)</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <!-- Passenger Name -->
                                    <div>
                                        <label for="passenger_name_<?php echo $index; ?>" class="form-label">Full Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="passenger_name_<?php echo $index; ?>" id="passenger_name_<?php echo $index; ?>" class="form-input" 
                                            value="<?php echo ($index === 0) ? htmlspecialchars($contact_person) : ''; ?>" required>
                                    </div>

                                    <!-- Passenger Phone -->
                                    <div>
                                        <label for="passenger_phone_<?php echo $index; ?>" class="form-label">Phone Number</label>
                                        <input type="tel" name="passenger_phone_<?php echo $index; ?>" id="passenger_phone_<?php echo $index; ?>" class="form-input" 
                                            value="<?php echo ($index === 0) ? htmlspecialchars($contact_phone) : ''; ?>">
                                        <p class="text-xs text-gray-500 mt-1">If left blank, group contact phone will be used</p>
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
