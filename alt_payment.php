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
    function formatCurrency($amount) {
        return 'KES ' . number_format($amount, 2);
    }
}

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$total_amount = $booking_data['total_amount'];
$passenger_details = $booking_data['passenger_details'];
$booking_reference = $booking_data['booking_reference'];

// Get user details
$sql = "SELECT email FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
        }
    }
    $stmt->close();
}

// Set proper CSP headers directly in PHP to allow all required resources
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https://* http://*;");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alternative Payment Page - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background: #1a56db;
            color: white;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .btn-primary {
            background: #1a56db;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            display: inline-block;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #1e429f;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="flex justify-between items-center py-4 mb-6">
            <h1 class="text-2xl font-bold">Complete Your Payment</h1>
            <a href="index.php" class="text-blue-600">← Back to Home</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Payment Card -->
            <div class="md:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-xl font-bold">Secure Payment</h2>
                    </div>
                    <div class="card-body">
                        <p class="mb-4">You are paying <strong><?php echo formatCurrency($total_amount); ?></strong> for your booking.</p>

                        <div class="mb-4">
                            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Payment System Update</h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p>Our online payment system is currently being updated. Please contact our customer service to complete your booking.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <a href="contact.php" class="btn-primary w-full flex items-center justify-center">
                                <i class="fas fa-phone-alt mr-2"></i> Contact Customer Service
                            </a>
                        </div>

                        <div class="text-sm text-gray-600">
                            <p class="mb-2">Your booking details are secure. Our customer service team will assist you with payment options.</p>
                            <p>By completing this booking, you agree to our <a href="#" class="text-blue-600">Terms of Service</a>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="md:col-span-1">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-xl font-bold">Booking Summary</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="text-sm text-gray-600">Reference:</p>
                            <p class="font-medium"><?php echo $booking_reference; ?></p>
                        </div>

                        <div class="mb-3">
                            <p class="text-sm text-gray-600">Selected Seats:</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <?php foreach ($selected_seats as $seat): ?>
                                <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs"><?php echo $seat; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <div class="flex justify-between">
                                <span class="font-medium">Total:</span>
                                <span class="font-bold text-lg"><?php echo formatCurrency($total_amount); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- No payment integration scripts needed -->
</body>
</html>