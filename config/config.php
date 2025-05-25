<?php
/**
 * Application Configuration File
 *
 * This file contains global configuration settings for the Isiolo Raha Bus Booking System
 */

// Application settings
define('APP_NAME', 'Isiolo Raha Bus Booking');

// Determine base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// More reliable way to get the application path
$scriptPath = $_SERVER['SCRIPT_FILENAME'];
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$relativePath = str_replace($documentRoot, '', dirname($scriptPath));
$relativePath = str_replace('\\', '/', $relativePath); // Fix for Windows paths

// Handle special cases
if (strpos($relativePath, '/config') !== false) {
    $relativePath = dirname($relativePath); // Go up one level if we're in the config directory
}

// Clean up the path
$relativePath = rtrim($relativePath, '/');
if ($relativePath === '') {
    $baseUrl = "$protocol://$host";
} else {
    // For subdirectory installations
    $baseUrl = "$protocol://$host$relativePath";
}

// Hardcoded fallback for XAMPP installations
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    // Check if we're using PHP's built-in server (which includes the port in the host)
    if (strpos($host, ':') !== false) {
        // Using PHP's built-in server (e.g., localhost:8000)
        $baseUrl = "$protocol://$host";
    } else {
        // Using regular web server like Apache (e.g., localhost)
        $baseUrl = "$protocol://$host/isioloraha";
    }
}

define('APP_URL', $baseUrl);
define('ADMIN_EMAIL', 'admin@isioloraha.com');

// Timezone settings
date_default_timezone_set('Africa/Nairobi');


// Error reporting (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security settings
define('HASH_COST', 10); // For password bcrypt hashing

// Paystack API settings
define('PAYSTACK_PUBLIC_KEY', 'pk_test_c7f8306f56b3fe44259a7e8d8a025c3f69e8102b');
define('PAYSTACK_SECRET_KEY', 'sk_test_36c2a669d1feb76b51dd0bff57eccdfebea18350'); // Replace with your actual secret key
define('PAYSTACK_CURRENCY', 'KES');
