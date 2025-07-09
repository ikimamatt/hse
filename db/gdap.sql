-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 09, 2025 at 07:12 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gdap`
--

-- --------------------------------------------------------

--
-- Table structure for table `bsoc_card_reports`
--

CREATE TABLE `bsoc_card_reports` (
  `id` int NOT NULL,
  `report_id` int NOT NULL,
  `entry_number` int NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text,
  `action_taken` text,
  `observer` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bsoc_card_reports`
--

INSERT INTO `bsoc_card_reports` (`id`, `report_id`, `entry_number`, `category`, `description`, `action_taken`, `observer`, `status`) VALUES
(3, 2, 1, 'U/C', 'Uncontrolled equipment movement', NULL, NULL, 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `bsoc_monthly_contribution`
--

CREATE TABLE `bsoc_monthly_contribution` (
  `id` int NOT NULL,
  `report_id` int NOT NULL,
  `entity_name` varchar(255) NOT NULL,
  `hazard` int DEFAULT '0',
  `unsafe_act` int DEFAULT '0',
  `near_miss` int DEFAULT '0',
  `safe_work` int DEFAULT '0',
  `total` int DEFAULT '0',
  `percentage` decimal(10,9) DEFAULT '0.000000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bsoc_monthly_contribution`
--

INSERT INTO `bsoc_monthly_contribution` (`id`, `report_id`, `entity_name`, `hazard`, `unsafe_act`, `near_miss`, `safe_work`, `total`, `percentage`) VALUES
(9, 2, 'GWDC', 2, 1, 0, 5, 8, '0.000000000'),
(10, 2, 'Client', 1, 0, 1, 3, 5, '0.000000000'),
(11, 2, '3rd Party', 0, 2, 0, 4, 6, '0.000000000'),
(12, 2, 'Catering', 0, 0, 1, 2, 3, '0.000000000');

-- --------------------------------------------------------

--
-- Table structure for table `daily_reports`
--

CREATE TABLE `daily_reports` (
  `id` int NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `report_date` date NOT NULL,
  `zyh_startup_date` date DEFAULT NULL,
  `phm_startup_date` date DEFAULT NULL,
  `no_lti_days_zyh` int DEFAULT NULL,
  `no_lti_days_phm` int DEFAULT NULL,
  `total_bsoc_cards` int DEFAULT NULL,
  `safe_cards` int DEFAULT NULL,
  `unsafe_bsoc` int DEFAULT NULL,
  `best_bsoc_title` varchar(255) DEFAULT NULL,
  `best_bsoc_description` text,
  `total_monthly_bsoc_cards` int DEFAULT NULL,
  `doctor` varchar(255) DEFAULT NULL,
  `prepared_by` varchar(255) DEFAULT NULL,
  `concerns` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `daily_reports`
--

INSERT INTO `daily_reports` (`id`, `company_name`, `report_date`, `zyh_startup_date`, `phm_startup_date`, `no_lti_days_zyh`, `no_lti_days_phm`, `total_bsoc_cards`, `safe_cards`, `unsafe_bsoc`, `best_bsoc_title`, `best_bsoc_description`, `total_monthly_bsoc_cards`, `doctor`, `prepared_by`, `concerns`, `created_at`) VALUES
(2, 'GWDC Indonesia', '2025-07-09', '2025-06-01', '2025-06-15', 150, 145, 20, 15, 5, 'Safety Equipment Usage', 'Ensured all workers used proper PPE during operations.', 80, 'Dr. Smith & Sir Jhon Doe', 'John Doe', 'Need to improve equipment safety checks.', '2025-07-09 07:07:17');

-- --------------------------------------------------------

--
-- Table structure for table `hse_summary`
--

CREATE TABLE `hse_summary` (
  `id` int NOT NULL,
  `report_id` int NOT NULL,
  `activity_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hse_summary`
--

INSERT INTO `hse_summary` (`id`, `report_id`, `activity_description`) VALUES
(7, 2, 'Attended GWDC and PHM supervisor morning meeting.'),
(8, 2, 'Attended and lead pre-tour meeting for day and night crew.'),
(9, 2, 'Daily walk through at the cement silo area.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bsoc_card_reports`
--
ALTER TABLE `bsoc_card_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `bsoc_monthly_contribution`
--
ALTER TABLE `bsoc_monthly_contribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hse_summary`
--
ALTER TABLE `hse_summary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bsoc_card_reports`
--
ALTER TABLE `bsoc_card_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bsoc_monthly_contribution`
--
ALTER TABLE `bsoc_monthly_contribution`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hse_summary`
--
ALTER TABLE `hse_summary`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bsoc_card_reports`
--
ALTER TABLE `bsoc_card_reports`
  ADD CONSTRAINT `bsoc_card_reports_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bsoc_monthly_contribution`
--
ALTER TABLE `bsoc_monthly_contribution`
  ADD CONSTRAINT `bsoc_monthly_contribution_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hse_summary`
--
ALTER TABLE `hse_summary`
  ADD CONSTRAINT `hse_summary_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
