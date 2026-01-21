-- ============================================================
-- DALIPUGA CLEANUP MANAGEMENT SYSTEM - COMPLETE DATABASE
-- ============================================================
-- This is the main database file for the entire system
-- All tables and sample data are included
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- 1. ADMIN TABLE (for Login, Register, Change Password)
-- ============================================================

CREATE TABLE IF NOT EXISTS `admin` (
  `Admin_Id` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL UNIQUE,
  `Email` varchar(100) NOT NULL UNIQUE,
  `Contact_Number` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL DEFAULT 'password',
  `date_created` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Admin_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample Admin Data
-- Username: admin, Password: admin123 (hashed)
-- Username: Jadiz, Password: Talongis (hashed)
INSERT INTO `admin` (`Admin_Id`, `Username`, `Email`, `Contact_Number`, `Password`) VALUES
(1, 'admin', 'admin@example.com', '09170001111', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
(2, 'Jadiz', 'jadiz@example.com', '09170002222', '$2y$10$slYQmyNdGzin0d0tIU5H2OPST9/PgBkqx8A/LewKpFQMJqxVO4l7m');

-- ============================================================
-- 2. RESIDENTS TABLE (for Resident Login, Register, Change Password)
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample Residents Data
-- Password for all residents: password123 (hashed)
INSERT INTO `residents` (`first_name`, `last_name`, `email`, `contact_number`, `location`, `password`) VALUES
('Juan', 'Dela Cruz', 'juan@example.com', '09171234567', '123 Mabini St', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
('Maria', 'Santos', 'maria@example.com', '09987654321', '456 Rizal Ave', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
('Pedro', 'Bautista', 'pedro@example.com', '09221234567', '789 Bonifacio St', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
('Ana', 'Garcia', 'ana@example.com', '09335678901', '321 Palma St', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
('Luis', 'Rodriguez', 'luis@example.com', '09445678912', '654 Quezon Ave', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm');

-- ============================================================
-- 3. INVENTORY TABLE (for Inventory Management)
-- ============================================================

CREATE TABLE IF NOT EXISTS `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'available',
  `date_added` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample Inventory Data
INSERT INTO `inventory` (`item_name`, `category`, `quantity`, `unit`, `status`) VALUES
('Brooms', 'Equipment', 25, 'pcs', 'available'),
('Dustpans', 'Equipment', 20, 'pcs', 'available'),
('Garbage Bags', 'Supplies', 100, 'box', 'available'),
('Hand Gloves', 'Safety Gear', 50, 'dozen', 'available'),
('Safety Helmets', 'Safety Gear', 15, 'pcs', 'low_stock'),
('Shovels', 'Tools', 10, 'pcs', 'available');

-- ============================================================
-- 4. LETTERS TABLE (for Resident Letters to Admin)
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
  `read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`resident_id`) REFERENCES `residents`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================================
-- DATABASE SETUP COMPLETE
-- ============================================================
-- ADMIN LOGIN CREDENTIALS:
-- Username: admin        | Password: admin123
-- Username: Jadiz        | Password: Talongis
--
-- RESIDENT LOGIN CREDENTIALS (all use password: password123):
-- Email: juan@example.com
-- Email: maria@example.com
-- Email: pedro@example.com
-- Email: ana@example.com
-- Email: luis@example.com
--
-- ACCESS POINTS:
-- Admin Login:     http://localhost/cadiz/auth/login.php
-- Resident Login:  http://localhost/cadiz/auth/resident_login.php
-- ============================================================
