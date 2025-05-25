<?php
// This is a test script to verify that admins can access bookings by reference

// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include functions
require_once 'includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo "You must be logged in as an admin to run this test.";
    exit();
}

// Include database connection
$conn = require_once 'config/database.php';

// Get the most recent booking
$sql = "SELECT b.id, b.booking_reference, b.user_id, b.passenger_name, 
        CONCAT(u.first_name, ' ', u.last_name) AS user_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    
    echo "<h1>Admin Booking Access Test</h1>";
    echo "<p>Testing access to the most recent booking:</p>";
    echo "<ul>";
    echo "<li>Booking ID: " . $booking['id'] . "</li>";
    echo "<li>Reference: " . $booking['booking_reference'] . "</li>";
    echo "<li>Passenger: " . $booking['passenger_name'] . "</li>";
    echo "<li>Created by: " . ($booking['user_name'] ?? 'Unknown') . " (ID: " . $booking['user_id'] . ")</li>";
    echo "</ul>";
    
    echo "<p>Access links:</p>";
    echo "<ul>";
    echo "<li><a href='admin/booking_details.php?id=" . $booking['id'] . "' target='_blank'>View by ID</a></li>";
    echo "<li><a href='admin/booking_details.php?reference=" . $booking['booking_reference'] . "' target='_blank'>View by Reference</a></li>";
    echo "<li><a href='print_ticket.php?reference=" . $booking['booking_reference'] . "' target='_blank'>Print Ticket</a></li>";
    echo "</ul>";
    
    // Check if the booking was created by the current admin
    if ($booking['user_id'] == $_SESSION['user_id']) {
        echo "<p style='color: green;'>This booking was created by you (the current admin).</p>";
    } else {
        echo "<p style='color: orange;'>This booking was created by another user (ID: " . $booking['user_id'] . ").</p>";
    }
} else {
    echo "<p>No bookings found in the system.</p>";
}

// Close connection
$conn->close();
?>
