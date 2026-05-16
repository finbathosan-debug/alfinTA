<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

// 1. Definisikan fungsi validasi
function isValidDate($date) {
    if (empty($date)) return false;
    return (DateTime::createFromFormat('Y-m-d', $date) !== false) ||
           (DateTime::createFromFormat('Y-m-d H:i:s', $date) !== false);
}

// 2. Tentukan rentang waktu DEFAULT (hari ini s/d 6 hari lalu)
$defaultEndDate = date('Y-m-d');
$defaultStartDate = date('Y-m-d', strtotime('-6 days'));

// 3. TANGKAP variabel dari URL (GET) atau gunakan DEFAULT
$startDate = isset($_GET['start_date']) && isValidDate($_GET['start_date']) ? $_GET['start_date'] : $defaultStartDate;
$endDate = isset($_GET['end_date']) && isValidDate($_GET['end_date']) ? $_GET['end_date'] : $defaultEndDate;

if ($startDate > $endDate) {
    $temp = $startDate;
    $startDate = $endDate;
    $endDate = $temp;
}

// 4. Gunakan variabel tersebut untuk Filter Query
$dateFilter = "WHERE DATE(t.tanggal_alfin) BETWEEN '$startDate' AND '$endDate'";
$queryString = "start_date=" . urlencode($startDate) . "&end_date=" . urlencode($endDate);

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

// Query untuk total diskon
$queryTotalDiskon = "SELECT SUM(t.diskon_alfin) as total_diskon FROM transaksi_alfin t " . $dateFilter;
$resultDiskon = mysqli_query($koneksiAlfin, $queryTotalDiskon);
$totalDiskon = mysqli_fetch_assoc($resultDiskon)['total_diskon'] ?? 0;

// Query untuk detail penjualan per produk (Ditambah kolom harga_alfin)
$queryDetailPenjualan = "
    SELECT
        p.nama_produk_alfin,
        p.kategori_alfin,
        p.harga_alfin,
        SUM(dt.jumlah_alfin) as jumlah_terjual,
        SUM(dt.subtotal_alfin) as total_pendapatan_produk,
        COUNT(DISTINCT dt.id_transaksi_alfin) as jumlah_transaksi
    FROM detail_transaksi_alfin dt
    JOIN produk_alfin p ON dt.id_produk_alfin = p.id_produk_alfin
    JOIN transaksi_alfin t ON dt.id_transaksi_alfin = t.id_transaksi_alfin
    " . $dateFilter . "
    GROUP BY p.id_produk_alfin, p.nama_produk_alfin, p.kategori_alfin, p.harga_alfin
    ORDER BY jumlah_terjual DESC
";
$resultDetail = mysqli_query($koneksiAlfin, $queryDetailPenjualan);

// Query untuk penjualan harian
$queryPenjualanHarian = "
    SELECT
        tanggal,
        COUNT(id_transaksi) as jumlah_transaksi,
        SUM(total_pendapatan) as total_pendapatan,
        SUM(total_diskon) as total_diskon,
        SUM(total_barang) as total_barang
    FROM (
        SELECT 
            DATE(t.tanggal_alfin) as tanggal,
            t.id_transaksi_alfin as id_transaksi,
            t.total_alfin as total_pendapatan,
            t.diskon_alfin as total_diskon,
            SUM(dt.jumlah_alfin) as total_barang
        FROM transaksi_alfin t
        LEFT JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin
        " . $dateFilter . "
        GROUP BY t.id_transaksi_alfin, t.tanggal_alfin, t.total_alfin, t.diskon_alfin
    ) summary_harian
    GROUP BY tanggal
    ORDER BY tanggal DESC
";
$resultHarian = mysqli_query($koneksiAlfin, $queryPenjualanHarian);

// ==========================================
// PAGINATION LOGIC
// ==========================================
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$totalPages = ceil($totalTransaksi / $limit);
if ($totalPages == 0) $totalPages = 1;

// Query Semua Transaksi (Ditambah GROUP_CONCAT untuk harga jual produk)
$querySemuaTransaksi = "
    SELECT 
        t.id_transaksi_alfin, 
        t.tanggal_alfin, 
        t.total_alfin, 
        t.diskon_alfin,
        SUM(dt.jumlah_alfin) as total_barang,
        GROUP_CONCAT(p.nama_produk_alfin SEPARATOR ', ') as daftar_produk,
        GROUP_CONCAT(p.harga_alfin SEPARATOR ',') as daftar_harga
    FROM transaksi_alfin t 
    LEFT JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin 
    LEFT JOIN produk_alfin p ON dt.id_produk_alfin = p.id_produk_alfin
    " . $dateFilter . "
    GROUP BY t.id_transaksi_alfin, t.tanggal_alfin, t.total_alfin, t.diskon_alfin
    ORDER BY t.tanggal_alfin DESC
    LIMIT $limit OFFSET $offset
";
$resultSemuaTransaksi = mysqli_query($koneksiAlfin, $querySemuaTransaksi);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
    <style>
        /* CSS Lengkap */
        .lp-page { background: #f1f5f9; min-height: 100vh; padding: 32px 24px 48px; font-family: 'Segoe UI', sans-serif; }
        .lp-container { max-width: 1100px; margin: 0 auto; }
        .lp-page-header { display: flex; align-items: center; gap: 16px; margin-bottom: 28px; }
        .lp-page-header .header-icon { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #2563eb, #3b82f6); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
        .lp-page-header .header-text h1 { margin: 0 0 2px; font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .lp-page-header .header-text p { margin: 0; font-size: 0.875rem; color: #64748b; }
        .lp-filter-panel { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 24px; margin-bottom: 28px; display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; justify-content: space-between; }
        .lp-filter-panel form { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; }
        .lp-filter-panel .field-group { display: flex; flex-direction: column; gap: 6px; }
        .lp-filter-panel label { font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .lp-filter-panel input[type="date"] { padding: 0.6rem 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; background: #f8fafc; outline: none; }
        .lp-filter-panel .btn-filter { padding: 0.62rem 1.4rem; background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .lp-filter-panel .periode-badge { display: flex; align-items: center; gap: 8px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 14px; font-size: 0.85rem; color: #475569; }
        .lp-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .lp-stat-card { background: #ffffff; border-radius: 16px; padding: 22px 24px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 18px; }
        .lp-stat-card .stat-icon { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .card-blue .stat-icon { background: #dbeafe; } .card-green .stat-icon { background: #dcfce7; } .card-amber .stat-icon { background: #fef3c7; }
        .lp-stat-card .stat-label { font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .lp-stat-card .stat-value { font-size: 1.7rem; font-weight: 800; color: #0f172a; }
        .card-blue .stat-value { color: #1d4ed8; } .card-green .stat-value { color: #153580; } .card-amber .stat-value { color: #b45309; }
        .lp-section { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; margin-bottom: 24px; overflow: hidden; }
        .lp-section-header { display: flex; align-items: center; gap: 10px; padding: 18px 24px; border-bottom: 1px solid #f1f5f9; }
        .lp-section-header .section-dot { width: 10px; height: 10px; border-radius: 50%; background: #2563eb; }
        .lp-section-header h3 { margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a; }
        .lp-summary-table { width: 100%; border-collapse: collapse; }
        .lp-summary-table td { padding: 14px 24px; font-size: 0.92rem; border-bottom: 1px solid #f1f5f9; }
        .lp-data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .lp-data-table thead th { background: #f8fafc; padding: 12px 18px; text-align: left; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .lp-data-table tbody td { padding: 13px 18px; color: #334155; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .lp-data-table tbody tr:hover { background: #f8fafc; }
        .produk-list { max-width: 250px; line-height: 1.4; color: #475569; }
        .td-badge { display: inline-flex; padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        .td-badge-blue { background: #dbeafe; color: #1d4ed8; } .td-badge-green { background: #dcfce7; color: #15803d; } .td-badge-gray { background: #f1f5f9; color: #475569; } .td-badge-purple { background: #ede9fe; color: #6d28d9; } .td-badge-red { background: #fee2e2; color: #dc2626; }
        .lp-pagination { display: flex; justify-content: center; gap: 6px; padding: 20px; border-top: 1px solid #f1f5f9; }
        .lp-page-link { padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 8px; color: #475569; text-decoration: none; font-weight: 600; font-size: 0.875rem; background: #ffffff; }
        .lp-page-link.active { background: #2563eb; color: white; border-color: #2563eb; }
        .lp-page-link.disabled { opacity: 0.5; pointer-events: none; }
        .lp-actions { display: flex; gap: 12px; margin-top: 8px; }
        .lp-btn-back, .lp-btn-print { padding: 0.65rem 1.3rem; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; }
        .lp-btn-back { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
        .lp-btn-print { background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; }
    </style>
</head>

<body>
    <div class="lp-page">
        <div class="lp-container">

            <div class="lp-page-header">
                <div class="header-icon">📊</div>
                <div class="header-text">
                    <h1>Laporan Penjualan</h1>
                    <p>Rekap data transaksi dan pendapatan berdasarkan periode</p>
                </div>
            </div>

            <div class="lp-filter-panel">
                <form method="GET">
                    <div class="field-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required />
                    </div>
                    <div class="field-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required />
                    </div>
                    <div style="display:flex;flex-direction:column;justify-content:flex-end;">
                        <button type="submit" class="btn-filter">🔍 Filter</button>
                    </div>
                </form>
                <div class="periode-badge">📅 <strong><?php echo date('d/m/Y', strtotime($startDate)); ?></strong>
                    &mdash; <strong><?php echo date('d/m/Y', strtotime($endDate)); ?></strong></div>
            </div>

            <div class="lp-stats-grid">
                <div class="lp-stat-card card-blue">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Barang Terjual</div>
                        <div class="stat-value"><?php echo number_format($totalBarangTerjual, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="lp-stat-card card-green">
                    <div class="stat-icon">🧾</div>
                    <div class="stat-info">
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value"><?php echo number_format($totalTransaksi, 0, ',', '.'); ?></div>
                    </div>
                </div>
                <div class="lp-stat-card card-amber">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <div class="stat-label">Pendapatan Bersih</div>
                        <div class="stat-value" style="font-size:1.35rem;">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <div class="lp-section">
                <div class="lp-section-header">
                    <div class="section-dot" style="background:#8b5cf6;"></div>
                    <h3>Daftar Seluruh Transaksi (Detail)</h3>
                </div>
                <div class="lp-table-wrap">
                    <table class="lp-data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Struk</th>
                                <th>Waktu Transaksi</th>
                                <th>Nama Produk</th>
                                <th>Harga Jual (Rp)</th> <th>Total Item</th>
                                <th>Harga Awal</th>
                                <th>Diskon</th>
                                <th>Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset + 1;
                            $hasDetailTransaksi = false;
                            if (mysqli_num_rows($resultSemuaTransaksi) > 0) {
                                while ($row = mysqli_fetch_assoc($resultSemuaTransaksi)):
                                    $hasDetailTransaksi = true;
                                    $harga_awal = $row['total_alfin'] + $row['diskon_alfin'];
                                    $daftar_produk = !empty($row['daftar_produk']) ? htmlspecialchars($row['daftar_produk'], ENT_QUOTES, 'UTF-8') : '-';
                                    
                                    // Memformat list Harga Jual agar ada pemisah titik (Ribu)
                                    $daftar_harga_html = '-';
                                    if (!empty($row['daftar_harga'])) {
                                        $harga_arr = explode(',', $row['daftar_harga']);
                                        $harga_formatted = array_map(function($h) {
                                            return number_format((int)$h, 0, ',', '.');
                                        }, $harga_arr);
                                        $daftar_harga_html = implode(', ', $harga_formatted);
                                    }

                                    $waktu_tr = strtotime($row['tanggal_alfin']);
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td style="font-weight:600;">#<?php echo $row['id_transaksi_alfin']; ?></td>

                                        <td style="white-space:nowrap;">
                                            <?php
                                            if (date('H:i', $waktu_tr) != '00:00') {
                                                echo date('d/m/Y H:i', $waktu_tr);
                                            } else {
                                                echo date('d/m/Y', $waktu_tr) . '<br><span style="color:#ef4444; font-size:11px;">(Waktu di DB kosong)</span>';
                                            }
                                            ?>
                                        </td>

                                        <td>
                                            <div class="produk-list"><?php echo $daftar_produk; ?></div>
                                        </td>
                                        <td>
                                            <div class="produk-list" style="color:#0ea5e9; font-weight:600;"><?php echo $daftar_harga_html; ?></div>
                                        </td>
                                        <td><span class="td-badge td-badge-purple"><?php echo number_format($row['total_barang'], 0, ',', '.'); ?> item</span></td>
                                        <td style="color:#64748b;">Rp <?php echo number_format($harga_awal, 0, ',', '.'); ?></td>
                                        <td>
                                            <?php if ($row['diskon_alfin'] > 0): ?>
                                                <span class="td-badge td-badge-red">-Rp <?php echo number_format($row['diskon_alfin'], 0, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span style="color:#94a3b8;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight:700;color:#15803d;">Rp <?php echo number_format($row['total_alfin'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php
                                endwhile;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalTransaksi > 0): ?>
                    <div class="lp-pagination">
                        <?php if ($page > 1): ?><a href="?<?php echo $queryString; ?>&page=<?php echo $page - 1; ?>" class="lp-page-link">&laquo; Prev</a><?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?><a href="?<?php echo $queryString; ?>&page=<?php echo $i; ?>" class="lp-page-link <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a><?php endfor; ?>
                        <?php if ($page < $totalPages): ?><a href="?<?php echo $queryString; ?>&page=<?php echo $page + 1; ?>" class="lp-page-link">Next &raquo;</a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lp-section">
                <div class="lp-section-header">
                    <div class="section-dot" style="background:#d97706;"></div>
                    <h3>Detail Penjualan per Produk</h3>
                </div>
                <div class="lp-table-wrap">
                    <table class="lp-data-table">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga Jual</th> <th>Jumlah Terjual</th>
                                <th>Jumlah Transaksi</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hasDetail = false;
                            if (mysqli_num_rows($resultDetail) > 0) {
                                while ($row = mysqli_fetch_assoc($resultDetail)):
                                    $hasDetail = true;
                                    ?>
                                    <tr>
                                        <td style="font-weight:600;color:#0f172a;"><?php echo htmlspecialchars($row['nama_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><span class="td-badge td-badge-gray"><?php echo htmlspecialchars($row['kategori_alfin'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        
                                        <td style="color:#0ea5e9; font-weight:600;">Rp <?php echo number_format($row['harga_alfin'], 0, ',', '.'); ?></td>
                                        
                                        <td><?php echo number_format($row['jumlah_terjual'], 0, ',', '.'); ?> item</td>
                                        <td><span class="td-badge td-badge-blue"><?php echo number_format($row['jumlah_transaksi'], 0, ',', '.'); ?></span></td>
                                        <td style="font-weight:600;color:#0f172a;">Rp <?php echo number_format($row['total_pendapatan_produk'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php 
                                endwhile; 
                            }
                            if (!$hasDetail): ?>
                                <tr><td colspan="6" class="td-empty">Belum ada data penjualan pada periode ini</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lp-actions">
                <form action="dashboard_alfin.php" method="GET" style="margin:0;"><button type="submit" class="lp-btn-back">← Kembali ke Dashboard</button></form>
                <form action="laporan_penjualan_pdf.php" method="GET" target="_blank" style="margin:0;"><button type="submit" class="lp-btn-print">🖨️ Cetak Laporan (PDF)</button></form>
            </div>

        </div>
    </div>
</body>

</html>