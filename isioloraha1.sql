-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 05:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `isioloraha`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'Login', 'User logged in successfully', '::1', '2025-05-16 08:24:20'),
(2, 1, 'Logout', '[info] User logged out successfully', '::1', '2025-05-16 11:02:20'),
(3, 3, 'Login', '[info] User logged in successfully', '::1', '2025-05-16 11:05:43'),
(4, 1, 'Logout', '[info] User logged out successfully', '::1', '2025-05-16 11:14:39'),
(5, 3, 'Login', '[info] User logged in successfully', '::1', '2025-05-16 11:14:54'),
(6, 3, 'Payment', '[info] Verifying payment with reference: IR654C35EF', '::1', '2025-05-16 14:49:26'),
(7, 1, 'Login', '[info] User logged in successfully', '::1', '2025-05-17 06:08:56'),
(8, NULL, 'Admin', '[info] Deleted schedule: Nakuru to Nairobi on 2025-05-20 with Isiolo Luxury', '::1', '2025-05-17 08:34:57'),
(9, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:43:16'),
(10, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:43:33'),
(11, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:45:32'),
(12, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:45:35'),
(13, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:46:31'),
(14, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:46:40'),
(15, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:48:49'),
(16, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:49:00'),
(17, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:51:15'),
(18, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:52:42'),
(19, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:55:23'),
(20, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:55:58'),
(21, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:57:14'),
(22, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:57:47'),
(23, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:57:58'),
(24, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 08:58:26'),
(25, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 09:00:27'),
(26, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 09:03:14'),
(27, 1, 'Reports', '[info] Generated daily report for period 2025-05-17 to 2025-05-17', '::1', '2025-05-17 09:11:49'),
(28, NULL, 'Admin', '[info] Added bus: test', '::1', '2025-05-17 09:43:17'),
(29, NULL, 'Admin', '[info] Deleted bus: test', '::1', '2025-05-17 09:43:29'),
(30, 1, 'Admin', '[info] Added route: isiolo to Nairobi', '::1', '2025-05-17 09:44:47'),
(31, NULL, 'Admin', '[info] Deleted schedule: Eldoret to Nairobi on 2025-05-16 with Isiolo Premier', '::1', '2025-05-17 09:47:46'),
(32, NULL, 'Admin', '[info] Deleted schedule: Nairobi to Mombasa on 2025-05-16 with Isiolo Premier', '::1', '2025-05-17 09:48:03'),
(33, NULL, 'Admin', '[info] Deleted schedule: Nairobi to Nakuru on 2025-05-16 with Isiolo Comfort', '::1', '2025-05-17 09:48:21'),
(34, NULL, 'Admin', '[info] Added bus: test', '::1', '2025-05-17 09:49:35'),
(35, NULL, 'Admin', '[info] Added schedule: isiolo to Nairobi on 2025-05-17 with test', '::1', '2025-05-17 09:50:12'),
(36, 3, 'Login', '[info] User logged in successfully', '::1', '2025-05-17 09:52:05'),
(37, 3, 'Payment', '[info] Verifying Paystack payment with reference: IR9D407196', '::1', '2025-05-17 11:17:09'),
(38, 3, 'Payment', '[info] Verifying Paystack payment with reference: IR9D407196', '::1', '2025-05-17 11:20:11'),
(39, 3, 'Payment', '[info] Verifying Paystack payment with reference: IR9D407196', '::1', '2025-05-17 11:22:39'),
(40, 3, 'Payment', '[info] Using test mode for Paystack verification with reference: IR9D407196', '::1', '2025-05-17 11:22:39'),
(41, 3, 'Booking', '[info] Booking completed successfully with reference: IR9D407196 via Paystack', '::1', '2025-05-17 11:22:39'),
(42, 3, 'Payment', '[info] Verifying Paystack payment with reference: IRE1642671', '::1', '2025-05-17 12:56:35'),
(43, 3, 'Payment', '[info] Using test mode for Paystack verification with reference: IRE1642671', '::1', '2025-05-17 12:56:35'),
(44, 3, 'Booking', '[info] Booking completed successfully with reference: IRE1642671 via Paystack', '::1', '2025-05-17 12:56:36'),
(45, 1, 'Login', '[info] User logged in successfully', '::1', '2025-05-17 14:16:01'),
(46, NULL, 'Admin', '[info] Created booking for testdmin with reference: IRBA3F2779', '::1', '2025-05-17 14:32:02'),
(47, NULL, 'Admin', '[info] Created booking for testdmin with reference: IR4B16CA96', '::1', '2025-05-17 14:32:32'),
(48, NULL, 'Admin', '[info] Created booking for testdmin with reference: IR6CCBF20C', '::1', '2025-05-17 14:32:56'),
(49, NULL, 'Admin', '[info] Created booking for qwer with reference: IRDD964AA4', '::1', '2025-05-17 14:35:14'),
(50, 1, 'Admin', '[info] Viewing booking details with ID: 2', '::1', '2025-05-17 14:38:27'),
(51, 1, 'Admin', '[info] Viewing booking details with Reference: IRE1642671', '::1', '2025-05-17 14:39:13'),
(52, 1, 'Admin', '[info] Viewing booking details with Reference: IRDD964AA4', '::1', '2025-05-17 14:39:51'),
(53, NULL, 'Admin', '[info] Created booking for qwer with reference: IRDABC16DA', '::1', '2025-05-17 14:40:08'),
(54, NULL, 'Admin', '[info] Created booking ID: 0 with reference: IRDABC16DA', '::1', '2025-05-17 14:40:08'),
(55, NULL, 'Admin', '[info] Created booking for testdmin with reference: IR33BBB60E', '::1', '2025-05-17 14:40:54'),
(56, NULL, 'Admin', '[info] Created booking ID: 0 with reference: IR33BBB60E', '::1', '2025-05-17 14:40:54'),
(57, NULL, 'Admin', '[info] Created booking for testdmin with reference: IR6228A2C4', '::1', '2025-05-17 14:45:00'),
(58, NULL, 'Admin', '[info] Created booking ID: 0 with reference: IR6228A2C4', '::1', '2025-05-17 14:45:00'),
(59, 1, 'Admin', '[info] Viewing booking details with Reference: IR6228A2C4', '::1', '2025-05-17 14:47:03'),
(61, NULL, 'Admin', '[error] Error creating booking: Execute failed: Column \'user_id\' cannot be null', '::1', '2025-05-17 14:53:31'),
(62, 1, 'Admin', '[info] Attempting to create booking with data: Reference: IR6734D644, User ID: 1, Schedule ID: 79, Seat: 5, Passenger: testdmin', '::1', '2025-05-17 14:55:05'),
(63, 1, 'Admin', '[info] Successfully inserted booking and payment records. Booking ID: 3', '::1', '2025-05-17 14:55:05'),
(64, 1, 'Admin', '[info] Created booking for testdmin with reference: IR6734D644', '::1', '2025-05-17 14:55:05'),
(65, 1, 'Admin', '[info] Created booking ID: 3 with reference: IR6734D644', '::1', '2025-05-17 14:55:05'),
(66, 1, 'Admin', '[info] Viewing booking details with Reference: IR6734D644', '::1', '2025-05-17 14:55:19'),
(67, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-17 14:55:51'),
(68, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-17 14:57:46'),
(69, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-17 15:02:11'),
(70, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-17 15:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) UNSIGNED NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `schedule_id` int(11) UNSIGNED NOT NULL,
  `seat_number` varchar(10) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `passenger_phone` varchar(20) NOT NULL,
  `passenger_id_number` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `schedule_id`, `seat_number`, `passenger_name`, `passenger_phone`, `passenger_id_number`, `amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 'IR9D407196', 3, 161, 'B3', 'Hassan adan', '0712345678', '5467845', 800.00, 'confirmed', '2025-05-17 11:22:39', '2025-05-17 11:22:39'),
(2, 'IRE1642671', 3, 3, 'A3', 'Hassan adan', '0712345678', '2345675', 200.00, 'confirmed', '2025-05-17 12:56:35', '2025-05-17 12:56:35'),
(3, 'IR6734D644', 1, 79, '5', 'testdmin', '0783456789', '68464846', 60.00, 'confirmed', '2025-05-17 14:55:05', '2025-05-17 14:55:05');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `registration_number` varchar(20) NOT NULL,
  `capacity` int(3) NOT NULL,
  `type` enum('standard','executive','luxury') DEFAULT 'standard',
  `amenities` text DEFAULT NULL,
  `status` enum('active','maintenance','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`id`, `name`, `registration_number`, `capacity`, `type`, `amenities`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Isiolo Express', 'KBZ 123A', 44, 'standard', 'Air Conditioning, Reclining Seats', 'active', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(2, 'Isiolo Luxury', 'KCB 456B', 36, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments', 'active', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(3, 'Isiolo Comfort', 'KDC 789C', 40, 'executive', 'Air Conditioning, Reclining Seats, WiFi, USB Charging', 'active', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(4, 'Isiolo Swift', 'KEF 012D', 44, 'standard', 'Air Conditioning, Reclining Seats', 'active', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(5, 'Isiolo Premier', 'KFG 345E', 36, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments', 'active', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(8, 'test', 'test 123', 70, 'executive', 'Air Conditioning, Reclining Seats, WiFi, Refreshments, Onboard Entertainment, Reading Lights', 'active', '2025-05-17 09:49:35', '2025-05-17 09:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','responded') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `name`, `email`, `subject`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'hassan', 'hassan@gmail.com', 'help', 'Book a ticket:', 'read', '2025-05-17 09:27:47', '2025-05-17 09:28:06');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `booking_id` int(11) UNSIGNED NOT NULL,
  `transaction_reference` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('paystack','cash','mpesa') NOT NULL,
  `status` enum('pending','successful','failed') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'KES',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `transaction_reference`, `amount`, `payment_method`, `status`, `payment_date`, `currency`, `created_at`, `updated_at`) VALUES
(1, 1, 'IR9D407196', 800.00, 'paystack', 'successful', '2025-05-17 11:22:39', 'KES', '2025-05-17 11:22:39', '2025-05-17 11:22:39'),
(2, 2, 'IRE1642671', 200.00, 'paystack', 'successful', '2025-05-17 12:56:35', 'KES', '2025-05-17 12:56:35', '2025-05-17 12:56:35'),
(10, 3, 'CASH-IR6734D644', 60.00, 'cash', 'successful', '2025-05-17 14:55:05', 'KES', '2025-05-17 14:55:05', '2025-05-17 14:55:05');

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` int(11) UNSIGNED NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `distance` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `origin`, `destination`, `distance`, `duration`, `created_at`, `updated_at`) VALUES
(1, 'Nairobi', 'Mombasa', 485.00, 420, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(2, 'Nairobi', 'Kisumu', 340.00, 360, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(3, 'Nairobi', 'Nakuru', 160.00, 120, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(4, 'Mombasa', 'Nairobi', 485.00, 420, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(5, 'Kisumu', 'Nairobi', 340.00, 360, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(6, 'Nakuru', 'Nairobi', 160.00, 120, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(7, 'Nairobi', 'Eldoret', 320.00, 300, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(8, 'Eldoret', 'Nairobi', 320.00, 300, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(9, 'Mombasa', 'Malindi', 120.00, 90, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(10, 'Malindi', 'Mombasa', 120.00, 90, '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(12, 'isiolo', 'Nairobi', 500.00, 240, '2025-05-17 09:44:47', '2025-05-17 09:44:47');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) UNSIGNED NOT NULL,
  `route_id` int(11) UNSIGNED NOT NULL,
  `bus_id` int(11) UNSIGNED NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `status` enum('scheduled','departed','arrived','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `route_id`, `bus_id`, `departure_time`, `arrival_time`, `fare`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-05-17 08:00:00', '2025-05-17 15:00:00', 250.00, 'scheduled', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(2, 1, 3, '2025-05-17 14:00:00', '2025-05-17 21:00:00', 300.00, 'scheduled', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(3, 2, 2, '2025-05-17 08:00:00', '2025-05-17 14:00:00', 200.00, 'scheduled', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(4, 2, 4, '2025-05-17 14:00:00', '2025-05-17 20:00:00', 180.00, 'scheduled', '2025-05-16 07:12:45', '2025-05-16 07:12:45'),
(5, 3, 5, '2025-05-18 08:00:00', '2025-05-18 10:00:00', 100.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(6, 3, 1, '2025-05-18 14:00:00', '2025-05-18 16:00:00', 100.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(7, 4, 2, '2025-05-18 08:00:00', '2025-05-18 15:00:00', 250.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(8, 4, 3, '2025-05-18 14:00:00', '2025-05-18 21:00:00', 300.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(9, 5, 4, '2025-05-19 08:00:00', '2025-05-19 14:00:00', 200.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(10, 5, 5, '2025-05-19 14:00:00', '2025-05-19 20:00:00', 180.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(11, 6, 1, '2025-05-19 08:00:00', '2025-05-19 10:00:00', 100.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(12, 6, 2, '2025-05-19 14:00:00', '2025-05-19 16:00:00', 100.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(13, 7, 3, '2025-05-20 08:00:00', '2025-05-20 13:00:00', 180.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(14, 7, 4, '2025-05-20 14:00:00', '2025-05-20 19:00:00', 180.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(15, 8, 5, '2025-05-20 08:00:00', '2025-05-20 13:00:00', 180.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(16, 8, 1, '2025-05-20 14:00:00', '2025-05-20 19:00:00', 180.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(17, 9, 2, '2025-05-21 08:00:00', '2025-05-21 09:30:00', 80.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(18, 9, 3, '2025-05-21 14:00:00', '2025-05-21 15:30:00', 80.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(19, 10, 4, '2025-05-21 08:00:00', '2025-05-21 09:30:00', 80.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(20, 10, 5, '2025-05-21 14:00:00', '2025-05-21 15:30:00', 80.00, 'scheduled', '2025-05-16 07:12:46', '2025-05-16 07:12:46'),
(22, 1, 3, '2025-05-16 14:00:00', '2025-05-16 21:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(23, 2, 3, '2025-05-16 08:00:00', '2025-05-16 14:00:00', 220.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(24, 2, 5, '2025-05-16 14:00:00', '2025-05-16 20:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(26, 3, 4, '2025-05-16 14:00:00', '2025-05-16 16:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(27, 4, 3, '2025-05-16 08:00:00', '2025-05-16 15:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(28, 4, 4, '2025-05-16 14:00:00', '2025-05-16 21:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(29, 5, 5, '2025-05-16 08:00:00', '2025-05-16 14:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(30, 5, 3, '2025-05-16 14:00:00', '2025-05-16 20:00:00', 220.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(31, 6, 3, '2025-05-16 08:00:00', '2025-05-16 10:00:00', 100.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(32, 6, 1, '2025-05-16 14:00:00', '2025-05-16 16:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(33, 7, 5, '2025-05-16 08:00:00', '2025-05-16 13:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(34, 7, 3, '2025-05-16 14:00:00', '2025-05-16 19:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(36, 8, 1, '2025-05-16 14:00:00', '2025-05-16 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(37, 9, 3, '2025-05-16 08:00:00', '2025-05-16 09:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(38, 9, 2, '2025-05-16 14:00:00', '2025-05-16 15:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(39, 10, 3, '2025-05-16 08:00:00', '2025-05-16 09:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(40, 10, 1, '2025-05-16 14:00:00', '2025-05-16 15:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(41, 1, 1, '2025-05-17 08:00:00', '2025-05-17 15:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(42, 1, 3, '2025-05-17 14:00:00', '2025-05-17 21:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(43, 2, 1, '2025-05-17 08:00:00', '2025-05-17 14:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(44, 2, 1, '2025-05-17 14:00:00', '2025-05-17 20:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(45, 3, 2, '2025-05-17 08:00:00', '2025-05-17 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(46, 3, 3, '2025-05-17 14:00:00', '2025-05-17 16:00:00', 100.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(47, 4, 5, '2025-05-17 08:00:00', '2025-05-17 15:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(48, 4, 4, '2025-05-17 14:00:00', '2025-05-17 21:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(49, 5, 1, '2025-05-17 08:00:00', '2025-05-17 14:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(50, 5, 4, '2025-05-17 14:00:00', '2025-05-17 20:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(51, 6, 1, '2025-05-17 08:00:00', '2025-05-17 10:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(52, 6, 5, '2025-05-17 14:00:00', '2025-05-17 16:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(53, 7, 4, '2025-05-17 08:00:00', '2025-05-17 13:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(54, 7, 2, '2025-05-17 14:00:00', '2025-05-17 19:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(55, 8, 3, '2025-05-17 08:00:00', '2025-05-17 13:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(56, 8, 1, '2025-05-17 14:00:00', '2025-05-17 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(57, 9, 3, '2025-05-17 08:00:00', '2025-05-17 09:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(58, 9, 5, '2025-05-17 14:00:00', '2025-05-17 15:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(59, 10, 2, '2025-05-17 08:00:00', '2025-05-17 09:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(60, 10, 3, '2025-05-17 14:00:00', '2025-05-17 15:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(61, 1, 3, '2025-05-18 08:00:00', '2025-05-18 15:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(62, 1, 3, '2025-05-18 14:00:00', '2025-05-18 21:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(63, 2, 4, '2025-05-18 08:00:00', '2025-05-18 14:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(64, 2, 4, '2025-05-18 14:00:00', '2025-05-18 20:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(65, 3, 2, '2025-05-18 08:00:00', '2025-05-18 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(66, 3, 1, '2025-05-18 14:00:00', '2025-05-18 16:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(67, 4, 3, '2025-05-18 08:00:00', '2025-05-18 15:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(68, 4, 2, '2025-05-18 14:00:00', '2025-05-18 21:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(69, 5, 3, '2025-05-18 08:00:00', '2025-05-18 14:00:00', 220.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(70, 5, 3, '2025-05-18 14:00:00', '2025-05-18 20:00:00', 220.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(71, 6, 1, '2025-05-18 08:00:00', '2025-05-18 10:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(72, 6, 1, '2025-05-18 14:00:00', '2025-05-18 16:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(73, 7, 3, '2025-05-18 08:00:00', '2025-05-18 13:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(74, 7, 5, '2025-05-18 14:00:00', '2025-05-18 19:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(75, 8, 2, '2025-05-18 08:00:00', '2025-05-18 13:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(76, 8, 3, '2025-05-18 14:00:00', '2025-05-18 19:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(77, 9, 3, '2025-05-18 08:00:00', '2025-05-18 09:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(78, 9, 4, '2025-05-18 14:00:00', '2025-05-18 15:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(79, 10, 4, '2025-05-18 08:00:00', '2025-05-18 09:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(80, 10, 3, '2025-05-18 14:00:00', '2025-05-18 15:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(81, 1, 3, '2025-05-19 08:00:00', '2025-05-19 15:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(82, 1, 2, '2025-05-19 14:00:00', '2025-05-19 21:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(83, 2, 1, '2025-05-19 08:00:00', '2025-05-19 14:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(84, 2, 2, '2025-05-19 14:00:00', '2025-05-19 20:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(85, 3, 5, '2025-05-19 08:00:00', '2025-05-19 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(86, 3, 3, '2025-05-19 14:00:00', '2025-05-19 16:00:00', 100.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(87, 4, 2, '2025-05-19 08:00:00', '2025-05-19 15:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(88, 4, 3, '2025-05-19 14:00:00', '2025-05-19 21:00:00', 320.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(89, 5, 5, '2025-05-19 08:00:00', '2025-05-19 14:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(90, 5, 2, '2025-05-19 14:00:00', '2025-05-19 20:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(91, 6, 3, '2025-05-19 08:00:00', '2025-05-19 10:00:00', 100.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(92, 6, 4, '2025-05-19 14:00:00', '2025-05-19 16:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(93, 7, 2, '2025-05-19 08:00:00', '2025-05-19 13:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(94, 7, 4, '2025-05-19 14:00:00', '2025-05-19 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(95, 8, 2, '2025-05-19 08:00:00', '2025-05-19 13:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(96, 8, 1, '2025-05-19 14:00:00', '2025-05-19 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(97, 9, 5, '2025-05-19 08:00:00', '2025-05-19 09:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(98, 9, 4, '2025-05-19 14:00:00', '2025-05-19 15:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(99, 10, 4, '2025-05-19 08:00:00', '2025-05-19 09:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(100, 10, 1, '2025-05-19 14:00:00', '2025-05-19 15:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(101, 1, 5, '2025-05-20 08:00:00', '2025-05-20 15:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(102, 1, 5, '2025-05-20 14:00:00', '2025-05-20 21:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(103, 2, 1, '2025-05-20 08:00:00', '2025-05-20 14:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(104, 2, 5, '2025-05-20 14:00:00', '2025-05-20 20:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(105, 3, 2, '2025-05-20 08:00:00', '2025-05-20 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(106, 3, 5, '2025-05-20 14:00:00', '2025-05-20 16:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(107, 4, 1, '2025-05-20 08:00:00', '2025-05-20 15:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(108, 4, 1, '2025-05-20 14:00:00', '2025-05-20 21:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(109, 5, 1, '2025-05-20 08:00:00', '2025-05-20 14:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(110, 5, 2, '2025-05-20 14:00:00', '2025-05-20 20:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(111, 6, 5, '2025-05-20 08:00:00', '2025-05-20 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(113, 7, 5, '2025-05-20 08:00:00', '2025-05-20 13:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(114, 7, 4, '2025-05-20 14:00:00', '2025-05-20 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(115, 8, 3, '2025-05-20 08:00:00', '2025-05-20 13:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(116, 8, 3, '2025-05-20 14:00:00', '2025-05-20 19:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(117, 9, 2, '2025-05-20 08:00:00', '2025-05-20 09:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(118, 9, 1, '2025-05-20 14:00:00', '2025-05-20 15:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(119, 10, 2, '2025-05-20 08:00:00', '2025-05-20 09:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(120, 10, 3, '2025-05-20 14:00:00', '2025-05-20 15:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(121, 1, 1, '2025-05-21 08:00:00', '2025-05-21 15:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(122, 1, 2, '2025-05-21 14:00:00', '2025-05-21 21:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(123, 2, 1, '2025-05-21 08:00:00', '2025-05-21 14:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(124, 2, 4, '2025-05-21 14:00:00', '2025-05-21 20:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(125, 3, 5, '2025-05-21 08:00:00', '2025-05-21 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(126, 3, 3, '2025-05-21 14:00:00', '2025-05-21 16:00:00', 100.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(127, 4, 4, '2025-05-21 08:00:00', '2025-05-21 15:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(128, 4, 2, '2025-05-21 14:00:00', '2025-05-21 21:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(129, 5, 2, '2025-05-21 08:00:00', '2025-05-21 14:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(130, 5, 1, '2025-05-21 14:00:00', '2025-05-21 20:00:00', 170.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(131, 6, 4, '2025-05-21 08:00:00', '2025-05-21 10:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(132, 6, 3, '2025-05-21 14:00:00', '2025-05-21 16:00:00', 100.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(133, 7, 3, '2025-05-21 08:00:00', '2025-05-21 13:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(134, 7, 1, '2025-05-21 14:00:00', '2025-05-21 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(135, 8, 2, '2025-05-21 08:00:00', '2025-05-21 13:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(136, 8, 1, '2025-05-21 14:00:00', '2025-05-21 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(137, 9, 5, '2025-05-21 08:00:00', '2025-05-21 09:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(138, 9, 3, '2025-05-21 14:00:00', '2025-05-21 15:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(139, 10, 4, '2025-05-21 08:00:00', '2025-05-21 09:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(140, 10, 4, '2025-05-21 14:00:00', '2025-05-21 15:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(141, 1, 2, '2025-05-22 08:00:00', '2025-05-22 15:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(142, 1, 5, '2025-05-22 14:00:00', '2025-05-22 21:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(143, 2, 2, '2025-05-22 08:00:00', '2025-05-22 14:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(144, 2, 5, '2025-05-22 14:00:00', '2025-05-22 20:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(145, 3, 5, '2025-05-22 08:00:00', '2025-05-22 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(146, 3, 5, '2025-05-22 14:00:00', '2025-05-22 16:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(147, 4, 2, '2025-05-22 08:00:00', '2025-05-22 15:00:00', 360.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(148, 4, 1, '2025-05-22 14:00:00', '2025-05-22 21:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(149, 5, 3, '2025-05-22 08:00:00', '2025-05-22 14:00:00', 220.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(150, 5, 2, '2025-05-22 14:00:00', '2025-05-22 20:00:00', 260.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(151, 6, 2, '2025-05-22 08:00:00', '2025-05-22 10:00:00', 120.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(152, 6, 4, '2025-05-22 14:00:00', '2025-05-22 16:00:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(153, 7, 1, '2025-05-22 08:00:00', '2025-05-22 13:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(154, 7, 4, '2025-05-22 14:00:00', '2025-05-22 19:00:00', 160.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(155, 8, 3, '2025-05-22 08:00:00', '2025-05-22 13:00:00', 210.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(156, 8, 5, '2025-05-22 14:00:00', '2025-05-22 19:00:00', 240.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(157, 9, 1, '2025-05-22 08:00:00', '2025-05-22 09:30:00', 60.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(158, 9, 3, '2025-05-22 14:00:00', '2025-05-22 15:30:00', 80.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(159, 10, 5, '2025-05-22 08:00:00', '2025-05-22 09:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(160, 10, 2, '2025-05-22 14:00:00', '2025-05-22 15:30:00', 90.00, 'scheduled', '2025-05-16 08:21:09', '2025-05-16 08:21:09'),
(161, 12, 8, '2025-05-17 12:49:00', '2025-05-17 17:50:00', 800.00, 'scheduled', '2025-05-17 09:50:12', '2025-05-17 09:50:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`, `reset_token`, `reset_token_expires`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@isioloraha.com', '0700000000', '$2y$10$ehPmEjVqKCH64OxqJshJ.uy4PP8F1.kSttIYUhyq1234/MEKZ3eAq', 'admin', NULL, NULL, '2025-05-16 07:12:45', '2025-05-16 08:23:31'),
(3, 'Hassan', 'adan', 'hassan@gmail.com', '0712345678', '$2y$10$8Q7LiVa3cXI2HeOGmFDiyu8OQeMGVOQ55hIqKpt768//ZBgJAm26a', 'user', NULL, NULL, '2025-05-16 11:03:27', '2025-05-16 11:03:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_reference` (`transaction_reference`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `origin_destination` (`origin`,`destination`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `bus_id` (`bus_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
