-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 25, 2025 at 03:50 PM
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
(70, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-17 15:03:54'),
(71, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-18 06:32:34'),
(72, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-18 06:34:27'),
(73, 1, 'Admin', '[info] Attempting to create booking with data: Reference: IR6AA633AB, User ID: 1, Schedule ID: 108, Seat: 9, Passenger: qwer', '::1', '2025-05-18 06:35:56'),
(74, 1, 'Admin', '[info] Successfully inserted booking and payment records. Booking ID: 4', '::1', '2025-05-18 06:35:56'),
(75, 1, 'Admin', '[info] Created booking for qwer with reference: IR6AA633AB', '::1', '2025-05-18 06:35:56'),
(76, 1, 'Admin', '[info] Created booking ID: 4 with reference: IR6AA633AB', '::1', '2025-05-18 06:35:56'),
(77, 1, 'Admin', '[info] Viewing booking details with Reference: IR6AA633AB', '::1', '2025-05-18 06:51:07'),
(78, 1, 'Admin', '[info] Viewing booking details with Reference: IR6AA633AB', '::1', '2025-05-18 06:51:14'),
(79, 1, 'Admin', '[info] Viewing booking details with ID: 4', '::1', '2025-05-18 06:51:37'),
(80, 1, 'Admin', '[info] Viewing booking details with ID: 3', '::1', '2025-05-18 06:59:36'),
(81, 1, 'Admin', '[info] Viewing booking details with ID: 4', '::1', '2025-05-18 07:07:58'),
(82, 1, 'Admin', '[info] Viewing booking details with ID: 4', '::1', '2025-05-18 07:20:01'),
(83, 1, 'Admin', '[info] Viewing booking details with ID: 4', '::1', '2025-05-18 07:22:07'),
(84, 1, 'Admin', '[info] Viewing booking details with ID: 4', '::1', '2025-05-18 07:23:00'),
(85, 3, 'Logout', '[info] User logged out successfully', '::1', '2025-05-18 07:24:14'),
(86, 3, 'Login', '[info] User logged in successfully', '::1', '2025-05-18 08:20:43'),
(87, 1, 'Admin', '[info] Viewing booking details with ID: 4', '::1', '2025-05-18 08:21:37'),
(88, 1, 'Logout', '[info] User logged out successfully', '::1', '2025-05-18 08:48:07'),
(89, 1, 'Login', '[info] User logged in successfully', '::1', '2025-05-18 08:48:39'),
(90, 1, 'Reports', '[info] Generated daily report for period 2025-05-18 to 2025-05-18', '::1', '2025-05-18 09:06:43'),
(91, 3, 'Logout', '[info] User logged out successfully', '::1', '2025-05-18 09:17:11'),
(92, 3, 'Login', '[info] User logged in successfully', '::1', '2025-05-18 09:18:51'),
(93, 3, 'Payment', '[info] Verifying Paystack payment with reference: IR192EBCE5', '::1', '2025-05-18 09:19:40'),
(94, 3, 'Payment', '[info] Using test mode for Paystack verification with reference: IR192EBCE5', '::1', '2025-05-18 09:19:40'),
(95, 3, 'Booking', '[info] Booking completed successfully with reference: IR192EBCE5 via Paystack', '::1', '2025-05-18 09:19:40'),
(96, 1, 'Reports', '[info] Generated daily report for period 2025-05-18 to 2025-05-18', '::1', '2025-05-18 11:22:07'),
(97, 1, 'Login', '[info] User logged in successfully', '::1', '2025-05-19 09:54:03'),
(98, 1, 'Login', '[info] User logged in successfully', '::1', '2025-05-23 09:06:27'),
(99, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-06-16 06:45:59'),
(100, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-06-16 06:49:21'),
(101, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-06-16 07:01:48'),
(102, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-06-16 07:03:04'),
(103, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-06-16 07:05:12'),
(104, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-06-16 07:05:19'),
(105, 1, 'Login', '[info] User logged in successfully', '::1', '2025-06-16 07:06:04'),
(106, NULL, 'Admin', '[info] Deleted schedule: Eldoret to Nairobi on 2025-05-22 with Isiolo Premier', '::1', '2025-06-16 07:07:13'),
(107, NULL, 'Admin', '[info] Deleted schedule: Nairobi to Mombasa on 2025-05-22 with Isiolo Premier', '::1', '2025-06-16 07:12:11'),
(108, NULL, 'Admin', '[info] Added schedule: isiolo to Nairobi on 2025-06-16 with Isiolo Comfort', '::1', '2025-06-16 07:15:01'),
(109, 3, 'Login', '[info] User logged in successfully', '::1', '2025-06-16 07:17:27'),
(110, 3, 'Payment', '[info] Verifying Paystack payment with reference: IR3C8DAE5C', '::1', '2025-06-16 07:18:24'),
(111, 3, 'Payment', '[info] Using test mode for Paystack verification with reference: IR3C8DAE5C', '::1', '2025-06-16 07:18:24'),
(112, 3, 'Booking', '[info] Booking completed successfully with reference: IR3C8DAE5C via Paystack', '::1', '2025-06-16 07:18:24'),
(113, NULL, 'Admin', '[info] Deleted bus: test', '::1', '2025-06-16 07:22:02'),
(114, 1, 'Login', '[info] User logged in successfully', '::1', '2025-06-18 09:17:42'),
(115, NULL, 'Admin', '[info] Added bus: Isiolo raha 1', '::1', '2025-06-18 09:25:20'),
(116, NULL, 'Admin', '[info] Updated bus: Isiolo raha 1', '::1', '2025-06-18 09:25:43'),
(117, 1, 'Admin', '[info] Added route: Meru to Nairobi', '::1', '2025-06-18 09:27:43'),
(118, NULL, 'Admin', '[info] Added schedule: Meru to Nairobi on 2025-06-18 with Isiolo raha 1', '::1', '2025-06-18 09:30:18'),
(119, 4, 'Registration', '[info] User registered successfully', '::1', '2025-06-18 09:36:47'),
(120, 4, 'Payment', '[info] Verifying Paystack payment with reference: IR781B092E', '::1', '2025-06-18 09:39:34'),
(121, 4, 'Payment', '[info] Using test mode for Paystack verification with reference: IR781B092E', '::1', '2025-06-18 09:39:34'),
(122, 4, 'Booking', '[info] Booking completed successfully with reference: IR781B092E via Paystack', '::1', '2025-06-18 09:39:34'),
(123, 1, 'Admin', '[info] Viewing booking details with ID: 6', '::1', '2025-06-18 09:43:50'),
(124, 1, 'Admin', '[info] Viewing booking details with ID: 7', '::1', '2025-06-18 09:44:02'),
(125, 1, 'Admin', '[info] Attempting to create booking with data: Reference: IRC721EA1D, User ID: 1, Schedule ID: 163, Seat: 2, Passenger: test2', '::1', '2025-06-18 09:46:07'),
(126, 1, 'Admin', '[info] Successfully inserted booking and payment records. Booking ID: 8', '::1', '2025-06-18 09:46:07'),
(127, 1, 'Admin', '[info] Created booking for test2 with reference: IRC721EA1D', '::1', '2025-06-18 09:46:07'),
(128, 1, 'Admin', '[info] Created booking ID: 8 with reference: IRC721EA1D', '::1', '2025-06-18 09:46:07'),
(129, 1, 'Admin', '[info] Viewing booking details with Reference: IRC721EA1D', '::1', '2025-06-18 09:46:35'),
(130, 1, 'Admin', '[info] Viewing booking details with ID: 6', '::1', '2025-06-18 10:01:25'),
(131, NULL, 'Admin', '[info] Deleted bus: Isiolo Swift', '::1', '2025-06-18 16:54:02'),
(132, NULL, 'Admin', '[info] Deleted bus: Isiolo Luxury', '::1', '2025-06-18 16:54:21'),
(133, 1, 'Admin', '[info] Deleted route: Malindi to Mombasa', '::1', '2025-06-18 16:56:49'),
(134, 1, 'Login', '[info] User logged in successfully', '::1', '2025-06-19 12:00:12'),
(135, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-16 09:27:27'),
(136, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-16 09:32:33'),
(137, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-17 06:43:53'),
(138, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-17 06:44:15'),
(139, NULL, 'Admin', '[info] Added bus: Isiolo raha 3', '::1', '2025-07-17 06:46:47'),
(140, NULL, 'Admin', '[info] Deleted bus: Isiolo raha 3', '::1', '2025-07-17 06:51:56'),
(141, NULL, 'Admin', '[info] Deleted bus: Isiolo Premier', '::1', '2025-07-17 06:52:19'),
(142, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-17 11:19:43'),
(143, 1, 'Logout', '[info] User logged out successfully', '::1', '2025-07-17 11:19:55'),
(144, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-07-17 11:20:13'),
(145, 1, 'password_reset_requested', '[info] Password reset requested for email: admin@isioloraha.com', '::1', '2025-07-17 11:30:09'),
(146, 1, 'password_reset_completed', '[info] Password reset completed successfully', '::1', '2025-07-17 11:35:48'),
(147, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-17 11:36:20'),
(148, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-19 06:23:11'),
(149, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 06:23:27'),
(150, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 06:23:58'),
(151, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 06:24:26'),
(152, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 07:22:49'),
(153, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 07:26:26'),
(154, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 07:34:40'),
(155, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 07:37:46'),
(156, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-19 08:26:20'),
(157, 1, 'Reports', '[info] Generated daily report for period 2025-07-19 to 2025-07-19', '::1', '2025-07-19 08:26:28'),
(158, 1, 'Reports', '[info] Generated daily report for period 2025-06-19 to 2025-07-19', '::1', '2025-07-19 08:31:57'),
(159, 1, 'Reports', '[info] Generated daily report for period 2025-06-19 to 2025-07-19', '::1', '2025-07-19 08:35:06'),
(160, 1, 'Reports', '[info] Generated daily report for period 2025-06-19 to 2025-07-19', '::1', '2025-07-19 08:35:33'),
(161, 1, 'Reports', '[info] Generated daily report for period 2025-06-18 to 2025-07-19', '::1', '2025-07-19 08:36:02'),
(162, 1, 'Reports', '[info] Generated daily report for period 2025-06-18 to 2025-07-19', '::1', '2025-07-19 08:41:05'),
(163, 1, 'Reports', '[info] Generated daily report for period 2025-06-18 to 2025-07-19', '::1', '2025-07-19 08:44:13'),
(164, 1, 'Reports', '[info] Generated daily report for period 2025-06-18 to 2025-07-19', '::1', '2025-07-19 09:42:55'),
(165, 5, 'Registration', '[info] User registered successfully', '::1', '2025-07-19 09:54:53'),
(166, 1, 'Feedback', '[info] Responded to feedback ID: 2', '::1', '2025-07-19 10:38:43'),
(167, NULL, 'Admin', '[info] Added schedule: Nairobi to Mombasa on 2025-07-19 with Isiolo raha 1', '::1', '2025-07-19 10:40:17'),
(168, 5, 'Payment', '[info] Verifying Paystack payment with reference: IR74E7B33C', '::1', '2025-07-19 10:43:31'),
(169, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IR74E7B33C', '::1', '2025-07-19 10:43:31'),
(170, 5, 'Payment', '[error] Error processing Paystack payment: Execute failed: Duplicate entry \'IR74E7B33C\' for key \'booking_reference\'', '::1', '2025-07-19 10:43:31'),
(171, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRDB8C55FD', '::1', '2025-07-19 10:45:52'),
(172, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRDB8C55FD', '::1', '2025-07-19 10:45:52'),
(173, 5, 'Booking', '[info] Booking completed successfully with reference: IRDB8C55FD via Paystack', '::1', '2025-07-19 10:45:52'),
(174, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRB35849B8', '::1', '2025-07-19 10:49:37'),
(175, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRB35849B8', '::1', '2025-07-19 10:49:37'),
(176, 5, 'Payment', '[error] Error processing Paystack payment: Execute failed: Duplicate entry \'IRB35849B8\' for key \'booking_reference\'', '::1', '2025-07-19 10:49:37'),
(177, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRB8E988092', '::1', '2025-07-19 10:54:12'),
(178, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRB8E988092', '::1', '2025-07-19 10:54:12'),
(179, 5, 'Payment', '[error] Error processing Paystack payment: Execute failed: Duplicate entry \'IRB8E988092\' for key \'booking_reference\'', '::1', '2025-07-19 10:54:12'),
(180, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRBB807D5C7', '::1', '2025-07-19 11:01:12'),
(181, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRBB807D5C7', '::1', '2025-07-19 11:01:12'),
(182, 5, 'Payment', '[error] Error processing Paystack payment: Execute failed: Duplicate entry \'IRBB807D5C7\' for key \'booking_reference\'', '::1', '2025-07-19 11:01:12'),
(183, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRBE7A1A913', '::1', '2025-07-19 11:16:18'),
(184, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRBE7A1A913', '::1', '2025-07-19 11:16:18'),
(185, 5, 'Payment', '[error] Error processing Paystack payment: Execute failed: Duplicate entry \'IRBE7A1A913\' for key \'booking_reference\'', '::1', '2025-07-19 11:16:18'),
(186, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-19 12:29:29'),
(187, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-20 07:54:38'),
(188, 5, 'Group Booking', '[info] Group booking created successfully with reference: IRBDED41550', '::1', '2025-07-20 09:08:46'),
(189, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-20 09:23:44'),
(190, 5, 'Group Booking', '[info] Group booking created successfully with Paystack payment. Reference: IRBD0528A61', '::1', '2025-07-20 09:48:14'),
(191, 5, 'Group Booking', '[info] Group booking created successfully with Paystack payment. Reference: IRB740C1B1C', '::1', '2025-07-20 10:55:29'),
(192, 5, 'Group Booking', '[info] Group booking created successfully with Paystack payment. Reference: IRB2A124AE1', '::1', '2025-07-20 11:05:12'),
(193, 5, 'Group Booking', '[info] Group booking created successfully with Paystack payment. Reference: IRB9DB2FCC7', '::1', '2025-07-20 11:35:55'),
(194, 5, 'Group Booking', '[info] Group booking created successfully with Paystack payment. Reference: IRBE799599C', '::1', '2025-07-20 11:48:47'),
(195, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-20 14:05:33'),
(196, NULL, 'Admin', '[info] Added schedule: Mombasa to Nairobi on 2025-07-20 with Isiolo Express', '::1', '2025-07-20 14:06:20'),
(197, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-20 14:07:44'),
(198, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-21 06:07:33'),
(199, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-21 06:08:03'),
(200, NULL, 'Admin', '[info] Added schedule: Nairobi to Mombasa on 2025-07-21 with Isiolo raha 1', '::1', '2025-07-21 06:09:56'),
(201, NULL, 'Admin', '[info] Added bus: isiolo raha 2', '::1', '2025-07-21 06:10:55'),
(202, NULL, 'Admin', '[info] Added bus: Isiolo raha 4', '::1', '2025-07-21 06:11:32'),
(203, NULL, 'Admin', '[info] Added bus: Isiolo raha 5', '::1', '2025-07-21 06:12:10'),
(204, NULL, 'Admin', '[info] Added bus: Isiolo raha 3', '::1', '2025-07-21 06:13:01'),
(205, NULL, 'Admin', '[info] Added bus: Isiolo raha 6', '::1', '2025-07-21 06:13:43'),
(206, NULL, 'Admin', '[info] Added schedule: Nairobi to Kisumu on 2025-07-22 with Isiolo raha 3', '::1', '2025-07-21 06:14:36'),
(207, NULL, 'Admin', '[info] Added schedule: Nairobi to Nakuru on 2025-07-23 with Isiolo raha 5', '::1', '2025-07-21 06:15:23'),
(208, 1, 'Reports', '[info] Generated daily report for period 2025-06-21 to 2025-07-21', '::1', '2025-07-21 06:16:25'),
(209, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-21 06:30:21'),
(210, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRB92C8B161', '::1', '2025-07-21 09:30:31'),
(211, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRB92C8B161', '::1', '2025-07-21 09:30:31'),
(212, 5, 'Payment', '[error] Error processing Paystack payment: Execute failed: Duplicate entry \'IRB92C8B161\' for key \'booking_reference\'', '::1', '2025-07-21 09:30:31'),
(213, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRB5F1CF124', '::1', '2025-07-21 09:49:33'),
(214, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRB5F1CF124', '::1', '2025-07-21 09:49:33'),
(215, 5, 'Payment', '[error] Error processing Paystack payment: Execute failed: Duplicate entry \'IRB5F1CF124\' for key \'booking_reference\'', '::1', '2025-07-21 09:49:33'),
(216, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-22 06:09:38'),
(217, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRB85DFAEC9', '::1', '2025-07-22 06:34:26'),
(218, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRB85DFAEC9', '::1', '2025-07-22 06:34:26'),
(219, 5, 'Booking', '[info] Booking completed successfully with reference: IRB85DFAEC9 via Paystack', '::1', '2025-07-22 06:34:26'),
(220, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRB55D7F13E', '::1', '2025-07-22 07:10:16'),
(221, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRB55D7F13E', '::1', '2025-07-22 07:10:16'),
(222, 5, 'Booking', '[info] Booking completed successfully with reference: IRB55D7F13E via Paystack', '::1', '2025-07-22 07:10:16'),
(223, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-22 07:11:34'),
(224, 1, 'Reports', '[info] Generated daily report for period 2025-06-22 to 2025-07-22', '::1', '2025-07-22 07:12:50'),
(225, 1, 'Reports', '[info] Generated daily report for period 2025-06-22 to 2025-07-22', '::1', '2025-07-22 07:18:38'),
(226, 1, 'Reports', '[info] Generated daily report for period 2025-06-22 to 2025-07-22', '::1', '2025-07-22 07:26:43'),
(227, 1, 'Login', '[info] User logged in successfully', '::1', '2025-07-25 11:46:01'),
(228, 5, 'Login', '[info] User logged in successfully', '::1', '2025-07-25 11:46:53'),
(229, 1, 'Feedback', '[info] Responded to feedback ID: 3', '::1', '2025-07-25 11:48:17'),
(230, 1, 'Reports', '[info] Generated daily report for period 2025-06-25 to 2025-07-25', '::1', '2025-07-25 11:50:53'),
(231, NULL, 'Admin', '[info] Deleted bus: Isiolo raha 6', '::1', '2025-07-25 11:52:59'),
(232, 1, 'Admin', '[info] Deleted bus: isiolo raha 2', '::1', '2025-07-25 11:57:24'),
(233, NULL, 'Admin', '[info] Added schedule: Mombasa to Nairobi on 2025-07-25 with Isiolo Express', '::1', '2025-07-25 12:36:28'),
(234, NULL, 'Admin', '[info] Added schedule: Mombasa to Nairobi on 2025-07-31 with Isiolo raha 1', '::1', '2025-07-25 12:43:41'),
(235, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRBBCC20CD8', '::1', '2025-07-25 12:46:12'),
(236, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRBBCC20CD8', '::1', '2025-07-25 12:46:12'),
(237, 5, 'Booking', '[info] Booking completed successfully with reference: IRBBCC20CD8 via Paystack', '::1', '2025-07-25 12:46:12'),
(238, NULL, 'Admin', '[info] Added schedule: Nairobi to Mombasa on 2025-07-31 with Isiolo raha 3', '::1', '2025-07-25 12:48:08'),
(239, 5, 'Payment', '[info] Verifying Paystack payment with reference: IRB2AFBB5D2', '::1', '2025-07-25 12:49:14'),
(240, 5, 'Payment', '[info] Using test mode for Paystack verification with reference: IRB2AFBB5D2', '::1', '2025-07-25 12:49:14'),
(241, 5, 'Booking', '[info] Booking completed successfully with reference: IRB2AFBB5D2 via Paystack', '::1', '2025-07-25 12:49:14');

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
(2, 'IRE1642671', 3, 3, 'A3', 'Hassan adan', '0712345678', '2345675', 200.00, 'confirmed', '2025-05-17 12:56:35', '2025-05-17 12:56:35'),
(7, 'IR781B092E', 4, 163, 'A1', 'Test1 test', '073-464-6464', '45735787', 1000.00, 'confirmed', '2025-06-18 09:39:34', '2025-06-18 09:39:34'),
(8, 'IRC721EA1D', 1, 163, '2', 'test2', '07345678445', '4567876', 1000.00, 'confirmed', '2025-06-18 09:46:07', '2025-06-18 09:46:07'),
(11, 'IRDB8C55FD', 5, 164, 'B5', 'hassan adan', '0734567765', '4323454', 700.00, 'confirmed', '2025-07-19 10:45:52', '2025-07-19 10:45:52'),
(20, 'IRBDED41550', 5, 163, 'A1', 'hassan', '0757489214', '23455', 1000.00, 'confirmed', '2025-07-20 09:08:46', '2025-07-20 09:08:46'),
(25, 'IRBD0528A61', 5, 163, 'C1', 'hassan', '0757489214', '3455434', 1000.00, 'confirmed', '2025-07-20 09:48:13', '2025-07-20 09:48:13'),
(27, 'IRB740C1B1C', 5, 163, 'B1', 'qwerty', '07234567765', '543456', 1000.00, 'confirmed', '2025-07-20 10:55:29', '2025-07-20 10:55:29'),
(29, 'IRB2A124AE1', 5, 163, 'A4', 'qwerty', '07234567765', '', 1000.00, 'confirmed', '2025-07-20 11:05:12', '2025-07-20 11:05:12'),
(33, 'IRB2A124AE1-P2', 5, 163, 'AUTO2', 'qwerty - Passenger 2', '07234567765', '', 1000.00, 'confirmed', '2025-07-20 11:32:01', '2025-07-20 11:32:01'),
(37, 'IRBE799599C', 5, 163, 'B3', 'qwerty', '07234567765', '245678', 1000.00, 'confirmed', '2025-07-20 11:48:47', '2025-07-20 11:48:47'),
(41, 'IRB9DB2FCC7', 5, 163, 'S01', 'qwerty', '07234567765', '234567', 1000.00, 'confirmed', '2025-07-20 12:16:51', '2025-07-20 12:16:51'),
(47, 'IRB85DFAEC9', 5, 167, 'A2', 'hassan adan', '0734567765', '76543', 900.00, 'confirmed', '2025-07-22 06:34:26', '2025-07-22 06:34:26'),
(48, 'PENDING-1753168079-5', 5, 167, 'A1', 'Pending', 'Pending', NULL, 0.00, 'pending', '2025-07-22 07:07:59', '2025-07-22 07:07:59'),
(49, 'PENDING-1753168184-5', 5, 167, 'B1', 'Pending', 'Pending', NULL, 0.00, 'pending', '2025-07-22 07:09:44', '2025-07-22 07:09:44'),
(50, 'IRB55D7F13E', 5, 167, 'B1', 'hassan adan', '0734567765', '345676', 900.00, 'confirmed', '2025-07-22 07:10:16', '2025-07-22 07:10:16'),
(51, 'PENDING-1753447516-5', 5, 169, 'A1', 'Pending', 'Pending', NULL, 0.00, 'pending', '2025-07-25 12:45:16', '2025-07-25 12:45:16'),
(52, 'IRBBCC20CD8', 5, 169, 'A1', 'hassan adan', '0734567765', '8765433', 3000.00, 'confirmed', '2025-07-25 12:46:12', '2025-07-25 12:46:12'),
(53, 'PENDING-1753447724-5', 5, 171, 'A1', 'Pending', 'Pending', NULL, 0.00, 'pending', '2025-07-25 12:48:44', '2025-07-25 12:48:44'),
(54, 'IRB2AFBB5D2', 5, 171, 'A1', 'hassan adan', '0734567765', '123456', 3000.00, 'confirmed', '2025-07-25 12:49:14', '2025-07-25 12:49:14');

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
(9, 'Isiolo raha 1', 'kbz 386k', 50, 'standard', 'Reclining Seats, WiFi, USB Charging, Refreshments, Onboard Entertainment, Reading Lights, Spacious Legroom, Power Outlets', 'active', '2025-06-18 09:25:20', '2025-06-18 09:25:43'),
(12, 'Isiolo raha 4', 'kbc 567', 70, 'standard', 'Reclining Seats, WiFi, USB Charging, Onboard Entertainment, Reading Lights, Spacious Legroom, Power Outlets, Blankets, Pillows', 'active', '2025-07-21 06:11:32', '2025-07-21 06:11:32'),
(13, 'Isiolo raha 5', 'kdz 386', 60, 'luxury', 'Air Conditioning, Reclining Seats, WiFi, USB Charging, Refreshments, Onboard Entertainment, Reading Lights, Spacious Legroom, Toilet, Power Outlets, Blankets, Pillows', 'active', '2025-07-21 06:12:10', '2025-07-21 06:12:10'),
(14, 'Isiolo raha 3', 'kbd 456', 55, 'standard', 'Air Conditioning, WiFi, USB Charging, Refreshments, Onboard Entertainment, Reading Lights, Spacious Legroom, Power Outlets', 'active', '2025-07-21 06:13:01', '2025-07-21 06:13:01');

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
  `admin_response` text DEFAULT NULL,
  `response_date` timestamp NULL DEFAULT NULL,
  `status` enum('unread','read','responded') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `name`, `email`, `subject`, `message`, `admin_response`, `response_date`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'hassan', 'hassan@gmail.com', 'help', 'Book a ticket:', NULL, NULL, 'read', '2025-05-17 09:27:47', '2025-05-17 09:28:06'),
(2, 5, 'hassan adan', 'adanhassan1910@gmail.com', 'test', 'test', 'test 2', '2025-07-19 10:38:42', 'responded', '2025-07-19 09:56:23', '2025-07-19 10:38:42'),
(3, 5, 'test', 'test@gmail.com', 'test', 'Book a ticket:', 'test', '2025-07-25 11:48:17', 'responded', '2025-07-25 11:47:27', '2025-07-25 11:48:17');

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
(2, 2, 'IRE1642671', 200.00, 'paystack', 'successful', '2025-05-17 12:56:35', 'KES', '2025-05-17 12:56:35', '2025-05-17 12:56:35'),
(14, 7, 'IR781B092E', 1000.00, 'paystack', 'successful', '2025-06-18 09:39:34', 'KES', '2025-06-18 09:39:34', '2025-06-18 09:39:34'),
(15, 8, 'CASH-IRC721EA1D', 1000.00, 'cash', 'successful', '2025-06-18 09:46:07', 'KES', '2025-06-18 09:46:07', '2025-06-18 09:46:07'),
(17, 11, 'IRDB8C55FD', 700.00, 'paystack', 'successful', '2025-07-19 10:45:52', 'KES', '2025-07-19 10:45:52', '2025-07-19 10:45:52'),
(22, 20, 'mpesa-IRBDED41550-A1', 1000.00, 'mpesa', 'successful', '2025-07-20 09:08:46', 'KES', '2025-07-20 09:08:46', '2025-07-20 09:08:46'),
(27, 25, 'GRP_IRBD0528A61_1753004875', 1000.00, 'paystack', 'successful', '2025-07-20 09:48:13', 'KES', '2025-07-20 09:48:13', '2025-07-20 09:48:13'),
(29, 27, 'GRP_IRB740C1B1C_1753008912', 1000.00, 'paystack', 'successful', '2025-07-20 10:55:29', 'KES', '2025-07-20 10:55:29', '2025-07-20 10:55:29'),
(31, 29, 'GRP_IRB2A124AE1_1753009494', 1000.00, 'paystack', 'successful', '2025-07-20 11:05:12', 'KES', '2025-07-20 11:05:12', '2025-07-20 11:05:12'),
(33, 33, 'IRB2A124AE1-P2-AUTO2', 1000.00, '', 'successful', '2025-07-20 11:32:01', 'KES', '2025-07-20 11:32:01', '2025-07-20 11:32:01'),
(37, 37, 'GRP_IRBE799599C_1753012111', 1000.00, 'paystack', 'successful', '2025-07-20 11:48:47', 'KES', '2025-07-20 11:48:47', '2025-07-20 11:48:47'),
(41, 41, 'completed-IRB9DB2FCC7-S01', 1000.00, '', 'successful', '2025-07-20 12:16:51', 'KES', '2025-07-20 12:16:51', '2025-07-20 12:16:51'),
(45, 47, 'IRB85DFAEC9', 900.00, 'paystack', 'successful', '2025-07-22 06:34:26', 'KES', '2025-07-22 06:34:26', '2025-07-22 06:34:26'),
(46, 50, 'IRB55D7F13E', 900.00, 'paystack', 'successful', '2025-07-22 07:10:16', 'KES', '2025-07-22 07:10:16', '2025-07-22 07:10:16'),
(47, 52, 'IRBBCC20CD8', 3000.00, 'paystack', 'successful', '2025-07-25 12:46:12', 'KES', '2025-07-25 12:46:12', '2025-07-25 12:46:12'),
(48, 54, 'IRB2AFBB5D2', 3000.00, 'paystack', 'successful', '2025-07-25 12:49:14', 'KES', '2025-07-25 12:49:14', '2025-07-25 12:49:14');

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
(12, 'isiolo', 'Nairobi', 500.00, 240, '2025-05-17 09:44:47', '2025-05-17 09:44:47'),
(13, 'Meru', 'Nairobi', 600.00, 360, '2025-06-18 09:27:43', '2025-06-18 09:27:43');

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
(163, 13, 9, '2025-07-23 14:00:00', '2025-07-23 20:00:00', 1000.00, 'scheduled', '2025-06-18 09:30:18', '2025-07-19 11:57:34'),
(164, 1, 9, '2025-07-24 20:00:00', '2025-07-25 02:00:00', 700.00, 'scheduled', '2025-07-19 10:40:17', '2025-07-19 11:57:34'),
(165, 4, 1, '2025-07-20 17:05:00', '2025-07-21 17:06:00', 1500.00, 'scheduled', '2025-07-20 14:06:20', '2025-07-20 14:06:20'),
(166, 1, 9, '2025-07-21 09:08:00', '2025-07-22 09:08:00', 700.00, 'scheduled', '2025-07-21 06:09:56', '2025-07-21 06:09:56'),
(167, 2, 14, '2025-07-22 09:14:00', '2025-07-23 09:14:00', 900.00, 'scheduled', '2025-07-21 06:14:36', '2025-07-21 06:14:36'),
(168, 3, 13, '2025-07-23 09:14:00', '2025-07-24 09:15:00', 750.00, 'scheduled', '2025-07-21 06:15:23', '2025-07-21 06:15:23'),
(169, 4, 1, '2025-07-25 15:36:00', '2025-07-26 15:36:00', 3000.00, 'scheduled', '2025-07-25 12:36:28', '2025-07-25 12:36:28'),
(170, 4, 9, '2025-07-31 15:42:00', '2025-08-01 15:43:00', 3000.00, 'scheduled', '2025-07-25 12:43:41', '2025-07-25 12:43:41'),
(171, 1, 14, '2025-07-31 15:47:00', '2025-08-01 15:47:00', 3000.00, 'scheduled', '2025-07-25 12:48:08', '2025-07-25 12:48:08');

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
(1, 'Admin', 'User', 'admin@isioloraha.com', '0700000000', '$2y$10$kF7yTkeoD6PmbZDMqdLkF.1j9pXwsOYhZ7rRN.c7xdwY22l4Bu32S', 'admin', '0fDWzw2tkS09FwgQng12esMZRiSGH8k0ylnzduNcgavKprbRnV', '2025-07-17 15:20:13', '2025-05-16 07:12:45', '2025-07-17 11:35:48'),
(3, 'Hassan', 'adan', 'hassan@gmail.com', '0712345678', '$2y$10$8Q7LiVa3cXI2HeOGmFDiyu8OQeMGVOQ55hIqKpt768//ZBgJAm26a', 'user', NULL, NULL, '2025-05-16 11:03:27', '2025-05-16 11:03:27'),
(4, 'Test1', 'test', 'test1@gmail.com', '073-464-6464', '$2y$10$7Obj74YmTeY/LTYht8.wSOkHyxWx5nggziZ.ZySwBcREiNHroE5uu', 'user', NULL, NULL, '2025-06-18 09:36:47', '2025-06-18 09:36:47'),
(5, 'hassan', 'adan', 'adanhassan1910@gmail.com', '0734567765', '$2y$10$mULPLgR6fCfxUgNC1npet.iN252i.lRdTOUQAK/rHPYZHZsm8./zC', 'user', NULL, NULL, '2025-07-19 09:54:53', '2025-07-19 09:54:53');

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=172;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
