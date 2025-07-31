<?php
/**
 * Application Configuration File
 *
 * This file contains global configuration settings for the Isiolo Raha Bus Booking System
 */

// Application settings
define('APP_NAME', 'Isiolo Raha Bus Booking');

// Detect environment
$isProduction = !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8000']);

// Determine base URL based on environment
if ($isProduction) {
    // Production environment (InfinityFree or custom domain)
    $protocol = 'https'; // Force HTTPS in production
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = "$protocol://$host";
} else {
    // Local development environment
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
}

define('APP_URL', $baseUrl);
define('IS_PRODUCTION', $isProduction);
define('ADMIN_EMAIL', 'admin@isioloraha.com');

// Timezone settings
date_default_timezone_set('Africa/Nairobi');


// Error reporting (environment-aware)
if (IS_PRODUCTION) {
    // Production: Hide errors from users, log them instead
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    // Development: Show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Security settings
define('HASH_COST', 10); // For password bcrypt hashing

// Paystack configuration is now handled in paystack_config.php
// This ensures environment-aware configuration loading
