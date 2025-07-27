<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to book tickets.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Check if booking data exists in session
if (!isset($_SESSION['booking_data']) || !isset($_SESSION['booking_data']['passenger_details'])) {
    // Set message
    setFlashMessage("error", "Please complete the booking process.");

    // Redirect to home page
    header("Location: index.php");
    exit();
}

// Include functions
require_once 'includes/functions.php';

// Include database connection
$conn = require_once 'config/database.php';

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$total_amount = $booking_data['total_amount'];
$passenger_details = $booking_data['passenger_details'];
$booking_reference = $booking_data['booking_reference'];

// Double check that user_id is set and valid
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "User session is invalid. Please login again.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

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

// Check if booking already exists
$check_sql = "SELECT COUNT(*) FROM bookings WHERE booking_reference = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $booking_reference);
$check_stmt->execute();
$check_stmt->bind_result($existing_count);
$check_stmt->fetch();
$check_stmt->close();

if ($existing_count > 0) {
    // Booking already exists, redirect to confirmation
    setFlashMessage("info", "This booking has already been processed.");
    header("Location: booking_confirmation.php?reference=" . $booking_reference);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // Insert bookings
    foreach ($passenger_details as $passenger) {
        $sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, passenger_id_number, amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siissssd", $booking_reference, $_SESSION['user_id'], $schedule_id, $passenger['seat'], $passenger['name'], $passenger['phone'], $passenger['id_number'], $schedule['fare']);
        $stmt->execute();

        $booking_id = $stmt->insert_id;
        $stmt->close();

        // Insert payment record
        $sql = "INSERT INTO payments (booking_id, transaction_reference, amount, payment_method, status, payment_date)
                VALUES (?, ?, ?, 'cash', 'pending', NULL)";

        $stmt = $conn->prepare($sql);
        $reference = 'CASH-' . $booking_reference;
        $stmt->bind_param("isd", $booking_id, $reference, $schedule['fare']);
        $stmt->execute();
        $stmt->close();
    }

    // Log activity
    logActivity("Booking", "Booking reserved successfully with reference: " . $booking_reference);

    // Commit transaction
    $conn->commit();

    // Set success message
    setFlashMessage("success", "Your seats have been reserved! Please pay at the station before departure.");

    // Redirect to booking confirmation page
    header("Location: booking_confirmation.php?reference=" . $booking_reference);
    exit();
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();

    // Check if it's a foreign key constraint error
    if (strpos($e->getMessage(), "foreign key constraint fails") !== false &&
        strpos($e->getMessage(), "user_id") !== false) {

        // Set error message with a link to fix the issue
        setFlashMessage("error", "There was an issue with your user account. <a href='fix_booking.php?reference=" . $booking_reference . "' class='underline'>Click here to fix it</a>.", false);
    } else {
        // Set generic error message
        setFlashMessage("error", "Error processing booking: " . $e->getMessage());
    }

    // Redirect to payment page
    header("Location: payment.php");
    exit();
}
?>
