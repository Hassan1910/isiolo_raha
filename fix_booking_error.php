<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include database connection
$conn = require_once 'config/database.php';

// Include functions
require_once 'includes/functions.php';

echo "<div class='container mx-auto px-4 py-8'>";
echo "<h1 class='text-3xl font-bold mb-6'>Booking Error Fix</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>";
    echo "<p>You are not logged in. Please <a href='login.php' class='underline'>login</a> to continue.</p>";
    echo "</div>";
    exit;
}

// Check if booking reference is provided
if (!isset($_GET['reference'])) {
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>";
    echo "<p>No booking reference provided. Please provide a booking reference.</p>";
    echo "</div>";
    exit;
}

$booking_reference = $_GET['reference'];

// Get booking details
$sql = "SELECT b.id, b.booking_reference, b.user_id, b.schedule_id, b.seat_number, b.passenger_name, b.passenger_phone, 
        b.passenger_id_number, b.amount, b.status, b.created_at
        FROM bookings b
        WHERE b.booking_reference = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("s", $booking_reference);
    
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $bookings = [];
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            
            echo "<div class='bg-white rounded-lg shadow-md p-6 mb-6'>";
            echo "<h2 class='text-xl font-bold mb-4'>Booking Details</h2>";
            
            echo "<table class='w-full border-collapse'>";
            echo "<thead>";
            echo "<tr class='bg-gray-100'>";
            echo "<th class='border p-2 text-left'>ID</th>";
            echo "<th class='border p-2 text-left'>Reference</th>";
            echo "<th class='border p-2 text-left'>User ID</th>";
            echo "<th class='border p-2 text-left'>Schedule ID</th>";
            echo "<th class='border p-2 text-left'>Seat</th>";
            echo "<th class='border p-2 text-left'>Passenger</th>";
            echo "<th class='border p-2 text-left'>Status</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            
            foreach ($bookings as $booking) {
                echo "<tr>";
                echo "<td class='border p-2'>" . $booking['id'] . "</td>";
                echo "<td class='border p-2'>" . $booking['booking_reference'] . "</td>";
                echo "<td class='border p-2'>" . $booking['user_id'] . "</td>";
                echo "<td class='border p-2'>" . $booking['schedule_id'] . "</td>";
                echo "<td class='border p-2'>" . $booking['seat_number'] . "</td>";
                echo "<td class='border p-2'>" . $booking['passenger_name'] . "</td>";
                echo "<td class='border p-2'>" . $booking['status'] . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
            
            // Check if user_id is null or invalid
            $first_booking = $bookings[0];
            if ($first_booking['user_id'] == 0 || $first_booking['user_id'] == null) {
                echo "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 my-4'>";
                echo "<p>The user ID for this booking is missing or invalid. Would you like to update it with your current user ID?</p>";
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='booking_reference' value='" . $booking_reference . "'>";
                echo "<button type='submit' name='update_user_id' class='bg-primary-600 hover:bg-primary-500 text-white font-bold py-2 px-4 rounded mt-2'>Update User ID</button>";
                echo "</form>";
                echo "</div>";
            } else {
                echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 my-4'>";
                echo "<p>The user ID for this booking is valid.</p>";
                echo "</div>";
            }
            
            echo "</div>";
            
            // Process form submission to update user_id
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user_id'])) {
                $conn->begin_transaction();
                
                try {
                    // Update bookings
                    $sql = "UPDATE bookings SET user_id = ? WHERE booking_reference = ?";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $_SESSION['user_id'], $booking_reference);
                    $stmt->execute();
                    
                    // Log activity
                    logActivity("Booking", "Updated user ID for booking with reference: " . $booking_reference);
                    
                    // Commit transaction
                    $conn->commit();
                    
                    echo "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>";
                    echo "<p>User ID updated successfully! You can now proceed with your booking.</p>";
                    echo "<p><a href='booking_confirmation.php?reference=" . $booking_reference . "' class='underline'>View Booking</a></p>";
                    echo "</div>";
                } catch (Exception $e) {
                    // Rollback transaction
                    $conn->rollback();
                    
                    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>";
                    echo "<p>Error updating user ID: " . $e->getMessage() . "</p>";
                    echo "</div>";
                }
            }
        } else {
            echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>";
            echo "<p>Booking not found with reference: " . $booking_reference . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>";
        echo "<p>Error executing query: " . $stmt->error . "</p>";
        echo "</div>";
    }
    
    // Close statement
    $stmt->close();
} else {
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>";
    echo "<p>Error preparing query: " . $conn->error . "</p>";
    echo "</div>";
}

echo "</div>";
?>
