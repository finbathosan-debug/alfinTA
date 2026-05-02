-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2026 at 05:46 AM
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
(1, 1, 6, 3, 12000),
(2, 2, 6, 2, 8000),
(3, 2, 7, 2, 7000),
(4, 3, 7, 1, 3500),
(5, 4, 6, 1, 4000),
(6, 5, 6, 1, 4000),
(7, 6, 6, 1, 4000),
(8, 7, 6, 1, 4000),
(9, 8, 6, 1, 4000),
(10, 9, 6, 1, 4000),
(11, 10, 6, 1, 4000),
(12, 11, 6, 1, 4000),
(13, 12, 6, 1, 4000),
(14, 13, 6, 1, 4000),
(15, 14, 6, 1, 4000),
(16, 15, 6, 1, 4000);

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
  `stok_alfin` int(10) NOT NULL,
  `barcode_alfin` varchar(35) NOT NULL,
  `kategori_alfin` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk_alfin`
--

INSERT INTO `produk_alfin` (`id_produk_alfin`, `nama_produk_alfin`, `harga_alfin`, `stok_alfin`, `barcode_alfin`, `kategori_alfin`) VALUES
(6, 'Bolu Kukus Keju', 4000, 13, 'BC3095148726', 'Makanan'),
(7, 'Bolu Ketan', 3500, 20, 'BC9683105274', 'Makanan'),
(8, 'Kue Basah', 2000, 22, 'BC9408572613', '-');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_alfin`
--

CREATE TABLE `transaksi_alfin` (
  `id_transaksi_alfin` int(11) NOT NULL,
  `id_pengguna_alfin` int(11) NOT NULL,
  `tanggal_alfin` date NOT NULL,
  `total_alfin` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi_alfin`
--

INSERT INTO `transaksi_alfin` (`id_transaksi_alfin`, `id_pengguna_alfin`, `tanggal_alfin`, `total_alfin`) VALUES
(1, 5, '2026-04-30', 12000),
(2, 5, '2026-04-30', 15000),
(3, 5, '2026-04-30', 3500),
(4, 5, '2026-04-30', 4000),
(5, 5, '2026-04-30', 4000),
(6, 5, '2026-04-30', 4000),
(7, 5, '2026-04-30', 4000),
(8, 5, '2026-04-30', 4000),
(9, 5, '2026-04-30', 4000),
(10, 5, '2026-04-30', 4000),
(11, 5, '2026-04-30', 4000),
(12, 5, '2026-04-30', 4000),
(13, 5, '2026-04-30', 4000),
(14, 5, '2026-04-30', 4000),
(15, 5, '2026-05-02', 4000);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `detail_transaksi_alfin`
--
ALTER TABLE `detail_transaksi_alfin`
  MODIFY `id_detail_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pengguna_alfin`
--
ALTER TABLE `pengguna_alfin`
  MODIFY `id_pengguna_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `produk_alfin`
--
ALTER TABLE `produk_alfin`
  MODIFY `id_produk_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaksi_alfin`
--
ALTER TABLE `transaksi_alfin`
  MODIFY `id_transaksi_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

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
