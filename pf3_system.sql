-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2026 at 07:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pf3_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `phone`, `password`, `created_at`, `status`) VALUES
(1, 'admin', 'admin@gmail.com', '1234567890', '$2y$10$hhfoFl0hZyVfCfDvndO9vewKpth0zgoiq46m5Q1P0L4zo/iWX0kEu', '2026-05-07 20:30:25', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('admin','doctor','police') NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_type`, `action`, `details`, `created_at`) VALUES
(1, 1, 'admin', 'Login', '', '2026-05-07 20:57:54'),
(2, 1, 'admin', 'Registered police', 'Majid kabalo', '2026-05-07 21:00:03'),
(3, 1, 'admin', 'Logout', '', '2026-05-07 21:00:34'),
(4, 1, 'police', 'Login', '', '2026-05-07 21:06:54'),
(5, 1, 'police', 'Case approved', 'PF3: PF3-296984', '2026-05-07 21:08:19'),
(6, 1, 'police', 'Logout', '', '2026-05-07 21:08:27'),
(7, 1, 'admin', 'Login', '', '2026-05-07 21:09:54'),
(8, 1, 'admin', 'Registered doctor', 'tzone JOHN', '2026-05-07 21:12:00'),
(9, 1, 'admin', 'Logout', '', '2026-05-07 21:12:14'),
(10, 1, 'doctor', 'Login', '', '2026-05-07 21:12:27'),
(11, 1, 'doctor', 'Logout', '', '2026-05-07 21:18:50'),
(12, 1, 'admin', 'Login', '', '2026-05-08 08:46:03'),
(13, 1, 'admin', 'Logout', '', '2026-05-08 08:47:58'),
(14, 1, 'police', 'Login', '', '2026-05-08 08:48:20'),
(15, 1, 'police', 'Logout', '', '2026-05-08 08:49:14'),
(16, 1, 'police', 'Login', '', '2026-05-08 08:49:57'),
(17, 1, 'police', 'Logout', '', '2026-05-08 08:50:15'),
(18, 1, 'doctor', 'Login', '', '2026-05-08 08:51:26'),
(19, 1, 'doctor', 'Medical report added', 'PF3: PF3-296984', '2026-05-08 08:54:28'),
(20, 1, 'doctor', 'Logout', '', '2026-05-08 08:57:49'),
(21, 1, 'admin', 'Login', '', '2026-05-12 17:22:51'),
(22, 1, 'admin', 'Logout', '', '2026-05-12 17:23:51'),
(23, 1, 'police', 'Login', '', '2026-05-12 17:24:02'),
(24, 1, 'police', 'Logout', '', '2026-05-12 17:25:06'),
(25, 1, 'police', 'Login', '', '2026-05-19 07:37:28'),
(26, 1, 'police', 'Logout', '', '2026-05-19 07:40:55'),
(27, 1, 'admin', 'Login', '', '2026-05-19 07:41:23'),
(28, 1, 'admin', 'Logout', '', '2026-05-19 07:42:29'),
(29, 1, 'police', 'Login', '', '2026-05-19 07:48:34'),
(30, 1, 'police', 'Login', '', '2026-05-19 09:13:39'),
(31, 1, 'police', 'Logout', '', '2026-05-19 09:14:05'),
(32, 1, 'police', 'Login', '', '2026-05-19 09:23:50'),
(33, 1, 'police', 'Logout', '', '2026-05-19 09:49:24'),
(34, 1, 'police', 'Login', '', '2026-05-19 09:49:45'),
(35, 1, 'police', 'Logout', '', '2026-05-19 09:50:01'),
(36, 1, 'police', 'Login', '', '2026-05-19 09:50:18'),
(37, 1, 'police', 'Case Approved', 'PF3: PF3-793998, RB: RB-IHMLFN', '2026-05-19 09:58:32'),
(38, 1, 'police', 'Logout', '', '2026-05-19 10:00:35'),
(39, 1, 'admin', 'Login', '', '2026-05-19 10:01:21'),
(40, 1, 'doctor', 'Login', '', '2026-05-19 10:01:51'),
(41, 1, 'police', 'Login', '', '2026-05-19 10:02:20'),
(42, 1, 'police', 'Logout', '', '2026-05-19 10:03:57'),
(43, 1, 'admin', 'Login', '', '2026-05-19 10:04:12'),
(44, 1, 'admin', 'Logout', '', '2026-05-19 10:05:49'),
(45, 1, 'admin', 'Login', '', '2026-05-19 10:06:01'),
(46, 1, 'admin', 'Logout', '', '2026-05-19 10:08:13'),
(47, 1, 'police', 'Login', '', '2026-05-19 10:08:19'),
(48, 1, 'police', 'Logout', '', '2026-05-19 10:11:29'),
(49, 1, 'police', 'Login', '', '2026-05-19 10:11:38'),
(50, 1, 'police', 'Logout', '', '2026-05-19 10:30:32'),
(51, 1, 'doctor', 'Login', '', '2026-05-19 10:30:46'),
(52, 1, 'doctor', 'Logout', '', '2026-05-19 10:39:43'),
(53, 1, 'admin', 'Login', '', '2026-05-19 10:39:50'),
(54, 1, 'admin', 'Toggled Police Status', 'Police ID: 1', '2026-05-19 11:00:27'),
(55, 1, 'admin', 'Toggled Doctor Status', 'Doctor ID: 1', '2026-05-19 11:00:37'),
(56, 1, 'admin', 'Toggled Doctor Status', 'Doctor ID: 1', '2026-05-19 11:00:41'),
(57, 1, 'admin', 'Toggled Doctor Status', 'Doctor ID: 1', '2026-05-19 11:03:23'),
(58, 1, 'admin', 'Toggled Doctor Status', 'Doctor ID: 1', '2026-05-19 11:04:53'),
(59, 1, 'admin', 'Logout', '', '2026-05-19 11:08:09'),
(60, 1, 'police', 'Login', '', '2026-05-19 11:08:16'),
(61, 1, 'police', 'Logout', '', '2026-05-19 11:08:56'),
(62, 1, 'admin', 'Login', '', '2026-05-19 11:09:04'),
(63, 1, 'admin', 'Logout', '', '2026-05-19 11:24:06'),
(64, 1, 'police', 'Login', '', '2026-05-19 11:25:28'),
(65, 1, 'police', 'Logout', '', '2026-05-19 11:25:39'),
(66, 1, 'admin', 'Login', '', '2026-05-19 11:25:49'),
(67, 1, 'admin', 'Logout', '', '2026-05-19 11:26:49'),
(68, 1, 'police', 'Login', '', '2026-05-19 12:18:30'),
(69, 1, 'police', 'Logout', '', '2026-05-19 12:21:41'),
(70, 1, 'doctor', 'Login', '', '2026-05-19 12:21:52'),
(71, 1, 'doctor', 'Logout', '', '2026-05-19 12:24:01'),
(72, 1, 'police', 'Login', '', '2026-05-19 12:24:11'),
(73, 1, 'police', 'Case Approved', 'PF3: PF3-727004, RB: RB-LONMUA', '2026-05-19 12:24:21'),
(74, 1, 'police', 'Logout', '', '2026-05-19 12:24:25'),
(75, 1, 'doctor', 'Login', '', '2026-05-19 12:24:37'),
(76, 1, 'doctor', 'Logout', '', '2026-05-19 12:28:38'),
(77, 1, 'police', 'Login', '', '2026-05-19 20:10:36'),
(78, 1, 'police', 'Profile Updated', 'Updated profile information', '2026-05-19 20:12:34'),
(79, 1, 'police', 'Logout', '', '2026-05-19 20:13:30'),
(80, 1, 'doctor', 'Login', '', '2026-05-19 20:13:49'),
(81, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-05-19 20:14:30'),
(82, 1, 'doctor', 'Logout', '', '2026-05-19 20:15:42'),
(83, 1, 'admin', 'Login', '', '2026-05-19 20:15:59'),
(84, 1, 'admin', 'Logout', '', '2026-05-19 20:19:26'),
(85, 1, 'police', 'Login', '', '2026-05-19 20:21:32'),
(86, 1, 'police', 'Logout', '', '2026-05-19 20:25:29'),
(87, 1, 'police', 'Login', '', '2026-05-19 20:33:37'),
(88, 1, 'police', 'Logout', '', '2026-05-19 20:39:40'),
(89, 1, 'admin', 'Login', '', '2026-05-19 20:39:56'),
(90, 1, 'admin', 'Updated Doctor', 'Doctor ID: 1', '2026-05-19 20:43:42'),
(91, 1, 'admin', 'Logout', '', '2026-05-19 20:47:33'),
(92, 1, 'police', 'Login', '', '2026-05-19 20:47:46'),
(93, 1, 'admin', 'Login', '', '2026-05-19 20:48:46'),
(94, 1, 'admin', 'Logout', '', '2026-05-19 20:51:04'),
(95, 1, 'doctor', 'Login', '', '2026-05-19 20:51:22'),
(96, 1, 'doctor', 'Logout', '', '2026-05-19 20:53:12'),
(97, 1, 'police', 'Login', '', '2026-05-19 20:53:29'),
(98, 1, 'police', 'Logout', '', '2026-05-19 20:53:52'),
(99, 1, 'admin', 'Login', '', '2026-05-19 20:54:04'),
(100, 1, 'admin', 'Logout', '', '2026-05-19 20:56:51'),
(101, 1, 'doctor', 'Login', '', '2026-05-19 20:57:10'),
(102, 1, 'doctor', 'Logout', '', '2026-05-19 20:59:57'),
(103, 1, 'police', 'Login', '', '2026-05-20 07:45:27'),
(104, 1, 'police', 'Logout', '', '2026-05-20 07:47:26'),
(105, 1, 'admin', 'Login', '', '2026-05-20 07:48:42'),
(106, 1, 'police', 'Login', '', '2026-05-21 07:53:59'),
(107, 1, 'police', 'Logout', '', '2026-05-21 07:56:14'),
(108, 1, 'doctor', 'Login', '', '2026-05-21 07:56:25'),
(109, 1, 'police', 'Login', '', '2026-05-21 20:19:05'),
(110, 1, 'police', 'Logout', '', '2026-05-21 20:19:26'),
(111, 1, 'doctor', 'Login', '', '2026-05-21 20:19:41'),
(112, 1, 'doctor', 'Logout', '', '2026-05-21 20:20:22'),
(113, 1, 'admin', 'Login', '', '2026-05-21 20:21:19'),
(114, 1, 'police', 'Login', '', '2026-05-22 06:47:05'),
(115, 1, 'police', 'Logout', '', '2026-05-22 07:15:11'),
(116, 1, 'police', 'Login', '', '2026-05-22 07:15:37'),
(117, 1, 'police', 'Logout', '', '2026-05-22 07:15:57'),
(118, 1, 'doctor', 'Login', '', '2026-05-22 07:16:08'),
(119, 1, 'doctor', 'Logout', '', '2026-05-22 07:17:32'),
(120, 1, 'admin', 'Login', '', '2026-05-22 07:17:44'),
(121, 1, 'admin', 'Logout', '', '2026-05-22 07:47:54'),
(122, 1, 'police', 'Login', '', '2026-05-22 07:48:03'),
(123, 1, 'police', 'Logout', '', '2026-05-22 08:12:36'),
(124, 1, 'police', 'Login', '', '2026-05-24 15:59:33'),
(125, 1, 'police', 'Logout', '', '2026-05-24 15:59:58'),
(126, 1, 'police', 'Login', '', '2026-05-24 16:18:04'),
(127, 1, 'police', 'Logout', '', '2026-05-24 16:18:18'),
(128, 1, 'police', 'Login', '', '2026-05-28 08:37:36'),
(129, 1, 'police', 'Login', '', '2026-06-09 15:43:40'),
(130, 1, 'police', 'Login', '', '2026-06-09 19:57:59'),
(131, 1, 'police', 'Login', '', '2026-06-22 07:39:02'),
(132, 1, 'police', 'Logout', '', '2026-06-22 07:43:14'),
(133, 1, 'admin', 'Login', '', '2026-06-22 07:43:26'),
(134, 1, 'admin', 'Logout', '', '2026-06-22 07:46:33'),
(135, 1, 'doctor', 'Login', '', '2026-06-22 07:46:46'),
(136, 1, 'police', 'Login', '', '2026-06-25 03:47:18'),
(137, 1, 'police', 'Logout', '', '2026-06-25 03:51:13'),
(138, 1, 'doctor', 'Login', '', '2026-06-25 03:51:28'),
(139, 1, 'doctor', 'Logout', '', '2026-06-25 04:00:44'),
(140, 1, 'admin', 'Login', '', '2026-06-25 04:00:58'),
(141, 1, 'admin', 'Registered Doctor: YUSUPH IBRAHIM', 'Email: Yusuph@gmail.com', '2026-06-25 04:08:17'),
(142, 1, 'admin', 'Registered Police Officer: NASTAY MWINJUMA', 'Email: Nastay@gmail.com', '2026-06-25 04:10:24'),
(143, 1, 'admin', 'Logout', '', '2026-06-25 04:34:45'),
(144, 1, 'police', 'Login', '', '2026-06-25 04:34:54'),
(145, 1, 'police', 'Logout', '', '2026-06-25 04:37:58'),
(146, 1, 'doctor', 'Login', '', '2026-06-25 04:38:09'),
(147, 1, 'doctor', 'Logout', '', '2026-06-25 05:22:13'),
(148, 2, 'doctor', 'Login', '', '2026-06-25 05:22:33'),
(149, 1, 'admin', 'Login', '', '2026-06-25 05:24:41'),
(150, 1, 'admin', 'Updated Police Officer', 'Police ID: 2', '2026-06-25 05:25:22'),
(151, 1, 'police', 'Login', '', '2026-06-25 08:20:07'),
(152, 1, 'doctor', 'Login', '', '2026-06-25 08:20:25'),
(153, 1, 'doctor', 'Logout', '', '2026-06-25 08:21:25'),
(154, 1, 'police', 'Login', '', '2026-06-25 09:13:16'),
(155, 1, 'police', 'Logout', '', '2026-06-25 09:13:40'),
(156, 1, 'admin', 'Login', '', '2026-06-25 09:13:50'),
(157, 2, 'doctor', 'Login', '', '2026-06-25 09:15:57'),
(158, 1, 'police', 'Login', '', '2026-07-17 05:23:06'),
(159, 1, 'police', 'Logout', '', '2026-07-17 05:23:36'),
(160, 1, 'doctor', 'Login', '', '2026-07-17 05:23:48'),
(161, 1, 'doctor', 'Logout', '', '2026-07-17 05:25:04'),
(162, 1, 'admin', 'Login', '', '2026-07-17 05:25:19'),
(163, 1, 'admin', 'Logout', '', '2026-07-17 05:51:03'),
(164, 1, 'doctor', 'Login', '', '2026-07-17 05:51:15'),
(165, 1, 'police', 'Login', '', '2026-07-17 08:53:27'),
(166, 1, 'doctor', 'Login', '', '2026-07-17 08:54:53'),
(167, 1, 'police', 'Case Approved', 'PF3: PF3-403690, RB: RB-KBNTOA', '2026-07-17 08:56:16'),
(168, 1, 'doctor', 'Logout', '', '2026-07-17 09:06:43'),
(169, 1, 'admin', 'Login', '', '2026-07-17 09:06:55'),
(170, 1, 'police', 'Logout', '', '2026-07-17 09:11:01'),
(171, 1, 'police', 'Login', '', '2026-07-17 09:11:14'),
(172, 1, 'police', 'Logout', '', '2026-07-17 09:12:01'),
(173, 1, 'admin', 'Login', '', '2026-07-17 09:12:12'),
(174, 1, 'admin', 'Logout', '', '2026-07-17 09:12:36'),
(175, 1, 'police', 'Login', '', '2026-07-17 09:12:46'),
(176, 1, 'police', 'Login', '', '2026-07-17 13:02:20'),
(177, 1, 'admin', 'Login', '', '2026-07-17 17:12:10'),
(178, 1, 'admin', 'Logout', '', '2026-07-17 17:19:12'),
(179, 1, 'doctor', 'Login', '', '2026-07-17 17:19:24'),
(180, 1, 'doctor', 'Logout', '', '2026-07-17 17:20:47'),
(181, 1, 'admin', 'Login', '', '2026-07-17 17:58:42'),
(182, 1, 'police', 'Login', '', '2026-07-17 19:08:19'),
(183, 1, 'police', 'Logout', '', '2026-07-17 19:12:01'),
(184, 1, 'admin', 'Login', '', '2026-07-17 19:12:13'),
(185, 1, 'admin', 'Logout', '', '2026-07-17 19:12:47'),
(186, 1, 'police', 'Login', '', '2026-07-17 19:13:00'),
(187, 1, 'police', 'Logout', '', '2026-07-17 19:13:09'),
(188, 1, 'doctor', 'Login', '', '2026-07-17 19:13:25'),
(189, 1, 'doctor', 'Logout', '', '2026-07-17 19:14:07'),
(190, 1, 'doctor', 'Login', '', '2026-07-17 19:14:19'),
(191, 1, 'doctor', 'Logout', '', '2026-07-17 19:15:42'),
(192, 1, 'doctor', 'Login', '', '2026-07-17 19:16:23'),
(193, 1, 'doctor', 'Logout', '', '2026-07-17 19:19:12'),
(194, 1, 'police', 'Login', '', '2026-07-17 19:21:51'),
(195, 1, 'police', 'Login', '', '2026-07-18 07:50:33'),
(196, 1, 'police', 'Logout', '', '2026-07-18 07:51:48'),
(197, 1, 'admin', 'Login', '', '2026-07-18 07:52:00'),
(198, 1, 'admin', 'Logout', '', '2026-07-18 07:53:04'),
(199, 1, 'doctor', 'Login', '', '2026-07-18 07:53:17'),
(200, 1, 'police', 'Login', '', '2026-07-18 08:12:53'),
(201, 1, 'doctor', 'Login', '', '2026-07-18 08:13:21'),
(202, 1, 'doctor', 'Logout', '', '2026-07-18 08:13:39'),
(203, 1, 'admin', 'Login', '', '2026-07-18 08:13:51'),
(204, 1, 'admin', 'Logout', '', '2026-07-18 08:14:27'),
(205, 1, 'police', 'Login', '', '2026-07-18 08:14:38'),
(206, 1, 'police', 'Login', '', '2026-07-18 08:16:43'),
(207, 1, 'police', 'Login', '', '2026-07-20 07:45:28'),
(208, 1, 'police', 'Logout', '', '2026-07-20 08:04:01'),
(209, 1, 'doctor', 'Login', '', '2026-07-20 08:04:12'),
(210, 1, 'doctor', 'Logout', '', '2026-07-20 08:05:03'),
(211, 1, 'police', 'Login', '', '2026-07-20 08:05:14'),
(212, 1, 'police', 'Login', '', '2026-07-20 09:11:01'),
(213, 1, 'police', 'Login', '', '2026-07-20 09:13:56'),
(214, 1, 'police', 'Login', '', '2026-07-20 09:14:22'),
(215, 1, 'police', 'Login', '', '2026-07-20 09:23:33'),
(216, 1, 'police', 'Login', '', '2026-07-20 10:41:59'),
(217, 1, 'police', 'Login', '', '2026-07-20 10:53:38'),
(218, 1, 'police', 'Login', '', '2026-07-20 11:09:45'),
(219, 1, 'police', 'Approved Case - Status changed to APPROVED', 'PF3: PF3-927996, Patient: JAMES JUMA SANGA, RB: RB-KYA1SP | Status updated from PENDING to APPROVE', '2026-07-20 12:27:15'),
(220, 1, 'police', 'Rejected Case - Status changed to REJECTED', 'PF3: PF3-378146, Patient: NELISTER  FESTON, Reason: hajakamilisha taarifa zake za msingi | Status updated from PENDING to REJECT', '2026-07-20 12:28:55'),
(221, 1, 'police', 'Logout', '', '2026-07-20 13:07:48'),
(222, 1, 'admin', 'Login', '', '2026-07-20 13:08:00'),
(223, 1, 'police', 'Login', '', '2026-07-22 05:31:23'),
(224, 1, 'police', 'Logout', '', '2026-07-22 05:32:14'),
(225, 1, 'doctor', 'Login', '', '2026-07-22 06:22:53'),
(226, 1, 'doctor', 'Logout', '', '2026-07-22 06:23:29'),
(227, 1, 'police', 'Login', '', '2026-07-22 06:23:40'),
(228, 1, 'police', 'Logout', '', '2026-07-22 06:23:49'),
(229, 1, 'doctor', 'Login', '', '2026-07-22 06:24:00'),
(230, 1, 'doctor', 'Logout', '', '2026-07-22 06:24:21'),
(231, 1, 'police', 'Login', '', '2026-07-22 06:24:31'),
(232, 1, 'police', 'Logout', '', '2026-07-22 06:24:52'),
(233, 1, 'doctor', 'Login', '', '2026-07-22 06:25:02'),
(234, 1, 'doctor', 'Logout', '', '2026-07-22 06:25:33'),
(235, 1, 'admin', 'Login', '', '2026-07-22 06:25:45'),
(236, 1, 'admin', 'Login', '', '2026-07-22 06:27:38'),
(237, 1, 'admin', 'Login', '', '2026-07-22 06:30:33'),
(238, 1, 'police', 'Login', '', '2026-07-30 05:38:33'),
(239, 1, 'police', 'Approved Case - Status changed to APPROVED', 'PF3: PF3-184011, Patient: DANIEL JOHN, RB: RB-FLLYD2 | Status updated from PENDING to APPROVE', '2026-07-30 05:40:19'),
(240, 1, 'police', 'Logout', '', '2026-07-30 05:41:22'),
(241, 1, 'doctor', 'Login', '', '2026-07-30 05:41:34'),
(242, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:42:40'),
(243, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:42:43'),
(244, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:42:45'),
(245, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:42:46'),
(246, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:42:47'),
(247, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:42:48'),
(248, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:43:06'),
(249, 1, 'doctor', 'Profile Updated', 'Updated profile information', '2026-07-30 05:43:24'),
(250, 1, 'doctor', 'Logout', '', '2026-07-30 05:47:46'),
(251, 1, 'admin', 'Login', '', '2026-07-30 05:48:00'),
(252, 1, 'admin', 'Logout', '', '2026-07-30 05:50:49'),
(253, 1, 'police', 'Login', '', '2026-07-30 05:54:44'),
(254, 1, 'police', 'Approved Case - Status changed to APPROVED', 'PF3: PF3-577374, Patient: DANIEL JOHN, RB: RB-DTV2W2 | Status updated from PENDING to APPROVE', '2026-07-30 05:55:07'),
(255, 1, 'police', 'Profile Updated', 'Updated profile information', '2026-07-30 05:57:22'),
(256, 1, 'police', 'Logout', '', '2026-07-30 05:57:44'),
(257, 1, 'police', 'Login', '', '2026-07-30 05:58:40'),
(258, 1, 'police', 'Logout', '', '2026-07-30 05:59:21'),
(259, 1, 'admin', 'Login', '', '2026-07-30 05:59:34'),
(260, 1, 'admin', 'Login', '', '2026-07-30 07:33:32'),
(261, 1, 'admin', 'Logout', '', '2026-07-30 08:18:15'),
(262, 1, 'doctor', 'Login', '', '2026-07-30 08:18:25'),
(263, 1, 'doctor', 'Logout', '', '2026-07-30 08:18:54'),
(264, 1, 'admin', 'Login', '', '2026-07-30 08:19:08'),
(265, 1, 'admin', 'Logout', '', '2026-07-30 08:19:25'),
(266, 1, 'police', 'Login', '', '2026-07-30 08:19:41'),
(267, 1, 'police', 'Logout', '', '2026-07-30 09:26:41'),
(268, 1, 'doctor', 'Login', '', '2026-08-01 05:24:55'),
(269, 1, 'doctor', 'Logout', '', '2026-08-01 05:25:47'),
(270, 1, 'admin', 'Login', '', '2026-08-01 05:25:57'),
(271, 1, 'doctor', 'Login', '', '2026-08-01 06:32:31'),
(272, 1, 'doctor', 'Logout', '', '2026-08-01 06:32:50'),
(273, 1, 'police', 'Login', '', '2026-08-01 06:33:15'),
(274, 1, 'police', 'Logout', '', '2026-08-01 06:34:09'),
(275, 1, 'admin', 'Login', '', '2026-08-01 10:17:39'),
(276, 1, 'admin', 'Logout', '', '2026-08-01 10:19:52'),
(277, 1, 'police', 'Login', '', '2026-08-01 10:20:06'),
(278, 1, 'police', 'Logout', '', '2026-08-01 10:22:39'),
(279, 1, 'doctor', 'Login', '', '2026-08-01 10:22:49'),
(280, 1, 'doctor', 'Medical report added', 'PF3: PF3-927996', '2026-08-01 10:27:47'),
(281, 1, 'doctor', 'Logout', '', '2026-08-01 10:29:35'),
(282, 1, 'police', 'Login', '', '2026-08-01 10:32:08'),
(283, 1, 'police', 'Logout', '', '2026-08-01 10:42:22'),
(284, 1, 'admin', 'Login', '', '2026-08-01 10:42:32'),
(285, 1, 'admin', 'Logout', '', '2026-08-01 10:46:33'),
(286, 1, 'admin', 'Login', '', '2026-08-01 11:27:28'),
(287, 1, 'admin', 'Logout', '', '2026-08-01 11:27:39'),
(288, 1, 'admin', 'Login', '', '2026-08-01 11:27:55'),
(289, 1, 'admin', 'Login', '', '2026-08-01 11:28:46'),
(290, 1, 'admin', 'Logout', '', '2026-08-01 11:28:51'),
(291, 1, 'admin', 'Login', '', '2026-08-01 11:30:30'),
(292, 1, 'admin', 'Logout', '', '2026-08-01 11:39:39'),
(293, 1, 'admin', 'Login', '', '2026-08-01 11:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `first_name`, `last_name`, `position`, `email`, `phone`, `password`, `created_at`, `status`) VALUES
(1, 'Emmanuel', 'john', 'MO', 'Emmanuel@gmail.com', '0629320942', '$2y$10$wkIbnUYrPk7QRkVcAx.SYuOQokRZCf.pE8LiRgCoCQvSghWpgCqRe', '2026-05-07 21:12:00', 'active'),
(2, 'YUSUPH', 'IBRAHIM', 'MO', 'Yusuph@gmail.com', '0673990181', '$2y$10$/3juYZU0AY7FCfifd5JdAuhSdNFzLbnTkn5OT04qugu3jylnJA8je', '2026-06-25 04:08:17', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `medical_reports`
--

CREATE TABLE `medical_reports` (
  `id` int(11) NOT NULL,
  `pf3_number` varchar(20) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `injury_type` varchar(100) NOT NULL,
  `severity` varchar(50) NOT NULL,
  `patient_condition` text NOT NULL,
  `medical_findings` text NOT NULL,
  `recommendations` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_reports`
--

INSERT INTO `medical_reports` (`id`, `pf3_number`, `doctor_id`, `injury_type`, `severity`, `patient_condition`, `medical_findings`, `recommendations`, `created_at`) VALUES
(1, 'PF3-296984', 1, 'Accident', 'Moderate', 'wastani', 'www', 'nnnn', '2026-05-08 08:54:28'),
(2, 'PF3-927996', 1, 'Accident', 'Mild', 'Doing good', 'Provide wound care and analgesics. Review after 7 days.', 'Admit for observation.', '2026-08-01 10:27:47');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `pf3_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `pf3_number`, `message`, `type`, `created_at`) VALUES
(1, 'PF3-793998', 'Case PF3: PF3-793998 has been APPROVED. RB Number: RB-IHMLFN', 'APPROVAL', '2026-05-19 09:58:32'),
(2, 'PF3-727004', 'Case PF3: PF3-727004 has been APPROVED. RB Number: RB-LONMUA', 'APPROVAL', '2026-05-19 12:24:21'),
(3, 'PF3-403690', 'Case PF3: PF3-403690 has been APPROVED. RB Number: RB-KBNTOA', 'APPROVAL', '2026-07-17 08:56:16'),
(4, 'PF3-927996', 'Your PF3 application #PF3-927996 has been APPROVED. RB Number: RB-KYA1SP. Please proceed to the hospital for medical examination.', 'APPROVE', '2026-07-20 12:27:15'),
(5, 'PF3-927996', 'SMS failed to send to 0629320949: Message is empty after cleaning', 'ERROR', '2026-07-20 12:27:15'),
(6, 'PF3-927996', 'Guardian SMS sent to: 255629320947', 'SMS', '2026-07-20 12:27:16'),
(7, 'PF3-378146', 'Your PF3 application #PF3-378146 has been REJECTED. Reason: hajakamilisha taarifa zake za msingi. Please contact the police station for more information.', 'REJECT', '2026-07-20 12:28:55'),
(8, 'PF3-378146', 'SMS failed to send to 0748820992: Message is empty after cleaning', 'ERROR', '2026-07-20 12:28:55'),
(9, 'PF3-378146', 'Guardian SMS sent to: 255748820993', 'SMS', '2026-07-20 12:28:56'),
(10, 'PF3-184011', 'Your PF3 application #PF3-184011 has been APPROVED. RB Number: RB-FLLYD2. Please proceed to the hospital for medical examination.', 'APPROVE', '2026-07-30 05:40:19'),
(11, 'PF3-184011', 'SMS failed to send to 255629320941: Message is empty after cleaning', 'ERROR', '2026-07-30 05:40:19'),
(12, 'PF3-577374', 'Your PF3 application #PF3-577374 has been APPROVED. RB Number: RB-DTV2W2. Please proceed to the hospital for medical examination.', 'APPROVE', '2026-07-30 05:55:07'),
(13, 'PF3-577374', 'SMS failed to send to 255629320941: Message is empty after cleaning', 'ERROR', '2026-07-30 05:55:07'),
(14, 'PF3-927996', 'Medical report has been added for PF3: PF3-927996', 'MEDICAL_REPORT', '2026-08-01 10:27:47');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `pf3_number` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `age` int(11) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `incident_date_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_application_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `pf3_number`, `full_name`, `gender`, `age`, `address`, `phone`, `guardian_phone`, `incident_date_time`, `created_at`, `last_application_date`) VALUES
(1, 'PF3-296984', 'DANIEL JOHN', 'Male', 23, '911 Dar es Salaam - Morogoro - Iringa Rd', '0629320941', '0629320941', '2026-05-07 15:01:00', '2026-05-07 21:01:09', NULL),
(2, 'PF3-793998', 'Ramadhan juma', 'Male', 24, '910', '0796328528', '0629320945', '2026-04-12 01:33:00', '2026-05-08 08:33:34', NULL),
(3, 'PF3-727004', 'hello', 'Male', 43, '88', '0629320948', '0629320841', '2026-05-19 01:12:00', '2026-05-19 08:12:23', NULL),
(4, 'PF3-378146', 'NELISTER  FESTON', 'Female', 27, '51', '0748820992', '0748820993', '2026-05-19 20:28:00', '2026-05-19 20:28:38', NULL),
(5, 'PF3-903360', 'DANIEL JOHN', 'Male', 23, '911 MAGOMEN', '0629320943', '0629320948', '2026-06-26 05:30:00', '2026-06-25 12:30:45', NULL),
(6, 'PF3-927996', 'JAMES JUMA SANGA', 'Female', 23, '820 TUNDUMA', '0629320949', '0629320947', '2026-07-13 22:16:00', '2026-07-17 05:17:29', NULL),
(7, 'PF3-403690', 'juma kabalo', 'Male', 23, '911 dar', '255629320941', '255629320943', '2026-07-17 11:47:00', '2026-07-17 08:47:49', NULL),
(8, 'PF3-184011', 'DANIEL JOHN', 'Female', 44, '911 Dar es Salaam - Morogoro - KILWA ROAD', '255629320941', '255629320943', '2026-07-22 08:47:00', '2026-07-22 05:48:20', '2026-07-21 22:48:20'),
(9, 'PF3-577374', 'DANIEL JOHN', 'Male', 44, '911 Dar es Salaam - Morogoro - Iringa Rd', '255629320941', '', '2026-07-30 08:51:00', '2026-07-30 05:52:13', '2026-07-29 22:52:13');

-- --------------------------------------------------------

--
-- Table structure for table `pf3_cases`
--

CREATE TABLE `pf3_cases` (
  `id` int(11) NOT NULL,
  `pf3_number` varchar(20) NOT NULL,
  `type_of_incident` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `police_station` varchar(255) NOT NULL,
  `guardian_name` varchar(255) NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `police_notes` text DEFAULT NULL,
  `rb_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pf3_cases`
--

INSERT INTO `pf3_cases` (`id`, `pf3_number`, `type_of_incident`, `description`, `police_station`, `guardian_name`, `status`, `police_notes`, `rb_number`, `created_at`, `updated_at`) VALUES
(1, 'PF3-296984', 'Accident', 'ajali ya basi ubumgo', 'ubungo', 'tadeo', 'APPROVED', 'muhudumie', 'RB-Q9YF9Y', '2026-05-07 21:05:39', '2026-05-07 21:08:19'),
(2, 'PF3-793998', 'Injury', 'apatiwe matibabu', 'kigogo post', 'frank', 'APPROVED', '', 'RB-IHMLFN', '2026-05-08 08:34:19', '2026-05-19 09:58:32'),
(3, 'PF3-727004', 'Assault', 'walibomoa chumbani  na kunishambulia', 'kigogo post', 'tadeo', 'APPROVED', '', 'RB-LONMUA', '2026-05-19 11:25:04', '2026-05-19 12:24:21'),
(4, 'PF3-378146', 'Rape', 'nilishambuliwa na kubakwa na vijana wawili', 'bima police post', 'Hamida', 'REJECTED', 'hajakamilisha taarifa zake za msingi', NULL, '2026-05-19 20:29:51', '2026-07-20 22:28:55'),
(5, 'PF3-927996', 'Accident', 'NILIVAMIWA NA WATU WASIOJULIKANA', 'bima police post', 'Hamida', 'APPROVED', '', 'RB-KYA1SP', '2026-07-17 05:18:31', '2026-07-20 22:27:15'),
(6, 'PF3-403690', 'Accident', 'niligongwa na gari', 'ubungo police post', 'Hamida', 'APPROVED', 'apatiwe matibabu', 'RB-KBNTOA', '2026-07-17 08:48:37', '2026-07-17 08:56:16'),
(7, 'PF3-184011', 'Violence', 'NILISHAMBULIWA', 'kigogo post', 'tadeo', 'APPROVED', '', 'RB-FLLYD2', '2026-07-22 05:52:07', '2026-07-30 15:40:19'),
(8, 'PF3-577374', 'Accident', 'NILIGONGWA NA GARI AINA YA HARRIER ', 'bima police post', 'KIZA', 'APPROVED', '', 'RB-DTV2W2', '2026-07-30 05:53:39', '2026-07-30 15:55:07');

-- --------------------------------------------------------

--
-- Table structure for table `police_officers`
--

CREATE TABLE `police_officers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `rank` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `police_officers`
--

INSERT INTO `police_officers` (`id`, `first_name`, `last_name`, `rank`, `email`, `phone`, `password`, `created_at`, `status`) VALUES
(1, 'Daniel', 'Daniel', 'Police Constable', 'Danielkalidas8@gmail.com', '0629320941', '$2y$10$DdU1Z8xyp4XC0QZm.ThMse7qxURhPQDyAx/1BmHPxWf8Q/GVsAsXW', '2026-05-07 21:00:03', 'inactive'),
(2, 'NASTAY', 'MWINJUMA', 'Police Constable', 'Nastay@gmail.com', '0629320949', '$2y$10$ZW6RMhkBE3GSm878cSkkseCwmsDDB8QyJsD6flAQlWv0xGE9whFvu', '2026-06-25 04:10:24', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `medical_reports`
--
ALTER TABLE `medical_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pf3_number` (`pf3_number`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pf3_number` (`pf3_number`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pf3_number` (`pf3_number`);

--
-- Indexes for table `pf3_cases`
--
ALTER TABLE `pf3_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pf3_number` (`pf3_number`);

--
-- Indexes for table `police_officers`
--
ALTER TABLE `police_officers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medical_reports`
--
ALTER TABLE `medical_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pf3_cases`
--
ALTER TABLE `pf3_cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `police_officers`
--
ALTER TABLE `police_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medical_reports`
--
ALTER TABLE `medical_reports`
  ADD CONSTRAINT `medical_reports_ibfk_1` FOREIGN KEY (`pf3_number`) REFERENCES `patients` (`pf3_number`),
  ADD CONSTRAINT `medical_reports_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`pf3_number`) REFERENCES `patients` (`pf3_number`);

--
-- Constraints for table `pf3_cases`
--
ALTER TABLE `pf3_cases`
  ADD CONSTRAINT `pf3_cases_ibfk_1` FOREIGN KEY (`pf3_number`) REFERENCES `patients` (`pf3_number`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
