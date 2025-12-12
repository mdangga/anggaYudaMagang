-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Dec 12, 2025 at 12:44 AM
-- Server version: 8.0.44
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `PetaMagang`
--

-- --------------------------------------------------------

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id_category`, `name_category`, `created_at`, `updated_at`) VALUES
(1, 'Software House', '2025-12-05 05:56:28', '2025-12-05 05:56:28');

-- --------------------------------------------------------

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id_location`, `name_location`, `description`, `longitude`, `latitude`, `image_path`, `id_category`, `created_at`, `updated_at`) VALUES
(1, 'PT. dnaks', 'sdaa[]saad', 115.23249400, -8.63912028, 'location_images/uJThZrQp6LIf1ffeJwz7FcYyeZhQe4lPpY0M6TSY.jpg', 1, '2025-12-05 11:23:16', '2025-12-05 11:23:16'),
(2, 'PT. Pertamina', 'PT. Pertamina', 115.21228800, -8.67041300, 'location_images/dnd9xsCx1Xv8fuINuceYfoCIHC5qsJetYsUYYocD.jpg', 1, '2025-12-06 15:20:15', '2025-12-06 15:20:15'),
(3, 'PT. Pertamina persero', 'daasd', 115.30166400, -8.56276300, 'location_images/kfyEzgr6kutY9t6hrzzPeiYOq8tubRQsBlL4KFo4.jpg', 1, '2025-12-11 12:42:15', '2025-12-11 12:42:15'),
(4, 'PT. PTan', 'sa', 116.30423100, -8.89503600, 'location_images/USGFzNFQcMuJSV4U8aKPUqyMH0E5IUfPyPWvXfJ8.webp', 1, '2025-12-11 12:47:34', '2025-12-11 12:47:34'),
(5, 'PT. Pertamina Green Energy', 'dsmaksa', 115.13333300, -8.49885800, 'location_images/MKUvgZc0s43nlI6xYJsKxLsT3yovrOtKWazIVjHW.jpg', 1, '2025-12-11 12:51:41', '2025-12-11 12:51:41'),
(6, 'PT. Pertamina Green Energyads', 'daasa', 114.62843800, -8.32540700, 'location_images/syJ5he9KI0rxvRHVFn8J8vAcIjZX5OV01kgYbg2z.jpg', 1, '2025-12-11 12:57:58', '2025-12-11 12:57:58'),
(7, 'PT. PTan1', 'sdaa', 114.48690600, -8.12799200, 'location_images/tPxeLxnAdjEx8hrBoBPhVu1T0uUVmsPh2CnbzafE.png', 1, '2025-12-11 13:02:29', '2025-12-11 13:02:29'),
(8, 'PT. PTan1', 'sdaa', 114.48690600, -8.12799200, 'location_images/tPxeLxnAdjEx8hrBoBPhVu1T0uUVmsPh2CnbzafE.png', 1, '2025-12-11 13:02:33', '2025-12-11 13:02:33'),
(9, 'PT. PTan21', 'sada', 114.31509200, -8.17379800, 'location_images/ET6b05l1f7vVFdAGRLvLtzlM1I24HqkhGYzSEftd.png', 1, '2025-12-11 13:05:20', '2025-12-11 13:05:20'),
(10, 'dasdada', 'sads', 112.90282300, -8.25688100, 'location_images/dk8pfJDYtmWZovDXR6vEYhA5vhfU5xZ0ugg0QeC5.jpg', 1, '2025-12-11 13:09:18', '2025-12-11 13:09:18'),
(11, 'dasdanjda', 'sads', 112.90282300, -8.25688100, 'location_images/dk8pfJDYtmWZovDXR6vEYhA5vhfU5xZ0ugg0QeC5.jpg', 1, '2025-12-11 13:18:42', '2025-12-11 13:18:42'),
(12, 'dasdanjda', 'sads', 112.90282300, -8.25688100, 'location_images/dk8pfJDYtmWZovDXR6vEYhA5vhfU5xZ0ugg0QeC5.jpg', 1, '2025-12-11 13:18:42', '2025-12-11 13:18:42');

-- --------------------------------------------------------

--
-- Dumping data for table `location_request`
--

INSERT INTO `location_request` (`id_request`, `student_name`, `nim`, `name_location`, `description`, `longitude`, `latitude`, `image_path`, `id_category`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 'I Keenan Yudhitana', '2313231089', 'PT. dnaks', 'sdaa[]saad', 115.23249400, -8.63912028, 'location_images/uJThZrQp6LIf1ffeJwz7FcYyeZhQe4lPpY0M6TSY.jpg', 1, '2025-12-05 11:24:11', '2025-12-04 22:06:54', '2025-12-04 22:06:54'),
(2, 'Made Ananta', '2313231069', 'PT. Pertamina', 'dasa', 115.21228800, -8.67041300, 'location_images/dnd9xsCx1Xv8fuINuceYfoCIHC5qsJetYsUYYocD.jpg', 1, '2025-12-06 15:21:09', '2025-12-06 07:19:21', '2025-12-06 07:19:21'),
(3, 'Made Ananta Putra', '2313231031', 'PT. Pertamina persero', 'daasd', 115.30166400, -8.56276300, 'location_images/kfyEzgr6kutY9t6hrzzPeiYOq8tubRQsBlL4KFo4.jpg', 1, '2025-12-11 12:42:15', '2025-12-06 08:55:28', '2025-12-11 12:42:15'),
(4, 'I Keenan Yudhitana Saputra', '2313231034', 'PT. PTan', 'sa', 116.30423100, -8.89503600, 'location_images/USGFzNFQcMuJSV4U8aKPUqyMH0E5IUfPyPWvXfJ8.webp', 1, '2025-12-11 12:47:34', '2025-12-07 00:13:11', '2025-12-11 12:47:34'),
(5, 'I Keenan Yudhitana', '2313231089', 'PT. Pertamina Green Energy', 'dsmaksa', 115.13333300, -8.49885800, 'location_images/MKUvgZc0s43nlI6xYJsKxLsT3yovrOtKWazIVjHW.jpg', 1, '2025-12-11 12:51:41', '2025-12-11 12:51:12', '2025-12-11 12:51:41'),
(6, 'I Keenan Yudhitana Saputra', '2313231034', 'PT. Pertamina Green Energyads', 'daasa', 114.62843800, -8.32540700, 'location_images/syJ5he9KI0rxvRHVFn8J8vAcIjZX5OV01kgYbg2z.jpg', 1, '2025-12-11 12:57:58', '2025-12-11 12:57:41', '2025-12-11 12:57:58'),
(7, 'I Keenan Yudhitana Saputra', '2313231031', 'PT. PTan1', 'sdaa', 114.48690600, -8.12799200, 'location_images/tPxeLxnAdjEx8hrBoBPhVu1T0uUVmsPh2CnbzafE.png', 1, '2025-12-11 13:02:33', '2025-12-11 13:02:22', '2025-12-11 13:02:33'),
(8, 'Made Ananta Putra', '2313231034', 'PT. PTan21', 'sada', 114.31509200, -8.17379800, 'location_images/ET6b05l1f7vVFdAGRLvLtzlM1I24HqkhGYzSEftd.png', 1, '2025-12-11 13:05:20', '2025-12-11 13:05:12', '2025-12-11 13:05:20'),
(9, 'I Keenan Yudhitana Saputra', '2313231034', 'dasdada', 'sads', 112.90282300, -8.25688100, 'location_images/dk8pfJDYtmWZovDXR6vEYhA5vhfU5xZ0ugg0QeC5.jpg', 1, '2025-12-11 13:09:18', '2025-12-11 13:09:01', '2025-12-11 13:09:18'),
(10, 'I Keenan Yudhitana Saputra', '2313231034', 'dasdanjda', 'sads', 112.90282300, -8.25688100, 'location_images/dk8pfJDYtmWZovDXR6vEYhA5vhfU5xZ0ugg0QeC5.jpg', 1, '2025-12-11 13:18:42', '2025-12-11 13:09:01', '2025-12-11 13:18:42');

-- --------------------------------------------------------

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$12$LDpx60cllOL6DXY7VJLCPu3UOSJLxvjtwrV498aP.twzkLDxkJvjW', 'Oka0EuEooNWAxJcuB6XSpxzGH1na8lwv21t7K4cPq2Q4zm2dUvJlZfMbfDOY', '2025-12-04 21:26:37', '2025-12-04 21:26:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id_category`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id_location`),
  ADD KEY `location_id_category_foreign` (`id_category`);

--
-- Indexes for table `location_request`
--
ALTER TABLE `location_request`
  ADD PRIMARY KEY (`id_request`),
  ADD KEY `location_request_id_category_foreign` (`id_category`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id_category` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id_location` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `location_request`
--
ALTER TABLE `location_request`
  MODIFY `id_request` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `location_id_category_foreign` FOREIGN KEY (`id_category`) REFERENCES `category` (`id_category`) ON DELETE CASCADE;

--
-- Constraints for table `location_request`
--
ALTER TABLE `location_request`
  ADD CONSTRAINT `location_request_id_category_foreign` FOREIGN KEY (`id_category`) REFERENCES `category` (`id_category`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
