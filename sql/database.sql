-- sql/database.sql
-- Bus & Railway Reservation System Database

CREATE DATABASE IF NOT EXISTS `reservation_system`;
USE `reservation_system`;

-- =====================================================
-- TABLE 1: users
-- =====================================================
CREATE TABLE `users` (
    `user_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Staff') NOT NULL DEFAULT 'Staff',
    `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 2: vendors
-- =====================================================
CREATE TABLE `vendors` (
    `vendor_id` INT(11) NOT NULL AUTO_INCREMENT,
    `vendor_name` VARCHAR(150) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `contact` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 3: customers
-- =====================================================
CREATE TABLE `customers` (
    `customer_id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_name` VARCHAR(150) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `contact` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 4: orders (Order_Master)
-- =====================================================
CREATE TABLE `orders` (
    `order_id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_date` DATE NOT NULL,
    `customer_id` INT(11) NOT NULL,
    `source` VARCHAR(100) NOT NULL,
    `destination` VARCHAR(100) NOT NULL,
    `mode` ENUM('Bus', 'Railway') NOT NULL,
    `journey_date` DATE NOT NULL,
    `passenger_count` INT(11) NOT NULL DEFAULT 1,
    `order_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Pending', 'Processing', 'Purchased', 'Sold', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `created_by` INT(11) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`order_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 5: order_passengers (Order_Details)
-- =====================================================
CREATE TABLE `order_passengers` (
    `order_passenger_id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `passenger_name` VARCHAR(150) NOT NULL,
    `age` INT(3) NOT NULL,
    `sex` ENUM('Male', 'Female', 'Other') NOT NULL,
    `seat` VARCHAR(20) DEFAULT NULL,
    `food_preference` ENUM('Veg', 'Non-Veg', 'No Food') NOT NULL DEFAULT 'No Food',
    `senior_citizen` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    `doctor` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    PRIMARY KEY (`order_passenger_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 6: purchases (PurchaseTicket_Master)
-- =====================================================
CREATE TABLE `purchases` (
    `purchase_id` INT(11) NOT NULL AUTO_INCREMENT,
    `purchase_date` DATE NOT NULL,
    `order_id` INT(11) NOT NULL,
    `vendor_id` INT(11) NOT NULL,
    `ticket_pnr` VARCHAR(50) DEFAULT NULL,
    `source` VARCHAR(100) NOT NULL,
    `destination` VARCHAR(100) NOT NULL,
    `mode` ENUM('Bus', 'Railway') NOT NULL,
    `journey_date` DATE NOT NULL,
    `passenger_count` INT(11) NOT NULL DEFAULT 1,
    `purchase_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Purchased', 'Cancelled') NOT NULL DEFAULT 'Purchased',
    `created_by` INT(11) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`purchase_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON UPDATE CASCADE,
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`vendor_id`) ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 7: purchase_passengers (PurchaseTicket_Details)
-- =====================================================
CREATE TABLE `purchase_passengers` (
    `purchase_passenger_id` INT(11) NOT NULL AUTO_INCREMENT,
    `purchase_id` INT(11) NOT NULL,
    `passenger_name` VARCHAR(150) NOT NULL,
    `age` INT(3) NOT NULL,
    `sex` ENUM('Male', 'Female', 'Other') NOT NULL,
    `seat` VARCHAR(20) DEFAULT NULL,
    `food_preference` ENUM('Veg', 'Non-Veg', 'No Food') NOT NULL DEFAULT 'No Food',
    `senior_citizen` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    `doctor` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    PRIMARY KEY (`purchase_passenger_id`),
    FOREIGN KEY (`purchase_id`) REFERENCES `purchases`(`purchase_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 8: sales
-- =====================================================
CREATE TABLE `sales` (
    `sales_id` INT(11) NOT NULL AUTO_INCREMENT,
    `purchase_id` INT(11) NOT NULL,
    `order_id` INT(11) NOT NULL,
    `customer_id` INT(11) NOT NULL,
    `sales_date` DATE NOT NULL,
    `purchase_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `commission` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sales_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('Completed', 'Cancelled') NOT NULL DEFAULT 'Completed',
    `created_by` INT(11) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`sales_id`),
    FOREIGN KEY (`purchase_id`) REFERENCES `purchases`(`purchase_id`) ON UPDATE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON UPDATE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 9: sales_passengers
-- =====================================================
CREATE TABLE `sales_passengers` (
    `sales_passenger_id` INT(11) NOT NULL AUTO_INCREMENT,
    `sales_id` INT(11) NOT NULL,
    `passenger_name` VARCHAR(150) NOT NULL,
    `age` INT(3) NOT NULL,
    `sex` ENUM('Male', 'Female', 'Other') NOT NULL,
    `seat` VARCHAR(20) DEFAULT NULL,
    `food_preference` ENUM('Veg', 'Non-Veg', 'No Food') NOT NULL DEFAULT 'No Food',
    `senior_citizen` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    `doctor` ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    PRIMARY KEY (`sales_passenger_id`),
    FOREIGN KEY (`sales_id`) REFERENCES `sales`(`sales_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE 10: settings (for commission percentage etc.)
-- =====================================================
CREATE TABLE `settings` (
    `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(50) NOT NULL UNIQUE,
    `setting_value` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DEFAULT DATA
-- =====================================================

-- Default admin user (password: admin123)
INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `status`)
VALUES ('Administrator', 'admin', 'admin@reservation.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Active');

-- Default commission setting (10%)
INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES ('commission_percentage', '10');

-- Sample Vendors
INSERT INTO `vendors` (`vendor_name`, `address`, `contact`, `email`, `status`) VALUES
('National Bus Services', '45, Transport Nagar, Delhi', '9876543210', 'nbs@email.com', 'Active'),
('Indian Railway Agent', '12, Station Road, Mumbai', '9876543211', 'ira@email.com', 'Active'),
('Express Travels', '78, MG Road, Bangalore', '9876543212', 'express@email.com', 'Active'),
('Royal Bus Corporation', '23, Bus Stand, Jaipur', '9876543213', 'royal@email.com', 'Active'),
('Metro Rail Services', '56, Metro Lane, Chennai', '9876543214', 'metro@email.com', 'Active');

-- Sample Customers
INSERT INTO `customers` (`customer_name`, `address`, `contact`, `email`) VALUES
('Rahul Sharma', '101, Green Park, Delhi', '9111111111', 'rahul@email.com'),
('Priya Patel', '202, Marine Drive, Mumbai', '9222222222', 'priya@email.com'),
('Amit Kumar', '303, MG Road, Bangalore', '9333333333', 'amit@email.com'),
('Sneha Reddy', '404, Banjara Hills, Hyderabad', '9444444444', 'sneha@email.com'),
('Vikram Singh', '505, Civil Lines, Jaipur', '9555555555', 'vikram@email.com');