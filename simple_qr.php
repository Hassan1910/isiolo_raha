<?php
/**
 * Simple QR Code Generator
 * 
 * This script generates a QR code using a free online QR code API
 * It's a fallback in case the local QR code library doesn't work
 */

// Check if URL parameter is provided
if (!isset($_GET['url']) || empty($_GET['url'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'URL parameter is required';
    exit;
}

// Get URL from query parameter
$url = $_GET['url'];

// Use a free online QR code API
$qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($url);

// Get the QR code image
$qr_image = @file_get_contents($qr_api_url);

// If we couldn't get the QR code from the API, use our fallback
if ($qr_image === false) {
    include 'fallback_qr.php';
    exit;
}

// Set content type to image/png
header('Content-Type: image/png');

// Output the QR code image
echo $qr_image;
