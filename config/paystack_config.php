<?php
/**
 * Paystack Configuration
 * 
 * This file contains Paystack API configuration settings.
 * Paystack constants are defined in config.php
 */

// Include main config file for Paystack constants
require_once __DIR__ . '/config.php';

// Environment-aware Paystack configuration
if (defined('IS_PRODUCTION') && IS_PRODUCTION) {
    // Load production Paystack configuration
    if (file_exists(__DIR__ . '/paystack_production.php')) {
        require_once __DIR__ . '/paystack_production.php';
    } else {
        // Fallback to test keys with warning
        error_log('WARNING: Production environment detected but paystack_production.php not found. Using test keys.');
        define('PAYSTACK_PUBLIC_KEY', 'pk_test_c7f8306f56b3fe44259a7e8d8a025c3f69e8102b');
        define('PAYSTACK_SECRET_KEY', 'sk_test_36c2a669d1feb76b51dd0bff57eccdfebea18350');
        define('PAYSTACK_CURRENCY', 'KES');
        define('PAYSTACK_ENVIRONMENT', 'test');
    }
} else {
    // Development/test environment
    define('PAYSTACK_PUBLIC_KEY', 'pk_test_c7f8306f56b3fe44259a7e8d8a025c3f69e8102b');
    define('PAYSTACK_SECRET_KEY', 'sk_test_36c2a669d1feb76b51dd0bff57eccdfebea18350');
    define('PAYSTACK_CURRENCY', 'KES');
    define('PAYSTACK_ENVIRONMENT', 'test');
    define('PAYSTACK_API_URL', 'https://api.paystack.co');
}

// Paystack API Configuration
define('PAYSTACK_BASE_URL', 'https://api.paystack.co');

// Payment Configuration
define('PAYSTACK_CALLBACK_URL', APP_URL . '/paystack_callback.php');

/**
 * Initialize Paystack payment
 * 
 * @param string $email Customer email
 * @param int $amount Amount in kobo (multiply by 100)
 * @param string $reference Unique payment reference
 * @param array $metadata Additional payment metadata
 * @return array Payment initialization response
 */
function initializePaystackPayment($email, $amount, $reference, $metadata = []) {
    $url = PAYSTACK_BASE_URL . '/transaction/initialize';
    
    $fields = [
        'email' => $email,
        'amount' => $amount * 100, // Convert to kobo
        'reference' => $reference,
        'currency' => PAYSTACK_CURRENCY,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'metadata' => $metadata
    ];
    
    $fields_string = http_build_query($fields);
    
    // Initialize cURL
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $fields_string,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ],
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        return ['status' => false, 'message' => 'cURL Error: ' . $err];
    }
    
    return json_decode($response, true);
}



/**
 * Verify Paystack payment
 * 
 * @param string $reference Payment reference
 * @return array Payment verification response
 */
function verifyPaystackPayment($reference) {
    $url = PAYSTACK_BASE_URL . '/transaction/verify/' . $reference;
    
    // Initialize cURL
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ],
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        return ['status' => false, 'message' => 'cURL Error: ' . $err];
    }
    
    return json_decode($response, true);
}
?>