<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include database connection
$conn = require_once 'config/database.php';

// Include functions
require_once 'includes/functions.php';

echo "<h1>Admin Booking Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>Not logged in. Please <a href='login.php'>login</a> first.</p>";
    exit;
}

echo "<p>Logged in as User ID: " . $_SESSION['user_id'] . "</p>";

// Test booking creation
if (isset($_POST['create_test_booking'])) {
    // Generate booking reference
    require_once 'includes/functions.php';
$booking_reference = generateUniqueBookingReference($conn);
    
    // Get a valid schedule
    $sql = "SELECT s.id, s.fare FROM schedules s WHERE s.status = 'scheduled' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $schedule = $result->fetch_assoc();
        $schedule_id = $schedule['id'];
        $fare = $schedule['fare'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Insert booking
            $sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, passenger_id_number, amount, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
            
            $stmt = $conn->prepare($sql);
            $passenger_name = "Test Passenger";
            $passenger_phone = "0700000000";
            $passenger_id = "12345678";
            $seat_number = "A1";
            
            $stmt->bind_param("siissssd", $booking_reference, $_SESSION['user_id'], $schedule_id, $seat_number, $passenger_name, $passenger_phone, $passenger_id, $fare);
            $stmt->execute();
            
            $booking_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert payment record
            $sql = "INSERT INTO payments (booking_id, transaction_reference, amount, payment_method, status, payment_date)
                    VALUES (?, ?, ?, ?, 'successful', NOW())";
            
            $stmt = $conn->prepare($sql);
            $transaction_reference = 'CASH-' . $booking_reference;
            $payment_method = 'cash';
            
            $stmt->bind_param("isds", $booking_id, $transaction_reference, $fare, $payment_method);
            $stmt->execute();
            $stmt->close();
            
            // Log activity
            logActivity("Admin", "Created test booking with reference: " . $booking_reference);
            
            // Commit transaction
            $conn->commit();
            
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
            echo "<h3>Test Booking Created Successfully!</h3>";
            echo "<p>Booking Reference: <strong>" . $booking_reference . "</strong></p>";
            echo "<p>Booking ID: <strong>" . $booking_id . "</strong></p>";
            echo "<p><a href='booking_confirmation.php?reference=" . $booking_reference . "' target='_blank'>View Booking Confirmation</a></p>";
            echo "<p><a href='admin/booking_details.php?reference=" . $booking_reference . "' target='_blank'>View Admin Booking Details</a></p>";
            echo "</div>";
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
            echo "<h3>Error Creating Test Booking</h3>";
            echo "<p>Error: " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
        echo "<h3>No Valid Schedule Found</h3>";
        echo "<p>Please create a schedule first.</p>";
        echo "</div>";
    }
}

// Check for foreign key constraints
echo "<h2>Database Foreign Key Constraints</h2>";

$sql = "SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' 
        AND TABLE_SCHEMA = 'isioloraha'
        AND TABLE_NAME IN ('bookings', 'payments')";

$result = $conn->query($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Table</th><th>Constraint Name</th><th>Referenced Table</th></tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['TABLE_NAME'] . "</td>";
        echo "<td>" . $row['CONSTRAINT_NAME'] . "</td>";
        
        // Get referenced table
        $sql2 = "SELECT REFERENCED_TABLE_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS 
                WHERE CONSTRAINT_SCHEMA = 'isioloraha' 
                AND CONSTRAINT_NAME = '" . $row['CONSTRAINT_NAME'] . "'";
        $result2 = $conn->query($sql2);
        $row2 = $result2->fetch_assoc();
        
        echo "<td>" . $row2['REFERENCED_TABLE_NAME'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No foreign key constraints found</td></tr>";
}

echo "</table>";

// Form to create test booking
echo "<h2>Create Test Booking</h2>";
echo "<form method='post'>";
echo "<input type='submit' name='create_test_booking' value='Create Test Booking'>";
echo "</form>";

// Close connection
$conn->close();
?>
