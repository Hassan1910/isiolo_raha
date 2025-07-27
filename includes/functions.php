<?php
/**
 * Utility Functions
 *
 * This file contains common utility functions used throughout the application
 */

/**
 * Sanitize user input
 *
 * @param string $data The input to sanitize
 * @return string The sanitized input
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect to a specific URL
 *
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 *
 * @return boolean True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 *
 * @return boolean True if user is an admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Generate a random string
 *
 * @param int $length The length of the string
 * @return string The random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Format currency
 *
 * @param float $amount The amount to format
 * @param string $currency The currency code (default: 'KES')
 * @return string The formatted amount
 */
function formatCurrency($amount, $currency = 'KES') {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Format date
 *
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
function formatDate($date, $format = 'd M, Y') {
    return date($format, strtotime($date));
}

/**
 * Format duration in minutes to hours and minutes
 *
 * @param int $minutes The duration in minutes
 * @return string The formatted duration
 */
function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;

    if ($hours > 0) {
        return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
    } else {
        return $mins . 'm';
    }
}

/**
 * Generate a unique booking reference
 *
 * @param mysqli $conn The database connection
 * @param int $maxAttempts Maximum number of attempts to generate a unique reference
 * @return string A unique booking reference
 * @throws Exception If unable to generate a unique reference after max attempts
 */
function generateUniqueBookingReference($conn, $maxAttempts = 10) {
    $attempts = 0;
    
    while ($attempts < $maxAttempts) {
        // Generate a more robust booking reference using multiple entropy sources
        $timestamp = time();
        $microseconds = microtime(true);
        $randomBytes = bin2hex(random_bytes(4)); // 8 character hex string
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        
        // Create a unique string combining multiple sources
        $uniqueString = $timestamp . $microseconds . $randomBytes . $userId . mt_rand(1000, 9999);
        
        // Generate the booking reference
        $bookingReference = 'IRB' . strtoupper(substr(hash('sha256', $uniqueString), 0, 8));
        
        // Check if this reference already exists in the database
        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE booking_reference = ?");
        $stmt->bind_param("s", $bookingReference);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        // If the reference is unique, return it
        if ($count == 0) {
            return $bookingReference;
        }
        
        $attempts++;
        
        // Add a small delay to prevent rapid-fire attempts
        usleep(1000); // 1ms delay
    }
    
    // If we couldn't generate a unique reference after max attempts, throw an exception
    throw new Exception("Unable to generate unique booking reference after $maxAttempts attempts");
}

/**
 * Display flash message
 *
 * @param string $type The type of message (success, error, info)
 * @param string $message The message to display
 * @param bool $escape Whether to escape HTML in the message (default: true)
 * @return void
 */
function setFlashMessage($type, $message, $escape = true) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $escape ? htmlspecialchars($message) : $message,
        'html' => !$escape
    ];
}

/**
 * Get flash message
 *
 * @return array|null The flash message or null if none exists
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Log activity
 *
 * @param string $action The action performed
 * @param string $description The description of the action
 * @param string|int $level_or_userId The log level ('error', 'warning', 'info') or user ID
 * @param int $userId The ID of the user who performed the action (only used if $level_or_userId is a log level)
 * @return void
 */
function logActivity($action, $description, $level_or_userId = null, $userId = null) {
    global $conn;

    // Check if activity_logs table exists
    if (!$conn) {
        error_log("Database connection not available for logging: $action - $description");
        return;
    }

    // Initialize variables
    $level = 'info'; // Default level

    // Check if the third parameter is a log level or user ID
    if (is_string($level_or_userId) && in_array($level_or_userId, ['error', 'warning', 'info'])) {
        $level = $level_or_userId;
    } else {
        // If it's not a log level, it must be a user ID
        $userId = $level_or_userId;
    }

    // If user ID is still null, try to get it from session
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }

    // Prepare the description with log level prefix for easier filtering
    $logDescription = "[$level] $description";

    try {
        // Check if the table has a level column
        $result = $conn->query("SHOW COLUMNS FROM activity_logs LIKE 'level'");

        if ($result && $result->num_rows > 0) {
            // Table has level column, use it
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, level, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $stmt->bind_param("issss", $userId, $action, $description, $level, $ipAddress);
        } else {
            // Table doesn't have level column, use the old structure
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $stmt->bind_param("isss", $userId, $action, $logDescription, $ipAddress);
        }

        $stmt->execute();
        $stmt->close();

        // For error level logs, also write to PHP error log for backup
        if ($level === 'error') {
            error_log("ISIOLO RAHA ERROR: [$action] $description");
        }
    } catch (Exception $e) {
        // If logging fails, write to PHP error log
        error_log("Failed to log activity: " . $e->getMessage());
        error_log("Activity details: [$action] $description");
    }
}

/**
 * Get the base URL of the application
 *
 * @return string The base URL
 */
function getBaseUrl() {
    // Check if APP_URL is already defined
    if (defined('APP_URL') && APP_URL !== '') {
        return APP_URL;
    }

    // Get the protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

    // Get the host
    $host = $_SERVER['HTTP_HOST'];

    // Get the script name and remove the file part
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $dirName = dirname($scriptName);

    // If we're at the root, just return an empty string
    if ($dirName == '/' || $dirName == '\\') {
        $dirName = '';
    }

    // Construct the base URL
    $baseUrl = "$protocol://$host$dirName";

    return $baseUrl;
}

/**
 * Display flash messages
 *
 * This function displays flash messages stored in the session.
 * It automatically retrieves and clears the flash message.
 *
 * @return void
 */
function displayFlashMessages() {
    $flash = getFlashMessage();
    if ($flash) {
        $type_class = '';
        $icon_class = '';

        switch ($flash['type']) {
            case 'success':
                $type_class = 'bg-green-100 border-green-500 text-green-700';
                $icon_class = 'fa-check-circle text-green-500';
                break;
            case 'error':
                $type_class = 'bg-red-100 border-red-500 text-red-700';
                $icon_class = 'fa-times-circle text-red-500';
                break;
            default: // info
                $type_class = 'bg-blue-100 border-blue-500 text-blue-700';
                $icon_class = 'fa-info-circle text-blue-500';
                break;
        }

        echo '<div class="' . $type_class . ' border-l-4 p-4 mb-4 rounded-r flex items-center">';
        echo '<div class="mr-3 text-xl">';
        echo '<i class="fas ' . $icon_class . '"></i>';
        echo '</div>';
        echo '<p>' . (isset($flash['html']) && $flash['html'] ? $flash['message'] : htmlspecialchars($flash['message'])) . '</p>';
        echo '</div>';
    }
}

/**
 * Format a timestamp into a human-readable "time ago" string
 *
 * @param string $timestamp The timestamp to format
 * @return string The formatted "time ago" string
 */
function timeAgo($timestamp) {
    $time_difference = time() - strtotime($timestamp);

    if ($time_difference < 1) {
        return 'just now';
    }

    $condition = array(
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;

        if ($d >= 1) {
            $t = round($d);
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
}

