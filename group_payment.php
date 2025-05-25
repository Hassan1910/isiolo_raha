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
if (!isset($_SESSION['group_booking_data']) || !isset($_SESSION['group_booking_data']['passenger_details'])) {
    // Set message
    setFlashMessage("error", "Please complete the group booking process.");

    // Redirect to group booking page
    header("Location: group_booking.php");
    exit();
}

// Get group booking data from session
$booking_data = $_SESSION['group_booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$passenger_details = $booking_data['passenger_details'];
$total_amount = $booking_data['total_amount'];
$booking_reference = $booking_data['booking_reference'];
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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate payment method
    if (empty($_POST["payment_method"])) {
        setFlashMessage("error", "Please select a payment method.");
    } else {
        $payment_method = $_POST["payment_method"];

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Insert group booking record
            $sql = "INSERT INTO group_bookings (booking_reference, user_id, schedule_id, group_name, contact_person, 
                    contact_phone, contact_email, total_passengers, total_amount, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siissssids", $booking_reference, $_SESSION['user_id'], $schedule_id, $group_name, 
                            $contact_person, $contact_phone, $contact_email, $total_passengers, $total_amount, $notes);
            $stmt->execute();

            $group_booking_id = $stmt->insert_id;
            $stmt->close();

            // Insert individual bookings
            foreach ($passenger_details as $passenger) {
                $sql = "INSERT INTO bookings (booking_reference, group_booking_id, user_id, schedule_id, seat_number, 
                        passenger_name, passenger_phone, passenger_id_number, amount, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siissssd", $booking_reference, $group_booking_id, $_SESSION['user_id'], $schedule_id, 
                                $passenger['seat'], $passenger['name'], $passenger['phone'], $passenger['id_number'], $schedule['fare']);
                $stmt->execute();

                $booking_id = $stmt->insert_id;
                $stmt->close();

                // Insert payment record for each booking
                $sql = "INSERT INTO payments (booking_id, transaction_reference, amount, payment_method, status, payment_date)
                        VALUES (?, ?, ?, ?, 'successful', NOW())";

                $stmt = $conn->prepare($sql);
                $transaction_reference = $payment_method . '-' . $booking_reference . '-' . $passenger['seat'];
                $stmt->bind_param("isds", $booking_id, $transaction_reference, $schedule['fare'], $payment_method);
                $stmt->execute();
                $stmt->close();
            }

            // Log activity
            logActivity("Group Booking", "Group booking created successfully with reference: " . $booking_reference);

            // Commit transaction
            $conn->commit();

            // Clear booking data from session
            unset($_SESSION['group_booking_data']);

            // Set success message
            setFlashMessage("success", "Your group booking has been confirmed!");

            // Redirect to booking confirmation page
            header("Location: group_booking_confirmation.php?reference=" . $booking_reference);
            exit();
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();

            // Set error message
            setFlashMessage("error", "Error processing booking: " . $e->getMessage());
        }
    }
}

// Set page title
$page_title = "Group Booking Payment";

// Include header
require_once 'includes/templates/header.php';
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6">Group Booking Payment</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Group and Trip Details -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Group Details</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Booking Reference</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($booking_reference); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Group Name</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_name); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Contact Person</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($contact_person); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Total Passengers</p>
                            <p class="font-semibold"><?php echo $total_passengers; ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Selected Seats</p>
                            <p class="font-semibold"><?php echo htmlspecialchars(implode(', ', $selected_seats)); ?></p>
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
                            <p class="text-sm text-gray-600">Bus</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($schedule['bus_name'] . ' (' . $schedule['bus_type'] . ')'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">Payment Method</h2>
                    <p class="mb-6 text-gray-600">Please select your preferred payment method to complete the booking.</p>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="space-y-4 mb-6">
                            <!-- Cash Payment Option -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-500 transition-colors">
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="payment_method" value="cash" class="mt-1 mr-3" checked>
                                    <div>
                                        <p class="font-medium">Pay at Station (Cash)</p>
                                        <p class="text-sm text-gray-600">Reserve now and pay in cash at the station before departure.</p>
                                    </div>
                                </label>
                            </div>

                            <!-- M-Pesa Payment Option -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-500 transition-colors">
                                <label class="flex items-start cursor-pointer">
                                    <input type="radio" name="payment_method" value="mpesa" class="mt-1 mr-3">
                                    <div>
                                        <p class="font-medium">M-Pesa</p>
                                        <p class="text-sm text-gray-600">Pay using M-Pesa mobile money.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn-primary">
                                Complete Booking
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
