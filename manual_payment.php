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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    setFlashMessage("error", "Invalid request method.");
    header("Location: payment.php");
    exit();
}

// Get form data
$booking_reference = isset($_POST['booking_reference']) ? $_POST['booking_reference'] : '';
$card_number = isset($_POST['card_number']) ? $_POST['card_number'] : '';
$expiry = isset($_POST['expiry']) ? $_POST['expiry'] : '';
$cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';

// Validate form data
if (empty($booking_reference) || empty($card_number) || empty($expiry) || empty($cvv)) {
    setFlashMessage("error", "All fields are required.");
    header("Location: payment.php");
    exit();
}

// Basic card validation (this is just for demonstration - in a real app, use a payment processor)
$card_number = str_replace([' ', '-'], '', $card_number);
if (!preg_match('/^\d{16}$/', $card_number)) {
    setFlashMessage("error", "Invalid card number. Please enter a 16-digit card number.");
    header("Location: payment.php");
    exit();
}

if (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
    setFlashMessage("error", "Invalid expiry date. Please use MM/YY format.");
    header("Location: payment.php");
    exit();
}

if (!preg_match('/^\d{3,4}$/', $cvv)) {
    setFlashMessage("error", "Invalid CVV. Please enter a 3 or 4 digit CVV.");
    header("Location: payment.php");
    exit();
}

// Log the manual payment attempt
logActivity("Payment", "Manual payment attempt for booking reference: " . $booking_reference);

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$total_amount = $booking_data['total_amount'];
$passenger_details = $booking_data['passenger_details'];

// Generate a transaction reference
$transaction_reference = 'MANUAL-' . $booking_reference . '-' . time();

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

// Begin transaction
$conn->begin_transaction();

try {
    // Insert bookings
    foreach ($passenger_details as $passenger) {
        $sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, passenger_id_number, amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siissssd", $booking_reference, $_SESSION['user_id'], $schedule_id, $passenger['seat'], $passenger['name'], $passenger['phone'], $passenger['id_number'], $schedule['fare']);
        $stmt->execute();

        $booking_id = $stmt->insert_id;
        $stmt->close();

        // Insert payment record
        $sql = "INSERT INTO payments (booking_id, transaction_reference, amount, payment_method, status, payment_date)
                VALUES (?, ?, ?, 'card', 'successful', NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isd", $booking_id, $transaction_reference, $schedule['fare']);
        $stmt->execute();
        $stmt->close();
    }

    // Log activity
    logActivity("Booking", "Manual booking completed successfully with reference: " . $booking_reference);

    // Commit transaction
    $conn->commit();

    // Set success message
    setFlashMessage("success", "Payment successful! Your booking has been confirmed.");

    // Redirect to booking confirmation page
    header("Location: booking_confirmation.php?reference=" . $booking_reference);
    exit();
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();

    // Log error
    logActivity("Payment", "Error processing manual payment: " . $e->getMessage(), "error");

    // Set error message
    setFlashMessage("error", "Error processing payment: " . $e->getMessage());

    // Redirect to payment page
    header("Location: payment.php");
    exit();
}
?>
