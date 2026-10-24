-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 10.123.0.16:3306
-- Generation Time: Mar 12, 2026 at 02:37 PM
-- Server version: 8.0.32
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sab2007_admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `att_lecture_staff`
--

CREATE TABLE `att_lecture_staff` (
  `id` bigint UNSIGNED NOT NULL,
  `lecture_id` bigint UNSIGNED NOT NULL,
  `is_excutive` tinyint DEFAULT '0',
  `date_excutive` date DEFAULT NULL,
  `actual_excutive_start_time` time DEFAULT NULL,
  `actual_excutive_end_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `att_lecture_staff`
--

INSERT INTO `att_lecture_staff` (`id`, `lecture_id`, `is_excutive`, `date_excutive`, `actual_excutive_start_time`, `actual_excutive_end_time`, `created_at`, `updated_at`) VALUES
(1, 11, 1, '2026-02-03', '10:00:00', '12:00:00', '2025-07-05 07:17:17', '2026-02-03 10:56:12'),
(2, 14, 1, '2025-07-30', '07:30:00', '08:30:00', '2025-07-05 10:55:58', '2025-07-30 14:20:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `att_lecture_staff`
--
ALTER TABLE `att_lecture_staff`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `att_attendance_staff_attendance_id_foreign` (`lecture_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `att_lecture_staff`
--
ALTER TABLE `att_lecture_staff`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `att_lecture_staff`
--
ALTER TABLE `att_lecture_staff`
  ADD CONSTRAINT `FK_att_attendance_staff_att_lectures` FOREIGN KEY (`lecture_id`) REFERENCES `att_lectures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
