<?php
/**
 * Smart Database Configuration Loader
 * 
 * This file automatically detects the environment and loads the appropriate database configuration
 */

// Detect environment based on hostname
$isProduction = !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8000']);

if ($isProduction) {
    // Load production database configuration for InfinityFree
    if (file_exists(__DIR__ . '/database_production.php')) {
        return require_once __DIR__ . '/database_production.php';
    } else {
        die("Production database configuration file not found. Please create config/database_production.php");
    }
} else {
    // Local development database configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'isioloraha');
    
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist (local development only)
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    // Return the connection
    return $conn;
}
