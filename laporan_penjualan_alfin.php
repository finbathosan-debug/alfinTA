<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$defaultEndDate = date('Y-m-d');
$defaultStartDate = date('Y-m-d', strtotime('-6 days'));

$startDate = isset($_GET['start_date']) && isValidDate($_GET['start_date']) ? $_GET['start_date'] : $defaultStartDate;
$endDate = isset($_GET['end_date']) && isValidDate($_GET['end_date']) ? $_GET['end_date'] : $defaultEndDate;

if ($startDate > $endDate) {
    $temp = $startDate;
    $startDate = $endDate;
    $endDate = $temp;
}

$dateFilter = "WHERE DATE(t.tanggal_alfin) BETWEEN '" . $startDate . "' AND '" . $endDate . "'";

// Query untuk total barang terjual
$queryBarangTerjual = "SELECT SUM(dt.jumlah_alfin) as total_barang FROM transaksi_alfin t JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin " . $dateFilter;
$resultBarang = mysqli_query($koneksiAlfin, $queryBarangTerjual);
$totalBarangTerjual = mysqli_fetch_assoc($resultBarang)['total_barang'] ?? 0;

// Query untuk total transaksi
$queryTotalTransaksi = "SELECT COUNT(*) as total_transaksi FROM transaksi_alfin t " . $dateFilter;
$resultTransaksi = mysqli_query($koneksiAlfin, $queryTotalTransaksi);
$totalTransaksi = mysqli_fetch_assoc($resultTransaksi)['total_transaksi'] ?? 0;

// Query untuk total pendapatan
$queryTotalPendapatan = "SELECT SUM(t.total_alfin) as total_pendapatan FROM transaksi_alfin t " . $dateFilter;
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
    JOIN transaksi_alfin t ON dt.id_transaksi_alfin = t.id_transaksi_alfin
    " . $dateFilter . "
    GROUP BY p.id_produk_alfin, p.nama_produk_alfin, p.kategori_alfin
    ORDER BY jumlah_terjual DESC
";
$resultDetail = mysqli_query($koneksiAlfin, $queryDetailPenjualan);

// Query untuk penjualan harian
$queryPenjualanHarian = "
    SELECT
        DATE(t.tanggal_alfin) as tanggal,
        COUNT(t.id_transaksi_alfin) as jumlah_transaksi,
        SUM(t.total_alfin) as total_pendapatan,
        SUM(dt.jumlah_alfin) as total_barang
    FROM transaksi_alfin t
    LEFT JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin
    " . $dateFilter . "
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
            <div class="report-header" style="text-align: center; margin-bottom: 20px;">
                <h1 style="margin-bottom: 8px;">Toko Alfin</h1>
                <p style="margin-bottom: 4px; font-size: 1rem; color: #475569;">alfinTA - Aplikasi Kasir & Laporan Penjualan</p>
                <p style="margin-bottom: 0; font-size: 0.95rem; color: #6b7280;">Alamat Toko atau Tagline</p>
                <hr style="margin: 16px auto; width: 60%; border-color: #cbd5e1;" />
                <h2 style="margin-top: 0;">Laporan Penjualan</h2>
            </div>

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

            <!-- Tabel Ringkasan Laporan -->
            <div style="margin-bottom: 30px;">
                <h3>Ringkasan Laporan</h3>
                <table style="margin-bottom: 0;">
                    <tbody>
                        <tr>
                            <td style="font-weight: 700; padding: 12px; width: 40%;">Total Barang Terjual</td>
                            <td style="padding: 12px;"><?php echo number_format($totalBarangTerjual, 0, ',', '.'); ?> item</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700; padding: 12px;">Total Transaksi</td>
                            <td style="padding: 12px;"><?php echo number_format($totalTransaksi, 0, ',', '.'); ?> transaksi</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700; padding: 12px;">Total Pendapatan</td>
                            <td style="padding: 12px;">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
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
                <form action="dashboard_alfin.php" method="GET">
                    <button type="submit" class="btn-secondary">← Kembali ke Dashboard</button>
                </form>
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
                background: white;
            }
            table {
                font-size: 11px;
            }
            .report-header h1,
            .report-header h2,
            .report-header p {
                color: #000 !important;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #d1d5db !important;
            }
            table {
                page-break-inside: avoid;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
    <div class="filter-panel" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; justify-content: space-between;">
                <form method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label for="start_date" style="font-weight: 600;">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required style="padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;" />
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label for="end_date" style="font-weight: 600;">Tanggal Selesai</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required style="padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;" />
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top: 1.25rem;">Filter</button>
                </form>
                <div style="font-size: 0.95rem; color: #475569;">Periode: <?php echo date('d/m/Y', strtotime($startDate)); ?> - <?php echo date('d/m/Y', strtotime($endDate)); ?></div>
            </div>
</body>

</html>