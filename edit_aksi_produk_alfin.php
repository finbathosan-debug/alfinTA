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
$harga = trim($_POST['hargaAlfin'] ?? '');
$kategori = trim($_POST['deskripsiAlfin'] ?? '');
$barcode = trim($_POST['barcodeAlfin'] ?? '');

if ($id <= 0 || $nama === '' || $harga === '' || $kategori === '' || $barcode === '') {
    header("Location: edit_produk_alfin.php?id={$id}&error=required");
    exit;
}

if (!is_numeric($harga) || $harga < 0) {
    header("Location: edit_produk_alfin.php?id={$id}&error=invalid");
    exit;
}

$harga = floatval($harga);

$stmt = mysqli_prepare(
    $koneksiAlfin,
    "UPDATE produk_alfin SET nama_produk_alfin = ?, harga_jual_alfin = ?, kategori_alfin = ?, barcode_alfin = ? WHERE id_produk_alfin = ?"
);

if (!$stmt) {
    header("Location: edit_produk_alfin.php?id={$id}&error=db");
    exit;
}

mysqli_stmt_bind_param($stmt, 'sdssi', $nama, $harga, $kategori, $barcode, $id);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    header("Location: produk_alfin.php?success=updated");
} else {
    header("Location: edit_produk_alfin.php?id={$id}&error=failed");
}
exit;
