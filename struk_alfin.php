<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: transaksi_penjualan_alfin.php");
    exit;
}

$idTransaksi = (int)$_GET['id'];

// Ambil data transaksi
$queryTransaksi = mysqli_prepare($koneksiAlfin, "SELECT t.id_transaksi_alfin, t.tanggal_alfin, t.total_alfin, p.nama_pengguna_alfin FROM transaksi_alfin t JOIN pengguna_alfin p ON t.id_pengguna_alfin = p.id_pengguna_alfin WHERE t.id_transaksi_alfin = ?");
mysqli_stmt_bind_param($queryTransaksi, 'i', $idTransaksi);
mysqli_stmt_execute($queryTransaksi);
$resultTransaksi = mysqli_stmt_get_result($queryTransaksi);
$transaksi = mysqli_fetch_assoc($resultTransaksi);
mysqli_stmt_close($queryTransaksi);

if (!$transaksi) {
    header("Location: transaksi_penjualan_alfin.php");
    exit;
}

// Ambil detail transaksi
$queryDetail = mysqli_prepare($koneksiAlfin, "SELECT d.jumlah_alfin, d.subtotal_alfin, pr.nama_produk_alfin, pr.harga_alfin FROM detail_transaksi_alfin d JOIN produk_alfin pr ON d.id_produk_alfin = pr.id_produk_alfin WHERE d.id_transaksi_alfin = ?");
mysqli_stmt_bind_param($queryDetail, 'i', $idTransaksi);
mysqli_stmt_execute($queryDetail);
$resultDetail = mysqli_stmt_get_result($queryDetail);
$details = [];
while ($row = mysqli_fetch_assoc($resultDetail)) {
    $details[] = $row;
}
mysqli_stmt_close($queryDetail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Belanja - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
    <style>
        .struk {
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 20px;
            font-family: monospace;
        }
        .struk h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .struk table {
            width: 100%;
            border-collapse: collapse;
        }
        .struk th, .struk td {
            text-align: left;
            padding: 5px;
        }
        .struk .total {
            border-top: 1px solid #000;
            font-weight: bold;
        }
        .struk .footer {
            text-align: center;
            margin-top: 20px;
        }
        @media print {
            body * { visibility: hidden; }
            .struk, .struk * { visibility: visible; }
            .struk { position: absolute; left: 0; top: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="struk">
            <h2>Struk Belanja</h2>
            <p><strong>ID Transaksi:</strong> <?php echo htmlspecialchars($transaksi['id_transaksi_alfin']); ?></p>
            <p><strong>Tanggal:</strong> <?php echo htmlspecialchars($transaksi['tanggal_alfin']); ?></p>
            <p><strong>Kasir:</strong> <?php echo htmlspecialchars($transaksi['nama_pengguna_alfin']); ?></p>
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $detail): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detail['nama_produk_alfin']); ?></td>
                            <td><?php echo htmlspecialchars($detail['jumlah_alfin']); ?></td>
                            <td>Rp <?php echo number_format($detail['harga_alfin']); ?></td>
                            <td>Rp <?php echo number_format($detail['subtotal_alfin']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="total">
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>Rp <?php echo number_format($transaksi['total_alfin']); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="footer">
                <p>Terima Kasih Atas Kunjungan Anda!</p>
                <button onclick="window.print()">Cetak Struk</button>
                <a href="transaksi_penjualan_alfin.php">Kembali ke Transaksi</a>
            </div>
        </div>
    </div>
</body>
</html>