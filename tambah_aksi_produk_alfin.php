<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_produk_alfin.php");
    exit;
}

$nama = trim($_POST['namaProdukAlfin'] ?? '');
$harga = trim($_POST['hargaAlfin'] ?? '');
$deskripsi = trim($_POST['deskripsiAlfin'] ?? '');

if ($nama === '' || $harga === '' || $deskripsi === '') {
    header("Location: tambah_produk_alfin.php?error=required");
    exit;
}

if (!is_numeric($harga) || $harga < 0) {
    header("Location: tambah_produk_alfin.php?error=invalid");
    exit;
}

$stmt = mysqli_prepare(
    $koneksiAlfin,
    "INSERT INTO produk_alfin (nama_produk_alfin, harga_jual_alfin, kategori_alfin) VALUES (?, ?, ?)"
);

if (!$stmt) {
    header("Location: tambah_produk_alfin.php?error=failed");
    exit;
}

$harga = floatval($harga);
mysqli_stmt_bind_param($stmt, 'sds', $nama, $harga, $deskripsi);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    header("Location: produk_alfin.php?success=created");
} else {
    header("Location: tambah_produk_alfin.php?error=failed");
}
exit;