-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2025 at 10:07 AM
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
-- Database: `online_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'ยา'),
(2, 'อาวุธ'),
(3, 'ชุดเกราะ'),
(12, 'else/อื่นๆ'),
(14, 'อาหาร/ขนม'),
(16, 'น้ำ'),
(17, 'พิเศษ');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `order_date`, `status`) VALUES
(19, NULL, 100.00, '2025-10-11 02:56:37', 'processing'),
(20, NULL, 700.00, '2025-10-11 03:05:20', 'pending'),
(21, 40, 11005060.00, '2025-10-11 06:23:08', 'shipped'),
(22, 40, 4091.00, '2025-10-11 06:24:11', 'cancelled'),
(23, 40, 50.00, '2025-10-11 06:36:44', 'pending'),
(24, 40, 100.00, '2025-10-11 06:45:18', 'pending'),
(25, 40, 10.00, '2025-10-11 06:48:58', 'pending'),
(26, 40, 150.00, '2025-10-11 07:03:37', 'pending'),
(27, 40, 100.00, '2025-10-11 07:09:02', 'pending'),
(28, 40, 2014.00, '2025-10-11 07:11:15', 'pending'),
(29, 40, 2077.00, '2025-10-11 07:13:55', 'pending'),
(30, 40, 669.00, '2025-10-11 07:18:45', 'pending'),
(31, 40, 10.00, '2025-10-11 07:19:47', 'pending'),
(32, 40, 10.00, '2025-10-11 07:20:09', 'pending'),
(33, 40, 10.00, '2025-10-11 07:20:56', 'pending'),
(34, 40, 10.00, '2025-10-11 07:21:35', 'pending'),
(35, 40, 50.00, '2025-10-11 07:21:51', 'pending'),
(36, 40, 50.00, '2025-10-11 07:23:58', 'pending'),
(37, 40, 10.00, '2025-10-11 07:26:15', 'pending'),
(38, 40, 10.00, '2025-10-11 07:27:11', 'pending'),
(39, 40, 10.00, '2025-10-11 07:36:07', 'pending'),
(40, 40, 10.00, '2025-10-11 07:36:41', 'pending'),
(41, 40, 100.00, '2025-10-11 07:39:11', 'pending'),
(42, 40, 100.00, '2025-10-11 07:42:36', 'pending'),
(43, 40, 500.00, '2025-10-11 07:44:27', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(25, 19, 13, 1, 100.00),
(26, 20, 12, 1, 100.00),
(27, 20, 3, 1, 250.00),
(28, 20, 2, 1, 350.00),
(29, 21, 16, 10, 10.00),
(30, 21, 15, 1, 669.00),
(31, 21, 14, 1, 10000000.00),
(32, 21, 13, 1, 100.00),
(33, 21, 12, 1, 100.00),
(34, 21, 1, 1, 1000000.00),
(35, 21, 2, 1, 2014.00),
(36, 21, 3, 1, 2077.00),
(37, 22, 3, 1, 2077.00),
(38, 22, 2, 1, 2014.00),
(39, 23, 17, 1, 50.00),
(40, 24, 13, 1, 100.00),
(41, 25, 16, 1, 10.00),
(42, 26, 17, 1, 50.00),
(43, 26, 13, 1, 100.00),
(44, 27, 12, 1, 100.00),
(45, 28, 2, 1, 2014.00),
(46, 29, 3, 1, 2077.00),
(47, 30, 15, 1, 669.00),
(48, 31, 16, 1, 10.00),
(49, 32, 16, 1, 10.00),
(50, 33, 16, 1, 10.00),
(51, 34, 16, 1, 10.00),
(52, 35, 17, 1, 50.00),
(53, 36, 17, 1, 50.00),
(54, 37, 16, 1, 10.00),
(55, 38, 16, 1, 10.00),
(56, 39, 16, 1, 10.00),
(57, 40, 16, 1, 10.00),
(58, 41, 13, 1, 100.00),
(59, 42, 13, 1, 100.00),
(60, 43, 17, 10, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `stock`, `image`, `category_id`, `created_at`) VALUES
(1, 'Elixir (limited edition)', 'ฟื้นฟู HP และ MP เต็ม: เป็นไอเทมที่ทรงพลังที่สุดในการฟื้นฟูพลังชีวิตและพลังเวทมนตร์ให้กลับมาเต็มที่', 1000000.00, 100, 'product_1758773263.png', 1, '2025-08-07 03:38:35'),
(2, 'Sword Excalibur', 'เป็นตำนานเล่าต่อกันมาว่า ผู้ที่สามารถดึงดาบออกมาได้นั้นจะขึ้นเป็น \"กษัตริย์\"', 2014.00, 1, 'product_1760155513.jpg', 2, '2025-08-07 03:38:35'),
(3, 'Power Armor', 'ชุดเกราะพลังงาน (Power Armor): เป็นชุดเกราะขนาดใหญ่ที่ช่วยเพิ่มความสามารถทางกายภาพของผู้สวมใส่ และให้การป้องกันที่สูง', 2077.00, 999, 'product_1760155844.jpg', 3, '2025-08-07 03:38:35'),
(12, 'ยาเพิ่ม HP', '-', 100.00, 999, 'product_1758773187.jpeg', 1, '2025-09-18 03:51:33'),
(13, 'ไก่ๆ', 'ไก่ๆๆๆๆๆ', 100.00, 999, 'product_1760146636.jpg', 14, '2025-10-11 01:37:16'),
(14, 'หนังสือสูตรโกง', 'หนังสือสูตรโกง เมื่อกดใช้จะสามารถเปลี่ยนแปลงความเป็นจริงได้(แค่ในเกม)', 10000000.00, 1, 'product_1760154395.png', 12, '2025-10-11 03:46:35'),
(15, 'นาฬิกาหยุดเวลา', 'นาฬิกาหยุดเวลา คุณรุูวิธีใช้อยู่แล้ว', 669.00, 1, 'product_1760154705.jpg', 12, '2025-10-11 03:51:45'),
(16, 'Stand arrow', 'ผู้ถุกเลือกเท่านั้นที่จะได้เป็น Stand user', 10.00, 999, 'product_1760155069.jpg', 12, '2025-10-11 03:57:49'),
(17, 'น้ำแข็ง', 'น้ำเย็นๆมากๆ', 50.00, 100, 'product_1760164367.jpg', 16, '2025-10-11 06:32:47'),
(18, 'touch grass', 'touch the grass!', 1.00, 999, 'product_1760169587.jpg', 17, '2025-10-11 07:59:47');

-- --------------------------------------------------------

--
-- Table structure for table `shipping`
--

CREATE TABLE `shipping` (
  `shipping_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `shipping_status` enum('not_shipped','shipped','delivered') DEFAULT 'not_shipped'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping`
--

INSERT INTO `shipping` (`shipping_id`, `order_id`, `address`, `city`, `postal_code`, `phone`, `shipping_status`) VALUES
(9, 19, '15/2', 'นครปฐม', '72000', '1150', 'delivered'),
(10, 20, '15/2', 'นครปฐม', '777', '1150', 'delivered'),
(11, 21, '15/2', 'นครปฐม', '777', '1150', 'delivered'),
(12, 22, '15/2', 'นครปฐม', '777', '1150', 'shipped'),
(13, 23, '132', 'อาหวัง', '72000', '1152', 'not_shipped'),
(14, 24, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(15, 25, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(16, 26, '15/2', 'นครปฐม', '777', '1152', 'not_shipped'),
(17, 27, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(18, 28, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(19, 29, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(20, 30, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(21, 31, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(22, 32, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(23, 33, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(24, 34, '11', 'นครปฐม', '777', '1150', 'not_shipped'),
(25, 35, '11', 'นครปฐม', '777', '1150', 'not_shipped'),
(26, 36, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(27, 37, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(28, 38, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(29, 39, '11', NULL, NULL, NULL, 'not_shipped'),
(30, 40, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(31, 41, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(32, 42, '15/2', 'นครปฐม', '777', '1150', 'not_shipped'),
(33, 43, '15/2', 'นครปฐม', '72000', '1150', 'not_shipped');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('Guild Master','Adventurer') DEFAULT 'Adventurer',
  `adventure_rank` char(1) NOT NULL DEFAULT 'F',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `class` varchar(50) NOT NULL DEFAULT 'นักผจญภัย'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `role`, `adventure_rank`, `created_at`, `class`) VALUES
(6, 'Tuchtapong011', '$2y$10$sL5joEyLJS65kAJoGJbyR./A2.ImvomiRBTwofBlUkmkEmfFnZvdu', '664230666@webmail.npru.ac.th', 'GOD OF WAR', 'Guild Master', '', '2025-08-14 04:40:11', 'นักผจญภัย'),
(40, 'test4', '$2y$10$ekR8p54iYrMcP6aSccBJ3e3.Rkq8T0r5p4ahyMrNeX.bx/IZnGdKq', 'test4@gmail.com', 'Forh', 'Adventurer', 'F', '2025-10-11 06:17:30', 'นักเวทย์'),
(41, 'test5', '$2y$10$AHY69ggjHCvrhLaPK9QZGeaJQMPLYqeQOuElCDmnwvi/L03GD6Brm', 'test5@gmail.com', '5', 'Adventurer', 'F', '2025-10-11 07:48:27', 'นักธนู');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `shipping`
--
ALTER TABLE `shipping`
  ADD PRIMARY KEY (`shipping_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `shipping`
--
ALTER TABLE `shipping`
  MODIFY `shipping_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `shipping`
--
ALTER TABLE `shipping`
  ADD CONSTRAINT `shipping_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
