-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 06:11 PM
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
-- Database: `glam_essentials`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(64) NOT NULL,
  `img_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `img_name`) VALUES
(1, 'Hair Care', 'hair_care'),
(2, 'Skincare', 'skincare'),
(3, 'Salon Tools & Accessories', 'salon_tools'),
(4, 'Nail & Body Care', 'nail_body_care');

-- --------------------------------------------------------

--
-- Table structure for table `contact_inquiries`
--

CREATE TABLE `contact_inquiries` (
  `inquiry_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `inquiry_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_responded` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `title` char(4) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `zipcode` char(10) DEFAULT NULL,
  `town` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `title`, `fullname`, `address`, `contact_no`, `zipcode`, `town`, `user_id`) VALUES
(4, 'Mrs.', 'Trisha Mia Morales', '', '', '', '', 14);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(20) DEFAULT 'pcs',
  `reorder_level` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`item_id`, `product_id`, `quantity`, `unit`, `reorder_level`) VALUES
(1, 1, 50, 'pcs', 10),
(2, 2, 80, 'pcs', 10),
(3, 3, 60, 'pcs', 10),
(4, 4, 30, 'pcs', 10),
(5, 5, 90, 'pcs', 10),
(6, 6, 40, 'pcs', 10),
(7, 7, 25, 'pcs', 10),
(8, 8, 100, 'pcs', 10),
(9, 9, 70, 'pcs', 10),
(10, 10, 55, 'pcs', 10),
(11, 11, 45, 'pcs', 10),
(12, 12, 65, 'pcs', 10),
(13, 13, 85, 'pcs', 10),
(14, 14, 20, 'pcs', 10),
(15, 15, 15, 'pcs', 10),
(16, 16, 10, 'pcs', 10),
(17, 17, 75, 'pcs', 10),
(18, 18, 95, 'pcs', 10),
(19, 19, 25, 'pcs', 10),
(20, 20, 12, 'pcs', 10),
(21, 21, 60, 'pcs', 10),
(22, 22, 50, 'pcs', 10),
(23, 23, 35, 'pcs', 10),
(24, 24, 80, 'pcs', 10),
(25, 25, 45, 'pcs', 10),
(26, 26, 10, 'pcs', 10);

-- --------------------------------------------------------

--
-- Stand-in structure for view `inventory_status`
-- (See below for the actual view)
--
CREATE TABLE `inventory_status` (
`product_id` int(11)
,`product_name` varchar(150)
,`quantity` int(11)
,`unit` varchar(20)
,`reorder_level` int(11)
,`stock_status` varchar(12)
);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `shipping_name` varchar(100) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_contact` varchar(20) NOT NULL,
  `payment_method` enum('Cash on Delivery','GCash','Credit Card') NOT NULL,
  `payment_status` enum('Pending','Paid','Refunded') DEFAULT 'Pending',
  `order_status` enum('Pending','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `order_summary`
-- (See below for the actual view)
--
CREATE TABLE `order_summary` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `order_transaction_details`
-- (See below for the actual view)
--
CREATE TABLE `order_transaction_details` (
);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `main_img_name` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `price`, `main_img_name`, `is_featured`, `is_available`, `created_at`) VALUES
(1, 1, 'Keratin Treatment Set', 'Smoothens and repairs damaged hair.', 1299.00, 'keratin_treatment', 0, 1, '2025-11-02 08:58:24'),
(2, 1, 'Argan Oil Shampoo', 'Moisturizing shampoo for soft and shiny hair.', 399.00, 'argan_shampoo', 0, 1, '2025-11-02 08:58:24'),
(3, 1, 'Collagen Conditioner', 'Rejuvenates and strengthens hair strands.', 429.00, 'collagen_conditioner', 0, 1, '2025-11-02 08:58:24'),
(4, 1, 'Hair Color Cream (Various Shades)', 'Vibrant and long-lasting hair color.', 299.00, 'hair_color_cream', 0, 1, '2025-11-02 08:58:24'),
(5, 1, 'Purple Shampoo', 'Neutralizes brassiness in blonde hair.', 359.00, 'purple_shampoo', 0, 1, '2025-11-02 08:58:24'),
(6, 1, 'Leave-In Hair Serum', 'Protects hair from heat and frizz.', 279.00, 'leave_in_serum', 0, 1, '2025-11-02 08:58:24'),
(7, 1, 'Hair Spa Cream', 'Salon-grade treatment for silky smooth hair.', 499.00, 'hair_spa_cream', 0, 1, '2025-11-02 08:58:24'),
(8, 2, 'Facial Cleanser', 'Gentle cleanser for all skin types.', 299.00, 'facial_cleanser', 0, 1, '2025-11-02 08:58:24'),
(9, 2, 'Hydrating Toner', 'Restores pH balance and refreshes skin.', 259.00, 'hydrating_toner', 0, 1, '2025-11-02 08:58:24'),
(10, 2, 'Vitamin C Serum', 'Brightens and reduces dark spots.', 799.00, 'vitamin_c_serum', 0, 1, '2025-11-02 08:58:24'),
(11, 2, 'Aloe Vera Gel', 'Soothes and hydrates skin naturally.', 199.00, 'aloe_vera_gel', 0, 1, '2025-11-02 08:58:24'),
(12, 2, 'Facial Sheet Masks (Pack of 5)', 'Instant hydration for glowing skin.', 349.00, 'sheet_masks', 0, 1, '2025-11-02 08:58:24'),
(13, 2, 'Sunscreen SPF 50', 'Protects skin from harmful UV rays.', 499.00, 'sunscreen_spf50', 0, 1, '2025-11-02 08:58:24'),
(14, 3, 'Professional Hair Dryer', 'High-speed dryer for professional use.', 1599.00, 'hair_dryer', 0, 1, '2025-11-02 08:58:24'),
(15, 3, 'Flat Iron / Hair Straightener', 'Smooth and straight styling tool.', 1299.00, 'flat_iron', 0, 1, '2025-11-02 08:58:24'),
(16, 3, 'Hair Curling Wand', 'Easy-to-use curling wand for waves.', 1399.00, 'curling_wand', 0, 1, '2025-11-02 08:58:24'),
(17, 3, 'Cutting Scissors & Thinning Shears Set', 'Durable stainless steel tools.', 899.00, 'scissors_set', 0, 1, '2025-11-02 08:58:24'),
(18, 3, 'Hair Brush & Comb Set', 'Complete styling brush set.', 299.00, 'brush_comb_set', 0, 1, '2025-11-02 08:58:24'),
(19, 3, 'Mixing Bowl and Applicator Brush', 'Essential tools for hair coloring.', 199.00, 'mixing_bowl_brush', 0, 1, '2025-11-02 08:58:24'),
(20, 3, 'Salon Cape and Gloves', 'Protective wear for salon use.', 249.00, 'salon_cape_gloves', 0, 1, '2025-11-02 08:58:24'),
(21, 4, 'Nail Polish Set (Assorted Colors)', 'Vibrant nail colors in a set.', 499.00, 'nail_polish_set', 0, 1, '2025-11-02 08:58:24'),
(22, 4, 'Nail File and Buffer Set', 'Smooth and shape your nails.', 149.00, 'nail_file_buffer', 0, 1, '2025-11-02 08:58:24'),
(23, 4, 'Cuticle Oil', 'Moisturizes and softens cuticles.', 199.00, 'cuticle_oil', 0, 1, '2025-11-02 08:58:24'),
(24, 4, 'Hand and Foot Scrub', 'Removes dead skin and softens.', 249.00, 'hand_foot_scrub', 0, 1, '2025-11-02 08:58:24'),
(25, 4, 'Body Lotion', 'Moisturizing and soothing lotion.', 299.00, 'body_lotion', 0, 1, '2025-11-02 08:58:24'),
(26, 4, 'Body Scrub (Coffee or Milk Variant)', 'Exfoliating scrub for glowing skin.', 349.00, 'body_scrub', 0, 1, '2025-11-02 08:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `img_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `img_name`) VALUES
(1, 1, 'keratin_1'),
(2, 1, 'keratin_2'),
(3, 1, 'keratin_3'),
(4, 2, 'argan_1'),
(5, 2, 'argan_2'),
(6, 2, 'argan_3'),
(7, 3, 'collagen_1'),
(8, 3, 'collagen_2'),
(9, 3, 'collagen_3'),
(10, 4, 'haircolor_1'),
(11, 4, 'haircolor_2'),
(12, 4, 'haircolor_3'),
(13, 5, 'purple_1'),
(14, 5, 'purple_2'),
(15, 5, 'purple_3'),
(16, 6, 'serum_1'),
(17, 6, 'serum_2'),
(18, 6, 'serum_3'),
(19, 7, 'spa_1'),
(20, 7, 'spa_2'),
(21, 7, 'spa_3'),
(22, 8, 'cleanser_1'),
(23, 8, 'cleanser_2'),
(24, 8, 'cleanser_3'),
(25, 9, 'toner_1'),
(26, 9, 'toner_2'),
(27, 9, 'toner_3'),
(28, 10, 'vitamin_1'),
(29, 10, 'vitamin_2'),
(30, 10, 'vitamin_3'),
(31, 11, 'aloe_1'),
(32, 11, 'aloe_2'),
(33, 11, 'aloe_3'),
(34, 12, 'mask_1'),
(35, 12, 'mask_2'),
(36, 12, 'mask_3'),
(37, 13, 'sunscreen_1'),
(38, 13, 'sunscreen_2'),
(39, 13, 'sunscreen_3'),
(40, 14, 'dryer_1'),
(41, 14, 'dryer_2'),
(42, 14, 'dryer_3'),
(43, 15, 'iron_1'),
(44, 15, 'iron_2'),
(45, 15, 'iron_3'),
(46, 16, 'curler_1'),
(47, 16, 'curler_2'),
(48, 16, 'curler_3'),
(49, 17, 'scissors_1'),
(50, 17, 'scissors_2'),
(51, 17, 'scissors_3'),
(52, 18, 'brush_1'),
(53, 18, 'brush_2'),
(54, 18, 'brush_3'),
(55, 19, 'mixing_1'),
(56, 19, 'mixing_2'),
(57, 19, 'mixing_3'),
(58, 20, 'cape_1'),
(59, 20, 'cape_2'),
(60, 20, 'cape_3'),
(61, 21, 'nailpolish_1'),
(62, 21, 'nailpolish_2'),
(63, 21, 'nailpolish_3'),
(64, 22, 'nailfile_1'),
(65, 22, 'nailfile_2'),
(66, 22, 'nailfile_3'),
(67, 23, 'cuticle_1'),
(68, 23, 'cuticle_2'),
(69, 23, 'cuticle_3'),
(70, 24, 'scrub_1'),
(71, 24, 'scrub_2'),
(72, 24, 'scrub_3'),
(73, 25, 'lotion_1'),
(74, 25, 'lotion_2'),
(75, 25, 'lotion_3'),
(76, 26, 'bodyscrub_1'),
(77, 26, 'bodyscrub_2'),
(78, 26, 'bodyscrub_3');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT 5,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `img_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `role` enum('admin','staff','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `img_name`, `is_active`, `last_login`, `role`, `created_at`) VALUES
(12, 'Shu Yamino', 'shuyamino@gmail.com', '$2y$10$mRwQvvZR1eLFFsL4JzggSOVEuW9ewrazkd8d9UVi/iysx1e8MFS4K', NULL, 1, '2025-11-10 17:26:26', 'customer', '2025-11-10 09:26:20'),
(13, 'Ike Eveland', 'ikeeveland@gmail.com', '$2y$10$X5he2.XWW5X8A1K1iaA0NewxiCno87PH5LimJaoMVPrvYn0brcEiO', 'user_13_1762767091.png', 1, '2025-11-11 01:09:27', 'customer', '2025-11-10 09:30:01'),
(14, 'Trisha Mia Morales', 'moralestrishamia@gmail.com', '$2y$10$QjlUZLRVPE8b0a6GNS87V.P3z4jrc.q82AdbOzgAnN8s2OJKra/MW', 'user_14_1762794234.png', 1, '2025-11-11 00:58:34', 'customer', '2025-11-10 09:32:17'),
(15, 'shami', 'shami@mail.com', '$2y$10$wi5WcYo7ymVsLGwsUopQNOXs84OIXxdkZ/THPcv5LgbAov9M/sn/u', 'nopfp.jpg', 1, '2025-11-10 23:37:15', 'customer', '2025-11-10 09:37:03');

-- --------------------------------------------------------

--
-- Structure for view `inventory_status`
--
DROP TABLE IF EXISTS `inventory_status`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `inventory_status`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, `i`.`quantity` AS `quantity`, `i`.`unit` AS `unit`, `i`.`reorder_level` AS `reorder_level`, CASE WHEN `i`.`quantity` = 0 THEN 'Out of Stock' WHEN `i`.`quantity` < `i`.`reorder_level` THEN 'Low Stock' ELSE 'In Stock' END AS `stock_status` FROM (`inventory` `i` join `products` `p` on(`p`.`product_id` = `i`.`product_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `order_summary`
--
DROP TABLE IF EXISTS `order_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_summary`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`transaction_id` AS `transaction_id`, `c`.`fname` AS `customer_fname`, `c`.`lname` AS `customer_lname`, `o`.`payment_status` AS `payment_status`, `o`.`order_status` AS `order_status`, `o`.`total_amount` AS `total_amount`, `o`.`order_date` AS `order_date` FROM (`orders` `o` join `customers` `c` on(`o`.`customer_id` = `c`.`customer_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `order_transaction_details`
--
DROP TABLE IF EXISTS `order_transaction_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_transaction_details`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`transaction_id` AS `transaction_id`, `o`.`customer_id` AS `customer_id`, `c`.`fname` AS `fname`, `c`.`lname` AS `lname`, `p`.`product_name` AS `product_name`, `oi`.`quantity` AS `quantity`, `oi`.`price` AS `price`, `oi`.`subtotal` AS `subtotal`, `o`.`total_amount` AS `total_amount`, `o`.`order_status` AS `order_status`, `o`.`payment_status` AS `payment_status`, `o`.`order_date` AS `order_date` FROM (((`orders` `o` join `order_items` `oi` on(`o`.`order_id` = `oi`.`order_id`)) join `products` `p` on(`oi`.`product_id` = `p`.`product_id`)) join `customers` `c` on(`o`.`customer_id` = `c`.`customer_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  ADD PRIMARY KEY (`inquiry_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `fk_customers_user` (`user_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_inventory_product` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_orderitems_order` (`order_id`),
  ADD KEY `fk_orderitems_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_products_category` (`category_id`);
ALTER TABLE `products` ADD FULLTEXT KEY `product_name` (`product_name`,`description`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_images_product` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_reviews_customer` (`customer_id`),
  ADD KEY `fk_reviews_product` (`product_id`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `fk_cart_user` (`user_id`),
  ADD KEY `fk_cart_product` (`product_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orderitems_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
