-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Nov 2026 pada 11.57
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phoenix_coffee`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(40) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('Tunai','QRIS') NOT NULL DEFAULT 'Tunai',
  `notes` varchar(255) DEFAULT NULL,
  `status` enum('Menunggu Konfirmasi','Diproses','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Konfirmasi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `total`, `payment_method`, `notes`, `status`, `created_at`) VALUES
(1, 'WEB26101018053569', 2, 62000.00, 'Tunai', '', 'Selesai', '2026-10-10 16:05:35'),
(2, 'WEB26101018055733', 2, 15000.00, 'QRIS', '', 'Selesai', '2026-10-10 16:05:57'),
(3, 'WEB26101018291526', 3, 15000.00, 'Tunai', '', 'Selesai', '2026-10-10 16:29:15'),
(4, 'WEB26102406431746', 5, 15000.00, 'Tunai', '', 'Selesai', '2026-10-24 04:43:17'),
(5, 'WEB26102407153510', 6, 15000.00, 'Tunai', '', 'Selesai', '2026-10-24 05:15:35'),
(6, 'WEB26102407171071', 6, 20000.00, 'Tunai', '', 'Selesai', '2026-10-24 05:17:10'),
(7, 'WEB26102408000969', 8, 72000.00, 'QRIS', '', 'Selesai', '2026-10-24 06:00:09'),
(8, 'WEB26110710420562', 11, 27000.00, 'Tunai', 'less suggar', 'Diproses', '2026-11-07 09:42:05'),
(9, 'WEB26110710473672', 13, 35000.00, 'QRIS', '', 'Menunggu Konfirmasi', '2026-11-07 09:47:36'),
(10, 'WEB26110711055831', 14, 40000.00, 'Tunai', '', 'Selesai', '2026-11-07 10:05:58'),
(11, 'WEB26110711131445', 15, 35000.00, 'QRIS', '', 'Menunggu Konfirmasi', '2026-11-07 10:13:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(120) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `qty`, `price`, `subtotal`) VALUES
(1, 1, 3, 'Cappuccino', 1, 20000.00, 20000.00),
(2, 1, 5, 'Choco Latte', 1, 20000.00, 20000.00),
(3, 1, 4, 'Matcha Latte', 1, 22000.00, 22000.00),
(4, 2, 2, 'Americano', 1, 15000.00, 15000.00),
(5, 3, 2, 'Americano', 1, 15000.00, 15000.00),
(6, 4, 2, 'Americano', 1, 15000.00, 15000.00),
(7, 5, 2, 'Americano', 1, 15000.00, 15000.00),
(8, 6, 3, 'Cappuccino', 1, 20000.00, 20000.00),
(9, 7, 3, 'Cappuccino', 3, 20000.00, 60000.00),
(10, 7, 7, 'Roti Bakar', 1, 12000.00, 12000.00),
(11, 8, 2, 'Americano', 1, 15000.00, 15000.00),
(12, 8, 7, 'Roti Bakar', 1, 12000.00, 12000.00),
(13, 9, 2, 'Americano', 1, 15000.00, 15000.00),
(14, 9, 3, 'Cappuccino', 1, 20000.00, 20000.00),
(15, 10, 3, 'Cappuccino', 1, 20000.00, 20000.00),
(16, 10, 5, 'Choco Latte', 1, 20000.00, 20000.00),
(17, 11, 2, 'Americano', 1, 15000.00, 15000.00),
(18, 11, 3, 'Cappuccino', 1, 20000.00, 20000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `category` enum('Minuman','Makanan') NOT NULL DEFAULT 'Minuman',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(30) NOT NULL DEFAULT 'pcs',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `stock`, `unit`, `created_at`) VALUES
(1, 'Es Kopi Susu', 'Minuman', 18000.00, 14, 'cup', '2026-10-10 12:17:11'),
(2, 'Americano', 'Minuman', 15000.00, 13, 'cup', '2026-10-10 12:17:11'),
(3, 'Cappuccino', 'Minuman', 20000.00, 5, 'cup', '2026-10-10 12:17:11'),
(4, 'Matcha Latte', 'Minuman', 22000.00, 9, 'cup', '2026-10-10 12:17:11'),
(5, 'Choco Latte', 'Minuman', 20000.00, 10, 'cup', '2026-10-10 12:17:11'),
(6, 'Croissant', 'Makanan', 15000.00, 17, 'pcs', '2026-10-10 12:17:11'),
(7, 'Roti Bakar', 'Makanan', 12000.00, 23, 'pcs', '2026-10-10 12:17:11'),
(8, 'Pisang Nugget', 'Makanan', 15000.00, 17, 'pcs', '2026-10-10 12:17:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `movement_type` enum('Masuk','Keluar') NOT NULL,
  `qty` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `invoice` varchar(40) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `payment` decimal(12,2) NOT NULL,
  `change_amount` decimal(12,2) NOT NULL,
  `payment_method` enum('Tunai','QRIS','Debit') NOT NULL DEFAULT 'Tunai',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `invoice`, `total`, `payment`, `change_amount`, `payment_method`, `created_at`) VALUES
(1, 'PHX26101014265568', 50000.00, 100000.00, 50000.00, 'QRIS', '2026-10-10 12:26:55'),
(2, 'INV20261010193842E07032', 73000.00, 100000.00, 27000.00, 'Tunai', '2026-10-10 17:38:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(120) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transaction_items`
--

INSERT INTO `transaction_items` (`id`, `transaction_id`, `product_id`, `product_name`, `qty`, `price`, `subtotal`) VALUES
(1, 1, 2, 'Americano', 1, 15000.00, 15000.00),
(2, 1, 5, 'Choco Latte', 1, 20000.00, 20000.00),
(3, 1, 6, 'Croissant', 1, 15000.00, 15000.00),
(4, 2, 2, 'Americano', 1, 15000.00, 15000.00),
(5, 2, 3, 'Cappuccino', 1, 20000.00, 20000.00),
(6, 2, 5, 'Choco Latte', 1, 20000.00, 20000.00),
(7, 2, 1, 'Es Kopi Susu', 1, 18000.00, 18000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `phone`) VALUES
(2, 'Tari', NULL, '', 'customer', '2026-10-10 16:04:50', '0812345678'),
(3, 'Tari', NULL, '', 'customer', '2026-10-10 16:17:45', '0812345678'),
(4, 'Kasir Phoenix', 'kasir@phoenixcoffee.com', 'kasir123!', 'kasir', '2026-10-10 16:43:10', NULL),
(5, 'Tari', NULL, '', 'customer', '2026-10-24 04:43:07', '0812345678'),
(6, 'Tari', NULL, '', 'customer', '2026-10-24 05:15:23', '0812345678'),
(7, 'Tari', NULL, '', 'customer', '2026-10-24 05:19:02', '0812345678'),
(8, 'Tari', NULL, '', 'customer', '2026-10-24 05:59:15', '0812345678'),
(9, 'Tari', NULL, '', 'customer', '2026-11-07 09:06:37', '0812345678'),
(10, 'Tari', NULL, '', 'customer', '2026-11-07 09:40:42', '0812345678'),
(11, 'dika', NULL, '', 'customer', '2026-11-07 09:41:27', '085766542314'),
(12, 'Tari', NULL, '', 'customer', '2026-11-07 09:47:02', '0812345678'),
(13, 'dika', NULL, '', 'customer', '2026-11-07 09:47:21', '085766542314'),
(14, 'dika', NULL, '', 'customer', '2026-11-07 10:05:46', '085766542314'),
(15, 'ANDIKA', NULL, '', 'customer', '2026-11-07 10:12:29', '085766542314');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice` (`invoice`);

--
-- Indeks untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
