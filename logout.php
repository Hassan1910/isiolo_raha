<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include functions
require_once 'includes/functions.php';

// Log activity if user is logged in
if (isset($_SESSION['user_id'])) {
    // Include database connection
    $conn = require_once 'config/database.php';
    
    // Log activity
    logActivity("Logout", "User logged out successfully");
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Set success message
session_start();
setFlashMessage("success", "You have been logged out successfully.");

// Redirect to login page
header("Location: login.php");
exit;
?>
