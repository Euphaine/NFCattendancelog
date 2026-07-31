-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2026 at 09:35 AM
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
-- Database: `attendance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendancelogs`
--

CREATE TABLE `attendancelogs` (
  `Id` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `NfcTagId` varchar(50) NOT NULL,
  `LogDate` date NOT NULL,
  `TimeIn` datetime NOT NULL,
  `TimeOut` datetime DEFAULT NULL,
  `Status` enum('ON Campus','OFF Campus') NOT NULL,
  `Remarks` varchar(50) DEFAULT 'On Time',
  `ActionStatus` varchar(20) DEFAULT 'ENTRY',
  `Punctuality` varchar(20) DEFAULT 'ON TIME'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendancelogs`
--

INSERT INTO `attendancelogs` (`Id`, `UserId`, `NfcTagId`, `LogDate`, `TimeIn`, `TimeOut`, `Status`, `Remarks`, `ActionStatus`, `Punctuality`) VALUES
(1, 1, '123a', '2026-07-29', '2026-07-29 11:07:44', '2026-07-29 11:08:43', 'OFF Campus', 'On Time', 'ENTRY', 'ON TIME'),
(2, 1, '123a', '2026-07-29', '2026-07-29 11:16:31', '2026-07-29 11:16:38', 'OFF Campus', 'On Time', 'ENTRY', 'ON TIME'),
(3, 1, '123a', '2026-07-29', '2026-07-29 11:17:36', '2026-07-29 11:17:54', 'OFF Campus', 'On Time', 'ENTRY', 'ON TIME'),
(4, 1, '123a', '2026-07-29', '2026-07-29 17:24:09', '2026-07-29 17:24:12', 'OFF Campus', 'On Time', 'ENTRY', 'ON TIME'),
(5, 1, '123a', '2026-07-29', '2026-07-29 17:54:45', '2026-07-29 17:54:51', 'OFF Campus', 'On Time', 'ENTRY', 'ON TIME'),
(6, 1, '123a', '2026-07-29', '2026-07-29 20:43:33', '2026-07-29 21:58:35', 'OFF Campus', 'On Time', 'ENTRY', 'ON TIME'),
(7, 2, '', '2026-07-29', '2026-07-29 21:58:42', '2026-07-29 21:58:52', 'OFF Campus', 'After Hours', 'ENTRY', 'ON TIME'),
(8, 1, '', '2026-07-29', '2026-07-29 21:58:44', '2026-07-29 21:58:47', 'OFF Campus', 'After Hours', 'ENTRY', 'ON TIME'),
(9, 2, '', '2026-07-29', '2026-07-29 21:58:54', NULL, 'ON Campus', 'After Hours', 'ENTRY', 'ON TIME'),
(10, 1, '', '2026-07-29', '2026-07-29 21:58:57', NULL, 'ON Campus', 'After Hours', 'ENTRY', 'ON TIME'),
(11, 1, '', '2026-07-30', '2026-07-30 00:42:05', '2026-07-30 01:14:19', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(12, 4, '', '2026-07-30', '2026-07-30 00:42:19', '2026-07-30 01:15:10', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(13, 3, '', '2026-07-30', '2026-07-30 00:42:22', '2026-07-30 01:14:46', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(14, 1, '123a', '2026-07-29', '2026-07-29 18:56:18', '2026-07-29 18:56:18', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(15, 1, '123a', '2026-07-29', '2026-07-29 18:56:27', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(16, 1, '123a', '2026-07-29', '2026-07-29 18:56:37', '2026-07-29 18:56:37', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(17, 1, '123a', '2026-07-29', '2026-07-29 18:56:40', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(18, 2, '123b', '2026-07-29', '2026-07-29 18:56:43', '2026-07-29 18:56:43', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(19, 3, '123c', '2026-07-29', '2026-07-29 18:56:47', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(20, 4, '123d', '2026-07-29', '2026-07-29 18:56:49', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(21, 1, '123a', '2026-07-29', '2026-07-29 18:56:54', '2026-07-29 18:56:54', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(22, 2, '123b', '2026-07-29', '2026-07-29 18:57:02', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(23, 3, '123c', '2026-07-29', '2026-07-29 18:57:03', '2026-07-29 18:57:03', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(24, 2, '123b', '2026-07-29', '2026-07-29 18:57:06', '2026-07-29 18:57:06', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(25, 4, '123d', '2026-07-29', '2026-07-29 18:57:27', '2026-07-29 18:57:27', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(26, 1, '123a', '2026-07-29', '2026-07-29 19:02:40', '2026-07-29 19:05:48', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(27, 1, '123a', '2026-07-29', '2026-07-29 19:10:33', '2026-07-29 19:11:01', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(28, 1, '123a', '2026-07-30', '2026-07-30 01:14:27', '2026-07-30 01:14:34', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(29, 1, '123a', '2026-07-30', '2026-07-30 01:14:42', '2026-07-30 02:40:56', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(30, 2, '123b', '2026-07-30', '2026-07-30 01:14:44', '2026-07-30 02:41:35', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(31, 3, '123c', '2026-07-30', '2026-07-30 01:14:58', '2026-07-30 02:41:42', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(32, 4, '123d', '2026-07-30', '2026-07-30 01:15:21', '2026-07-30 02:41:48', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(33, 5, '123e', '2026-07-30', '2026-07-30 02:41:56', NULL, 'ON Campus', 'On Time', 'ENTRY', 'EARLY'),
(34, 11, '123f', '2026-07-30', '2026-07-30 02:42:00', '2026-07-30 02:42:03', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(35, 7, '123g', '2026-07-30', '2026-07-30 02:42:06', '2026-07-30 02:42:09', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(36, 12, '123h', '2026-07-30', '2026-07-30 02:42:19', NULL, 'ON Campus', 'On Time', 'ENTRY', 'EARLY'),
(37, 7, '123g', '2026-07-30', '2026-07-30 02:42:33', '2026-07-30 14:54:14', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(38, 11, '123f', '2026-07-30', '2026-07-30 02:42:37', NULL, 'ON Campus', 'On Time', 'ENTRY', 'EARLY'),
(39, 1, '123a', '2026-07-30', '2026-07-30 10:51:53', '2026-07-30 13:56:14', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(40, 13, '123i', '2026-07-30', '2026-07-30 12:27:31', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(41, 1, '123a', '2026-07-30', '2026-07-30 13:58:16', '2026-07-30 14:01:52', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(42, 1, '123a', '2026-07-30', '2026-07-30 14:05:55', '2026-07-30 14:36:25', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(43, 1, '123a', '2026-07-30', '2026-07-30 14:51:13', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(44, 2, '123b', '2026-07-30', '2026-07-30 14:54:03', '2026-07-30 14:54:05', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(45, 4, '123d', '2026-07-30', '2026-07-30 14:54:08', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(46, 2, '123b', '2026-07-30', '2026-07-30 14:54:10', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(47, 3, '123c', '2026-07-30', '2026-07-30 14:54:10', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(48, 1, '123a', '2026-07-31', '2026-07-31 13:43:02', '0000-00-00 00:00:00', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(49, 1, '123a', '2026-07-31', '2026-07-31 14:00:07', '2026-07-31 14:00:22', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(50, 1, '123a', '2026-07-31', '2026-07-31 14:00:23', '2026-07-31 14:00:25', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(51, 1, '123a', '2026-07-31', '2026-07-31 14:00:26', '2026-07-31 14:03:15', 'OFF Campus', 'Late', 'EXIT', 'OFF CAMPUS'),
(52, 1, '123a', '2026-07-31', '2026-07-31 14:03:16', '2026-07-31 14:03:19', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(53, 1, '123a', '2026-07-31', '2026-07-31 14:03:20', '2026-07-31 14:07:23', 'OFF Campus', 'On Time', 'EXIT', 'OFF CAMPUS'),
(54, 1, '123a', '2026-07-31', '2026-07-31 14:07:24', '2026-07-31 14:22:12', 'OFF Campus', 'Present', 'EXIT', ''),
(55, 2, '123b', '2026-07-31', '0000-00-00 00:00:00', '2026-07-31 14:22:17', 'OFF Campus', 'Present', 'EXIT', ''),
(56, 3, '123c', '2026-07-31', '0000-00-00 00:00:00', '2026-07-31 14:22:20', 'OFF Campus', 'Present', 'EXIT', ''),
(57, 4, '123d', '2026-07-31', '0000-00-00 00:00:00', '2026-07-31 14:22:22', 'OFF Campus', 'LATE', 'EXIT', 'LATE'),
(58, 5, '123e', '2026-07-31', '0000-00-00 00:00:00', '2026-07-31 14:22:24', 'OFF Campus', 'LATE', 'EXIT', 'LATE'),
(59, 11, '123f', '2026-07-31', '0000-00-00 00:00:00', '2026-07-31 14:22:26', 'OFF Campus', 'Present', 'EXIT', ''),
(60, 1, '123a', '2026-07-31', '2026-07-31 14:22:15', '2026-07-31 14:23:23', 'OFF Campus', 'Present', 'EXIT', ''),
(61, 7, '123g', '2026-07-31', '2026-07-31 14:22:27', '2026-07-31 14:24:25', 'OFF Campus', 'Present', 'EXIT', ''),
(62, 12, '123h', '2026-07-31', '2026-07-31 14:22:29', '2026-07-31 14:24:29', 'OFF Campus', 'Present', 'EXIT', ''),
(63, 13, '123i', '2026-07-31', '2026-07-31 14:22:31', '2026-07-31 14:24:33', 'OFF Campus', 'Late', 'EXIT', 'LATE'),
(64, 2, '123b', '2026-07-31', '2026-07-31 14:23:29', '2026-07-31 14:23:31', 'OFF Campus', 'Present', 'EXIT', ''),
(65, 2, '123b', '2026-07-31', '2026-07-31 14:23:35', '2026-07-31 14:23:52', 'OFF Campus', 'Present', 'EXIT', ''),
(66, 2, '123b', '2026-07-31', '2026-07-31 14:23:58', '2026-07-31 14:24:11', 'OFF Campus', 'Present', 'EXIT', ''),
(67, 2, '123b', '2026-07-31', '2026-07-31 14:24:13', '2026-07-31 14:25:10', 'OFF Campus', 'Present', 'EXIT', ''),
(68, 1, '123a', '2026-07-31', '2026-07-31 14:24:18', '2026-07-31 14:25:21', 'OFF Campus', 'Present', 'EXIT', ''),
(69, 13, '123i', '2026-07-31', '2026-07-31 14:24:54', NULL, 'ON Campus', 'Late', 'ENTRY', 'LATE'),
(70, 12, '123h', '2026-07-31', '2026-07-31 14:25:16', '2026-07-31 14:32:45', 'OFF Campus', 'Present', 'EXIT', ''),
(71, 3, '123c', '2026-07-31', '2026-07-31 14:32:38', '2026-07-31 14:33:00', 'OFF Campus', 'Present', 'EXIT', ''),
(72, 1, '123a', '2026-07-31', '2026-07-31 14:32:57', '2026-07-31 14:37:37', 'OFF Campus', 'Present', 'EXIT', ''),
(73, 1, '123a', '2026-07-31', '2026-07-31 15:18:24', NULL, 'ON Campus', 'Present', 'ENTRY', ''),
(74, 2, '123b', '2026-07-31', '2026-07-31 15:18:48', '2026-07-31 15:18:50', 'OFF Campus', 'Present', 'EXIT', ''),
(75, 2, '123b', '2026-07-31', '2026-07-31 15:18:52', NULL, 'ON Campus', 'Present', 'ENTRY', '');

-- --------------------------------------------------------

--
-- Table structure for table `campussettings`
--

CREATE TABLE `campussettings` (
  `Id` int(11) NOT NULL,
  `OpeningTime` time NOT NULL DEFAULT '08:00:00',
  `LateThresholdTime` time NOT NULL DEFAULT '08:30:00',
  `ClosingTime` time NOT NULL DEFAULT '17:00:00',
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `EducationalLevel` varchar(50) DEFAULT 'College',
  `ThresholdEnabled` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campussettings`
--

INSERT INTO `campussettings` (`Id`, `OpeningTime`, `LateThresholdTime`, `ClosingTime`, `UpdatedAt`, `EducationalLevel`, `ThresholdEnabled`) VALUES
(1, '08:00:00', '09:00:00', '18:00:00', '2026-07-31 06:00:18', 'College', 0),
(2, '06:30:00', '07:30:00', '18:00:00', '2026-07-31 05:50:48', 'Senior High School', 1),
(3, '06:30:00', '07:30:00', '17:00:00', '2026-07-31 05:50:50', 'Junior High School', 1),
(4, '07:00:00', '07:45:00', '16:00:00', '2026-07-31 05:50:53', 'Elementary', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `SchoolId` varchar(50) NOT NULL,
  `NfcTagId` varchar(50) NOT NULL,
  `Role` varchar(50) NOT NULL DEFAULT 'Student',
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Suffix` varchar(50) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `EducationalLevel` varchar(100) DEFAULT NULL,
  `Course` varchar(100) DEFAULT NULL,
  `YearLevel` varchar(20) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `SchoolId`, `NfcTagId`, `Role`, `FirstName`, `LastName`, `Suffix`, `Department`, `EducationalLevel`, `Course`, `YearLevel`, `CreatedAt`) VALUES
(1, '210133', '123a', 'Student', 'AJ', 'CALIXTRO', NULL, 'CCIS', 'College', 'IT', '4th', '2026-07-29 08:42:45'),
(2, '210134', '123b', 'Student', 'Bebs', 'Calixtro', NULL, 'CMAHS', 'College', 'Midwifery', '3rd', '2026-07-29 13:25:12'),
(3, '210135', '123c', 'Student', 'Niko', 'Calixtro', NULL, 'CBA', 'College', 'Acountancy', '1st', '2026-07-29 16:18:09'),
(4, '210136', '123d', 'Student', 'Kem', 'Calixtro', NULL, NULL, 'Senior High School', 'HUMMS', 'Grade 12', '2026-07-29 16:28:41'),
(5, '210137', '123e', 'Student', 'Jersus Arzareth', 'Calixtro', NULL, NULL, 'Elementary', NULL, 'Grade 1', '2026-07-29 17:30:06'),
(7, '210139', '123g', 'Staff', 'Angelito', 'Calixtro', NULL, 'TECHNICAL', NULL, NULL, NULL, '2026-07-29 17:58:24'),
(11, '210138', '123f', 'Teacher', 'Jocelyn', 'Zamora', NULL, 'CHED', NULL, NULL, NULL, '2026-07-29 18:30:32'),
(12, '210149', '123h', 'Teacher', 'Jenny', 'Calixtro', NULL, 'CECD', NULL, NULL, NULL, '2026-07-29 18:40:20'),
(13, '210140', '123i', 'Student', 'Bryan', 'Bisin', NULL, NULL, 'Junior High School', NULL, 'Grade 9', '2026-07-30 04:27:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendancelogs`
--
ALTER TABLE `attendancelogs`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `UserId` (`UserId`);

--
-- Indexes for table `campussettings`
--
ALTER TABLE `campussettings`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `SchoolId` (`SchoolId`),
  ADD UNIQUE KEY `NfcTagId` (`NfcTagId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendancelogs`
--
ALTER TABLE `attendancelogs`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `campussettings`
--
ALTER TABLE `campussettings`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendancelogs`
--
ALTER TABLE `attendancelogs`
  ADD CONSTRAINT `attendancelogs_ibfk_1` FOREIGN KEY (`UserId`) REFERENCES `users` (`Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
