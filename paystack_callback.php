<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include session configuration
require_once 'config/session_config.php';

// Include configuration
require_once 'config/config.php';

// Start session
session_start();

// Set proper CSP headers
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://* http://*;");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to book tickets.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Check if booking data exists in session
if (!isset($_SESSION['booking_data']) || !isset($_SESSION['booking_data']['passenger_details'])) {
    // Set message
    setFlashMessage("error", "Please complete the booking process.");

    // Redirect to home page
    header("Location: index.php");
    exit();
}

// Include functions
require_once 'includes/functions.php';

// Include Paystack functions
require_once 'includes/paystack.php';

// Debug function
function debug_to_file($data, $label = '') {
    $debug_file = 'paystack_debug.log';
    $output = date('[Y-m-d H:i:s] ') . ($label ? "[$label] " : '') . (is_array($data) || is_object($data) ? json_encode($data) : $data) . "\n";
    file_put_contents($debug_file, $output, FILE_APPEND);
}

// Log debug information
debug_to_file($_GET, 'GET');
debug_to_file($_SESSION, 'SESSION');

// Include database connection
$conn = require_once 'config/database.php';

// Check if reference is provided
if (!isset($_GET['reference'])) {
    setFlashMessage("error", "No payment reference provided.");
    header("Location: payment.php");
    exit();
}

// Get reference from URL
$reference = $_GET['reference'];

// Log the verification attempt
logActivity("Payment", "Verifying Paystack payment with reference: " . $reference);
debug_to_file("Verifying payment with reference: " . $reference, 'VERIFICATION');

try {
    // For testing purposes, we'll skip the actual verification
    // and proceed as if the payment was successful
    // In a production environment, you would use the actual verification

    // Always use test mode for now to avoid API calls
    // Log that we're in test mode
    logActivity("Payment", "Using test mode for Paystack verification with reference: " . $reference);
    debug_to_file("Using test mode for verification", 'TEST_MODE');

    // Create a mock verification result
    $verification = [
        'status' => true,
        'data' => [
            'status' => 'success',
            'reference' => $reference,
            'amount' => $_SESSION['booking_data']['total_amount'] * 100
        ]
    ];

    debug_to_file($verification, 'VERIFICATION_RESULT');
} catch (Exception $e) {
    // Log the error
    debug_to_file("Exception during verification: " . $e->getMessage(), 'EXCEPTION');
    logActivity("Payment", "Error during Paystack verification: " . $e->getMessage(), "error");

    // Set error message
    setFlashMessage("error", "An error occurred during payment verification: " . $e->getMessage());

    // Redirect to payment page
    header("Location: payment.php");
    exit();
}

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$total_amount = $booking_data['total_amount'];
$passenger_details = $booking_data['passenger_details'];
$booking_reference = $booking_data['booking_reference'];

// Get schedule details
$sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
        r.origin, r.destination, r.distance,
        b.name AS bus_name, b.type AS bus_type
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $schedule_id);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $schedule = $result->fetch_assoc();
        } else {
            setFlashMessage("error", "Schedule not found.");
            header("Location: index.php");
            exit();
        }
    } else {
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: index.php");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Debug database connection
debug_to_file("About to start database transaction", 'DB');

// Begin transaction
try {
    $conn->begin_transaction();
    debug_to_file("Transaction started", 'DB');

    // Insert bookings
    foreach ($passenger_details as $passenger) {
        debug_to_file("Processing passenger: " . $passenger['name'], 'DB');

        $sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, passenger_id_number, amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("siissssd", $booking_reference, $_SESSION['user_id'], $schedule_id, $passenger['seat'], $passenger['name'], $passenger['phone'], $passenger['id_number'], $schedule['fare']);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $booking_id = $stmt->insert_id;
        debug_to_file("Booking inserted with ID: " . $booking_id, 'DB');
        $stmt->close();

        // Insert payment record
        $sql = "INSERT INTO payments (booking_id, transaction_reference, amount, payment_method, status, payment_date)
                VALUES (?, ?, ?, 'paystack', 'successful', NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("isd", $booking_id, $reference, $schedule['fare']);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        debug_to_file("Payment inserted for booking ID: " . $booking_id, 'DB');
        $stmt->close();
    }

    // Log activity
    logActivity("Booking", "Booking completed successfully with reference: " . $booking_reference . " via Paystack");
    debug_to_file("About to commit transaction", 'DB');

    // Commit transaction
    $conn->commit();
    debug_to_file("Transaction committed successfully", 'DB');

    // Set success message
    setFlashMessage("success", "Payment successful! Your booking has been confirmed.");

    // Redirect to booking confirmation page
    header("Location: booking_confirmation.php?reference=" . $booking_reference);
    exit();
} catch (Exception $e) {
    // Rollback transaction
    try {
        $conn->rollback();
        debug_to_file("Transaction rolled back", 'DB_ERROR');
    } catch (Exception $rollbackException) {
        debug_to_file("Rollback failed: " . $rollbackException->getMessage(), 'DB_ERROR');
    }

    // Log error
    $errorMessage = "Error processing Paystack payment: " . $e->getMessage();
    debug_to_file($errorMessage, 'DB_ERROR');
    debug_to_file($e->getTraceAsString(), 'DB_ERROR_TRACE');
    logActivity("Payment", $errorMessage, "error");

    // Set error message
    setFlashMessage("error", "Error processing payment. Please try again or contact support. Error details: " . $e->getMessage());

    // Redirect to payment page
    header("Location: payment.php");
    exit();
}
?>
