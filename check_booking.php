<?php
// Connect to database
$conn = new mysqli('localhost', 'root', '', 'isioloraha');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get booking reference from URL or use a default
$reference = isset($_GET['reference']) ? $_GET['reference'] : 'IR6734D644';

// Query booking
$sql = "SELECT b.*, p.payment_method, p.status as payment_status, p.transaction_reference 
        FROM bookings b 
        LEFT JOIN payments p ON b.id = p.booking_id 
        WHERE b.booking_reference = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Booking Details for Reference: " . htmlspecialchars($reference) . "</h2>";

if ($result && $result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    
    foreach ($booking as $field => $value) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($field) . "</td>";
        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<p>Booking found in database!</p>";
    
    // Add links to view booking
    echo "<p><a href='admin/booking_details.php?reference=" . htmlspecialchars($reference) . "' target='_blank'>View in Admin Panel</a></p>";
    echo "<p><a href='print_ticket.php?reference=" . htmlspecialchars($reference) . "' target='_blank'>Print Ticket</a></p>";
} else {
    echo "<p style='color: red;'>No booking found with reference: " . htmlspecialchars($reference) . "</p>";
    
    // Check if any bookings exist
    $sql = "SELECT booking_reference FROM bookings ORDER BY created_at DESC LIMIT 5";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<h3>Recent Bookings:</h3>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li><a href='check_booking.php?reference=" . $row['booking_reference'] . "'>" . $row['booking_reference'] . "</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No bookings found in the database.</p>";
    }
}

// Close connection
$conn->close();
?>
