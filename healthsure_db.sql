-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 09:30 AM
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
-- Database: `healthsure_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `agent_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`agent_id`, `user_id`, `first_name`, `last_name`, `phone`, `branch`, `license_number`, `hire_date`, `created_at`) VALUES
(1, 2, 'Manoj', 'Sharma', '', '', '', '2025-11-07', '2025-11-07 06:23:18');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `holder_id` int(11) NOT NULL,
  `claim_amount` decimal(10,2) NOT NULL,
  `claim_reason` text NOT NULL,
  `claim_date` date NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_amount` decimal(10,2) DEFAULT 0.00,
  `documents` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`claim_id`, `holder_id`, `claim_amount`, `claim_reason`, `claim_date`, `status`, `approved_amount`, `documents`, `admin_notes`, `processed_by`, `processed_date`, `created_at`) VALUES
(1, 1, 25000.00, 'Medical treatment for fever and infection', '2025-11-07', 'pending', 0.00, NULL, NULL, NULL, NULL, '2025-11-07 07:12:15'),
(2, 1, 15000.00, 'Dental treatment and consultation', '2025-11-07', 'approved', 15000.00, NULL, NULL, NULL, NULL, '2025-11-07 07:12:15'),
(3, 1, 5000.00, 'Regular health checkup and tests', '2025-11-07', 'rejected', 0.00, NULL, NULL, NULL, NULL, '2025-11-07 07:12:15');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `first_name`, `last_name`, `phone`, `address`, `date_of_birth`, `gender`, `agent_id`, `created_at`) VALUES
(1, 3, 'samreen', 'akhter', '', '', '2025-01-29', 'female', 1, '2025-11-07 06:41:21'),
(2, 4, 'John', 'Doe', '+1234567890', '123 Main Street, City', '1990-01-15', 'male', NULL, '2025-11-07 07:12:15');

-- --------------------------------------------------------

--
-- Table structure for table `family_policies`
--

CREATE TABLE `family_policies` (
  `policy_id` int(11) NOT NULL,
  `no_of_dependents` int(11) DEFAULT 0,
  `maternity_cover` tinyint(1) DEFAULT 0,
  `dependent_age_limit` int(11) DEFAULT 25,
  `family_floater_sum` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_policies`
--

INSERT INTO `family_policies` (`policy_id`, `no_of_dependents`, `maternity_cover`, `dependent_age_limit`, `family_floater_sum`) VALUES
(2, 4, 1, 25, 1000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `health_policies`
--

CREATE TABLE `health_policies` (
  `policy_id` int(11) NOT NULL,
  `hospital_coverage` text DEFAULT NULL,
  `pre_existing_conditions` tinyint(1) DEFAULT 0,
  `network_hospitals` text DEFAULT NULL,
  `cashless_limit` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_policies`
--

INSERT INTO `health_policies` (`policy_id`, `hospital_coverage`, `pre_existing_conditions`, `network_hospitals`, `cashless_limit`) VALUES
(1, 'All major hospitals covered', 0, 'Apollo, Fortis, Max Healthcare', 300000.00),
(4, 'Premium hospital network', 1, 'Apollo, Fortis, Max Healthcare, AIIMS', 1000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `life_policies`
--

CREATE TABLE `life_policies` (
  `policy_id` int(11) NOT NULL,
  `nominee_name` varchar(200) DEFAULT NULL,
  `nominee_relation` varchar(50) DEFAULT NULL,
  `term_years` int(11) DEFAULT NULL,
  `maturity_benefit` decimal(12,2) DEFAULT NULL,
  `death_benefit` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `life_policies`
--

INSERT INTO `life_policies` (`policy_id`, `nominee_name`, `nominee_relation`, `term_years`, `maturity_benefit`, `death_benefit`) VALUES
(3, 'To be updated', 'spouse', 20, 0.00, 2000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `holder_id` int(11) DEFAULT NULL,
  `claim_id` int(11) DEFAULT NULL,
  `payment_type` enum('premium','claim_settlement') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank_transfer','online') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `holder_id`, `claim_id`, `payment_type`, `amount`, `payment_method`, `transaction_id`, `payment_date`, `status`, `created_at`) VALUES
(1, 1, 2, 'claim_settlement', 15000.00, 'bank_transfer', NULL, '2025-11-07', 'completed', '2025-11-07 07:12:15'),
(2, 1, NULL, 'premium', 5000.00, 'online', 'TXN123456789', '2025-11-07', 'completed', '2025-11-07 07:12:15'),
(3, 2, NULL, 'premium', 100000.00, 'online', '', '2025-11-07', 'completed', '2025-11-07 07:24:41');

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `policy_id` int(11) NOT NULL,
  `policy_name` varchar(200) NOT NULL,
  `policy_type` enum('health','life','family') NOT NULL,
  `description` text DEFAULT NULL,
  `base_premium` decimal(10,2) NOT NULL,
  `coverage_amount` decimal(12,2) NOT NULL,
  `duration_years` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`policy_id`, `policy_name`, `policy_type`, `description`, `base_premium`, `coverage_amount`, `duration_years`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Basic Health Plan', 'health', 'Comprehensive health coverage for individuals', 5000.00, 500000.00, 1, 'active', '2025-11-07 06:03:50', '2025-11-07 07:26:34'),
(2, 'Family Health Shield', 'family', 'Complete health protection for your family', 12000.00, 1000000.00, 1, 'active', '2025-11-07 06:03:50', '2025-11-07 06:03:50'),
(3, 'Term Life Insurance', 'life', 'Life insurance with term benefits', 8000.00, 2000000.00, 20, 'active', '2025-11-07 06:03:50', '2025-11-07 06:03:50'),
(4, 'Premium Health Plus', 'health', 'Enhanced health coverage with premium benefits', 15000.00, 1500000.00, 1, 'active', '2025-11-07 06:03:50', '2025-11-07 06:03:50');

-- --------------------------------------------------------

--
-- Table structure for table `policy_holders`
--

CREATE TABLE `policy_holders` (
  `holder_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `policy_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `premium_amount` decimal(10,2) NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `policy_holders`
--

INSERT INTO `policy_holders` (`holder_id`, `customer_id`, `policy_id`, `agent_id`, `start_date`, `end_date`, `premium_amount`, `status`, `created_at`) VALUES
(1, 2, 1, NULL, '2025-11-07', '2026-11-07', 5000.00, 'active', '2025-11-07 07:12:15'),
(2, 1, 1, NULL, '2025-11-07', '2026-11-07', 5000.00, 'active', '2025-11-07 07:24:04');

-- --------------------------------------------------------

--
-- Table structure for table `support_queries`
--

CREATE TABLE `support_queries` (
  `query_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','in_progress','resolved') DEFAULT 'open',
  `response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','agent','customer') NOT NULL,
  `status` enum('active','blocked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin@healthsure.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', '2025-11-07 06:03:50', '2025-11-07 06:03:50'),
(2, 'manaj123@gmail.com', '$2y$10$gF4eK2Hdu.dPG3j3DrMmge4cqp/kiMDRkmwRPJQ4Tzl2pZpCkprve', 'agent', 'active', '2025-11-07 06:23:18', '2025-11-07 06:23:18'),
(3, 'samreenakhter2020@gmail.com', '$2y$10$xlF59g11IJiYVDXZzaNgHeDCWJhIzRpukfU9khGBCOBXGXPNBs7Ky', 'customer', 'active', '2025-11-07 06:41:21', '2025-11-07 06:41:21'),
(4, 'customer@test.com', '$2y$10$xKHCk5mikz.NvCB4sy4whuvHpb41m61UKkTHPnnAYSO4jO3URpA0S', 'customer', 'active', '2025-11-07 07:12:15', '2025-11-07 07:12:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`agent_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `holder_id` (`holder_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `family_policies`
--
ALTER TABLE `family_policies`
  ADD PRIMARY KEY (`policy_id`);

--
-- Indexes for table `health_policies`
--
ALTER TABLE `health_policies`
  ADD PRIMARY KEY (`policy_id`);

--
-- Indexes for table `life_policies`
--
ALTER TABLE `life_policies`
  ADD PRIMARY KEY (`policy_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `holder_id` (`holder_id`),
  ADD KEY `claim_id` (`claim_id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`policy_id`);

--
-- Indexes for table `policy_holders`
--
ALTER TABLE `policy_holders`
  ADD PRIMARY KEY (`holder_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `policy_id` (`policy_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `support_queries`
--
ALTER TABLE `support_queries`
  ADD PRIMARY KEY (`query_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `agent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `policy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `policy_holders`
--
ALTER TABLE `policy_holders`
  MODIFY `holder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `support_queries`
--
ALTER TABLE `support_queries`
  MODIFY `query_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`holder_id`) REFERENCES `policy_holders` (`holder_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`agent_id`) ON DELETE SET NULL;

--
-- Constraints for table `family_policies`
--
ALTER TABLE `family_policies`
  ADD CONSTRAINT `family_policies_ibfk_1` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`policy_id`) ON DELETE CASCADE;

--
-- Constraints for table `health_policies`
--
ALTER TABLE `health_policies`
  ADD CONSTRAINT `health_policies_ibfk_1` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`policy_id`) ON DELETE CASCADE;

--
-- Constraints for table `life_policies`
--
ALTER TABLE `life_policies`
  ADD CONSTRAINT `life_policies_ibfk_1` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`policy_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`holder_id`) REFERENCES `policy_holders` (`holder_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`claim_id`) ON DELETE CASCADE;

--
-- Constraints for table `policy_holders`
--
ALTER TABLE `policy_holders`
  ADD CONSTRAINT `policy_holders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `policy_holders_ibfk_2` FOREIGN KEY (`policy_id`) REFERENCES `policies` (`policy_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `policy_holders_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`agent_id`) ON DELETE SET NULL;

--
-- Constraints for table `support_queries`
--
ALTER TABLE `support_queries`
  ADD CONSTRAINT `support_queries_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
