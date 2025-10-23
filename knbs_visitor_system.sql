-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 09:30 AM
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
-- Database: `knbs_visitor_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `LogID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Action` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`LogID`, `UserID`, `Action`, `Description`, `IPAddress`, `Timestamp`) VALUES
(1, 1, 'Login', 'User logged into the system', '::1', '2025-10-13 09:20:22'),
(2, 1, 'Login', 'User logged into the system', '::1', '2025-10-13 09:31:27'),
(3, 1, 'Visitor Registration', 'Registered visitor: Michelle Kabura', '::1', '2025-10-13 09:50:16'),
(4, 1, 'Visitor Checkout', 'Checked out visitor ID: 3', '::1', '2025-10-13 09:50:54'),
(5, 1, 'Badge Return', 'Badge returned for visitor ID: 3', '::1', '2025-10-13 09:51:05'),
(6, 1, 'Visitor Registration', 'Registered visitor: samson opiyo', '::1', '2025-10-13 09:53:07'),
(7, 1, 'Visitor Checkout', 'Checked out visitor ID: 4', '::1', '2025-10-13 11:01:36'),
(8, 1, 'Visitor Registration', 'Registered visitor: Michelle Kabura', '::1', '2025-10-13 11:03:42'),
(9, 1, 'Visitor Registration', 'Registered visitor: David Waweru', '::1', '2025-10-13 11:13:31'),
(10, 1, 'Badge Return', 'Badge returned for visitor ID: 4', '::1', '2025-10-13 11:18:02'),
(11, 1, 'Logout', 'User logged out of the system', '::1', '2025-10-13 11:18:29'),
(12, 1, 'Badge Return', 'Badge returned for visitor ID: 4', '::1', '2025-10-13 11:18:33'),
(13, 6, 'Login', 'User logged into the system', '::1', '2025-10-13 11:19:02'),
(14, 1, 'Badge Return', 'Badge returned for visitor ID: 4', '::1', '2025-10-13 11:19:04'),
(15, 1, 'Badge Return', 'Badge returned for visitor ID: 4', '::1', '2025-10-13 11:19:29'),
(16, 1, 'Visitor Checkout', 'Checked out visitor ID: 6', '::1', '2025-10-13 11:19:49'),
(17, 6, 'Visitor Checkout', 'Checked out visitor ID: 5', '::1', '2025-10-13 11:20:08'),
(18, 1, 'Visitor Checkout', 'Checked out visitor ID: 6', '::1', '2025-10-13 11:20:19'),
(19, 6, 'Badge Return', 'Badge returned for visitor ID: 6', '::1', '2025-10-13 11:20:32'),
(20, 6, 'Badge Return', 'Badge returned for visitor ID: 5', '::1', '2025-10-13 11:20:39'),
(21, 1, 'Visitor Registration', 'Registered visitor: David Waweru', '::1', '2025-10-13 12:00:56'),
(22, 1, 'Logout', 'User logged out of the system', '::1', '2025-10-13 12:11:22'),
(23, 1, 'Login', 'User logged into the system', '::1', '2025-10-13 12:11:33'),
(24, 6, 'Logout', 'User logged out of the system', '::1', '2025-10-13 12:11:54'),
(25, 1, 'Visitor Checkout', 'Checked out visitor ID: 7', '::1', '2025-10-13 12:16:09'),
(26, 1, 'User Updated', 'Updated user: Joseph', '::1', '2025-10-13 12:27:02'),
(27, 6, 'Login', 'User logged into the system', '::1', '2025-10-13 12:28:31'),
(28, 6, 'Badge Return', 'Badge returned for visitor ID: 7', '::1', '2025-10-13 12:28:44'),
(29, 1, 'User Updated', 'Updated user: Joseph', '::1', '2025-10-13 12:33:13'),
(30, 1, 'User Updated', 'Updated user: Joseph', '::1', '2025-10-13 12:36:27'),
(31, 1, 'Visitor Registration', 'Registered visitor: Griffin An unga', '::1', '2025-10-14 05:54:08'),
(32, 1, 'Login', 'User logged into the system', '::1', '2025-10-14 09:01:27'),
(33, 1, 'User Updated', 'Updated user: Kabura Ndibui', '::1', '2025-10-14 09:02:40'),
(34, 1, 'User Updated', 'Updated user: Kabura Ndibui', '::1', '2025-10-14 09:02:45'),
(35, 1, 'Logout', 'User logged out of the system', '::1', '2025-10-14 09:02:49'),
(36, 1, 'Login', 'User logged into the system', '::1', '2025-10-14 09:02:52'),
(37, 1, 'Visitor Registration', 'Registered visitor: iani onsula', '::1', '2025-10-14 11:55:48'),
(38, 1, 'Visitor Checkout', 'Checked out visitor ID: 9', '::1', '2025-10-14 12:12:23'),
(39, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:12:43'),
(40, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:13:13'),
(41, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:13:43'),
(42, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:14:13'),
(43, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:14:44'),
(44, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:15:14'),
(45, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:15:44'),
(46, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:17:33'),
(47, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:18:03'),
(48, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:18:34'),
(49, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:19:05'),
(50, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:19:36'),
(51, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:20:06'),
(52, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:20:37'),
(53, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:21:08'),
(54, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:21:39'),
(55, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:22:10'),
(56, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:22:41'),
(57, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:23:12'),
(58, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:23:43'),
(59, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:24:14'),
(60, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:24:45'),
(61, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:25:18'),
(62, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:25:48'),
(63, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:26:19'),
(64, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:26:50'),
(65, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:27:21'),
(66, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:27:52'),
(67, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:28:23'),
(68, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:28:54'),
(69, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:29:25'),
(70, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:29:56'),
(71, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:30:27'),
(72, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:30:58'),
(73, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:31:29'),
(74, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:32:00'),
(75, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:32:31'),
(76, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:33:02'),
(77, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:33:33'),
(78, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:34:04'),
(79, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:34:35'),
(80, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:35:06'),
(81, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:35:37'),
(82, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:36:08'),
(83, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:36:39'),
(84, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:37:10'),
(85, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:37:41'),
(86, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:38:12'),
(87, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:38:43'),
(88, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:39:14'),
(89, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:39:45'),
(90, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:40:16'),
(91, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:40:47'),
(92, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:41:18'),
(93, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:41:49'),
(94, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:42:20'),
(95, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:42:51'),
(96, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:43:22'),
(97, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:43:53'),
(98, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:44:24'),
(99, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:44:55'),
(100, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:45:26'),
(101, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:45:57'),
(102, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:46:28'),
(103, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:46:59'),
(104, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:47:30'),
(105, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:48:01'),
(106, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:48:32'),
(107, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:49:03'),
(108, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:49:34'),
(109, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:50:05'),
(110, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:50:36'),
(111, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:51:07'),
(112, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:51:38'),
(113, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:52:09'),
(114, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:52:40'),
(115, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:53:11'),
(116, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:53:42'),
(117, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:54:13'),
(118, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:54:44'),
(119, 1, 'Visitor Checkout', 'Checked out visitor ID: 8', '::1', '2025-10-14 12:55:17'),
(120, 1, 'Visitor Registration', 'Registered visitor: iani onsula', '::1', '2025-10-14 12:55:33'),
(121, 1, 'Visitor Checkout', 'Checked out visitor ID: 10', '::1', '2025-10-15 06:41:55'),
(122, 1, 'Visitor Checkout', 'Checked out visitor ID: 10', '::1', '2025-10-15 06:41:56'),
(123, 1, 'Visitor Checkout', 'Checked out visitor ID: 10', '::1', '2025-10-15 06:41:57'),
(124, 1, 'Visitor Checkout', 'Checked out visitor ID: 10', '::1', '2025-10-15 06:42:01'),
(125, 1, 'Visitor Checkout', 'Checked out visitor ID: 10', '::1', '2025-10-15 06:42:02'),
(126, 1, 'Badge Return', 'Badge returned for visitor ID: 10', '::1', '2025-10-15 07:41:15'),
(127, 1, 'Visitor Registration', 'Registered visitor: Michelle Kabura', '::1', '2025-10-15 07:41:45'),
(128, 1, 'Visitor Checkout', 'Checked out visitor ID: 11', '::1', '2025-10-15 07:49:01'),
(129, 1, 'Badge Return', 'Badge returned for visitor ID: 11', '::1', '2025-10-15 07:49:14'),
(130, 1, 'Badge Return', 'Badge returned for visitor ID: 9', '::1', '2025-10-15 07:49:20'),
(131, 1, 'Badge Return', 'Badge returned for visitor ID: 8', '::1', '2025-10-15 07:49:25'),
(132, 1, 'Visitor Registration', 'Registered visitor: Michelle Kabura', '::1', '2025-10-15 07:49:41'),
(133, 1, 'Logout', 'User logged out of the system', '::1', '2025-10-15 07:50:16'),
(134, 1, 'Login', 'User logged into the system', '::1', '2025-10-15 07:50:19'),
(135, 1, 'Login', 'User logged into the system', '::1', '2025-10-15 11:53:17'),
(136, 1, 'Login', 'User logged into the system', '::1', '2025-10-15 14:12:55'),
(137, 1, 'Login', 'User logged into the system', '::1', '2025-10-15 14:13:40'),
(138, 1, 'Login', 'User logged into the system', '::1', '2025-10-15 14:14:57'),
(139, 1, 'QR Code Generated', 'Feedback QR code generated for visitor ID: 12', '::1', '2025-10-15 14:17:24'),
(140, 1, 'Visitor Checkout', 'Checked out visitor ID: 12', '::1', '2025-10-15 14:17:24'),
(141, 1, 'Badge Return', 'Badge returned for visitor ID: 12', '::1', '2025-10-15 14:18:16'),
(142, 1, 'Logout', 'User logged out of the system', '::1', '2025-10-15 14:28:39'),
(143, 5, 'Login', 'User logged into the system', '::1', '2025-10-15 14:28:57');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'enable_email_notifications', '1', 'Enable email notifications for system events', '2025-10-14 08:51:34'),
(2, 'enable_audit_logging', '1', 'Enable audit logging for all system activities', '2025-10-14 08:51:34'),
(3, 'smtp_host', 'smtp.gmail.com', 'SMTP server host', '2025-10-14 08:51:34'),
(4, 'smtp_port', '587', 'SMTP server port', '2025-10-14 08:51:34'),
(5, 'smtp_username', '', 'SMTP username/email', '2025-10-14 08:51:34'),
(6, 'smtp_password', '', 'SMTP password', '2025-10-14 08:51:34'),
(7, 'from_email', 'noreply@knbs.or.ke', 'Default from email address', '2025-10-14 08:51:34'),
(8, 'email_subject_prefix', '[KNBS Visitor System]', 'Prefix for all email subjects', '2025-10-14 08:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `PFNumber` varchar(20) DEFAULT NULL,
  `IDNumber` varchar(20) DEFAULT NULL,
  `Role` enum('System Administrator','Main Front Desk Officer','Secondary Front Desk Officer') NOT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `PhoneNumber` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `IsActive` tinyint(1) DEFAULT 1,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `FullName`, `PFNumber`, `IDNumber`, `Role`, `Department`, `PhoneNumber`, `Email`, `PasswordHash`, `IsActive`, `CreatedAt`) VALUES
(1, 'System Administrator', 'ADMIN001', NULL, 'System Administrator', 'ICT', '254700000000', 'admin@knbs.go.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-10-13 08:09:15'),
(5, 'Kabura Ndibui', 'REC001', '39971712', 'Main Front Desk Officer', 'Reception', '254700000002', 'reception@knbs.go.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-10-13 08:31:51'),
(6, 'Joseph', 'SDO001', '3997190', 'Secondary Front Desk Officer', 'Reception', '254700000003', 'desk@knbs.go.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-10-13 08:31:51');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `VisitorID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Gender` enum('Male','Female','Other') NOT NULL,
  `PWDStatus` tinyint(1) DEFAULT 0,
  `IDType` enum('ID','Passport','Driving License') NOT NULL,
  `IDNumber` varchar(50) NOT NULL,
  `PhoneNumber` varchar(15) DEFAULT NULL,
  `Organization` varchar(100) DEFAULT NULL,
  `PurposeOfVisit` text NOT NULL,
  `HostName` varchar(100) DEFAULT NULL,
  `BadgeNumber` varchar(20) DEFAULT NULL,
  `BadgeReturned` tinyint(1) DEFAULT 0,
  `HasLuggage` tinyint(1) DEFAULT 0,
  `LuggageNumber` varchar(20) DEFAULT NULL,
  `AdmittingOfficer` varchar(100) NOT NULL,
  `CheckOutOfficer` varchar(100) DEFAULT NULL,
  `CheckInTime` datetime NOT NULL,
  `CheckOutTime` datetime DEFAULT NULL,
  `Feedback` text DEFAULT NULL,
  `Status` enum('Checked In','Checked Out') DEFAULT 'Checked In',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `HostAvailable` tinyint(1) DEFAULT 1,
  `VisitorMessage` text DEFAULT NULL,
  `FeedbackToken` varchar(32) DEFAULT NULL,
  `FeedbackSubmitted` tinyint(1) DEFAULT 0,
  `FeedbackTimestamp` datetime DEFAULT NULL,
  `QRCodeScanned` tinyint(1) DEFAULT 0,
  `QRScanTimestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`VisitorID`, `FullName`, `Gender`, `PWDStatus`, `IDType`, `IDNumber`, `PhoneNumber`, `Organization`, `PurposeOfVisit`, `HostName`, `BadgeNumber`, `BadgeReturned`, `HasLuggage`, `LuggageNumber`, `AdmittingOfficer`, `CheckOutOfficer`, `CheckInTime`, `CheckOutTime`, `Feedback`, `Status`, `CreatedAt`, `HostAvailable`, `VisitorMessage`, `FeedbackToken`, `FeedbackSubmitted`, `FeedbackTimestamp`, `QRCodeScanned`, `QRScanTimestamp`) VALUES
(3, 'Michelle Kabura', 'Female', 0, 'ID', '39971712', '0110097767', 'jkuat', 'attachee', 'ict', 'KNBS20251013648', 1, 0, NULL, 'System Administrator', 'System Administrator', '2025-10-13 12:50:16', '2025-10-13 12:50:54', NULL, 'Checked Out', '2025-10-13 09:50:16', 1, NULL, '3fa3539d39b228c4c4e77cb090490a38', 0, NULL, 0, NULL),
(4, 'samson opiyo', 'Male', 0, 'Passport', 'er48856443', '0708156304', 'helb', 'finance', 'januaris', 'KNBS20251013661', 1, 1, '01', 'System Administrator', 'System Administrator', '2025-10-13 12:53:07', '2025-10-13 14:01:36', NULL, 'Checked Out', '2025-10-13 09:53:07', 1, NULL, NULL, 0, NULL, 0, NULL),
(5, 'Michelle Kabura', 'Female', 0, 'ID', '39971712', '0110097767', 'jkuat', 'atachee', 'ict', 'KNBS20251013462', 1, 0, NULL, 'System Administrator', 'Secondary Desk Officer', '2025-10-13 14:03:42', '2025-10-13 14:20:08', NULL, 'Checked Out', '2025-10-13 11:03:42', 1, NULL, NULL, 0, NULL, 0, NULL),
(6, 'David Waweru', 'Male', 1, 'ID', '399717245', '0110097789', 'davewawerueng', 'nill', 'jane', 'KNBS20251013929', 1, 0, NULL, 'System Administrator', 'System Administrator', '2025-10-13 14:13:31', '2025-10-13 14:20:19', NULL, 'Checked Out', '2025-10-13 11:13:31', 1, NULL, NULL, 0, NULL, 0, NULL),
(7, 'David Waweru', 'Male', 1, 'ID', '3997190', '0110097789', 'davewawerueng', 'jobinquiry', 'jane', 'KNBS20251013141', 1, 0, NULL, 'System Administrator', 'System Administrator', '2025-10-13 15:00:56', '2025-10-13 15:16:09', '', 'Checked Out', '2025-10-13 12:00:56', 1, '', NULL, 0, NULL, 0, NULL),
(8, 'Griffin An unga', 'Male', 1, 'ID', '41978273', '0757972725', 'KNBS', 'See Joseph', 'Jospeh', 'KNBS20251014962', 1, 1, '002', 'System Administrator', 'System Administrator', '2025-10-14 08:54:08', '2025-10-14 15:55:17', 'ok', 'Checked Out', '2025-10-14 05:54:08', 1, '', NULL, 0, NULL, 0, NULL),
(9, 'iani onsula', 'Male', 0, 'ID', '41978179', '072290902725', 'KNBS', 'dropping of lunch', 'hildah', 'KNBS20251014250', 1, 0, NULL, 'System Administrator', 'System Administrator', '2025-10-14 14:55:48', '2025-10-14 15:12:23', 'good', 'Checked Out', '2025-10-14 11:55:48', 1, '', NULL, 0, NULL, 0, NULL),
(10, 'iani onsula', 'Male', 1, 'Passport', '41978179', '072290902725', 'KNBS', 'you', 'hildah', 'KNBS20251014817', 1, 0, NULL, 'System Administrator', 'System Administrator', '2025-10-14 15:55:33', '2025-10-15 09:42:02', '', 'Checked Out', '2025-10-14 12:55:33', 1, '', NULL, 0, NULL, 0, NULL),
(11, 'Michelle Kabura', 'Female', 0, 'ID', '39971712', '0110097767', 'jkuat', 'inquiry', 'ict', 'KNBS20251015985', 1, 0, NULL, 'System Administrator', 'System Administrator', '2025-10-15 10:41:45', '2025-10-15 10:49:01', '', 'Checked Out', '2025-10-15 07:41:45', 1, '', NULL, 0, NULL, 0, NULL),
(12, 'Michelle Kabura', 'Female', 0, 'ID', '39971712', '0110097767', 'jkuat', 'ict', 'ict', 'KNBS20251015360', 1, 0, NULL, 'System Administrator', 'System Administrator', '2025-10-15 10:49:41', '2025-10-15 17:17:24', '', 'Checked Out', '2025-10-15 07:49:41', 1, '', 'ba1cce21b315db2da5310a6343303af7', 0, NULL, 0, NULL),
(13, 'Test Visitor', 'Male', 0, 'ID', '', NULL, NULL, '', NULL, 'TEST20251015362', 0, 0, NULL, '', NULL, '0000-00-00 00:00:00', NULL, NULL, 'Checked In', '2025-10-15 08:45:31', 1, NULL, 'a231097b358c875aea7c27ba33e6dbe1', 0, NULL, 0, NULL),
(14, 'Test Visitor', 'Male', 0, 'ID', '', NULL, NULL, '', NULL, 'TEST20251015946', 0, 0, NULL, '', NULL, '0000-00-00 00:00:00', NULL, NULL, 'Checked In', '2025-10-15 08:46:05', 1, NULL, '5bc5b3ee5e55e1c556d5c18b6cc46f7b', 0, NULL, 0, NULL),
(15, 'Test Visitor', 'Male', 0, 'ID', '', NULL, NULL, '', NULL, 'TEST20251015497', 0, 0, NULL, '', NULL, '0000-00-00 00:00:00', NULL, NULL, 'Checked In', '2025-10-15 08:47:08', 1, NULL, '88eb09014fccab41d02ad2d38ee24885', 0, NULL, 0, NULL),
(16, 'Test Visitor', 'Male', 0, 'ID', '', NULL, NULL, '', NULL, 'TEST20251015819', 0, 0, NULL, '', NULL, '0000-00-00 00:00:00', NULL, NULL, 'Checked In', '2025-10-15 09:27:52', 1, NULL, '8b4751df1e6baf7741df9821493260cb', 0, NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `PFNumber` (`PFNumber`),
  ADD UNIQUE KEY `IDNumber` (`IDNumber`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`VisitorID`),
  ADD UNIQUE KEY `BadgeNumber` (`BadgeNumber`),
  ADD UNIQUE KEY `FeedbackToken` (`FeedbackToken`),
  ADD KEY `AdmittingOfficerID` (`AdmittingOfficer`),
  ADD KEY `CheckOutOfficerID` (`CheckOutOfficer`),
  ADD KEY `idx_feedback_token` (`FeedbackToken`),
  ADD KEY `idx_feedback_submitted` (`FeedbackSubmitted`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `VisitorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
