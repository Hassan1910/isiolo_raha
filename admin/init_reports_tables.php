<?php
// Initialize required tables for reports functionality
require_once '../config/database.php';

echo "<h1>Database Table Initialization for Reports</h1>";

// Check if we have a valid connection
if (!$conn || $conn->connect_error) {
    die("Connection failed: " . ($conn ? $conn->connect_error : "No connection object"));
}

echo "<p>Connected to database successfully.</p>";

// Create tables if they don't exist
$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        reset_token VARCHAR(100) NULL,
        reset_token_expires DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'buses' => "CREATE TABLE IF NOT EXISTS buses (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        registration_number VARCHAR(20) NOT NULL UNIQUE,
        capacity INT(3) NOT NULL,
        type ENUM('standard', 'executive', 'luxury') DEFAULT 'standard',
        amenities TEXT NULL,
        status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'routes' => "CREATE TABLE IF NOT EXISTS routes (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        origin VARCHAR(100) NOT NULL,
        destination VARCHAR(100) NOT NULL,
        distance DECIMAL(10,2) NULL,
        duration INT(11) NULL COMMENT 'Duration in minutes',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY origin_destination (origin, destination)
    )",
    
    'schedules' => "CREATE TABLE IF NOT EXISTS schedules (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        route_id INT(11) UNSIGNED NOT NULL,
        bus_id INT(11) UNSIGNED NOT NULL,
        departure_time DATETIME NOT NULL,
        arrival_time DATETIME NOT NULL,
        fare DECIMAL(10,2) NOT NULL,
        status ENUM('scheduled', 'departed', 'arrived', 'cancelled') DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
        FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE
    )",
    
    'bookings' => "CREATE TABLE IF NOT EXISTS bookings (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        booking_reference VARCHAR(20) NOT NULL UNIQUE,
        user_id INT(11) UNSIGNED NOT NULL,
        schedule_id INT(11) UNSIGNED NOT NULL,
        seat_number VARCHAR(10) NOT NULL,
        passenger_name VARCHAR(100) NOT NULL,
        passenger_phone VARCHAR(20) NOT NULL,
        passenger_id_number VARCHAR(20) NULL,
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $table_name => $sql) {
    echo "<h3>Creating table: $table_name</h3>";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Table '$table_name' created successfully or already exists.</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating table '$table_name': " . $conn->error . "</p>";
    }
}

// Insert sample data if tables are empty
echo "<h2>Inserting Sample Data</h2>";

// Check if admin user exists
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $admin_sql = "INSERT INTO users (first_name, last_name, email, phone, password, role)
                      VALUES ('Admin', 'User', 'admin@isioloraha.com', '0700000000', 
                      '$2y$10\$Ql9Xj.Rh9NyP4xEZpbF0eeHQOYOdnKGBFEKWF9bQCYLYt0q/yOzHu', 'admin')";
        if ($conn->query($admin_sql)) {
            echo "<p style='color: green;'>✓ Admin user created (email: admin@isioloraha.com, password: admin123)</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating admin user: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✓ Admin user already exists</p>";
    }
}

// Insert sample buses if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM buses");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $buses_sql = "INSERT INTO buses (name, registration_number, capacity, type, amenities, status) VALUES
                      ('Isiolo Express', 'KBZ 123A', 44, 'standard', 'Air Conditioning, Reclining Seats', 'active'),
                      ('Isiolo Luxury', 'KCB 456B', 36, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments', 'active'),
                      ('Isiolo Comfort', 'KDC 789C', 40, 'executive', 'Air Conditioning, Reclining Seats, WiFi, USB Charging', 'active')";
        if ($conn->query($buses_sql)) {
            echo "<p style='color: green;'>✓ Sample buses created</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating buses: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✓ Buses already exist</p>";
    }
}

// Insert sample routes if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM routes");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $routes_sql = "INSERT INTO routes (origin, destination, distance, duration) VALUES
                       ('Nairobi', 'Mombasa', 485, 420),
                       ('Nairobi', 'Kisumu', 340, 360),
                       ('Nairobi', 'Nakuru', 160, 120),
                       ('Mombasa', 'Nairobi', 485, 420),
                       ('Kisumu', 'Nairobi', 340, 360)";
        if ($conn->query($routes_sql)) {
            echo "<p style='color: green;'>✓ Sample routes created</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating routes: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✓ Routes already exist</p>";
    }
}

// Insert sample schedules if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM schedules");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $schedules_sql = "INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status) VALUES
                          (1, 1, '$tomorrow 08:00:00', '$tomorrow 15:00:00', 2500, 'scheduled'),
                          (1, 2, '$tomorrow 14:00:00', '$tomorrow 21:00:00', 3000, 'scheduled'),
                          (2, 3, '$tomorrow 08:00:00', '$tomorrow 14:00:00', 2000, 'scheduled')";
        if ($conn->query($schedules_sql)) {
            echo "<p style='color: green;'>✓ Sample schedules created</p>";
        } else {
            echo "<p style='color: red;'>✗ Error creating schedules: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>✓ Schedules already exist</p>";
    }
}

// Insert sample bookings if none exist
$result = $conn->query("SELECT COUNT(*) as count FROM bookings");
if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        // First get a user ID and schedule ID
        $user_result = $conn->query("SELECT id FROM users LIMIT 1");
        $schedule_result = $conn->query("SELECT id FROM schedules LIMIT 1");
        
        if ($user_result && $schedule_result && $user_result->num_rows > 0 && $schedule_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            $schedule = $schedule_result->fetch_assoc();
            
            $today = date('Y-m-d H:i:s');
            $yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
            
            $bookings_sql = "INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, passenger_name, passenger_phone, passenger_id_number, amount, status, created_at) VALUES
                             ('BK001', {$user['id']}, {$schedule['id']}, 'A1', 'John Doe', '0700000001', '12345678', 2500, 'confirmed', '$today'),
                             ('BK002', {$user['id']}, {$schedule['id']}, 'A2', 'Jane Smith', '0700000002', '87654321', 2500, 'confirmed', '$yesterday'),
                             ('BK003', {$user['id']}, {$schedule['id']}, 'A3', 'Bob Johnson', '0700000003', '11223344', 2500, 'cancelled', '$yesterday')";
            
            if ($conn->query($bookings_sql)) {
                echo "<p style='color: green;'>✓ Sample bookings created</p>";
            } else {
                echo "<p style='color: red;'>✗ Error creating bookings: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Cannot create sample bookings - missing users or schedules</p>";
        }
    } else {
        echo "<p>✓ Bookings already exist</p>";
    }
}

echo "<h2>Initialization Complete</h2>";
echo "<p><a href='debug_reports.php'>Run Debug Test</a> | <a href='reports.php'>View Reports</a> | <a href='index.php'>Back to Dashboard</a></p>";

$conn->close();
?>
