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

// Include configuration
require_once 'config/config.php';

// Include database connection
$conn = require_once 'config/database.php';

// Define formatCurrency function directly in this file
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currency = 'KES') {
        return $currency . ' ' . number_format($amount, 2);
    }
}

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$total_amount = $booking_data['total_amount'];
$booking_reference = $booking_data['booking_reference'];

// Get user details
$sql = "SELECT email FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
        } else {
            setFlashMessage("error", "User information not found.");
            header("Location: index.php");
            exit();
        }
    }
    $stmt->close();
}

// Generate a contact URL
$contact_url = "contact.php";

// No payment gateway integration
$checkout_url = $contact_url;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Payment - <?php echo APP_NAME; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .header {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background: #28a745;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 0;
        }
        .back {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment System Update</h1>
        <p>Our online payment system is currently being updated.</p>
    </div>

    <div class="card">
        <h2>Booking Details</h2>
        <p><strong>Amount:</strong> <?php echo formatCurrency($total_amount); ?></p>
        <p><strong>Reference:</strong> <?php echo $booking_reference; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>

        <a href="<?php echo $checkout_url; ?>" class="button" style="background: #0066cc;">Contact Customer Service</a>
    </div>

    <div class="info">
        <p>Please contact our customer service team to complete your booking payment.</p>
        <p>Your booking details have been saved, but it is not confirmed until payment is complete.</p>
    </div>

    <a href="index.php" class="back">← Return to Home</a>
</body>
</html>