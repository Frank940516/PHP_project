-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-05-08 18:31:21
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

--
-- 傾印資料表的資料 `accounts`
--

INSERT INTO `accounts` (`No`, `Email`, `Password`, `Name`, `Type`, `Status`, `block_reason`) VALUES
(1, 'test@gmail.com', 'test1234', 'Ayaya .w.Ayaya .w.', 'Admin', 'active', NULL),
(2, '12121', '121212', '12121', 'User', 'blocked', '123'),
(4, 'test', 'ee', 'TEST123', 'User', 'active', NULL),
(6, 'test2@gmail.com', 'test2test', 'TESTADMIN2', 'Admin', 'active', NULL);

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

--
-- 傾印資料表的資料 `announcement`
--

INSERT INTO `announcement` (`No`, `Title`, `Content`, `Date`, `Publisher`) VALUES
(2, 'Test Edit Function and Correct Time', '**就單純想啦 :D**', '2025-05-06 19:31:55', 1),
(3, '測試測試測試測試測試測試測試測試測試測試測試測試測試測試測試測試測試測試測試測試測試測測試', '12121212121212\r\nAyaya wants to test edit function lol :) yesEvcjidhnfdjfpoidjoifh n;hhhhhh;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;h;hhhhhhhhihdof;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;f;ffffffffffffffffffffffffffffffffffffffffffffffffffffff', '2025-05-06 21:24:22', 1);

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

--
-- 傾印資料表的資料 `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(37, 4, 22, 1, '2025-05-08 23:39:12', '2025-05-08 23:39:12');

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

--
-- 傾印資料表的資料 `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount`, `expiration_date`, `is_active`, `start_date`, `discount_type`, `redeem_limit`, `redeem_count`) VALUES
(1, 'WELCOME10', 10.00, '2025-12-31', 2, '2025-05-14', 'percentage', 1, 0),
(2, 'SUMMER20', 20.00, '2025-08-31', 1, '2025-05-08', 'percentage', 1, 0),
(3, 'BLACKFRIDAY50', 25.00, '2025-11-29', 1, '2025-05-08', 'percentage', 10, 1),
(4, 'EXPIRED5', 5.00, '2024-12-31', 0, '2025-05-08', 'percentage', 1, 1),
(7, 'LIMITED', 10.00, '2025-05-08', 0, '2025-05-08', 'percentage', 2, 2),
(8, '!', 100.00, '2025-05-09', 2, '2025-05-09', 'fixed', 1, 0),
(11, 'test%&$', 100.00, '2025-06-04', 1, '2025-05-08', 'fixed', 5, 2);

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

--
-- 傾印資料表的資料 `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `created_at`, `updated_at`, `payment_method`, `coupon_code`, `coupon_discount`) VALUES
(1, 1, 600, '2025-05-05 08:59:02', '2025-05-05 08:59:02', '', NULL, NULL),
(2, 1, 661, '2025-05-05 09:19:55', '2025-05-05 09:19:55', '', NULL, NULL),
(3, 1, 1500, '2025-05-05 09:20:50', '2025-05-05 09:20:50', '', NULL, NULL),
(4, 1, 900, '2025-05-05 12:44:11', '2025-05-05 12:44:11', '', NULL, NULL),
(5, 1, 300, '2025-05-05 12:47:14', '2025-05-05 12:47:14', '', NULL, NULL),
(6, 4, 300, '2025-05-05 13:09:11', '2025-05-05 13:09:11', '', NULL, NULL),
(7, 4, 24, '2025-05-05 13:09:38', '2025-05-05 13:09:38', '', NULL, NULL),
(8, 1, 121, '2025-05-05 13:29:03', '2025-05-05 13:29:03', '', NULL, NULL),
(9, 1, 600, '2025-05-06 11:37:44', '2025-05-06 11:37:44', '', NULL, NULL),
(10, 6, 500, '2025-05-06 12:38:33', '2025-05-06 12:38:33', '', NULL, NULL),
(11, 4, 500, '2025-05-06 12:39:33', '2025-05-06 12:39:33', '', NULL, NULL),
(12, 6, 12, '2025-05-06 12:40:06', '2025-05-06 12:40:06', '', NULL, NULL),
(13, 1, 300, '2025-05-06 12:53:48', '2025-05-06 12:53:48', '', NULL, NULL),
(14, 4, 111, '2025-05-08 02:56:24', '2025-05-08 02:56:24', '', NULL, NULL),
(15, 1, 300, '2025-05-08 03:41:02', '2025-05-08 03:41:02', '', NULL, NULL),
(16, 1, 121, '2025-05-08 03:41:20', '2025-05-08 03:41:20', '', NULL, NULL),
(17, 1, 121, '2025-05-08 03:45:21', '2025-05-08 03:45:21', 'paypal', NULL, NULL),
(18, 1, 121, '2025-05-08 03:49:28', '2025-05-08 03:49:28', 'bank_transfer', NULL, NULL),
(19, 4, 111, '2025-05-08 14:51:26', '2025-05-08 14:51:26', 'credit_card', NULL, 0.00),
(20, 4, 111, '2025-05-08 15:03:47', '2025-05-08 15:03:47', 'credit_card', NULL, 0.00),
(21, 4, 800, '2025-05-08 15:10:25', '2025-05-08 15:10:25', 'paypal', NULL, 0.00),
(22, 4, 150, '2025-05-08 15:19:17', '2025-05-08 15:19:17', 'bank_transfer', NULL, 0.00),
(23, 4, 600, '2025-05-08 15:35:20', '2025-05-08 15:35:20', 'bank_transfer', 'BLACKFRIDAY50', 200.00);

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

--
-- 傾印資料表的資料 `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 11, 2, 300, 600),
(5, 3, 11, 1, 300, 300),
(6, 4, 11, 3, 300, 900),
(7, 5, 11, 1, 300, 300),
(8, 6, 11, 1, 300, 300),
(10, 8, 15, 1, 121, 121),
(11, 9, 11, 2, 300, 600),
(15, 13, 11, 1, 300, 300),
(16, 14, 22, 1, 111, 111),
(17, 15, 11, 1, 300, 300),
(18, 16, 15, 1, 121, 121),
(19, 17, 15, 1, 121, 121),
(20, 18, 15, 1, 121, 121),
(21, 19, 22, 1, 111, 111),
(22, 20, 22, 1, 111, 111),
(23, 21, 17, 1, 800, 800),
(24, 22, 13, 1, 150, 150),
(25, 23, 17, 1, 800, 800);

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

--
-- 傾印資料表的資料 `products`
--

INSERT INTO `products` (`id`, `name`, `author`, `category`, `seller_id`, `condition`, `description`, `location`, `attachment`, `price`, `stock`, `created_at`, `updated_at`, `is_deleted`) VALUES
(11, 'PHP教科書2', '', '電腦/資訊', 6, '九成新', '去年買的，有一點小筆跡', '', '-11.png', 300, 1, '2025-05-04 23:41:25', '2025-05-08 11:41:02', 0),
(13, 'more books', '', '文學/小說', 1, '九成新', '前年買的，沒什麼畫過\r\n需要者收，可議價', '', 'Ayaya-13.png', 150, 2, '2025-05-05 10:39:04', '2025-05-08 23:19:17', 0),
(15, '1212', 'test author', '文學/小說', 4, '五成新', 'idk', 'Earth', '1-15.png', 121, 1, '2025-05-05 21:10:53', '2025-05-08 11:49:28', 0),
(17, 'Java Advanced Textbook', '', '考試用書/教科書', 1, '九成新', '去年買的', '', 'Ayaya .w.Ayaya .w.-17.png', 800, 10, '2025-05-06 22:07:52', '2025-05-08 23:35:20', 0),
(22, 'test new field', 'new author:)', '漫畫/輕小說', 1, '九成新', '1111', 'Taiwan:)', '1-22.png', 111, 8, '2025-05-07 23:46:58', '2025-05-08 23:03:47', 0);

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

--
-- 傾印資料表的資料 `user_coupons`
--

INSERT INTO `user_coupons` (`id`, `user_id`, `coupon_id`, `redeem_time`, `is_used`) VALUES
(5, 4, 11, '2025-05-08 21:23:27', 0),
(6, 4, 3, '2025-05-08 21:25:49', 1);

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
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `accounts`
--
ALTER TABLE `accounts`
  MODIFY `No` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `announcement`
--
ALTER TABLE `announcement`
  MODIFY `No` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user_coupons`
--
ALTER TABLE `user_coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
