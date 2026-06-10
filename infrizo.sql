SET FOREIGN_KEY_CHECKS = 0;

-- =========================
-- TABLE: admins
-- =========================
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'SuperAdmin',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`username`, `password`, `role`)
VALUES
('mehediofficials28@gmail.com', '$2y$12$zyV5k7yBSP.LTi4xI.nJo.BGCQCtvknAzfXAHm.Mtgdt3LY9bZASa', 'SuperAdmin');

-- =========================
-- TABLE: categories
-- =========================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `status` enum('Active','Hidden') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `slug`, `description`) VALUES 
('Digital Constructs', 'software', 'Enterprise software and algorithmic solutions.'),
('Network Infrastructure', 'network', 'High-speed nodes and routing frameworks.'),
('Physical Assets', 'hardware', 'Heavy-duty processing units and racks.');

-- =========================
-- TABLE: products
-- =========================
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `sku` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `list_description` json DEFAULT NULL,
  `full_description` text,
  `price_numeric` decimal(10,2) DEFAULT '0.00',
  `price_display` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(150) DEFAULT NULL,
  `meta_description` text,
  `status` enum('Active','Maintenance','In Stock','Low Stock','Out of Stock','Hidden') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_product_category` (`category_id`),
  CONSTRAINT `fk_product_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`category_id`, `sku`, `name`, `slug`, `short_description`, `icon`, `price_display`, `status`) VALUES
(1, 'SW-01', 'Custom Web Dev', 'custom-web-dev', 'Full-stack web apps compiled with modern architecture.', '⟨/⟩', 'Custom Quote', 'Active'),
(1, 'SW-02', 'ERP Systems', 'erp-systems', 'Enterprise resource planning to automate operations.', '[⚙]', 'Custom Quote', 'Active'),
(1, 'SW-03', 'POS Interface', 'pos-interface', 'Point-of-sale terminals for retail and service sectors.', '◈', 'Custom Quote', 'Active'),
(1, 'SW-04', 'HRM & Payroll', 'hrm-payroll', 'Algorithmic HR management and payroll processing.', '⎔', 'Custom Quote', 'Active'),
(1, 'SW-05', 'Mobile App Dev', 'mobile-app-dev', 'Cross-platform mobile applications.', '📱', 'Custom Quote', 'Active'),
(1, 'SW-06', 'Cloud Sync', 'cloud-sync', 'Secure cloud migration and continuous integration.', '☁', 'Custom Quote', 'Active');

-- =========================
-- TABLE: orders
-- =========================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `order_type` enum('Order','Quotation') DEFAULT 'Order',
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `order_status` enum('Processing','Shipped','Delivered','Cancelled','Quote Sent','Quote Accepted','Quote Rejected') DEFAULT 'Processing',
  `quote_file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_order_product` (`product_id`),
  CONSTRAINT `fk_order_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- TABLE: software_information
-- =========================
DROP TABLE IF EXISTS `software_information`;
CREATE TABLE `software_information` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `feature_value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_software_info_product` (`product_id`),
  CONSTRAINT `fk_software_info_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `software_information` (`product_id`, `feature_name`, `feature_value`) VALUES
(1, 'Architecture', 'Microservices'),
(1, 'Tech Stack', 'React & Node.js'),
(2, 'Compliance', 'ISO 27001'),
(2, 'Deployment', 'Cloud / On-Premise');

-- =========================
-- TABLE: order_items
-- =========================
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `price_at_order` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `order_items_ibfk_1`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- TABLE: inquiries
-- =========================
DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('Unread','In Progress','Closed') DEFAULT 'Unread',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_inquiry_product` (`product_id`),
  CONSTRAINT `fk_inquiry_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- TABLE: freelancer_applications
-- =========================
DROP TABLE IF EXISTS `freelancer_applications`;
CREATE TABLE `freelancer_applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `portfolio_link` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) NOT NULL,
  `cv_path` varchar(255) NOT NULL,
  `cover_letter` text,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- TABLE: engineers
-- =========================
DROP TABLE IF EXISTS `engineers`;
CREATE TABLE `engineers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `unit_class` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('Active','Hidden') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- TABLE: settings
-- =========================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES 
('site_name', 'INFRIZO'),
('seo_description', 'Automated IT infrastructure and robotic software solutions. Best IT software company in BD.');

SET FOREIGN_KEY_CHECKS = 1;