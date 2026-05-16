<?php
include 'koneksi_alfin.php';
session_start();

// Proteksi: jika belum login, arahkan ke form login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

// Query untuk statistik
$totalProduk = mysqli_query($koneksiAlfin, "SELECT COUNT(*) as total FROM produk_alfin");
$dataProduk = mysqli_fetch_assoc($totalProduk);
$countProduk = $dataProduk['total'];

$totalTransaksi = mysqli_query($koneksiAlfin, "SELECT COUNT(*) as total FROM transaksi_alfin");
$dataTransaksi = mysqli_fetch_assoc($totalTransaksi);
$countTransaksi = $dataTransaksi['total'];

$totalRevenue = mysqli_query($koneksiAlfin, "SELECT SUM(total_alfin) as total FROM transaksi_alfin");
$dataRevenue = mysqli_fetch_assoc($totalRevenue);
$revenue = $dataRevenue['total'] ?? 0;

$totalStok = mysqli_query($koneksiAlfin, "SELECT SUM(stok_alfin) as total FROM produk_alfin");
$dataStok = mysqli_fetch_assoc($totalStok);
$stokTotal = $dataStok['total'] ?? 0;

// Produk terlaris berdasarkan jumlah terjual
$produkTerlarisQuery = mysqli_query($koneksiAlfin, "SELECT p.nama_produk_alfin, SUM(d.jumlah_alfin) AS total_terjual FROM detail_transaksi_alfin d JOIN produk_alfin p ON d.id_produk_alfin = p.id_produk_alfin GROUP BY d.id_produk_alfin ORDER BY total_terjual DESC LIMIT 1");
$produkTerlaris = mysqli_fetch_assoc($produkTerlarisQuery);
$produkTerlarisNama = $produkTerlaris['nama_produk_alfin'] ?? '-';
$produkTerlarisJumlah = $produkTerlaris['total_terjual'] ?? 0;

$countPengguna = 0;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $totalPengguna = mysqli_query($koneksiAlfin, "SELECT COUNT(*) as total FROM pengguna_alfin");
    $dataPengguna = mysqli_fetch_assoc($totalPengguna);
    $countPengguna = $dataPengguna['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
    <style>
        .navbar {
            background-color: #6366f1;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar .brand {
            color: white;
            font-size: 20px;
            font-weight: bold;
            padding: 15px 30px;
            margin: 0;
        }
        .navbar-menu {
            display: flex;
            gap: 0;
            align-items: center;
            flex: 1;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .navbar-menu li a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: background-color 0.3s;
        }
        .navbar-menu li a:hover {
            background-color: #4f46e5;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-right: 30px;
        }
        .navbar-right .user-info {
            color: white;
            font-size: 14px;
        }
        .navbar-right .btn-danger {
            padding: 8px 16px;
            font-size: 12px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .stat-card:nth-child(1) { border-left-color: #3498db; }
        .stat-card:nth-child(2) { border-left-color: #e74c3c; }
        .stat-card:nth-child(3) { border-left-color: #2ecc71; }
        .stat-card:nth-child(4) { border-left-color: #f39c12; }
        .dashboard-content {
            padding: 20px 0;
        }
        .dashboard-content h2 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="brand">Minimarket Barcode</div>
        <ul class="navbar-menu">
            <li><a href="dashboard_alfin.php">🏠 Dashboard</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="pengguna_alfin.php">👥 Kelola Pengguna</a></li>
                <li><a href="produk_alfin.php">📦 Kelola Produk</a></li>
                <li><a href="barang_masuk_alfin.php">⬆️ Barang Masuk</a></li>
                <li><a href="barang_keluar_alfin.php">⬇️ Barang Keluar</a></li>
                <li><a href="laporan_penjualan_alfin.php">📊 Laporan Penjualan</a></li>
            <?php else: ?>
                <li><a href="produk_alfin.php">📦 Produk</a></li>
                <li><a href="transaksi_penjualan_alfin.php">💳 Transaksi</a></li>
            <?php endif; ?>
        </ul>
        <div class="navbar-right">
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <a href="logout_alfin.php" class="btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-content">
            <h2>Dashboard Statistik</h2>
            <p>Ringkasan data aplikasi Anda</p>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Produk</h3>
                    <div class="number"><?php echo $countProduk; ?></div>
                    <p style="margin: 0; color: #95a5a6;">Produk aktif</p>
                </div>

                <div class="stat-card">
                    <h3>Total Transaksi</h3>
                    <div class="number"><?php echo $countTransaksi; ?></div>
                    <p style="margin: 0; color: #95a5a6;">Transaksi berhasil</p>
                </div>

                <div class="stat-card">
                    <h3>Total Stok</h3>
                    <div class="number"><?php echo $stokTotal; ?></div>
                    <p style="margin: 0; color: #95a5a6;">Unit tersedia</p>
                </div>

                <div class="stat-card">
                    <h3>Produk Terlaris</h3>
                    <div class="number"><?php echo htmlspecialchars($produkTerlarisNama, ENT_QUOTES, 'UTF-8'); ?></div>
                    <p style="margin: 0; color: #95a5a6;">
                        Terjual <?php echo $produkTerlarisJumlah; ?> unit
                    </p>
                </div>

                <div class="stat-card">
                    <h3>Total Penjualan</h3>
                    <div class="number">Rp <?php echo number_format($revenue, 0, ',', '.'); ?></div>
                    <p style="margin: 0; color: #95a5a6;">Revenue total</p>
                </div>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="stat-card">
                    <h3>Total Pengguna</h3>
                    <div class="number"><?php echo $countPengguna; ?></div>
                    <p style="margin: 0; color: #95a5a6;">User terdaftar</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>