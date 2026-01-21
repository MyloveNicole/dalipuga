-- ============================================================
-- SAMPLE DATA FOR TESTING
-- ============================================================

-- Sample Admin Users
-- Password: TestAdmin123! (hashed)
INSERT INTO `admin` (`Username`, `Email`, `Contact_Number`, `Password`) VALUES
('admin', 'admin@example.com', '09170001111', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
('supervisor', 'supervisor@example.com', '09170002222', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm');

-- Sample Residents
-- Password: TestResident123! (hashed)
INSERT INTO `residents` (`first_name`, `last_name`, `email`, `contact_number`, `location`, `password`) VALUES
('Juan', 'Dela Cruz', 'juan@example.com', '09171234567', '123 Mabini Street', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
('Maria', 'Santos', 'maria@example.com', '09271234567', '456 Rizal Avenue', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm'),
('Pedro', 'Bautista', 'pedro@example.com', '09371234567', '789 Bonifacio Street', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/DSm');

-- Sample Inventory
INSERT INTO `inventory` (`item_name`, `category`, `quantity`, `unit`, `status`) VALUES
('Brooms', 'Equipment', 25, 'pcs', 'available'),
('Dustpans', 'Equipment', 20, 'pcs', 'available'),
('Garbage Bags', 'Supplies', 100, 'box', 'available'),
('Hand Gloves', 'Safety Gear', 50, 'dozen', 'available'),
('Safety Helmets', 'Safety Gear', 15, 'pcs', 'low_stock'),
('Shovels', 'Tools', 10, 'pcs', 'available');
