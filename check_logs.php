<?php
// Connect to database
$conn = new mysqli('localhost', 'root', '', 'isioloraha');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query activity logs
$sql = "SELECT * FROM activity_logs WHERE action = 'Admin' AND description LIKE '%booking%' ORDER BY created_at DESC LIMIT 20";
$result = $conn->query($sql);

echo "<h2>Admin Booking Activity Logs</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Time</th><th>User ID</th><th>Description</th></tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['description'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No logs found</td></tr>";
}

echo "</table>";

// Query all bookings
echo "<h2>All Bookings</h2>";
$sql = "SELECT * FROM bookings ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Reference</th><th>User ID</th><th>Status</th><th>Created At</th></tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['booking_reference'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>No bookings found</td></tr>";
}

echo "</table>";

// Close connection
$conn->close();
?>
