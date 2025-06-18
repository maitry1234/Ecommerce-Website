-- Create the database
CREATE DATABASE IF NOT EXISTS `fabrique` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Use the newly created database
USE `fabrique`;

-- Create categories table
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `category_description` text DEFAULT NULL,
  `category_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into categories
INSERT INTO `categories` (`category_id`, `category_name`, `category_description`, `category_image`) VALUES
(1, 'Bedsheets', 'Premium quality bedsheets with variety of designs', 'assets/bed1.jpg'),
(2, 'Totebags', 'Hand-painted totebags for daily use', 'assets/Totebag.jpg'),
(3, 'Blankets', 'Warm and cozy Nepali pure cotton blankets', 'assets/bed2.jpg');

-- Create orders table
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create order_items table
CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create products table
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `product_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into products
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `product_description`, `price`, `stock_quantity`, `product_image`, `created_at`) VALUES
(1, 1, 'Premium Cotton Bedsheet', 'Luxurious 100% cotton bedsheet with 300 thread count.', 2500.00, 25, 'assets/bed1.jpg', '2025-05-03 04:30:33'),
(2, 1, 'Floral Pattern Bedsheet', 'Beautiful floral pattern bedsheet for a vibrant bedroom.', 2000.00, 18, 'assets/bedsheet_floral.jpg', '2025-05-03 04:30:33'),
(3, 1, 'Silk Bedsheet', 'Premium silk bedsheet for ultimate comfort.', 3000.00, 12, 'assets/bedsheetHaipuri.jpg', '2025-05-03 04:30:33'),
(4, 2, 'Hand-Painted Totebag', 'Uniquely designed hand-painted totebag.', 900.00, 30, 'assets/Totebag.jpg', '2025-05-03 04:30:33'),
(5, 2, 'Canvas Totebag', 'Durable canvas totebag for everyday use.', 800.00, 45, 'assets/totebag_canvas.jpg', '2025-05-03 04:30:33'),
(6, 2, 'Embroidered Totebag', 'Beautifully embroidered totebag with traditional patterns.', 1000.00, 20, 'assets/totebag_embroidered.jpg', '2025-05-03 04:30:33'),
(7, 3, 'Nepali Cotton Blanket', 'Warm and cozy blanket made from premium Nepali cotton.', 2500.00, 15, 'assets/bed2.jpg', '2025-05-03 04:30:33'),
(8, 3, 'Wool Blend Blanket', 'Soft wool blend blanket perfect for cold nights.', 1100.00, 22, 'assets/blanket_wool.jpg', '2025-05-03 04:30:33'),
(9, 3, 'Patterned Throw Blanket', 'Stylish throw blanket with traditional patterns.', 750.00, 28, 'assets/blanket_throw.jpg', '2025-05-03 04:30:33');

-- Create users table
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into users
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`) VALUES
(1, 'Maitry', 'maitrybajra123@gmail.com', '$2y$10$3./nVv037Uz7/RhUZuT6Z.A1F94/04RclIRz8ALSfWlI9R7uucUma'),
(2, 'Maitry', 'maitrybajra123@gmail.com', '$2y$10$5hAfAWXwy7wo.DaXegDwHO9viTn5KmvZBtp11x1seR5UX2YHI63se'),
(4, 'Username', 'example@gmail.com', '$2y$10$6RKaYUyPFEHGUA8xSlKJ..uNVKqij1pfrIcrH1FCjNer1Nn3HIc.6'),
(5, 'Kiran', 'kiran@gmail.com', '$2y$10$ARzkaq68qhBz9OQeCmHdk.WQHE8Qw2B7iq41AoSVtkkuY.4Ri8wOG'),
(6, 'Alberto', 'albert@gmail.com', '$2y$10$lXkt2vgMvLoSB/MDeQTuWenbHiCkMuE6khz/M814NkWxyS6v0I0LC');

-- Add primary keys
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

-- Set auto-increment
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

-- Add foreign key constraints
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

-- Commit the transaction
COMMIT;