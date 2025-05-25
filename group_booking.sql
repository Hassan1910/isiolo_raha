-- Group Booking Table for Isiolo Raha Bus Booking System

-- Create group_bookings table
CREATE TABLE IF NOT EXISTS group_bookings (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    user_id INT(11) UNSIGNED NOT NULL,
    schedule_id INT(11) UNSIGNED NOT NULL,
    group_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    contact_email VARCHAR(100) NULL,
    total_passengers INT(3) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
);

-- Add group_booking_id column to bookings table
ALTER TABLE bookings ADD COLUMN group_booking_id INT(11) UNSIGNED NULL AFTER booking_reference;
ALTER TABLE bookings ADD CONSTRAINT fk_group_booking FOREIGN KEY (group_booking_id) REFERENCES group_bookings(id) ON DELETE SET NULL;
