-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2026 at 06:32 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `pengguna_alfin`
--

CREATE TABLE `pengguna_alfin` (
  `id_pengguna_alfin` int(11) NOT NULL,
  `nama_pengguna_alfin` varchar(35) NOT NULL,
  `username_alfin` varchar(35) NOT NULL,
  `password_alfin` varchar(8) NOT NULL,
  `role_alfin` enum('admin','kasir') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produk_alfin`
--

CREATE TABLE `produk_alfin` (
  `id_produk_alfin` int(11) NOT NULL,
  `nama_produk_alfin` varchar(35) NOT NULL,
  `harga_beli_alfin` int(35) NOT NULL,
  `harga_jual_alfin` int(35) NOT NULL,
  `stok_alfin` int(10) NOT NULL,
  `barcode_alfin` varchar(35) NOT NULL,
  `kategori_alfin` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  MODIFY `id_detail_alfin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengguna_alfin`
--
ALTER TABLE `pengguna_alfin`
  MODIFY `id_pengguna_alfin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `produk_alfin`
--
ALTER TABLE `produk_alfin`
  MODIFY `id_produk_alfin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi_alfin`
--
ALTER TABLE `transaksi_alfin`
  MODIFY `id_transaksi_alfin` int(11) NOT NULL AUTO_INCREMENT;

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
