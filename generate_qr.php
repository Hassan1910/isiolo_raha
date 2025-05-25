<?php
// Import QR code classes at the top level
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Color\Color;

// Check if URL parameter is provided
if (!isset($_GET['url']) || empty($_GET['url'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'URL parameter is required';
    exit;
}

// Get URL from query parameter
$url = $_GET['url'];

try {
    // Try to use the Endroid QR Code library if available
    if (file_exists('vendor/autoload.php')) {
        // Include Composer autoloader
        require 'vendor/autoload.php';

        // Create QR code
        $qrCode = new QrCode($url);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
        $qrCode->setForegroundColor(new Color(0, 0, 0));
        $qrCode->setBackgroundColor(new Color(255, 255, 255));

        // Create writer
        $writer = new PngWriter();

        // Generate QR code
        $result = $writer->write($qrCode);

        // Set appropriate content type
        header('Content-Type: ' . $result->getMimeType());

        // Output the QR code image
        echo $result->getString();
        exit;
    } else {
        // If library is not available, throw an exception to use fallback
        throw new Exception('QR code library not available');
    }
} catch (Exception $e) {
    // If anything goes wrong, use the fallback QR code generator
    include 'fallback_qr.php';
}
