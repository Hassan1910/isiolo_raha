-- Isiolo Raha Bus Booking System Database
-- Created based on the PHP initialization scripts

-- Drop database if it exists and create a new one
DROP DATABASE IF EXISTS isioloraha;
CREATE DATABASE isioloraha;
USE isioloraha;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
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
);

-- Create buses table
CREATE TABLE IF NOT EXISTS buses (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    registration_number VARCHAR(20) NOT NULL UNIQUE,
    capacity INT(3) NOT NULL,
    type ENUM('standard', 'executive', 'luxury') DEFAULT 'standard',
    amenities TEXT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create routes table
CREATE TABLE IF NOT EXISTS routes (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    distance DECIMAL(10,2) NULL,
    duration INT(11) NULL COMMENT 'Duration in minutes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY origin_destination (origin, destination)
);

-- Create schedules table
CREATE TABLE IF NOT EXISTS schedules (
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
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
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
);

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
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
);

-- Create feedback table
CREATE TABLE IF NOT EXISTS feedback (
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
);

-- Create activity_logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user
-- Password: admin123 (hashed with BCRYPT)
INSERT INTO users (first_name, last_name, email, phone, password, role)
VALUES ('Admin', 'User', 'admin@isioloraha.com', '0700000000', '$2y$10$Ql9Xj.Rh9NyP4xEZpbF0eeHQOYOdnKGBFEKWF9bQCYLYt0q/yOzHu', 'admin');

-- Insert sample buses
INSERT INTO buses (name, registration_number, capacity, type, amenities, status) VALUES
('Isiolo Express', 'KBZ 123A', 44, 'standard', 'Air Conditioning, Reclining Seats', 'active'),
('Isiolo Luxury', 'KCB 456B', 36, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments', 'active'),
('Isiolo Comfort', 'KDC 789C', 40, 'executive', 'Air Conditioning, Reclining Seats, WiFi, USB Charging', 'active'),
('Isiolo Swift', 'KEF 012D', 44, 'standard', 'Air Conditioning, Reclining Seats', 'active'),
('Isiolo Premier', 'KFG 345E', 36, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments', 'active');

-- Insert sample routes
INSERT INTO routes (origin, destination, distance, duration) VALUES
('Nairobi', 'Mombasa', 485, 420),
('Nairobi', 'Kisumu', 340, 360),
('Nairobi', 'Nakuru', 160, 120),
('Mombasa', 'Nairobi', 485, 420),
('Kisumu', 'Nairobi', 340, 360),
('Nakuru', 'Nairobi', 160, 120),
('Nairobi', 'Eldoret', 320, 300),
('Eldoret', 'Nairobi', 320, 300),
('Mombasa', 'Malindi', 120, 90),
('Malindi', 'Mombasa', 120, 90);

-- Insert sample schedules for the next 7 days
-- Note: These are example schedules with fixed dates. You may need to update them.
-- The following schedules are for demonstration purposes only.

-- Day 1 schedules
INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status) VALUES
(1, 1, '2025-05-17 08:00:00', '2025-05-17 15:00:00', 250, 'scheduled'),
(1, 3, '2025-05-17 14:00:00', '2025-05-17 21:00:00', 300, 'scheduled'),
(2, 2, '2025-05-17 08:00:00', '2025-05-17 14:00:00', 200, 'scheduled'),
(2, 4, '2025-05-17 14:00:00', '2025-05-17 20:00:00', 180, 'scheduled');

-- Day 2 schedules
INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status) VALUES
(3, 5, '2025-05-18 08:00:00', '2025-05-18 10:00:00', 100, 'scheduled'),
(3, 1, '2025-05-18 14:00:00', '2025-05-18 16:00:00', 100, 'scheduled'),
(4, 2, '2025-05-18 08:00:00', '2025-05-18 15:00:00', 250, 'scheduled'),
(4, 3, '2025-05-18 14:00:00', '2025-05-18 21:00:00', 300, 'scheduled');

-- Day 3 schedules
INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status) VALUES
(5, 4, '2025-05-19 08:00:00', '2025-05-19 14:00:00', 200, 'scheduled'),
(5, 5, '2025-05-19 14:00:00', '2025-05-19 20:00:00', 180, 'scheduled'),
(6, 1, '2025-05-19 08:00:00', '2025-05-19 10:00:00', 100, 'scheduled'),
(6, 2, '2025-05-19 14:00:00', '2025-05-19 16:00:00', 100, 'scheduled');

-- Day 4 schedules
INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status) VALUES
(7, 3, '2025-05-20 08:00:00', '2025-05-20 13:00:00', 180, 'scheduled'),
(7, 4, '2025-05-20 14:00:00', '2025-05-20 19:00:00', 180, 'scheduled'),
(8, 5, '2025-05-20 08:00:00', '2025-05-20 13:00:00', 180, 'scheduled'),
(8, 1, '2025-05-20 14:00:00', '2025-05-20 19:00:00', 180, 'scheduled');

-- Day 5 schedules
INSERT INTO schedules (route_id, bus_id, departure_time, arrival_time, fare, status) VALUES
(9, 2, '2025-05-21 08:00:00', '2025-05-21 09:30:00', 80, 'scheduled'),
(9, 3, '2025-05-21 14:00:00', '2025-05-21 15:30:00', 80, 'scheduled'),
(10, 4, '2025-05-21 08:00:00', '2025-05-21 09:30:00', 80, 'scheduled'),
(10, 5, '2025-05-21 14:00:00', '2025-05-21 15:30:00', 80, 'scheduled');
