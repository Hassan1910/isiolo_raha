<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include database connection
$conn = require_once 'config/database.php';

// Include functions
require_once 'includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

$schedule_id = $data['schedule_id'] ?? null;
$seat_number = $data['seat_number'] ?? null;
$action = $data['action'] ?? null;

if (!$schedule_id || !$seat_number || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($action === 'select') {
    // Check if the seat is already booked
    $sql = "SELECT id FROM bookings WHERE schedule_id = ? AND seat_number = ? AND status IN ('confirmed', 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $schedule_id, $seat_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Seat already booked.']);
        exit();
    }

    // Create a pending booking
    $booking_reference = 'PENDING-' . time() . '-' . $user_id;
    $sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $passenger_name = 'Pending';
    $passenger_phone = 'Pending';
    $amount = 0; // Amount will be updated later
    $stmt->bind_param("siisssd", $booking_reference, $user_id, $schedule_id, $seat_number, $passenger_name, $passenger_phone, $amount);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Seat reserved.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reserve seat.']);
    }
} elseif ($action === 'deselect') {
    // Delete the pending booking
    $sql = "DELETE FROM bookings WHERE schedule_id = ? AND seat_number = ? AND user_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $schedule_id, $seat_number, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reservation cancelled.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel reservation.']);
    }
}

$stmt->close();
$conn->close();
?>