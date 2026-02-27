-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 27, 2026 at 05:43 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `group_pj`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_code` varchar(20) NOT NULL,
  `contact_name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `membership_level` enum('STANDARD','PREMIUM','ELITE') DEFAULT 'STANDARD',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `customer_code`, `contact_name`, `address`, `membership_level`, `created_at`) VALUES
(2, NULL, 'CLI-2026-1806', 'Ryu', '185', 'ELITE', '2026-02-26 05:55:07'),
(3, NULL, 'CLI-2026-6114', 'Ryu', 'gjbjkk', 'PREMIUM', '2026-02-26 08:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `employee_code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `employee_code`, `name`, `position`, `is_active`, `created_at`) VALUES
(1, 1, 'EMP-1772078777', 'New Employee', 'Unassigned', 0, '2026-02-26 04:06:17'),
(2, NULL, 'EMP-2026-6756', 'KEN ARAI', 'Sales Associate', 1, '2026-02-26 04:38:44'),
(3, NULL, 'EMP-2026-1345', 'jnkjkjk', 'Procurement Lead', 1, '2026-02-26 05:00:40'),
(4, 2, 'EMP-1772092360', 'New Employee', 'Unassigned', 1, '2026-02-26 07:52:40'),
(5, NULL, 'EMP-2026-4868', 'Nangfah', 'Sales Associate', 1, '2026-02-26 08:37:32'),
(6, NULL, 'EMP-2026-2861', 'Kenji', 'Sales Associate', 1, '2026-02-26 08:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_reference` varchar(30) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment_method` enum('CASH') DEFAULT 'CASH',
  `payment_status` enum('PAID','PENDING') DEFAULT 'PAID',
  `invoice_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_reference`, `order_id`, `customer_id`, `total_amount`, `payment_method`, `payment_status`, `invoice_date`) VALUES
(5, 'INV-2026-40579', 10, 3, 85.61, 'CASH', 'PAID', '2026-02-25 17:00:00'),
(6, 'INV-2026-81408', 11, 3, 552.50, 'CASH', 'PAID', '2026-02-26 17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_code` varchar(20) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `product_type` varchar(100) NOT NULL,
  `product_description` text DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `stock_qty` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_code`, `product_name`, `product_type`, `product_description`, `cost_price`, `selling_price`, `stock_qty`, `created_at`, `image_path`) VALUES
(2, 'ENG-2026-3489', 'ท่อดีงเด็กแว้นตึงๆ', 'Engine', '', 20.00, 25.00, 4, '2026-02-26 05:09:20', NULL),
(3, 'EXH-2026-5531', 'Test', 'Exhaust', '', 10.00, 15.02, 2, '2026-02-26 08:37:51', NULL),
(5, 'ENG-2026-8916', 'ท่อดังระเบิด', 'Engine', '', 0.04, 0.04, 3, '2026-02-26 08:46:16', NULL),
(6, 'ENG-2026-1991', 'Test', 'Engine', '', 100.00, 130.00, 9985, '2026-02-26 09:23:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sale_orders`
--

CREATE TABLE `sale_orders` (
  `order_id` int(11) NOT NULL,
  `reference_id` varchar(30) NOT NULL,
  `po_reference` varchar(50) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `membership_discount` decimal(12,2) DEFAULT 0.00,
  `special_discount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `order_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_orders`
--

INSERT INTO `sale_orders` (`order_id`, `reference_id`, `po_reference`, `customer_id`, `employee_id`, `subtotal`, `membership_discount`, `special_discount`, `total_amount`, `order_date`) VALUES
(10, 'REF-2026-49089', 'PO-2026-49089', 3, 6, 690.12, 34.51, 69.01, 586.60, '2026-02-26 02:12:31'),
(11, 'REF-2026-21176', 'PO-2026-21176', 3, 2, 650.00, 32.50, 65.00, 552.50, '2026-02-26 21:11:51'),
(12, 'REF-2026-87135', 'PO-2026-87135', 3, 6, 650.00, 32.50, 65.00, 552.50, '2026-02-26 21:12:58'),
(13, 'REF-2026-73519', 'PO-2026-73519', 2, 2, 0.00, 0.00, 0.00, 0.00, '2026-02-26 21:31:38');

-- --------------------------------------------------------

--
-- Table structure for table `sale_order_details`
--

CREATE TABLE `sale_order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_order_details`
--

INSERT INTO `sale_order_details` (`detail_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `total_price`) VALUES
(10, 10, 3, 6, 15.02, 90.12),
(11, 10, 6, 5, 120.00, 600.00),
(12, 11, 6, 5, 130.00, 650.00),
(13, 12, 6, 5, 130.00, 650.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('employee','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin@example.com', '$2y$12$3JTyG6.xLIwReu.ILB8BbOWJ/A8dniaTE4OC0WfLzVUFNc.QKhnVS', 'employee', '2026-02-26 04:06:17'),
(2, 'test@gmail.com', '$2y$12$r4n8xc1ImOYwR9UqExRGXuc3LeBBYH7.OSJZdLupwsKNDD/T2p.z2', 'employee', '2026-02-26 07:52:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `fk_customers_users` (`user_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `fk_employees_users` (`user_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_reference` (`invoice_reference`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `sale_orders`
--
ALTER TABLE `sale_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `reference_id` (`reference_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `sale_order_details`
--
ALTER TABLE `sale_order_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sale_orders`
--
ALTER TABLE `sale_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sale_order_details`
--
ALTER TABLE `sale_order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sale_orders` (`order_id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `sale_orders`
--
ALTER TABLE `sale_orders`
  ADD CONSTRAINT `sale_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `sale_orders_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `sale_order_details`
--
ALTER TABLE `sale_order_details`
  ADD CONSTRAINT `sale_order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sale_orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
