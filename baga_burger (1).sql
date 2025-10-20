-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 11:23 AM
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
-- Database: `baga_burger`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message`, `submitted_at`) VALUES
(8, 'clark duke sulit', 'clarksulit5@gmail.com', 'qweqweqweqwe', '2025-08-13 02:12:09'),
(9, 'qweqwe', 'clarksulit5@gmail.com', 'qweqwe', '2025-08-13 02:16:35'),
(10, 'qweqweqw', 'clarksulit5@gmail.com', 'qweqweqwe', '2025-08-13 02:19:08'),
(11, 'ceedee213', 'sulitclark5@gmail.com', 'qweqweqwe', '2025-08-13 02:26:43'),
(12, 'ceedee88', 'clarksulit5@gmail.com', 'agasgsagasdfasfdasdfadf', '2025-08-13 02:32:11'),
(13, 'ceedee11', 'clarksulit0@gmail.com', '12312312312312qewqweqwe', '2025-08-13 02:35:44'),
(14, 'clark duke sulit ', 'clarksulit5@gmail.com', 'my order did not reach me', '2025-09-30 21:35:28'),
(15, 'qweqwe', 'sulitclark5@gmail.com', 'qweqweqwe', '2025-09-30 21:41:18'),
(16, 'clark', 'clarksulit0@gmail.com', 'qweqweqweqwe', '2025-10-02 06:12:51'),
(17, 'qweqweqwe', 'qweqweqwe@yahoo.com', 'qweqweqwe', '2025-10-09 01:43:03');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `category` varchar(50) NOT NULL DEFAULT 'Main',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `price`, `code`, `stock`, `category`, `created_at`) VALUES
(1, 'Beef Baga', 45.00, 'B1', 96, 'Main', '2025-09-24 05:58:25'),
(2, 'Beef Baga W/Cheese', 55.00, 'B2', 83, 'Main', '2025-09-24 05:58:25'),
(3, 'Coleslaw Baga', 87.00, 'B3', 67, 'Main', '2025-09-24 05:58:25'),
(4, 'Cheesy Bacon Baga', 91.00, 'B4', 83, 'Main', '2025-09-24 05:58:25'),
(5, 'Double Cheesy Baga', 100.00, 'B5', 74, 'Main', '2025-09-24 05:58:25'),
(6, 'BBQ Bacon Beefy Baga W/Egg', 111.00, 'B6', 94, 'Main', '2025-09-24 05:58:25'),
(7, 'Chicken Crunch', 45.00, 'C1', 81, 'Main', '2025-09-24 05:58:25'),
(8, 'Cheesy Chicken Crunch', 55.00, 'C2', 89, 'Main', '2025-09-24 05:58:25'),
(9, 'Coleslaw Chicken Crunch', 87.00, 'C3', 78, 'Main', '2025-09-24 05:58:25'),
(10, 'Hotdog Sandwich', 40.00, 'H1', 89, 'Main', '2025-09-24 05:58:25'),
(11, 'Egg Sandwich', 20.00, 'Egg Sandwich', 81, 'Main', '2025-09-24 05:58:25'),
(12, 'Ham Sandwich', 20.00, 'Ham Sandwich', 90, 'Main', '2025-09-24 05:58:25'),
(13, 'Beef Loaf Sandwich', 20.00, 'Beef Loaf Sandwich', 89, 'Main', '2025-09-24 05:58:25'),
(14, 'Footlong Sandwich', 56.00, 'Footlong Sandwich', 89, 'Main', '2025-09-24 05:58:25'),
(15, 'Hungarian Sandwich', 60.00, 'Hungarian Sandwich', 88, 'Main', '2025-09-24 05:58:25'),
(16, 'Bacon', 20.00, 'Bacon', 56, 'Add-ons', '2025-09-24 05:58:25'),
(17, 'Egg', 20.00, 'Egg', 91, 'Add-ons', '2025-09-24 05:58:25'),
(18, 'Coleslaw', 20.00, 'Coleslaw', 89, 'Add-ons', '2025-09-24 05:58:25'),
(19, 'Patty', 20.00, 'Patty', 90, 'Add-ons', '2025-09-24 05:58:25'),
(20, 'Cheese', 20.00, 'Cheese', 89, 'Add-ons', '2025-09-24 05:58:25'),
(21, 'Chicharap', 20.00, 'Chicharap', 90, 'Add-ons', '2025-09-24 05:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_reference` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `created_at`, `status`, `total_amount`, `payment_method`, `payment_reference`) VALUES
(8, 10, '2025-08-13 10:26:29', 'Pending', 0.00, '', NULL),
(9, 11, '2025-08-13 10:32:51', 'Pending', 0.00, '', NULL),
(10, 12, '2025-08-13 10:36:18', 'Pending', 0.00, '', NULL),
(11, 1, '2025-08-27 17:10:21', 'Pending', 0.00, '', NULL),
(12, 23, '2025-09-10 14:44:07', 'Pending', 0.00, '', NULL),
(13, 23, '2025-09-10 15:34:55', 'Pending', 0.00, '', NULL),
(14, 2, '2025-09-10 15:35:40', 'Pending', 0.00, '', NULL),
(15, 23, '2025-09-10 15:41:18', 'Pending', 0.00, '', NULL),
(16, 23, '2025-09-10 15:53:04', 'Pending', 0.00, '', NULL),
(17, 23, '2025-09-10 15:54:26', 'Pending', 0.00, '', NULL),
(18, 23, '2025-09-10 15:56:00', 'Pending', 0.00, '', NULL),
(19, 1, '2025-09-10 15:56:50', 'Pending', 0.00, '', NULL),
(20, 23, '2025-09-10 15:59:44', 'Pending', 0.00, '', NULL),
(21, 23, '2025-09-10 16:00:51', 'Pending', 0.00, '', NULL),
(22, 23, '2025-09-10 16:01:01', 'Pending', 0.00, '', NULL),
(23, 23, '2025-09-18 05:21:09', 'Pending', 0.00, '', NULL),
(24, 23, '2025-09-18 05:23:33', 'Pending', 0.00, '', NULL),
(25, 23, '2025-09-18 05:34:14', 'Completed', 831.00, 'gcash', NULL),
(26, 1, '2025-09-18 05:42:44', 'Completed', 831.00, 'gcash', NULL),
(27, 1, '2025-09-18 05:58:01', 'Completed', 831.00, 'gcash', NULL),
(28, 1, '2025-09-18 05:58:19', 'Completed', 831.00, 'gcash', 'qweqweqwe'),
(29, 1, '2025-09-18 06:00:44', 'Completed', 1092.00, 'gcash', 'qweqweqweqwe'),
(30, 1, '2025-09-18 06:01:29', 'Completed', 165.00, 'paymaya', NULL),
(31, 27, '2025-09-18 06:14:44', 'Completed', 135.00, 'gcash', NULL),
(32, 33, '2025-09-18 08:05:02', 'Completed', 396.00, 'gcash', 'qweqweqweqwe'),
(33, 15, '2025-09-18 08:08:36', 'Completed', 1152.00, 'gcash', NULL),
(34, 15, '2025-09-18 14:39:28', 'Completed', 1467.00, 'gcash', 'qweqwesdqw'),
(35, 15, '2025-09-24 12:11:47', 'Completed', 1152.00, 'gcash', 'qweqweqwewqe'),
(36, 35, '2025-09-24 13:03:09', 'Completed', 1092.00, 'gcash', NULL),
(37, 35, '2025-09-24 13:21:23', 'Completed', 831.00, 'gcash', NULL),
(38, 35, '2025-09-24 13:25:31', 'Completed', 831.00, 'gcash', 'asdasdasdsdads'),
(39, 35, '2025-09-24 13:35:39', 'Completed', 831.00, 'gcash', NULL),
(40, 35, '2025-09-24 13:53:26', 'Completed', 230.00, 'gcash', 'qweqweqwe'),
(41, 35, '2025-09-24 13:54:55', 'Completed', 530.00, 'gcash', 'qweqweqweqwe'),
(42, 35, '2025-09-24 13:55:44', 'Completed', 1599.00, 'gcash', 'qweqweqwe'),
(43, 35, '2025-09-24 14:06:53', 'Completed', 604.00, 'gcash', 'qweqweqwe'),
(44, 35, '2025-09-24 14:15:59', 'Completed', 564.00, 'gcash', 'qweqweqwe'),
(45, 35, '2025-09-24 14:25:59', 'Pending Payment', 176.00, 'gcash', NULL),
(46, 35, '2025-09-24 14:28:24', 'Completed', 6604.00, 'gcash', 'qweqweqweqew'),
(47, 37, '2025-09-25 17:37:55', 'For Confirmation', 2925.00, 'gcash', 'qweqweqwe'),
(48, 37, '2025-09-25 17:39:05', 'Completed', 2925.00, 'gcash', ';;kasda;sdasdads'),
(49, 4, '2025-10-01 05:33:35', 'For Confirmation', 271.00, 'gcash', 'qweqweqwe'),
(50, 4, '2025-10-02 14:12:32', 'For Confirmation', 241.00, 'gcash', 'qweqwe'),
(51, 25, '2025-10-09 09:43:58', 'Completed', 507.00, 'gcash', '99988222111'),
(52, 25, '2025-10-09 10:02:33', 'Pending Payment', 372.00, 'gcash', NULL),
(53, 25, '2025-10-09 10:41:00', 'Pending Payment', 225.00, 'gcash', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `quantity`, `price_at_purchase`) VALUES
(28, 8, 1, 3, 0.00),
(29, 8, 3, 3, 0.00),
(30, 8, 5, 3, 0.00),
(31, 8, 7, 3, 0.00),
(32, 9, 1, 3, 0.00),
(33, 9, 2, 3, 0.00),
(34, 9, 3, 3, 0.00),
(35, 9, 4, 3, 0.00),
(36, 9, 5, 3, 0.00),
(37, 9, 6, 3, 0.00),
(38, 9, 7, 3, 0.00),
(39, 9, 8, 3, 0.00),
(40, 10, 1, 3, 0.00),
(41, 10, 2, 3, 0.00),
(42, 10, 3, 3, 0.00),
(43, 10, 4, 3, 0.00),
(44, 10, 5, 3, 0.00),
(45, 10, 6, 3, 0.00),
(46, 10, 7, 3, 0.00),
(47, 10, 8, 3, 0.00),
(48, 10, 9, 3, 0.00),
(49, 10, 10, 3, 0.00),
(50, 11, 1, 2, 0.00),
(51, 11, 3, 2, 0.00),
(52, 12, 5, 3, 0.00),
(53, 12, 7, 20, 0.00),
(54, 13, 1, 20, 0.00),
(55, 14, 1, 15, 0.00),
(56, 15, 1, 20, 0.00),
(57, 16, 1, 20, 0.00),
(58, 16, 3, 20, 0.00),
(59, 17, 1, 20, 0.00),
(60, 18, 1, 15, 0.00),
(61, 19, 1, 20, 0.00),
(62, 20, 1, 20, 0.00),
(63, 21, 16, 23, 0.00),
(64, 22, 1, 15, 0.00),
(65, 23, 1, 3, 0.00),
(66, 24, 1, 3, 0.00),
(67, 25, 1, 3, 45.00),
(68, 25, 3, 3, 87.00),
(69, 25, 5, 3, 100.00),
(70, 25, 7, 3, 45.00),
(71, 26, 1, 3, 45.00),
(72, 26, 3, 3, 87.00),
(73, 26, 5, 3, 100.00),
(74, 26, 7, 3, 45.00),
(75, 27, 1, 3, 45.00),
(76, 27, 3, 3, 87.00),
(77, 27, 5, 3, 100.00),
(78, 27, 7, 3, 45.00),
(79, 28, 1, 3, 45.00),
(80, 28, 3, 3, 87.00),
(81, 28, 5, 3, 100.00),
(82, 28, 7, 3, 45.00),
(83, 29, 1, 3, 45.00),
(84, 29, 3, 3, 87.00),
(85, 29, 5, 3, 100.00),
(86, 29, 7, 3, 45.00),
(87, 29, 9, 3, 87.00),
(88, 30, 2, 3, 55.00),
(89, 31, 1, 3, 45.00),
(90, 32, 1, 3, 45.00),
(91, 32, 3, 3, 87.00),
(92, 33, 1, 3, 45.00),
(93, 33, 3, 3, 87.00),
(94, 33, 5, 3, 100.00),
(95, 33, 7, 3, 45.00),
(96, 33, 9, 3, 87.00),
(97, 33, 11, 3, 20.00),
(98, 34, 1, 3, 45.00),
(99, 34, 2, 3, 55.00),
(100, 34, 3, 3, 87.00),
(101, 34, 4, 3, 91.00),
(102, 34, 5, 3, 100.00),
(103, 34, 6, 3, 111.00),
(104, 35, 1, 3, 45.00),
(105, 35, 3, 3, 87.00),
(106, 35, 5, 3, 100.00),
(107, 35, 7, 3, 45.00),
(108, 35, 9, 3, 87.00),
(109, 35, 11, 3, 20.00),
(110, 36, 1, 3, 45.00),
(111, 36, 3, 3, 87.00),
(112, 36, 5, 3, 100.00),
(113, 36, 7, 3, 45.00),
(114, 36, 9, 3, 87.00),
(115, 37, 1, 3, 45.00),
(116, 37, 3, 3, 87.00),
(117, 37, 5, 3, 100.00),
(118, 37, 7, 3, 45.00),
(119, 38, 1, 3, 45.00),
(120, 38, 3, 3, 87.00),
(121, 38, 5, 3, 100.00),
(122, 38, 7, 3, 45.00),
(123, 39, 1, 3, 45.00),
(124, 39, 3, 3, 87.00),
(125, 39, 5, 3, 100.00),
(126, 39, 7, 3, 45.00),
(127, 40, 13, 2, 20.00),
(128, 40, 20, 2, 20.00),
(129, 40, 8, 2, 55.00),
(130, 40, 21, 2, 20.00),
(131, 41, 9, 2, 87.00),
(132, 41, 5, 1, 100.00),
(133, 41, 11, 1, 20.00),
(134, 41, 14, 1, 56.00),
(135, 41, 12, 1, 20.00),
(136, 41, 10, 2, 40.00),
(137, 41, 15, 1, 60.00),
(138, 41, 19, 1, 20.00),
(139, 42, 16, 6, 20.00),
(140, 42, 6, 9, 111.00),
(141, 42, 1, 6, 45.00),
(142, 42, 2, 2, 55.00),
(143, 42, 13, 2, 20.00),
(144, 42, 20, 3, 20.00),
(145, 43, 16, 8, 20.00),
(146, 43, 6, 4, 111.00),
(147, 44, 16, 6, 20.00),
(148, 44, 6, 4, 111.00),
(149, 45, 6, 1, 111.00),
(150, 45, 1, 1, 45.00),
(151, 45, 20, 1, 20.00),
(152, 46, 16, 7, 20.00),
(153, 46, 6, 7, 111.00),
(154, 46, 1, 7, 45.00),
(155, 46, 2, 8, 55.00),
(156, 46, 13, 7, 20.00),
(157, 46, 20, 6, 20.00),
(158, 46, 4, 8, 91.00),
(159, 46, 8, 7, 55.00),
(160, 46, 21, 7, 20.00),
(161, 46, 7, 7, 45.00),
(162, 46, 18, 7, 20.00),
(163, 46, 3, 6, 87.00),
(164, 46, 9, 6, 87.00),
(165, 46, 5, 6, 100.00),
(166, 46, 17, 6, 20.00),
(167, 46, 11, 5, 20.00),
(168, 46, 14, 5, 56.00),
(169, 46, 12, 5, 20.00),
(170, 46, 10, 6, 40.00),
(171, 46, 15, 6, 60.00),
(172, 46, 19, 6, 20.00),
(173, 47, 16, 5, 20.00),
(174, 47, 6, 4, 111.00),
(175, 47, 1, 2, 45.00),
(176, 47, 2, 2, 55.00),
(177, 47, 13, 2, 20.00),
(178, 47, 20, 3, 20.00),
(179, 47, 4, 2, 91.00),
(180, 47, 8, 2, 55.00),
(181, 47, 21, 2, 20.00),
(182, 47, 7, 2, 45.00),
(183, 47, 18, 2, 20.00),
(184, 47, 3, 2, 87.00),
(185, 47, 9, 3, 87.00),
(186, 47, 5, 3, 100.00),
(187, 47, 17, 2, 20.00),
(188, 47, 11, 4, 20.00),
(189, 47, 14, 4, 56.00),
(190, 47, 12, 4, 20.00),
(191, 47, 10, 4, 40.00),
(192, 47, 15, 4, 60.00),
(193, 47, 19, 3, 20.00),
(194, 48, 16, 5, 20.00),
(195, 48, 6, 4, 111.00),
(196, 48, 1, 2, 45.00),
(197, 48, 2, 2, 55.00),
(198, 48, 13, 2, 20.00),
(199, 48, 20, 3, 20.00),
(200, 48, 4, 2, 91.00),
(201, 48, 8, 2, 55.00),
(202, 48, 21, 2, 20.00),
(203, 48, 7, 2, 45.00),
(204, 48, 18, 2, 20.00),
(205, 48, 3, 2, 87.00),
(206, 48, 9, 3, 87.00),
(207, 48, 5, 3, 100.00),
(208, 48, 17, 2, 20.00),
(209, 48, 11, 4, 20.00),
(210, 48, 14, 4, 56.00),
(211, 48, 12, 4, 20.00),
(212, 48, 10, 4, 40.00),
(213, 48, 15, 4, 60.00),
(214, 48, 19, 3, 20.00),
(215, 49, 16, 1, 20.00),
(216, 49, 6, 1, 111.00),
(217, 49, 1, 1, 45.00),
(218, 49, 2, 1, 55.00),
(219, 49, 13, 1, 20.00),
(220, 49, 20, 1, 20.00),
(221, 50, 16, 1, 20.00),
(222, 50, 6, 1, 111.00),
(223, 50, 1, 2, 45.00),
(224, 50, 20, 1, 20.00),
(225, 51, 16, 1, 20.00),
(226, 51, 6, 1, 111.00),
(227, 51, 1, 1, 45.00),
(228, 51, 13, 1, 20.00),
(229, 51, 20, 1, 20.00),
(230, 51, 8, 1, 55.00),
(231, 51, 18, 1, 20.00),
(232, 51, 5, 1, 100.00),
(233, 51, 14, 1, 56.00),
(234, 51, 15, 1, 60.00),
(235, 52, 16, 1, 20.00),
(236, 52, 6, 2, 111.00),
(237, 52, 1, 2, 45.00),
(238, 52, 20, 2, 20.00),
(239, 53, 2, 2, 55.00),
(240, 53, 8, 1, 55.00),
(241, 53, 20, 3, 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verification_token` varchar(255) DEFAULT NULL,
  `role` enum('user','admin','owner') DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `contact_number`, `password`, `verification_token`, `role`, `is_active`, `is_verified`) VALUES
(1, 'ceedee213', NULL, NULL, '$2y$10$/SP0tRh41JcQYJiIte3eYe1KABnypiJRqDAtfCetGeVpFEmTo8TUK', NULL, 'user', 0, 0),
(2, 'ceedee123', NULL, NULL, '$2y$10$iOeD7JeYFkKyR2CU0yu4puKq3nWyNRJV1pJH4RcErF2lBqGR3cg3G', NULL, 'user', 0, 0),
(3, 'ceedee', NULL, NULL, '$2y$10$EoLo3i/iOUnnEwljXAPai.css0H5fEOARBtXvzjAJNADW02e43Auy', NULL, 'user', 1, 0),
(4, 'ceedee1', NULL, NULL, '$2y$10$DysOA5G89YHoLF4DLcsRjO/KfEYSpHcQQ91S23RU2k1xNN6IXmEIG', NULL, 'user', 1, 0),
(5, 'ceedee2', NULL, NULL, '$2y$10$c95T1aBLI2Ir1yu3xcMsEusoC0FPDtrSxcAsu9nBy3IXDXPciu.BO', NULL, 'user', 1, 0),
(6, 'ceedee23', NULL, NULL, '$2y$10$h84IrH60yjLDjjvh4hd6Ne7t/H/1Ymti3Au50YwHerEkVPjXiaxYO', NULL, 'user', 1, 0),
(7, 'qwerty123', NULL, NULL, '$2y$10$YFjk4Zh4evbgpSdWn4BGLePM7lqMiwYbsYw84mdZ1U0HAoiVWnYPK', NULL, 'user', 1, 0),
(8, 'qwerty222', NULL, NULL, '$2y$10$SND024eTUf8dPMOmEnbVs.noe87RVKFG/JQ2xFTu0NCcE3U8fCWKC', NULL, 'user', 1, 0),
(9, 'ceedee1799', NULL, NULL, '$2y$10$l9iO4JAt31.1iAE45Q3XJOlZv6.fd8YqpAB2jVPiCEfgjkm4oS0cm', NULL, 'user', 1, 0),
(10, 'ceedee99', NULL, NULL, '$2y$10$ntEEOqcp57hrv5wBjEj7qOKr8UvJigxnprpsmIgsE.fTHid/NjrGy', NULL, 'user', 1, 0),
(11, 'ceedee88', NULL, NULL, '$2y$10$wxzkivUF2IZ1IIKvF10VAO85z2dM1B1lBOBETTGdBiaBVWQaQHqCW', NULL, 'user', 1, 0),
(12, 'ceedee11', NULL, NULL, '$2y$10$iptRDgH03NS1mbQff/vcyeqh3X4gCl4Qd9YKdnQZ3qr7xEo8bt75a', NULL, 'user', 1, 0),
(13, 'ceedee21311', NULL, NULL, '$2y$10$Jqh8NV8hnHeTrJ1JSgqK9.6BGo8yjYvMkHku5VQd2w3HhhPmLdrka', NULL, 'user', 1, 0),
(14, 'dede1', NULL, NULL, '$2y$10$gp5veeI6oASGvpg2.Qq.iOev4m15Xi9OB8xL5c803nYX0JdJx5Xou', NULL, 'user', 1, 0),
(15, 'qqqqq1', 'qweqwe@gmail.com', '09568832213', '$2y$10$1l5WvFHjJrEnHhszu1TYqe6mB6cUGfikGT5tK4YTShoro2OG1J0fW', NULL, 'user', 1, 0),
(16, 'ceedee2211111111', 'sulitclark1231231235@gmail.com', '09568832213', '$2y$10$9hDABPaczdRcRnA527Pje.5UX75A6nbWaAspZlFPzXraYb2b1FOuy', NULL, 'user', 1, 0),
(23, 'admin1', 'admin1@example.com', '09123456789', '$2y$10$AeYGJNg/3TpR7M8tz8Ug7eNdDEjthlNudbRXC.Qa2dbsP95ELJgkO', NULL, 'admin', 1, 0),
(24, 'admin2', 'admin2@example.com', '09987654321', '$2y$10$cOb0rYgMUuEpGeTqDt4ZQe94VZsEZC8h1BZMYfNjQXPM1AkbqDi8i', NULL, 'owner', 1, 0),
(25, 'ceedee998', 'clarksulit523232@gmail.com', '09568832213', '$2y$10$G0AkWclUjDPz3W0UGWsVR.zVtUlxNIsBTyfSwTXwdwXbPJejn08rK', NULL, 'user', 1, 0),
(26, 'qwerty990', 'clarksulit5222199@gmail.com', '09568832213', '$2y$10$oPk1K61TKY.TxNKHaJBqK.GEzFHT9NYPHNmuYx5BrK.v/YNh5r9R2', NULL, 'user', 1, 0),
(27, 'Owner1', 'owner@bagaburger.com', NULL, '$2y$10$qwbnHoG6X8d6eSEDfp0FMOegA5WVMzzFWNA3b8/RvBH/FutIQdk2S', NULL, 'owner', 1, 0),
(28, 'qwerty1', 'clarksulit2323235@gmail.com', NULL, '$2y$10$AtmobdRebevNks6OclkObeZ7zbQCpj0KWJ69q8r7S7ev6yu97pieW', NULL, 'admin', 1, 0),
(31, 'qwerty2', 'clarksulit2323235@gmail.com', NULL, '$2y$10$BtRej/AC2ExQTZUtgDK2NuhEI7zkmaKaln9DvZbm3jZBr5SutMgiW', NULL, 'user', 1, 0),
(32, 'ceedee9', 'clarksulit5123123123@gmail.com', '09568832213', '$2y$10$WzvNUW6rEB2rAiQQtAWkiOf0IG1Bk1RcoNVcNwxeM3Ggy3Ls/pfbG', NULL, 'user', 0, 0),
(33, 'admin3', 'clarksulit0@gmail.com', '09568832213', '$2y$10$h4lcJRPPHBN59obFoRv.Gu2aZA3VXXDpy9pr2OFpygv4QfJJluP4W', NULL, 'user', 1, 0),
(34, 'boyet1', 'clarksulit5@gmail.com', '09568832213', '$2y$10$l0QkexBYuWEEnKtKuMEhe.xDbWKg7dluw/3dwtMyx8yXI9.VLRzHq', '696401023f4e2cd98641186f8e2e6a6ddaba00b28c88b5cdb923945b9c9d4d62', 'user', 0, 0),
(35, 'boyet2', 'clarksqweqweqweulit1@gmail.com', '09568832213', '$2y$10$uyWsJAvJEPhOwt41mKMUa.cJ9FYgIxKCayctyYianVx.KtdrpfYXC', NULL, 'user', 1, 1),
(36, 'boyet3', 'clarksulit1@gmail.com', '09568832213', '$2y$10$6c6x3ob.jI60yt1SnsdngOVdEXiJsxCw7MO8oRhTF0I4tbkrkkGnG', NULL, 'user', 1, 1),
(37, 'clark1', 'sulitclark0@gmail.com', '09123123222', '$2y$10$p6MQGHUduaS.g4GUVuBAnO5uLvMhFbwA6V94bVnd/RsPS4r3GP78C', NULL, 'user', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_id` (`menu_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
