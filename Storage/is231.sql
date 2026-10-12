-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 23 2026 г., 06:24
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `is231`
--

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `fio` varchar(120) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `all_sum` float NOT NULL,
  `created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `fio`, `address`, `phone`, `email`, `all_sum`, `created`) VALUES
(1, 'Данила', 'Кемерово', '8888888', '2008danil04@gmail.com', 44950, '2026-04-13 12:03:13'),
(2, 'Данила', 'кемерово', '890000000', '2008danil04@gmail.com', 13490, '2026-04-15 08:37:07'),
(3, 'test', 'test', 'test', '2008danil04@gmail.com', 8000, '2026-04-15 08:52:58');

-- --------------------------------------------------------

--
-- Структура таблицы `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `count_item` int(11) NOT NULL,
  `price_item` float NOT NULL,
  `sum_item` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `order_item`
--

INSERT INTO `order_item` (`id`, `order_id`, `product_id`, `count_item`, `price_item`, `sum_item`) VALUES
(1, 1, 1, 3, 8990, 26970),
(2, 1, 2, 1, 5490, 5490),
(3, 1, 4, 1, 12490, 12490),
(4, 2, 1, 1, 8000, 8000),
(5, 2, 2, 1, 5490, 5490),
(6, 3, 1, 1, 8000, 8000);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id_product` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(120) DEFAULT NULL,
  `price` float NOT NULL,
  `created` datetime DEFAULT current_timestamp(),
  `updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` varchar(50) DEFAULT 'other'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id_product`, `name`, `description`, `image`, `price`, `created`, `updated`, `category`) VALUES
(1, 'Механическая клавиатура HyperX Alloy FPS', 'Профессиональная механическая клавиатура с RGB-подсветкой.', '/assets/images/keyboard1.png', 8000, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'keyboard'),
(2, 'Игровая мышь Logitech G502 HERO', 'Высокоточная игровая мышь с сенсором HERO 25K.', '/assets/images/mouse1.png', 5490, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'mouse'),
(3, 'Монитор ASUS TUF Gaming VG27AQ', '27-дюймовый игровой монитор с разрешением WQHD.', '/assets/images/monitor1.png', 32990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'monitor'),
(4, 'Гарнитура SteelSeries Arctis 7', 'Беспроводная игровая гарнитура с объемным звуком 7.1.', '/assets/images/headset1.png', 12490, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'headset'),
(5, 'Веб-камера Logitech C920 HD Pro', 'Full HD веб-камера с автофокусом и стереозвуком.', '/assets/images/webcam1.png', 6790, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'webcam'),
(6, 'Видеокарта NVIDIA GeForce RTX 4060', 'Мощная игровая видеокарта с архитектурой Ada Lovelace.', '/assets/images/gpu1.png', 35990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'gpu'),
(7, 'Процессор AMD Ryzen 7 5800X', '8-ядерный процессор с 16 потоками для игр и работы.', '/assets/images/cpu1.png', 28490, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'cpu'),
(8, 'Оперативная память Corsair Vengeance RGB Pro 16GB', 'Комплект из 2 модулей DDR4 по 8 ГБ. Частота 3200 МГц.', '/assets/images/ram1.png', 6790, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'ram'),
(9, 'SSD накопитель Samsung 970 EVO Plus 1TB', 'Высокоскоростной NVMe M.2 SSD. Скорость чтения до 3500 МБ/с.', '/assets/images/ssd1.png', 8990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'storage'),
(10, 'Корпус NZXT H510 Elite', 'Стильный Mid-Tower корпус с закаленным стеклом.', '/assets/images/case1.png', 9490, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'case'),
(11, 'Механическая клавиатура Razer BlackWidow V3', 'Профессиональная игровая клавиатура с механическими переключателями Razer Green. RGB-подсветка Chroma, алюминиевая рамка, программируемые клавиши.', '/assets/images/keyboard_razer.png', 12990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'keyboard'),
(12, 'Игровая мышь Razer DeathAdder V2', 'Эргономичная игровая мышь с сенсором 20 000 DPI. 8 программируемых кнопок, RGB-подсветка, оптические переключатели, вес 82 грамма.', '/assets/images/mouse_razer.png', 6990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'mouse'),
(13, 'Монитор LG UltraGear 27GN800', '27-дюймовый игровой IPS-монитор с разрешением QHD. Частота 144 Гц, время отклика 1 мс, поддержка G-Sync, HDR10, регулировка высоты.', '/assets/images/monitor_lg.png', 34990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'monitor'),
(14, 'Гарнитура Razer Kraken V3 X', 'Игровая гарнитура с объемным звуком 7.1. RGB-подсветка Chroma, трифорсные драйверы 50 мм, съемный микрофон, легкие 250 грамм.', '/assets/images/headset_razer.png', 8990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'headset'),
(15, 'Веб-камера Razer Kiyo Pro', 'Профессиональная веб-камера для стриминга с адаптивным кольцевым светом. 1080p 60fps, сенсор Sony STARVIS, широкий угол 103°.', '/assets/images/webcam_razer.png', 9990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'webcam'),
(16, 'Видеокарта AMD Radeon RX 7800 XT', 'Мощная видеокарта с архитектурой RDNA 3. 16 ГБ памяти GDDR6, трассировка лучей, FSR 3.0, TDP 263W, идеальна для 1440p гейминга.', '/assets/images/gpu_amd.png', 54990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'gpu'),
(17, 'Процессор Intel Core i7-13700K', '16-ядерный процессор 13-го поколения (8P+8E). Частота до 5.4 ГГц, кэш 30 МБ, сокет LGA1700, TDP 125W, встроенная графика UHD 770.', '/assets/images/cpu_intel.png', 36990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'cpu'),
(18, 'Оперативная память G.Skill Trident Z5 RGB 32GB', 'Комплект DDR5 из 2 модулей по 16 ГБ. Частота 6000 МГц, тайминги CL30, алюминиевый радиатор, RGB-подсветка, Intel XMP 3.0.', '/assets/images/ram_gskill.png', 12990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'ram'),
(19, 'SSD накопитель Western Digital Black SN850X 2TB', 'Высокоскоростной NVMe M.2 SSD с радиатором. Скорость чтения до 7300 МБ/с, записи до 6600 МБ/с, PCIe 4.0, TBW 1200, 5 лет гарантии.', '/assets/images/ssd_wd.png', 16990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'storage'),
(20, 'Корпус Corsair 4000D Airflow', 'Mid-Tower корпус с превосходной вентиляцией. Сетчатая передняя панель, закаленное стекло, 2 вентилятора 120мм, кабель-менеджмент, USB-C.', '/assets/images/case_corsair.png', 8990, '2026-04-13 11:57:03', '2026-04-23 11:24:06', 'case');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT '',
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `token`, `is_verified`, `created_at`, `phone`, `address`, `avatar`) VALUES
(1, 'Данила', '2008danil04@gmail.com', '$2y$10$RRwl.QpUejhAH4KBFwgP8.PzOUgzDaBBG8Tp1RL3KnI8YI9/1qTfG', 'd515fbb146e4206f206f0af409e2561f913033adaae025850ee27e9f69b2cea1', 1, '2026-04-14 18:26:50', '888888888', 'г. Кемерово ул. Тухачевского', '/Storage/avatars/avatar_1_1776340011.jpg');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id_product`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id_product`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
