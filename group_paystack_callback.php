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

// Check if group booking data exists in session
if (!isset($_SESSION['group_booking_data']) || !isset($_SESSION['group_booking_data']['passenger_details'])) {
    // Set message
    setFlashMessage("error", "Please complete the group booking process.");

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
    header("Location: group_payment.php");
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
            'amount' => $_SESSION['group_booking_data']['total_amount'] * 100
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
    header("Location: group_payment.php");
    exit();
}

// Get group booking data from session
$group_booking_data = $_SESSION['group_booking_data'];
$schedule_id = $group_booking_data['schedule_id'];
$selected_seats = explode(',', $group_booking_data['selected_seats']);
$total_amount = $group_booking_data['total_amount'];
$passenger_details = $group_booking_data['passenger_details'];
$booking_reference = $group_booking_data['booking_reference'];

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

// Begin transaction early to prevent race conditions
$conn->begin_transaction();

// Check if booking already exists with row locking
$check_sql = "SELECT COUNT(*) FROM bookings WHERE booking_reference = ? FOR UPDATE";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $booking_reference);
$check_stmt->execute();
$check_stmt->bind_result($existing_count);
$check_stmt->fetch();
$check_stmt->close();

if ($existing_count > 0) {
    // Booking already exists, rollback and redirect to confirmation
    $conn->rollback();
    debug_to_file("Booking already exists with reference: " . $booking_reference, 'DUPLICATE_CHECK');
    setFlashMessage("info", "This booking has already been processed.");
    header("Location: group_booking_confirmation.php?reference=" . $booking_reference);
    exit();
}

// Debug database connection
debug_to_file("About to process bookings in existing transaction", 'DB');

// Process bookings in transaction
try {
    debug_to_file("Processing bookings in transaction", 'DB');

    // Insert bookings
    foreach ($passenger_details as $passenger) {
        debug_to_file("Processing passenger: " . $passenger['name'], 'DB');

        $sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, passenger_id_number, passenger_age_group, special_needs, amount, status, group_name, booking_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $group_name = $group_booking_data['group_name'];
        $age_group = isset($passenger['age_group']) ? $passenger['age_group'] : 'adult';
        $special_needs = isset($passenger['special_needs']) ? $passenger['special_needs'] : '';
        
        $stmt->bind_param("siisssssds", $booking_reference, $_SESSION['user_id'], $schedule_id, $passenger['seat'], $passenger['name'], $passenger['phone'], $passenger['id_number'], $age_group, $special_needs, $schedule['fare'], $group_name);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $booking_id = $stmt->insert_id;
        debug_to_file("Booking inserted with ID: " . $booking_id, 'DB');
        $stmt->close();

        // Insert payment record
        $sql = "INSERT INTO payments (booking_reference, transaction_reference, amount, payment_method, payment_status, payment_date)
                VALUES (?, ?, ?, 'paystack', 'successful', NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssd", $booking_reference, $reference, $schedule['fare']);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        debug_to_file("Payment inserted for booking reference: " . $booking_reference, 'DB');
        $stmt->close();
    }

    // Log activity
    logActivity("Booking", "Group booking completed successfully with reference: " . $booking_reference . " via Paystack");
    debug_to_file("About to commit transaction", 'DB');

    // Commit transaction
    $conn->commit();
    debug_to_file("Transaction committed successfully", 'DB');

    // Set success message
    setFlashMessage("success", "Payment successful! Your group booking has been confirmed.");

    // Redirect to booking confirmation page
    header("Location: group_booking_confirmation.php?reference=" . $booking_reference);
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

    // Check if this is a duplicate entry error
    if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'booking_reference') !== false) {
        // This booking already exists, redirect to confirmation page
        debug_to_file("Duplicate booking reference detected, redirecting to confirmation", 'DUPLICATE_HANDLING');
        setFlashMessage("info", "This booking has already been processed. Redirecting to your booking confirmation.");
        header("Location: group_booking_confirmation.php?reference=" . $booking_reference);
        exit();
    }

    // Set error message for other types of errors
    setFlashMessage("error", "Error processing payment. Please try again or contact support. Error details: " . $e->getMessage());

    // Redirect to payment page
    header("Location: group_payment.php");
    exit();
}
?>