-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 01:46 AM
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
-- Database: `cake_site`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Chocolate', 'Delicious chocolate cakes and desserts'),
(2, 'Vanilla', 'Classic vanilla flavored cakes'),
(3, 'Vegetable', 'Cakes made with vegetables like carrot'),
(4, 'Fruit', 'Fruit flavored cakes and desserts'),
(5, 'No-Bake', 'Desserts that require no baking'),
(6, 'Birthday', 'Special cakes for birthday celebrations');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `recipe_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 6, 2, 3, '', '2025-04-09 22:24:42'),
(4, 9, 2, 3, '', '2025-04-09 23:23:39'),
(5, 9, 4, 5, '', '2025-04-09 23:32:12');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ingredients` text NOT NULL,
  `instructions` text NOT NULL,
  `preparation_time` int(11) NOT NULL,
  `cooking_time` int(11) DEFAULT 0,
  `servings` int(11) DEFAULT 4,
  `difficulty` enum('easy','medium','hard') NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `category_id`, `title`, `description`, `ingredients`, `instructions`, `preparation_time`, `cooking_time`, `servings`, `difficulty`, `image_url`, `created_at`) VALUES
(1, 1, 1, 'Chocolate Fudge Cake', 'Rich chocolate cake with fudge frosting.', '2 cups all-purpose flour\r\n2 cups sugar\r\n3/4 cup cocoa powder\r\n2 tsp baking soda\r\n1 tsp baking powder\r\n1/2 tsp salt\r\n1 cup buttermilk\r\n1 cup coffee\r\n1/2 cup oil\r\n2 eggs', '1. Preheat oven to 175°C.\r\n2. Mix dry ingredients, then add wet.\r\n3. Bake for 30-35 mins.\r\n4. Let cool and frost.', 45, 0, 4, 'easy', 'uploads/chocoCake.jpeg', '2025-04-09 22:21:31'),
(2, 1, 2, 'Classic Vanilla Cake', 'Fluffy vanilla cake with buttercream.', '2.5 cups flour\r\n2 cups sugar\r\n3 tsp baking powder\r\n1 tsp salt\r\n1 cup milk\r\n1/2 cup oil\r\n1 tbsp vanilla extract\r\n2 eggs\r\n1 cup boiling water', '1. Preheat oven to 175°C.\r\n2. Mix dry ingredients, then wet.\r\n3. Add boiling water carefully.\r\n4. Bake for 30-35 mins.', 55, 0, 4, 'easy', 'uploads/Perfect Vanilla Cake Recipe- Moist & Easy -Baking a Moment.jpeg', '2025-04-09 22:21:32'),
(3, 1, 3, 'Carrot Cake', 'Spiced carrot cake with cream cheese frosting and walnuts.', '2 cups grated carrots\r\n1.5 cups flour\r\n1.5 cups sugar\r\n1 tsp cinnamon\r\n1 tsp baking soda\r\n1/2 tsp salt\r\n3/4 cup oil\r\n3 eggs', '1. Preheat oven to 180°C.\r\n2. Mix all ingredients.\r\n3. Bake for 40-45 mins.\r\n4. Cool and frost with cream cheese.', 70, 0, 4, 'medium', 'uploads/Easy Carrot Cake Recipe - Preppy Kitchen.jpeg', '2025-04-09 22:21:32'),
(4, 1, 4, 'Lemon Cake', 'Tangy lemon cake with lemon zest and glaze.', '2 cups flour\r\n1.5 cups sugar\r\n1 tbsp lemon zest\r\n1/2 cup lemon juice\r\n1/2 cup butter\r\n2 eggs\r\n1 tsp baking powder\r\n1/2 tsp salt', '1. Preheat oven to 180°C.\r\n2. Cream butter and sugar.\r\n3. Add rest of ingredients.\r\n4. Bake 35-40 mins.', 65, 0, 4, 'medium', 'uploads/Lemon Sponge Cake.jpeg', '2025-04-09 22:21:32'),
(5, 1, 5, 'Classic Cheesecake', 'Creamy, smooth cheesecake with graham cracker crust.', '2 cups cream cheese\r\n1 cup sugar\r\n1 tsp vanilla\r\n3 eggs\r\n1 cup sour cream\r\nGraham cracker crust', '1. Preheat oven to 160°C.\r\n2. Mix ingredients until smooth.\r\n3. Pour into crust and bake 60 mins.\r\n4. Chill for 3+ hours.', 120, 0, 4, 'hard', 'uploads/Cheesecakes.jpeg', '2025-04-09 22:21:32'),
(6, 1, 6, 'Birthday Cake', 'Festive funfetti cake with colorful sprinkles.', '2.5 cups flour\r\n2 cups sugar\r\n1 cup milk\r\n1/2 cup butter\r\n2 tsp vanilla\r\n3 eggs\r\n1 tbsp sprinkles', '1. Preheat oven to 175°C.\r\n2. Cream butter and sugar.\r\n3. Add remaining ingredients and mix.\r\n4. Bake for 35-40 mins. Cool and decorate.', 90, 0, 4, 'medium', 'uploads/Vanilla Cake with Vanilla Buttercream Frosting.jpeg', '2025-04-09 22:21:32'),
(9, 3, 4, 'وصفة تجريبية', 'الوووووصف', 'المكونات', 'خطوات عمل الوصفة', 4, 4, 4, 'easy', 'uploads/recipe_67f7015487959.jpeg', '2025-04-09 23:23:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'users1', 'users1@email.com', '123456', '2025-04-09 19:56:32'),
(2, 'users2', 'users2@email.com', '123456', '2025-04-09 21:12:07'),
(3, 'users3', 'users3@email.com', '123456', '2025-04-09 23:22:29'),
(4, 'users4', 'users4@email.com', '123456', '2025-04-09 23:31:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
