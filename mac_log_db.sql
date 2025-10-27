-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2025 at 07:11 PM
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
-- Database: `mac_log_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'ITSD2026', '$2y$10$Cw3dIhyvhAIuzVfRQbhbJuBHcXyv.khUew5Kd1OMpqvs7.9xyhT/u', '2025-10-24 06:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `logbook_entries`
--

CREATE TABLE `logbook_entries` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `mac_number` varchar(20) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `purpose` text NOT NULL,
  `time_in` timestamp NOT NULL DEFAULT current_timestamp(),
  `time_out` timestamp NULL DEFAULT NULL,
  `duration` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logbook_entries`
--

INSERT INTO `logbook_entries` (`id`, `student_id`, `full_name`, `course`, `year_level`, `mac_number`, `subject`, `purpose`, `time_in`, `time_out`, `duration`) VALUES
(1, '03-2526-012345', 'Dela Cruz, Juan', 'BSCS', '1st Year', 'MAC-08', 'ITE 300', 'class session', '2025-10-26 18:09:28', '2025-10-26 18:10:07', 419);

-- --------------------------------------------------------

--
-- Table structure for table `mac_computers`
--

CREATE TABLE `mac_computers` (
  `id` int(11) NOT NULL,
  `mac_number` varchar(20) NOT NULL,
  `status` varchar(20) DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mac_computers`
--

INSERT INTO `mac_computers` (`id`, `mac_number`, `status`, `created_at`) VALUES
(1, 'MAC-01', 'Available', '2025-10-24 06:10:24'),
(2, 'MAC-02', 'Available', '2025-10-24 06:10:24'),
(3, 'MAC-03', 'Available', '2025-10-24 06:10:24'),
(4, 'MAC-04', 'Available', '2025-10-24 06:10:24'),
(5, 'MAC-05', 'Available', '2025-10-24 06:10:24'),
(6, 'MAC-06', 'Available', '2025-10-24 06:10:24'),
(7, 'MAC-07', 'Available', '2025-10-24 06:10:24'),
(8, 'MAC-08', 'Available', '2025-10-24 06:10:24'),
(9, 'MAC-09', 'Available', '2025-10-24 06:10:24'),
(10, 'MAC-10', 'Available', '2025-10-24 06:10:24');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `full_name`, `email`, `password`, `course`, `year_level`, `profile_picture`, `created_at`) VALUES
(2, '03-2526-012345', 'Dela Cruz, Juan', 'jt.delacruz.up@phinmaed.com', '$2y$10$RDlS75Xfy8Jlz3Ru2VH6j.8DrZ67hA4TVS3XaEOtoGkNbJx5KoWUq', 'BSCS', '1st Year', 'uploads/profiles/03-2526-012345_1761501731.jpg', '2025-10-25 12:27:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `logbook_entries`
--
ALTER TABLE `logbook_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `mac_number` (`mac_number`);

--
-- Indexes for table `mac_computers`
--
ALTER TABLE `mac_computers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mac_number` (`mac_number`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
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
-- AUTO_INCREMENT for table `logbook_entries`
--
ALTER TABLE `logbook_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mac_computers`
--
ALTER TABLE `mac_computers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logbook_entries`
--
ALTER TABLE `logbook_entries`
  ADD CONSTRAINT `logbook_entries_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logbook_entries_ibfk_2` FOREIGN KEY (`mac_number`) REFERENCES `mac_computers` (`mac_number`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
