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

// Include database connection
$conn = require_once 'config/database.php';

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$total_amount = $booking_data['total_amount'];
$booking_reference = $booking_data['booking_reference'];

// Get user email
$sql = "SELECT email FROM users WHERE id = ?";
$user_email = "";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $user_email = $user['email'];
        }
    }

    $stmt->close();
}

// Check if we have a valid email
if (empty($user_email)) {
    setFlashMessage("error", "Cannot process payment: Missing or invalid email address.");
    header("Location: payment.php");
    exit();
}

// Configuration
require_once 'config/config.php';

// Store booking reference in session for later use
$_SESSION['pending_booking_reference'] = $booking_reference;

// Log the redirect attempt
error_log("Redirecting to payment page with booking reference: " . $booking_reference);

// Redirect to payment page
header("Location: payment.php");
exit();