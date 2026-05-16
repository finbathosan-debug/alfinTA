<?php
include 'koneksi_alfin.php';
session_start();
// 1. Set timezone agar waktu sesuai WIB
date_default_timezone_set('Asia/Jakarta');

// 2. Gunakan format Y-m-d H:i:s (H:i:s inilah yang mengambil Jam:Menit:Detik)
$tanggal_sekarang = date('Y-m-d H:i:s');

// 3. Pastikan di query INSERT menggunakan variabel tersebut
$query = "INSERT INTO transaksi_alfin (id_pengguna_alfin, tanggal_alfin, total_alfin, ...) 
          VALUES ('$id_kasir', '$tanggal_sekarang', '$total', ...)";

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: transaksi_penjualan_alfin.php");
    exit;
}

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    header("Location: transaksi_penjualan_alfin.php?error=empty_cart");
    exit;
}

// Hitung total sebelum diskon
$totalTransaksi = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $totalTransaksi += $item['subtotal'];
}

// Tangkap data diskon dari form
$persen = isset($_POST['persen_final']) ? (int) $_POST['persen_final'] : 0;
$diskonRp = isset($_POST['diskon_final']) ? (int) $_POST['diskon_final'] : 0;
$totalAkhir = max(0, $totalTransaksi - $diskonRp);

mysqli_autocommit($koneksiAlfin, false);

try {
    $idPengguna = $_SESSION['user_id'] ?? 1;
    $tanggal = date('Y-m-d H:i:s');

    // 1. Catat Data Transaksi Utama
    $queryTransaksi = mysqli_prepare($koneksiAlfin, "INSERT INTO transaksi_alfin (id_pengguna_alfin, tanggal_alfin, diskon_persen_alfin, diskon_alfin, total_alfin) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($queryTransaksi, 'isiii', $idPengguna, $tanggal, $persen, $diskonRp, $totalAkhir);
    mysqli_stmt_execute($queryTransaksi);
    $idTransaksi = mysqli_insert_id($koneksiAlfin);
    mysqli_stmt_close($queryTransaksi);

    // Siapkan query untuk detail transaksi
    $queryDetail = mysqli_prepare($koneksiAlfin, "INSERT INTO detail_transaksi_alfin (id_transaksi_alfin, id_produk_alfin, jumlah_alfin, subtotal_alfin) VALUES (?, ?, ?, ?)");

    // 2. Proses Setiap Barang di Keranjang
    foreach ($_SESSION['keranjang'] as $item) {
        // Masukkan ke detail_transaksi
        mysqli_stmt_bind_param($queryDetail, 'iiii', $idTransaksi, $item['id_produk'], $item['jumlah'], $item['subtotal']);
        mysqli_stmt_execute($queryDetail);

        // 3. Kurangi stok produk (DENGAN VALIDASI ANTI-MINUS)
        // Perhatikan ada tambahan kondisi: AND stok_alfin >= ?
        $queryUpdateStok = mysqli_prepare($koneksiAlfin, "UPDATE produk_alfin SET stok_alfin = stok_alfin - ? WHERE id_produk_alfin = ? AND stok_alfin >= ?");

        // Parameter: jumlah_beli, id_produk, jumlah_beli
        mysqli_stmt_bind_param($queryUpdateStok, 'iii', $item['jumlah'], $item['id_produk'], $item['jumlah']);
        mysqli_stmt_execute($queryUpdateStok);

        // Cek apakah stok berhasil dikurangi
        // Jika affected_rows = 0, artinya syarat (stok_alfin >= jumlah_beli) tidak terpenuhi (STOK KURANG)
        if (mysqli_stmt_affected_rows($queryUpdateStok) <= 0) {
            throw new Exception("Stok tidak mencukupi untuk produk: " . $item['nama']);
        }

        mysqli_stmt_close($queryUpdateStok);
    }
    mysqli_stmt_close($queryDetail);

    // Jika semua mulus dan stok aman, simpan permanen (Commit)
    mysqli_commit($koneksiAlfin);
    mysqli_autocommit($koneksiAlfin, true);
    $_SESSION['keranjang'] = []; // Kosongkan keranjang

    header("Location: struk_alfin.php?id=$idTransaksi");
    exit;

} catch (Exception $e) {
    // JIKA ADA STOK YANG KURANG/MINUS, BATALKAN SEMUA PROSES (Rollback)
    mysqli_rollback($koneksiAlfin);
    mysqli_autocommit($koneksiAlfin, true);

    // Kembalikan ke halaman kasir dengan pesan error
    header("Location: transaksi_penjualan_alfin.php?error=transaction_failed");
    exit;
}
?>