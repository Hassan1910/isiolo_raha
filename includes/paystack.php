<?php
/**
 * Paystack Integration Functions
 *
 * This file contains functions for integrating with the Paystack payment gateway
 */

// Include configuration if not already included
if (!defined('PAYSTACK_PUBLIC_KEY')) {
    require_once __DIR__ . '/../config/paystack_config.php';
}

/**
 * Initialize a Paystack transaction
 *
 * @param string $email Customer email
 * @param float $amount Amount to charge (in KES)
 * @param string $reference Unique transaction reference
 * @param string $callback_url URL to redirect to after payment
 * @return array|bool Response from Paystack or false on failure
 */
function initializePaystackTransaction($email, $amount, $reference, $callback_url) {
    // Convert amount to kobo/cents (Paystack requires amount in the smallest currency unit)
    $amount_in_cents = (int)($amount * 100);

    // Set up the API endpoint
    $url = 'https://api.paystack.co/transaction/initialize';

    // Set up the request data
    $fields = [
        'email' => $email,
        'amount' => $amount_in_cents,
        'reference' => $reference,
        'callback_url' => $callback_url,
        'currency' => PAYSTACK_CURRENCY
    ];

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
        'Cache-Control: no-cache'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        // Log the error
        error_log('Paystack cURL Error: ' . curl_error($ch));
        return false;
    }

    // Close cURL
    curl_close($ch);

    // Decode the response
    $result = json_decode($response, true);

    // Check if the request was successful
    if (!$result['status']) {
        // Log the error
        error_log('Paystack API Error: ' . $result['message']);
        return false;
    }

    // Return the result
    return $result;
}

/**
 * Verify a Paystack transaction
 *
 * @param string $reference Transaction reference
 * @return array|bool Response from Paystack or false on failure
 */
function verifyPaystackTransaction($reference) {
    // Set up the API endpoint
    $url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
        'Cache-Control: no-cache'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        // Log the error
        error_log('Paystack cURL Error: ' . curl_error($ch));
        return false;
    }

    // Close cURL
    curl_close($ch);

    // Decode the response
    $result = json_decode($response, true);

    // Check if the request was successful
    if (!$result['status']) {
        // Log the error
        error_log('Paystack API Error: ' . $result['message']);
        return false;
    }

    // Return the result
    return $result;
}

/**
 * Get Paystack checkout URL
 *
 * @param string $email Customer email
 * @param float $amount Amount to charge (in KES)
 * @param string $reference Unique transaction reference
 * @param string $callback_url URL to redirect to after payment
 * @return string Checkout URL
 */
function getPaystackCheckoutUrl($email, $amount, $reference, $callback_url) {
    // Convert amount to kobo/cents (Paystack requires amount in the smallest currency unit)
    $amount_in_cents = (int)($amount * 100);

    // Generate the direct Paystack URL
    $paystack_url = "https://checkout.paystack.com/";

    $params = [
        'key' => PAYSTACK_PUBLIC_KEY,
        'email' => $email,
        'amount' => $amount_in_cents,
        'currency' => PAYSTACK_CURRENCY,
        'ref' => $reference,
        'callback_url' => $callback_url
    ];

    return $paystack_url . '?' . http_build_query($params);
}
