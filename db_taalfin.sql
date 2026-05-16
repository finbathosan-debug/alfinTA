-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2026 at 03:48 PM
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
-- Database: `db_taalfin`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar_alfin`
--

CREATE TABLE `barang_keluar_alfin` (
  `id_keluar_alfin` int(11) NOT NULL,
  `id_produk_alfin` int(11) NOT NULL,
  `jumlah_keluar` int(11) NOT NULL,
  `tanggal_keluar` datetime NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_keluar_alfin`
--

INSERT INTO `barang_keluar_alfin` (`id_keluar_alfin`, `id_produk_alfin`, `jumlah_keluar`, `tanggal_keluar`, `keterangan`) VALUES
(1, 8, 2, '2026-05-13 14:18:36', '');

-- --------------------------------------------------------

--
-- Table structure for table `barang_masuk_alfin`
--

CREATE TABLE `barang_masuk_alfin` (
  `id_masuk_alfin` int(11) NOT NULL,
  `id_produk_alfin` int(11) NOT NULL,
  `jumlah_masuk` int(11) NOT NULL,
  `tanggal_masuk` datetime NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_masuk_alfin`
--

INSERT INTO `barang_masuk_alfin` (`id_masuk_alfin`, `id_produk_alfin`, `jumlah_masuk`, `tanggal_masuk`, `keterangan`) VALUES
(1, 6, 5, '2026-05-13 11:06:47', ''),
(2, 6, 2, '2026-05-13 11:07:14', ''),
(3, 7, 10, '2026-05-13 11:09:51', 'bolu'),
(4, 6, 5, '2026-05-13 13:32:37', ''),
(5, 7, 2, '2026-05-13 14:17:01', ''),
(6, 7, 15, '2026-05-13 14:54:27', ''),
(7, 19, 10, '2026-05-13 14:54:34', ''),
(8, 7, 15, '2026-05-13 16:40:43', '');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi_alfin`
--

CREATE TABLE `detail_transaksi_alfin` (
  `id_detail_alfin` int(11) NOT NULL,
  `id_transaksi_alfin` int(11) NOT NULL,
  `id_produk_alfin` int(11) NOT NULL,
  `jumlah_alfin` int(50) NOT NULL,
  `subtotal_alfin` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi_alfin`
--

INSERT INTO `detail_transaksi_alfin` (`id_detail_alfin`, `id_transaksi_alfin`, `id_produk_alfin`, `jumlah_alfin`, `subtotal_alfin`) VALUES
(1, 1, 7, 4, 14000),
(2, 2, 6, 2, 8000),
(3, 3, 20, 3, 12000);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna_alfin`
--

CREATE TABLE `pengguna_alfin` (
  `id_pengguna_alfin` int(11) NOT NULL,
  `nama_pengguna_alfin` varchar(35) NOT NULL,
  `username_alfin` varchar(35) NOT NULL,
  `password_alfin` varchar(225) NOT NULL,
  `role_alfin` enum('admin','kasir') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna_alfin`
--

INSERT INTO `pengguna_alfin` (`id_pengguna_alfin`, `nama_pengguna_alfin`, `username_alfin`, `password_alfin`, `role_alfin`) VALUES
(5, 'noor', 'noor123', '$2y$10$rB0UG4f77ZEEKPrJQ/1s5eqjmniA9aAKOpHdKJo4oTIHkltypHFMC', 'kasir'),
(7, 'alfin bathosan', 'alpin123', '$2y$10$PKSSWeO4PRHU6jvrDRz93uKEu/kzMfKcAkcFekd7MNg.ysBCRlDbi', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `produk_alfin`
--

CREATE TABLE `produk_alfin` (
  `id_produk_alfin` int(11) NOT NULL,
  `nama_produk_alfin` varchar(35) NOT NULL,
  `harga_alfin` int(35) NOT NULL,
  `stok_alfin` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `barcode_alfin` varchar(35) NOT NULL,
  `kategori_alfin` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk_alfin`
--

INSERT INTO `produk_alfin` (`id_produk_alfin`, `nama_produk_alfin`, `harga_alfin`, `stok_alfin`, `barcode_alfin`, `kategori_alfin`) VALUES
(6, 'Bolu Kukus Keju', 4000, 16, 'BC3095148726', 'Makanan Manis'),
(7, 'Bolu Ketan', 3500, 18, 'BC9683105274', 'Makanan Manis'),
(8, 'Kue Basah', 2000, 20, 'BC9408572613', 'Makanan Manis'),
(19, 'Bolu Kukus Coklat', 4500, 14, 'BC4310675829', 'Makanan Manis'),
(20, 'Donat Strawbery', 4000, 7, 'BC5689134702', 'Makanan Manis'),
(21, 'Donat Kacang', 4000, 10, 'BC1625743089', 'Makanan Asin'),
(22, 'Donat Coklat', 4000, 10, 'BC9702456138', 'Makanan Manis'),
(23, 'Donat Keju', 4000, 10, 'BC8012579436', 'Makanan Manis'),
(24, 'Bolu Pisang', 4500, 10, 'BC2860517934', 'Makanan Manis'),
(25, 'Croissant', 6000, 10, 'BC6728501439', 'Makanan Asin'),
(26, 'Kue Sus', 3500, 10, 'BC2798405361', 'Makanan Manis');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_alfin`
--

CREATE TABLE `transaksi_alfin` (
  `id_transaksi_alfin` int(11) NOT NULL,
  `id_pengguna_alfin` int(11) NOT NULL,
  `tanggal_alfin` datetime DEFAULT NULL,
  `diskon_persen_alfin` int(3) NOT NULL DEFAULT 0,
  `diskon_alfin` int(11) NOT NULL DEFAULT 0,
  `total_alfin` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi_alfin`
--

INSERT INTO `transaksi_alfin` (`id_transaksi_alfin`, `id_pengguna_alfin`, `tanggal_alfin`, `diskon_persen_alfin`, `diskon_alfin`, `total_alfin`) VALUES
(1, 5, '2026-05-13 16:27:36', 6, 840, 13160),
(2, 5, '2026-05-13 16:40:07', 10, 800, 7200),
(3, 5, '2026-05-13 16:41:17', 0, 0, 12000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_keluar_alfin`
--
ALTER TABLE `barang_keluar_alfin`
  ADD PRIMARY KEY (`id_keluar_alfin`),
  ADD KEY `fk_bk_produk` (`id_produk_alfin`);

--
-- Indexes for table `barang_masuk_alfin`
--
ALTER TABLE `barang_masuk_alfin`
  ADD PRIMARY KEY (`id_masuk_alfin`),
  ADD KEY `fk_bm_produk` (`id_produk_alfin`);

--
-- Indexes for table `detail_transaksi_alfin`
--
ALTER TABLE `detail_transaksi_alfin`
  ADD PRIMARY KEY (`id_detail_alfin`),
  ADD KEY `fk2` (`id_transaksi_alfin`),
  ADD KEY `fk3` (`id_produk_alfin`);

--
-- Indexes for table `pengguna_alfin`
--
ALTER TABLE `pengguna_alfin`
  ADD PRIMARY KEY (`id_pengguna_alfin`);

--
-- Indexes for table `produk_alfin`
--
ALTER TABLE `produk_alfin`
  ADD PRIMARY KEY (`id_produk_alfin`);

--
-- Indexes for table `transaksi_alfin`
--
ALTER TABLE `transaksi_alfin`
  ADD PRIMARY KEY (`id_transaksi_alfin`),
  ADD KEY `fk1` (`id_pengguna_alfin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang_keluar_alfin`
--
ALTER TABLE `barang_keluar_alfin`
  MODIFY `id_keluar_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barang_masuk_alfin`
--
ALTER TABLE `barang_masuk_alfin`
  MODIFY `id_masuk_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `detail_transaksi_alfin`
--
ALTER TABLE `detail_transaksi_alfin`
  MODIFY `id_detail_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengguna_alfin`
--
ALTER TABLE `pengguna_alfin`
  MODIFY `id_pengguna_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `produk_alfin`
--
ALTER TABLE `produk_alfin`
  MODIFY `id_produk_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `transaksi_alfin`
--
ALTER TABLE `transaksi_alfin`
  MODIFY `id_transaksi_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang_keluar_alfin`
--
ALTER TABLE `barang_keluar_alfin`
  ADD CONSTRAINT `fk_bk_produk` FOREIGN KEY (`id_produk_alfin`) REFERENCES `produk_alfin` (`id_produk_alfin`) ON DELETE CASCADE;

--
-- Constraints for table `barang_masuk_alfin`
--
ALTER TABLE `barang_masuk_alfin`
  ADD CONSTRAINT `fk_bm_produk` FOREIGN KEY (`id_produk_alfin`) REFERENCES `produk_alfin` (`id_produk_alfin`) ON DELETE CASCADE;

--
-- Constraints for table `detail_transaksi_alfin`
--
ALTER TABLE `detail_transaksi_alfin`
  ADD CONSTRAINT `fk2` FOREIGN KEY (`id_transaksi_alfin`) REFERENCES `transaksi_alfin` (`id_transaksi_alfin`),
  ADD CONSTRAINT `fk3` FOREIGN KEY (`id_produk_alfin`) REFERENCES `produk_alfin` (`id_produk_alfin`);

--
-- Constraints for table `transaksi_alfin`
--
ALTER TABLE `transaksi_alfin`
  ADD CONSTRAINT `fk1` FOREIGN KEY (`id_pengguna_alfin`) REFERENCES `pengguna_alfin` (`id_pengguna_alfin`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
