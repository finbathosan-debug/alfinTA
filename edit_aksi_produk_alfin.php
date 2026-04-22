<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: produk_alfin.php");
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nama = trim($_POST['namaProdukAlfin'] ?? '');
$hargaBeli = trim($_POST['hargaBeliAlfin'] ?? '');
$hargaJual = trim($_POST['hargaJualAlfin'] ?? '');
$stok = trim($_POST['stokAlfin'] ?? '');
$kategori = trim($_POST['kategoriAlfin'] ?? '');
$barcode = trim($_POST['barcodeAlfin'] ?? '');

if ($id <= 0 || $nama === '' || $hargaBeli === '' || $hargaJual === '' || $stok === '' || $kategori === '' || $barcode === '') {
    header("Location: edit_produk_alfin.php?id={$id}&error=required");
    exit;
}

if (!is_numeric($hargaBeli) || $hargaBeli < 0 || !is_numeric($hargaJual) || $hargaJual < 0 || !is_numeric($stok) || $stok < 0) {
    header("Location: edit_produk_alfin.php?id={$id}&error=invalid");
    exit;
}

$hargaBeli = floatval($hargaBeli);
$hargaJual = floatval($hargaJual);
$stok = intval($stok);

$stmt = mysqli_prepare(
    $koneksiAlfin,
    "UPDATE produk_alfin SET nama_produk_alfin = ?, harga_beli_alfin = ?, harga_jual_alfin = ?, stok_alfin = ?, barcode_alfin = ?, kategori_alfin = ? WHERE id_produk_alfin = ?"
);

if (!$stmt) {
    header("Location: edit_produk_alfin.php?id={$id}&error=db");
    exit;
}

mysqli_stmt_bind_param($stmt, 'sddissi', $nama, $hargaBeli, $hargaJual, $stok, $barcode, $kategori, $id);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    header("Location: produk_alfin.php?success=updated");
} else {
    header("Location: edit_produk_alfin.php?id={$id}&error=failed");
}
exit;
