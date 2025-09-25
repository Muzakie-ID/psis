-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Sep 25, 2025 at 01:55 PM
-- Server version: 8.0.43
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sis`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `teacher_id` int DEFAULT NULL,
  `urgency` enum('low','medium','high') COLLATE utf8mb4_general_ci DEFAULT 'low',
  `status` enum('pending','process','resolved') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `category`, `title`, `description`, `attachment`, `teacher_id`, `urgency`, `status`, `created_at`, `updated_at`) VALUES
(4, 1, 'bullying', 'y4', '5u6v5yuv654', NULL, NULL, 'low', 'process', '2025-09-25 00:30:07', '2025-09-25 00:30:14'),
(5, 1, 'facility', 'ac', 'ac', NULL, NULL, 'low', 'resolved', '2025-09-25 00:32:48', '2025-09-25 00:46:26'),
(6, 1, 'bullying', 'ADAWDWA', 'AWDWADAW', NULL, NULL, 'low', 'pending', '2025-09-25 00:46:20', NULL),
(7, 1, 'bullying', 'AWDWA', 'AWDAWDAW', NULL, NULL, 'low', 'pending', '2025-09-25 00:46:40', NULL),
(8, 3, 'facility', 'sfsf', 'sdfsf', NULL, NULL, 'low', 'process', '2025-09-25 13:07:57', '2025-09-25 13:12:48'),
(9, 3, 'facility', 'AC MATI', 'ini kan ac mati nah jadi panas', NULL, NULL, 'low', 'process', '2025-09-25 13:38:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaint_responses`
--

CREATE TABLE `complaint_responses` (
  `id` int NOT NULL,
  `complaint_id` int NOT NULL,
  `user_id` int NOT NULL,
  `response` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint_responses`
--

INSERT INTO `complaint_responses` (`id`, `complaint_id`, `user_id`, `response`, `created_at`) VALUES
(1, 8, 2, 'siap di cek', '2025-09-25 13:12:48'),
(2, 8, 3, 'oke siap', '2025-09-25 13:28:46'),
(3, 8, 2, 'oke siapppp', '2025-09-25 13:33:25'),
(4, 9, 2, 'siap besok ya', '2025-09-25 13:38:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nisn` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'siswa',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `nisn`, `email`, `kelas`, `role`, `created_at`) VALUES
(1, 'siswa', '$2y$10$pqh4cRo0lumefK4k7FzPdupWCwkRFi34DeOBkvK8UFsI9Vm3jg4.W', 'Dwi Artanto', '4534534543', NULL, 'XII-RPL', 'siswa', '2025-09-24 23:22:56'),
(2, 'admin', '$2y$10$ZYUWLOq/3U2SQL5LO635jeKuIdWWrdoTyH7atAApITHcoyea6kPEu', 'Kakab Setiawan', '34234324', NULL, 'XXY', 'admin', '2025-09-24 23:38:31'),
(3, 'muzakie', '$2y$10$x0Owx4cEJAwzjIjHipXhnuo8W3L5Fz11cbYJ7Q.Bvb6Tz.UfOd6yi', 'muzakie', '23874678', NULL, 'xo', 'siswa', '2025-09-25 12:48:24'),
(4, 'mayla', '$2y$10$q57DfZ/piighH0sDlf4OreQTDprhe4RH9h5HlMSrt4MQ19TTWNBvq', 'mayla', '31276492', NULL, 'XII RPL', 'siswa', '2025-09-25 13:35:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `complaint_responses`
--
ALTER TABLE `complaint_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `admin_id` (`user_id`);

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
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `complaint_responses`
--
ALTER TABLE `complaint_responses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaint_responses`
--
ALTER TABLE `complaint_responses`
  ADD CONSTRAINT `complaint_responses_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_responses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
