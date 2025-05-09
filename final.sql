-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-05-09 12:47:43
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `final`
--

-- --------------------------------------------------------

--
-- 資料表結構 `accounts`
--

CREATE TABLE `accounts` (
  `No` int(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Type` varchar(100) NOT NULL,
  `Status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `block_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `announcement`
--

CREATE TABLE `announcement` (
  `No` int(200) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Content` text NOT NULL,
  `Date` datetime NOT NULL,
  `Publisher` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL COMMENT '優惠券代碼，最多 20 個字元',
  `discount` decimal(5,2) NOT NULL COMMENT '折扣百分比/金額',
  `expiration_date` date NOT NULL COMMENT '到期日',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否有效 (2:未生效 1: 有效, 0: 無效)',
  `start_date` date NOT NULL DEFAULT curdate() COMMENT '開始生效日期',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `redeem_limit` int(11) NOT NULL DEFAULT 1,
  `redeem_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` varchar(50) NOT NULL COMMENT '付款方式',
  `coupon_code` varchar(50) DEFAULT NULL COMMENT '使用的優惠券代碼',
  `coupon_discount` decimal(10,2) DEFAULT NULL COMMENT '優惠券折扣金額'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `subtotal` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `condition` enum('全新','九成新','七成新','五成新') NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `price` decimal(10,0) NOT NULL,
  `stock` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `user_coupons`
--

CREATE TABLE `user_coupons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '使用者 ID',
  `coupon_id` int(11) NOT NULL COMMENT '優惠券 ID',
  `redeem_time` datetime NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0 COMMENT '是否已使用 (0: 未使用, 1: 已使用)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `wish_list`
--

CREATE TABLE `wish_list` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_name` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`No`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- 資料表索引 `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`No`),
  ADD KEY `fk_publisher` (`Publisher`);

--
-- 資料表索引 `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 資料表索引 `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- 資料表索引 `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 資料表索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 資料表索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- 資料表索引 `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- 資料表索引 `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- 資料表索引 `wish_list`
--
ALTER TABLE `wish_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `accounts`
--
ALTER TABLE `accounts`
  MODIFY `No` int(100) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `announcement`
--
ALTER TABLE `announcement`
  MODIFY `No` int(200) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user_coupons`
--
ALTER TABLE `user_coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `wish_list`
--
ALTER TABLE `wish_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `fk_publisher` FOREIGN KEY (`Publisher`) REFERENCES `accounts` (`No`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`No`) ON DELETE CASCADE;

--
-- 資料表的限制式 `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`No`) ON DELETE CASCADE;

--
-- 資料表的限制式 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`No`);

--
-- 資料表的限制式 `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- 資料表的限制式 `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `accounts` (`No`) ON DELETE CASCADE;

--
-- 資料表的限制式 `user_coupons`
--
ALTER TABLE `user_coupons`
  ADD CONSTRAINT `user_coupons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`No`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_coupons_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `wish_list`
--
ALTER TABLE `wish_list`
  ADD CONSTRAINT `wish_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`No`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
