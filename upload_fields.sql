-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: mariadb_ota
-- Время создания: Дек 21 2022 г., 12:30
-- Версия сервера: 10.4.6-MariaDB-1:10.4.6+maria~bionic-log
-- Версия PHP: 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `google`
--

-- --------------------------------------------------------

--
-- Структура таблицы `upload_fields`
--

CREATE TABLE `upload_fields` (
  `id` int(11) NOT NULL,
  `name` varchar(512) NOT NULL,
  `comment` varchar(1024) DEFAULT NULL,
  `price_field` varchar(2048) DEFAULT NULL,
  `product_field` varchar(256) DEFAULT NULL,
  `row_id` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `default_visible` tinyint(1) NOT NULL,
  `is_select_field` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `upload_fields`
--

INSERT INTO `upload_fields` (`id`, `name`, `comment`, `price_field`, `product_field`, `row_id`, `position`, `default_visible`, `is_select_field`) VALUES
(1, 'Seller', 'Comment for Seller', 'Seller', 'seller', 'seller', 1, 1, 0),
(2, 'PriceName', 'Comment for PriceName', 'PriceName', 'seller', 'price_name', 2, 1, 0),
(3, 'Price', 'Select the Price field', 'Price', 'seller', 'price', 8, 1, 1),
(4, 'UPC', 'Select the UPC code|EAN code |Part Number field', 'UPC', 'seller', 'upc', 6, 1, 1),
(5, 'EAN', 'Select the UPC code|EAN code |Part Number field', 'EAN', 'seller', 'ean', 7, 1, 1),
(6, 'Item', 'Select the Item field', 'Item', 'seller', 'item', 11, 0, 1),
(7, 'Product', 'Select the Product field', 'Product', 'seller', 'product', 10, 0, 1),
(8, 'MQO', 'Select the MOQ field', 'MQO', 'seller', 'mqo', 13, 0, 1),
(9, 'Description', 'Select the Description field', 'Description', 'seller', 'description', 16, 0, 1),
(10, 'PN/Model', 'Select the Part Number field', 'PN/Model', 'seller', 'pn_model', 9, 0, 1),
(11, 'Product_URL', 'Select the URL field', 'Product_URL', 'seller', 'product_url', 15, 0, 1),
(12, 'Brand', 'Select the Brand field', 'Brand', 'seller', 'brand', 14, 0, 1),
(13, 'Brand_Stat', 'Comment for Brand_Stat', 'Brand_Stat', 'seller', 'brand_stat', 4, 1, 0),
(14, 'Pack', 'Select the Pack field', 'Pack', 'seller', 'pack', 12, 0, 1),
(15, 'Note', 'Comment for Note', 'null', 'seller', 'note', 5, 1, 0),
(16, 'Min. Order Amount (MOA)', 'Min. Order Amount (MOA)', 'null', 'seller', 'moa', 3, 1, 0),
(17, 'stock', 'Select the Stock field', NULL, NULL, 'stock', 17, 0, 1),
(18, 'Sales (30)', 'Select the Sales (30) field', NULL, NULL, 'sales_30', 18, 0, 1),
(19, 'Sheet', 'Comment for sheet', NULL, NULL, 'sheet', -1, 1, 0),
(20, 'Line', 'Comment for line', NULL, NULL, 'line', -1, 1, 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `upload_fields`
--
ALTER TABLE `upload_fields`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `upload_fields`
--
ALTER TABLE `upload_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
