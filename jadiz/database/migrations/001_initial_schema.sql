-- ============================================================
-- DALIPUGA CLEANUP MANAGEMENT SYSTEM - DATABASE SCHEMA
-- ============================================================
-- Migration file for initial database setup
-- Date: 2026-01-21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- 1. ADMIN TABLE
-- ============================================================

CREATE TABLE IF NOT EXISTS `admin` (
  `Admin_Id` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL UNIQUE,
  `Email` varchar(100) NOT NULL UNIQUE,
  `Contact_Number` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Admin_Id`),
  INDEX `idx_username` (`Username`),
  INDEX `idx_email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 2. RESIDENTS TABLE
-- ============================================================

CREATE TABLE IF NOT EXISTS `residents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) UNIQUE,
  `contact_number` varchar(20) DEFAULT NULL,
  `location` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_added` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 3. INVENTORY TABLE
-- ============================================================

CREATE TABLE IF NOT EXISTS `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'available',
  `date_added` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 4. LETTERS TABLE
-- ============================================================

CREATE TABLE IF NOT EXISTS `letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` longtext NOT NULL,
  `resident_location` varchar(150),
  `resident_name` varchar(200),
  `resident_email` varchar(100),
  `resident_contact` varchar(20),
  `status` varchar(50) DEFAULT 'unread',
  `date_sent` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`),
  INDEX `idx_resident_id` (`resident_id`),
  INDEX `idx_date_sent` (`date_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 5. CLEANUP APPOINTMENTS TABLE
-- ============================================================

CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11),
  `title` varchar(200) NOT NULL,
  `description` longtext,
  `appointment_date` date NOT NULL,
  `appointment_time` time,
  `location` varchar(150),
  `status` varchar(50) DEFAULT 'scheduled',
  `created_by` int(11),
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `admin`(`Admin_Id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_appointment_date` (`appointment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 6. ACTIVITY LOGS TABLE
-- ============================================================

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `user_type` enum('admin', 'resident') NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` longtext,
  `ip_address` varchar(45),
  `user_agent` text,
  `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
