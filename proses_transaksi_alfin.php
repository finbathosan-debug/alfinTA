<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: transaksi_penjualan_alfin.php");
    exit;
}

// Cek apakah keranjang ada dan tidak kosong
if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    header("Location: transaksi_penjualan_alfin.php?error=empty_cart");
    exit;
}

// Hitung total transaksi
$totalTransaksi = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $totalTransaksi += $item['subtotal'];
}

// Mulai transaksi database
mysqli_autocommit($koneksiAlfin, false);

try {
    // 1. Insert ke tabel transaksi_alfin
    $idPengguna = $_SESSION['user_id'] ?? 1; // Default ke 1 jika tidak ada
    $tanggal = date('Y-m-d');

    $queryTransaksi = mysqli_prepare($koneksiAlfin, "INSERT INTO transaksi_alfin (id_pengguna_alfin, tanggal_alfin, total_alfin) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($queryTransaksi, 'isi', $idPengguna, $tanggal, $totalTransaksi);
    mysqli_stmt_execute($queryTransaksi);
    $idTransaksi = mysqli_insert_id($koneksiAlfin);
    mysqli_stmt_close($queryTransaksi);

    // 2. Insert detail transaksi ke tabel detail_transaksi_alfin
    $queryDetail = mysqli_prepare($koneksiAlfin, "INSERT INTO detail_transaksi_alfin (id_transaksi_alfin, id_produk_alfin, jumlah_alfin, subtotal_alfin) VALUES (?, ?, ?, ?)");

    foreach ($_SESSION['keranjang'] as $item) {
        mysqli_stmt_bind_param($queryDetail, 'iiii', $idTransaksi, $item['id_produk'], $item['jumlah'], $item['subtotal']);
        mysqli_stmt_execute($queryDetail);
    }
    mysqli_stmt_close($queryDetail);

    // Commit transaksi
    mysqli_commit($koneksiAlfin);
    mysqli_autocommit($koneksiAlfin, true);

    // Kosongkan keranjang setelah transaksi berhasil
    $_SESSION['keranjang'] = [];

    // Redirect ke halaman sukses
    header("Location: transaksi_penjualan_alfin.php?success=transaction&id=$idTransaksi");
    exit;

} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($koneksiAlfin);
    mysqli_autocommit($koneksiAlfin, true);
    header("Location: transaksi_penjualan_alfin.php?error=transaction_failed");
    exit;
}
?>