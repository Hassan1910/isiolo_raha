<?php
/**
 * Paystack Configuration
 * 
 * This file contains Paystack API configuration settings.
 * Paystack constants are defined in config.php
 */

// Include main config file for Paystack constants
require_once __DIR__ . '/config.php';

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