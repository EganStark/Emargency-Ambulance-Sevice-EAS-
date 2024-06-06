-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2024 at 07:09 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ambulance_service`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `AdminID` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `PasswordHash` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`AdminID`, `Name`, `PasswordHash`, `Email`) VALUES
(1, 'Anik', '12345', 'aniklal2020@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `DriverID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `LicenseNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`DriverID`, `UserID`, `LicenseNumber`) VALUES
(16, 4, '25262626');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `ServiceID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `DriverID` int(11) DEFAULT NULL,
  `VehicleID` int(11) DEFAULT NULL,
  `PickupLocation` varchar(255) DEFAULT NULL,
  `DropoffLocation` varchar(255) DEFAULT NULL,
  `StartTime` datetime DEFAULT NULL,
  `EndTime` datetime DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`ServiceID`, `UserID`, `DriverID`, `VehicleID`, `PickupLocation`, `DropoffLocation`, `StartTime`, `EndTime`, `Status`) VALUES
(12, 2, 16, 5, 'Bannasree ', 'Manikganj', '2023-12-24 18:59:00', '2023-12-24 20:41:49', '0');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `FirstName` varchar(255) DEFAULT NULL,
  `LastName` varchar(255) DEFAULT NULL,
  `PhoneNumber` varchar(15) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `PasswordHash` varchar(255) DEFAULT NULL,
  `IsDriver` tinyint(1) DEFAULT 0,
  `NIDNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `FirstName`, `LastName`, `PhoneNumber`, `Email`, `Address`, `PasswordHash`, `IsDriver`, `NIDNumber`) VALUES
(2, 'Anik', 'Dey', '01537156605', 'anikdey228@gmail.com', 'Banasree,Dhaka', '$2y$10$dG0ouSnGHjDpMTAMWGOYaeDGPUDT4.AxST0PfrDDaYfSd4iTVnx/a', 0, '3536363636'),
(4, 'Firoz', 'Rizvi', '01738438383', 'rizvi@gmail.com', 'Rampura,Dhaka', '$2y$10$Sx.9x/aFHtNpk20lSvMQF.IwhRZgXEYblD2GhpDSQ3GUqWc8FsUTq', 1, '2036353677');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `VehicleID` int(11) NOT NULL,
  `VehicleNumber` varchar(20) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `CurrentLocation` varchar(255) DEFAULT NULL,
  `Availability` tinyint(1) DEFAULT NULL,
  `DriverID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`VehicleID`, `VehicleNumber`, `Type`, `CurrentLocation`, `Availability`, `DriverID`) VALUES
(5, '24234', 'Hiace', 'Rampura,Dhaka', 1, 16);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`DriverID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`ServiceID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `DriverID` (`DriverID`),
  ADD KEY `VehicleID` (`VehicleID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`VehicleID`),
  ADD KEY `FK_Vehicle_Driver` (`DriverID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `AdminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `DriverID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `ServiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `VehicleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`),
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`DriverID`) REFERENCES `drivers` (`DriverID`),
  ADD CONSTRAINT `services_ibfk_3` FOREIGN KEY (`VehicleID`) REFERENCES `vehicles` (`VehicleID`);

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `FK_Vehicle_Driver` FOREIGN KEY (`DriverID`) REFERENCES `drivers` (`DriverID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
