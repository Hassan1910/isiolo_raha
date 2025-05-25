<?php
/**
 * Session Configuration File
 *
 * This file contains session-related settings for the Isiolo Raha Bus Booking System.
 * It must be included BEFORE session_start() is called.
 */

// Start output buffering to prevent "headers already sent" errors
ob_start();

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
