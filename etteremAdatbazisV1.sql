-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2026 at 08:20 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `allergens`
--

CREATE TABLE `allergens` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'Allergén megnevezése',
  `code` varchar(10) NOT NULL COMMENT 'Rövid kód / ikon azonosító'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `allergens`
--

INSERT INTO `allergens` (`id`, `name`, `code`) VALUES
(1, 'Glutén', 'GLU'),
(2, 'Tej / Laktóz', 'LAC'),
(3, 'Tojás', 'EGG'),
(4, 'Diófélék', 'NUT');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL COMMENT 'Kategória megnevezése',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Megjelenítési sorrend az étlapon'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `sort_order`) VALUES
(1, 'Előételek', 1),
(2, 'Levesek', 2),
(3, 'Főételek', 3),
(4, 'Desszertek', 4);

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL COMMENT 'Kapcsolódó kategória azonosítója',
  `name` varchar(120) NOT NULL COMMENT 'Étel/ital megnevezése',
  `description` text DEFAULT NULL COMMENT 'Részletes leírás és összetevők',
  `price` decimal(10,2) NOT NULL COMMENT 'Ár (Ft)',
  `image_url` varchar(255) DEFAULT NULL COMMENT 'Feltöltött kép elérési útvonala',
  `dietary_tags` varchar(150) DEFAULT NULL COMMENT 'Diétás címkék vesszővel elválasztva (pl. vegan,gluten-free)',
  `is_available` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Elérhető-e jelenleg (1 = igen, 0 = nem)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`id`, `category_id`, `name`, `description`, `price`, `image_url`, `dietary_tags`, `is_available`) VALUES
(1, 1, 'Grillezett kecskesajt friss salátaágyon', 'Mézes-diós öntettel, rukkolával és pirított magvakkal tálalt kézműves kecskesajt.', 2890.00, 'assets/img/dishes/kecskesajt.jpg', 'vegetarian,gluten-free', 1),
(2, 2, 'Sütőtök krémleves pirított tökmaggal', 'Kókusztejjel selymesített, fűszeres sült sütőtök krémleves hidegen sajtolt tökmagolajjal.', 2190.00, 'assets/img/dishes/sutotok_leves.jpg', 'vegan,gluten-free,lactose-free', 1),
(3, 3, 'Ropogós kacsacomb párolt lilakáposztával', 'Lassan sült kacsacomb hagymás törtburgonyával és vörösboros aszalt szilvás lilakáposztával.', 5490.00, 'assets/img/dishes/kacsacomb.jpg', 'gluten-free,lactose-free', 1),
(4, 4, 'Belga étcsokoládés brownie', 'Selymes étcsokoládés sütemény vaníliafagylalttal és erdei gyümölcs raguval.', 2390.00, 'assets/img/dishes/brownie.jpg', 'vegetarian', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dish_allergens`
--

CREATE TABLE `dish_allergens` (
  `dish_id` int(10) UNSIGNED NOT NULL,
  `allergen_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `dish_allergens`
--

INSERT INTO `dish_allergens` (`dish_id`, `allergen_id`) VALUES
(1, 2),
(1, 4),
(4, 1),
(4, 2),
(4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Regisztrált felhasználó ID (nem regisztrált vendégnél NULL)',
  `guest_name` varchar(100) NOT NULL COMMENT 'Foglaló neve',
  `guest_email` varchar(150) NOT NULL COMMENT 'Foglaló e-mail címe',
  `guest_phone` varchar(25) NOT NULL COMMENT 'Foglaló telefonszáma',
  `reservation_date` date NOT NULL COMMENT 'Foglalás napja',
  `reservation_time` time NOT NULL COMMENT 'Foglalás időpontja',
  `guests_count` int(10) UNSIGNED NOT NULL COMMENT 'Vendégek létszáma',
  `notes` text DEFAULT NULL COMMENT 'Egyéb megjegyzés (pl. etetőszék, allergia)',
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Foglalás státusza'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `guest_name`, `guest_email`, `guest_phone`, `reservation_date`, `reservation_time`, `guests_count`, `notes`, `status`) VALUES
(1, 2, 'Kovács Péter', 'peter.kovacs@pelda.hu', '+36209876543', '2026-11-28', '19:00:00', 4, 'Ablak melletti asztalt kérnénk, egyik vendég laktózérzékeny.', 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Teljes név',
  `email` varchar(150) NOT NULL COMMENT 'E-mail cím (egyedi bejelentkezési azonosító)',
  `password_hash` varchar(255) NOT NULL COMMENT 'BCrypt/Argon2 titkosított jelszó',
  `password_salt` varchar(64) NOT NULL DEFAULT '' COMMENT 'Egyedi kriptográfiai SALT a jelszó ellenőrzéséhez',
  `phone` varchar(25) DEFAULT NULL COMMENT 'Telefonszám (opcionális)',
  `role` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Jogosultsági szint (1-7: 1 = normál felhasználó, 7 = admin)',
  `newsletter_subscribed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Hírlevél feliratkozás (0 = nem, 1 = igen)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Regisztráció időpontja'
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `password_salt`, `phone`, `role`, `newsletter_subscribed`, `created_at`) VALUES
(1, 'Adminisztrátor Anna', 'admin@etterem.hu', '02c4e9b3392a40391ff4e3c95c8703a2456ecb79ed5ca815c818ca75f9c6f995', 'a8f5f167f44f4964e6c998dee827110c', '+36301112233', 7, 0, '2026-11-23 18:52:26'),
(2, 'Kovács Péter', 'peter.kovacs@pelda.hu', '034446a3455296655e2a53748d358aa9ad54ef96c5e16f5464114b049184ca81', '3b9c8d2e1f4a7065c8e2b9d4a1f60387', '+36209876543', 1, 1, '2026-11-23 18:52:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allergens`
--
ALTER TABLE `allergens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_allergens_code` (`code`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dishes_category` (`category_id`);

--
-- Indexes for table `dish_allergens`
--
ALTER TABLE `dish_allergens`
  ADD PRIMARY KEY (`dish_id`,`allergen_id`),
  ADD KEY `idx_dish_allergens_allergen` (`allergen_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reservations_user` (`user_id`),
  ADD KEY `idx_reservations_date_time` (`reservation_date`,`reservation_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allergens`
--
ALTER TABLE `allergens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `fk_dishes_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `dish_allergens`
--
ALTER TABLE `dish_allergens`
  ADD CONSTRAINT `fk_da_allergen` FOREIGN KEY (`allergen_id`) REFERENCES `allergens` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_da_dish` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
