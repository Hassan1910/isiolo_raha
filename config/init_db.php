<?php
/**
 * Database Initialization Script
 * 
 * This script creates all the necessary tables for the Isiolo Raha Bus Booking System
 */

// Include database connection
$conn = require_once 'database.php';

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
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
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Create buses table
$sql = "CREATE TABLE IF NOT EXISTS buses (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    registration_number VARCHAR(20) NOT NULL UNIQUE,
    capacity INT(3) NOT NULL,
    type ENUM('standard', 'executive', 'luxury') DEFAULT 'standard',
    amenities TEXT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating buses table: " . $conn->error);
}

// Create routes table
$sql = "CREATE TABLE IF NOT EXISTS routes (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    distance DECIMAL(10,2) NULL,
    duration INT(11) NULL COMMENT 'Duration in minutes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY origin_destination (origin, destination)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating routes table: " . $conn->error);
}

// Create schedules table
$sql = "CREATE TABLE IF NOT EXISTS schedules (
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
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating schedules table: " . $conn->error);
}

// Create bookings table
$sql = "CREATE TABLE IF NOT EXISTS bookings (
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
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating bookings table: " . $conn->error);
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT(11) UNSIGNED NOT NULL,
    transaction_reference VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('paystack', 'cash', 'mpesa') NOT NULL,
    status ENUM('pending', 'successful', 'failed') DEFAULT 'pending',
    payment_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating payments table: " . $conn->error);
}

// Create feedback table
$sql = "CREATE TABLE IF NOT EXISTS feedback (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'responded') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating feedback table: " . $conn->error);
}

// Create activity_logs table
$sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating activity_logs table: " . $conn->error);
}

// Insert default admin user
$adminPassword = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
$sql = "INSERT INTO users (first_name, last_name, email, phone, password, role) 
        VALUES ('Admin', 'User', 'admin@isioloraha.com', '0700000000', ?, 'admin')
        ON DUPLICATE KEY UPDATE id=id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminPassword);

if ($stmt->execute() === FALSE) {
    die("Error creating default admin user: " . $stmt->error);
}

echo "Database initialization completed successfully!";
