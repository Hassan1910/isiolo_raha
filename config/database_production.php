<?php
/**
 * Production Database Configuration for InfinityFree
 * 
 * IMPORTANT: Update these credentials with your actual InfinityFree database details
 * You can find these in your InfinityFree Control Panel under "MySQL Databases"
 */

// InfinityFree database credentials - REPLACE WITH YOUR ACTUAL VALUES
define('DB_HOST', 'sql200.infinityfree.com');     // Your InfinityFree MySQL hostname
define('DB_USER', 'if0_12345678');               // Your InfinityFree database username  
define('DB_PASS', 'your_database_password');     // Your InfinityFree database password
define('DB_NAME', 'if0_12345678_isioloraha');    // Your InfinityFree database name

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Log error instead of displaying it in production
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please contact support.");
}

// Set charset to handle international characters properly
$conn->set_charset("utf8mb4");

// Set SQL mode for better compatibility
$conn->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");

// Return the connection
return $conn;
?>