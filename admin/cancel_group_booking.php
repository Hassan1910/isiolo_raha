<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Set message
    setFlashMessage("error", "You do not have permission to access this page.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Include database connection
$conn = require_once '../config/database.php';

// Include functions
require_once '../includes/functions.php';

// Check if reference is provided
if (!isset($_GET['reference'])) {
    // Set message
    setFlashMessage("error", "Invalid booking reference.");

    // Redirect to group bookings page
    header("Location: group_bookings.php");
    exit();
}

// Get reference from URL
$booking_reference = $_GET['reference'];

// Begin transaction
$conn->begin_transaction();

try {
    // Update group booking status
    $sql = "UPDATE group_bookings SET status = 'cancelled' WHERE booking_reference = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $booking_reference);
    $stmt->execute();
    $stmt->close();
    
    // Update individual bookings status
    $sql = "UPDATE bookings SET status = 'cancelled' WHERE booking_reference = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $booking_reference);
    $stmt->execute();
    $stmt->close();
    
    // Log activity
    logActivity("Admin", "Cancelled group booking: " . $booking_reference);
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    setFlashMessage("success", "Group booking cancelled successfully.");
    
    // Redirect to group bookings page
    header("Location: group_bookings.php");
    exit();
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    // Set error message
    setFlashMessage("error", "Error cancelling booking: " . $e->getMessage());
    
    // Redirect to group bookings page
    header("Location: group_bookings.php");
    exit();
}
?>
