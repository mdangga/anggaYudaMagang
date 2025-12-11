-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Dec 11, 2025 at 12:29 PM
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
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `id_location` bigint UNSIGNED NOT NULL,
  `name_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `image_path` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_category` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id_location`, `name_location`, `description`, `longitude`, `latitude`, `image_path`, `id_category`, `created_at`, `updated_at`) VALUES
(1, 'PT. dnaks', 'sdaa[]saad', 115.23249400, -8.63912028, 'location_images/uJThZrQp6LIf1ffeJwz7FcYyeZhQe4lPpY0M6TSY.jpg', 1, '2025-12-05 11:23:16', '2025-12-05 11:23:16'),
(2, 'PT. Pertamina', 'PT. Pertamina', 115.21228800, -8.67041300, 'location_images/dnd9xsCx1Xv8fuINuceYfoCIHC5qsJetYsUYYocD.jpg', 1, '2025-12-06 15:20:15', '2025-12-06 15:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `location_request`
--

CREATE TABLE `location_request` (
  `id_request` bigint UNSIGNED NOT NULL,
  `student_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nim` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `image_path` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_category` bigint UNSIGNED NOT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location_request`
--

INSERT INTO `location_request` (`id_request`, `student_name`, `nim`, `name_location`, `description`, `longitude`, `latitude`, `image_path`, `id_category`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 'I Keenan Yudhitana', '2313231089', 'PT. dnaks', 'sdaa[]saad', 115.23249400, -8.63912028, 'location_images/uJThZrQp6LIf1ffeJwz7FcYyeZhQe4lPpY0M6TSY.jpg', 1, '2025-12-05 11:24:11', '2025-12-04 22:06:54', '2025-12-04 22:06:54'),
(2, 'Made Ananta', '2313231069', 'PT. Pertamina', 'dasa', 115.21228800, -8.67041300, 'location_images/dnd9xsCx1Xv8fuINuceYfoCIHC5qsJetYsUYYocD.jpg', 1, '2025-12-06 15:21:09', '2025-12-06 07:19:21', '2025-12-06 07:19:21'),
(3, 'Made Ananta Putra', '2313231031', 'PT. Pertamina persero', 'daasd', 115.30166400, -8.56276300, 'location_images/kfyEzgr6kutY9t6hrzzPeiYOq8tubRQsBlL4KFo4.jpg', 1, NULL, '2025-12-06 08:55:28', '2025-12-06 08:55:28'),
(4, 'I Keenan Yudhitana Saputra', '2313231034', 'PT. PTan', 'sa', 116.30423100, -8.89503600, 'location_images/USGFzNFQcMuJSV4U8aKPUqyMH0E5IUfPyPWvXfJ8.webp', 1, NULL, '2025-12-07 00:13:11', '2025-12-07 00:13:11');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id_location` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `location_request`
--
ALTER TABLE `location_request`
  MODIFY `id_request` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
