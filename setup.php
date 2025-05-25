<?php
/**
 * Database Setup Script
 *
 * This script initializes the database and creates sample data for the Isiolo Raha Bus Booking System
 */

// Include database initialization script
require_once 'config/init_db.php';

// Insert sample data
echo "<h2>Inserting sample data...</h2>";

// Insert sample buses
$sql = "INSERT INTO buses (name, registration_number, capacity, type, amenities, status) VALUES
        ('Isiolo Express', 'KBZ 123A', 44, 'standard', 'Air Conditioning, Reclining Seats', 'active'),
        ('Isiolo Luxury', 'KCB 456B', 36, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments', 'active'),
        ('Isiolo Comfort', 'KDC 789C', 40, 'executive', 'Air Conditioning, Reclining Seats, WiFi, USB Charging', 'active'),
        ('Isiolo Swift', 'KEF 012D', 44, 'standard', 'Air Conditioning, Reclining Seats', 'active'),
        ('Isiolo Premier', 'KFG 345E', 36, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments', 'active')
        ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        capacity = VALUES(capacity),
        type = VALUES(type),
        amenities = VALUES(amenities),
        status = VALUES(status)";

if ($conn->query($sql) === TRUE) {
    echo "<p>Sample buses added successfully</p>";
} else {
    echo "<p>Error adding sample buses: " . $conn->error . "</p>";
}

// Insert sample routes
$sql = "INSERT INTO routes (origin, destination, distance, duration) VALUES
        ('Nairobi', 'Mombasa', 485, 420),
        ('Nairobi', 'Kisumu', 340, 360),
        ('Nairobi', 'Nakuru', 160, 120),
        ('Mombasa', 'Nairobi', 485, 420),
        ('Kisumu', 'Nairobi', 340, 360),
        ('Nakuru', 'Nairobi', 160, 120),
        ('Nairobi', 'Eldoret', 320, 300),
        ('Eldoret', 'Nairobi', 320, 300),
        ('Mombasa', 'Malindi', 120, 90),
        ('Malindi', 'Mombasa', 120, 90)
        ON DUPLICATE KEY UPDATE
        distance = VALUES(distance),
        duration = VALUES(duration)";

if ($conn->query($sql) === TRUE) {
    echo "<p>Sample routes added successfully</p>";
} else {
    echo "<p>Error adding sample routes: " . $conn->error . "</p>";
}

// Insert sample schedules
// Get route IDs and details
$sql = "SELECT id, origin, destination, distance, duration FROM routes";
$result = $conn->query($sql);
$routes = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Get bus IDs
$sql = "SELECT id, name, type FROM buses";
$result = $conn->query($sql);
$buses = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $buses[] = $row;
    }
}

// Create schedules for the next 7 days
$schedules_sql = "INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status) VALUES ";
$schedules_values = [];

for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i days"));

    foreach ($routes as $route) {
        // Morning departure
        $bus_id = $buses[array_rand($buses)]['id'];
        $departure_time = $date . ' 08:00:00';
        // Ensure duration exists and is not null
        $duration = isset($route['duration']) && !is_null($route['duration']) ? $route['duration'] : 120; // Default to 2 hours if missing
        $arrival_time_timestamp = strtotime($departure_time) + ($duration * 60);
        $arrival_time = date('Y-m-d H:i:s', $arrival_time_timestamp);

        // Set fare based on bus type and distance
        $bus_type_index = array_search($bus_id, array_column($buses, 'id'));
        $bus_type = $buses[$bus_type_index]['type'];
        $fare_multiplier = ($bus_type === 'standard') ? 1 : (($bus_type === 'executive') ? 1.3 : 1.5);
        // Ensure distance exists and is not null
        $distance = isset($route['distance']) && !is_null($route['distance']) ? $route['distance'] : 100; // Default to 100km if missing
        $fare = round(($distance * 0.5) * $fare_multiplier, -1); // Round to nearest 10

        $schedules_values[] = "({$route['id']}, $bus_id, '$departure_time', '$arrival_time', $fare, 'scheduled')";

        // Evening departure
        $bus_id = $buses[array_rand($buses)]['id'];
        $departure_time = $date . ' 14:00:00';
        // Ensure duration exists and is not null
        $duration = isset($route['duration']) && !is_null($route['duration']) ? $route['duration'] : 120; // Default to 2 hours if missing
        $arrival_time_timestamp = strtotime($departure_time) + ($duration * 60);
        $arrival_time = date('Y-m-d H:i:s', $arrival_time_timestamp);

        // Set fare based on bus type and distance
        $bus_type_index = array_search($bus_id, array_column($buses, 'id'));
        $bus_type = $buses[$bus_type_index]['type'];
        $fare_multiplier = ($bus_type === 'standard') ? 1 : (($bus_type === 'executive') ? 1.3 : 1.5);
        // Ensure distance exists and is not null
        $distance = isset($route['distance']) && !is_null($route['distance']) ? $route['distance'] : 100; // Default to 100km if missing
        $fare = round(($distance * 0.5) * $fare_multiplier, -1); // Round to nearest 10

        $schedules_values[] = "({$route['id']}, $bus_id, '$departure_time', '$arrival_time', $fare, 'scheduled')";
    }
}

$schedules_sql .= implode(', ', $schedules_values);

if ($conn->query($schedules_sql) === TRUE) {
    echo "<p>Sample schedules added successfully</p>";
} else {
    echo "<p>Error adding sample schedules: " . $conn->error . "</p>";
}

echo "<h2>Setup completed successfully!</h2>";
echo "<p>You can now <a href='index.php'>go to the homepage</a> and start using the system.</p>";
echo "<p>Login with the following credentials:</p>";
echo "<ul>";
echo "<li>Email: admin@isioloraha.com</li>";
echo "<li>Password: admin123</li>";
echo "</ul>";

// Close connection
$conn->close();
?>
