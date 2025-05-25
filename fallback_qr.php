<?php
// This is a fallback QR code generator that creates a simple HTML-based QR code
// It's used when the main QR code generator fails

// Check if URL parameter is provided
if (!isset($_GET['url']) || empty($_GET['url'])) {
    header('HTTP/1.1 400 Bad Request');
    echo 'URL parameter is required';
    exit;
}

// Get URL and reference from query parameter
$url = $_GET['url'];
$reference = '';

// Extract reference from URL if possible
if (preg_match('/reference=([^&]+)/', $url, $matches)) {
    $reference = $matches[1];
}

// Set content type to image/png
header('Content-Type: image/png');

// Create a blank image
$image = imagecreatetruecolor(160, 160);

// Define colors
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

// Fill the background
imagefill($image, 0, 0, $white);

// Draw a border
imagerectangle($image, 0, 0, 159, 159, $black);

// Add text
$font = 5; // Built-in font
$text = "SCAN ME";
$text2 = "Booking Ref:";
$text3 = $reference;

// Calculate text position
$text_width = imagefontwidth($font) * strlen($text);
$text_height = imagefontheight($font);
$x = (160 - $text_width) / 2;
$y = 50;

// Draw text
imagestring($image, $font, $x, $y, $text, $black);
imagestring($image, $font, 20, 80, $text2, $black);
imagestring($image, $font, 20, 100, $text3, $black);

// Output the image
imagepng($image);

// Free memory
imagedestroy($image);
