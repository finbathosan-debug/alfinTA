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
$hargaBeli = trim($_POST['hargaBeliAlfin'] ?? '');
$hargaJual = trim($_POST['hargaJualAlfin'] ?? '');
$stok = trim($_POST['stokAlfin'] ?? '');
$kategori = trim($_POST['kategoriAlfin'] ?? '');
$barcode = trim($_POST['barcodeAlfin'] ?? '');

if ($nama === '' || $hargaBeli === '' || $hargaJual === '' || $stok === '' || $kategori === '' || $barcode === '') {
    header("Location: tambah_produk_alfin.php?error=required");
    exit;
}

if (!is_numeric($hargaBeli) || $hargaBeli < 0 || !is_numeric($hargaJual) || $hargaJual < 0 || !is_numeric($stok) || $stok < 0) {
    header("Location: tambah_produk_alfin.php?error=invalid");
    exit;
}

$stmt = mysqli_prepare(
    $koneksiAlfin,
    "INSERT INTO produk_alfin (nama_produk_alfin, harga_beli_alfin, harga_jual_alfin, stok_alfin, barcode_alfin, kategori_alfin) VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    header("Location: tambah_produk_alfin.php?error=failed");
    exit;
}

$hargaBeli = floatval($hargaBeli);
$hargaJual = floatval($hargaJual);
$stok = intval($stok);
mysqli_stmt_bind_param($stmt, 'sddiss', $nama, $hargaBeli, $hargaJual, $stok, $barcode, $kategori);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    header("Location: produk_alfin.php?success=created");
} else {
    header("Location: tambah_produk_alfin.php?error=failed");
}
exit;