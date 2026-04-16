<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

// Query untuk total barang terjual
$queryBarangTerjual = "SELECT SUM(jumlah_alfin) as total_barang FROM detail_transaksi_alfin";
$resultBarang = mysqli_query($koneksiAlfin, $queryBarangTerjual);
$totalBarangTerjual = mysqli_fetch_assoc($resultBarang)['total_barang'] ?? 0;

// Query untuk total transaksi
$queryTotalTransaksi = "SELECT COUNT(*) as total_transaksi FROM transaksi_alfin";
$resultTransaksi = mysqli_query($koneksiAlfin, $queryTotalTransaksi);
$totalTransaksi = mysqli_fetch_assoc($resultTransaksi)['total_transaksi'] ?? 0;

// Query untuk total pendapatan
$queryTotalPendapatan = "SELECT SUM(total_alfin) as total_pendapatan FROM transaksi_alfin";
$resultPendapatan = mysqli_query($koneksiAlfin, $queryTotalPendapatan);
$totalPendapatan = mysqli_fetch_assoc($resultPendapatan)['total_pendapatan'] ?? 0;

// Query untuk detail penjualan per produk
$queryDetailPenjualan = "
    SELECT
        p.nama_produk_alfin,
        p.kategori_alfin,
        SUM(dt.jumlah_alfin) as jumlah_terjual,
        SUM(dt.subtotal_alfin) as total_pendapatan_produk,
        COUNT(DISTINCT dt.id_transaksi_alfin) as jumlah_transaksi
    FROM detail_transaksi_alfin dt
    JOIN produk_alfin p ON dt.id_produk_alfin = p.id_produk_alfin
    GROUP BY p.id_produk_alfin, p.nama_produk_alfin, p.kategori_alfin
    ORDER BY jumlah_terjual DESC
";
$resultDetail = mysqli_query($koneksiAlfin, $queryDetailPenjualan);

// Query untuk penjualan harian (7 hari terakhir)
$queryPenjualanHarian = "
    SELECT
        DATE(t.tanggal_alfin) as tanggal,
        COUNT(t.id_transaksi_alfin) as jumlah_transaksi,
        SUM(t.total_alfin) as total_pendapatan,
        SUM(dt.jumlah_alfin) as total_barang
    FROM transaksi_alfin t
    LEFT JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin
    WHERE t.tanggal_alfin >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(t.tanggal_alfin)
    ORDER BY tanggal DESC
";
$resultHarian = mysqli_query($koneksiAlfin, $queryPenjualanHarian);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
    <div class="container">
        <div style="margin-top: 20px;">
            <h2>Laporan Penjualan</h2>

            <!-- Ringkasan Statistik -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <h3 style="margin: 0 0 10px 0; color: var(--primary-color);">Total Barang Terjual</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0; color: var(--text-primary);"><?php echo number_format($totalBarangTerjual, 0, ',', '.'); ?> item</p>
                </div>

                <div class="card" style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <h3 style="margin: 0 0 10px 0; color: var(--primary-color);">Total Transaksi</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0; color: var(--text-primary);"><?php echo number_format($totalTransaksi, 0, ',', '.'); ?> transaksi</p>
                </div>

                <div class="card" style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <h3 style="margin: 0 0 10px 0; color: var(--primary-color);">Total Pendapatan</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0; color: var(--text-primary);">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></p>
                </div>
            </div>

            <!-- Penjualan Harian -->
            <div style="margin-bottom: 30px;">
                <h3>Penjualan 7 Hari Terakhir</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Barang</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($resultHarian)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td><?php echo number_format($row['jumlah_transaksi'], 0, ',', '.'); ?></td>
                                <td><?php echo number_format($row['total_barang'], 0, ',', '.'); ?> item</td>
                                <td>Rp <?php echo number_format($row['total_pendapatan'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($resultHarian) == 0): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-secondary);">Belum ada data penjualan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Detail Penjualan per Produk -->
            <div>
                <h3>Detail Penjualan per Produk</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Jumlah Terjual</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($resultDetail)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['kategori_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo number_format($row['jumlah_terjual'], 0, ',', '.'); ?> item</td>
                                <td><?php echo number_format($row['jumlah_transaksi'], 0, ',', '.'); ?> transaksi</td>
                                <td>Rp <?php echo number_format($row['total_pendapatan_produk'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($resultDetail) == 0): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-secondary);">Belum ada data penjualan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="dashboard_alfin.php" class="btn-secondary">← Kembali ke Dashboard</a>
                <button onclick="window.print()" class="btn-primary">🖨️ Cetak Laporan</button>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .btn-primary, .btn-secondary {
                display: none !important;
            }
            body {
                font-size: 12px;
            }
            table {
                font-size: 11px;
            }
        }
    </style>
</body>

</html>