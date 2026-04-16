<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$barcode = trim($_POST['barcode'] ?? '');

if ($barcode === '') {
    echo json_encode(['success' => false, 'message' => 'Barcode tidak boleh kosong']);
    exit;
}

// Cari produk berdasarkan barcode
$query = mysqli_prepare($koneksiAlfin, "SELECT * FROM produk_alfin WHERE barcode_alfin = ? LIMIT 1");
mysqli_stmt_bind_param($query, 's', $barcode);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Produk dengan barcode tersebut tidak ditemukan']);
    exit;
}

$produk = mysqli_fetch_assoc($result);
mysqli_stmt_close($query);

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Cek apakah produk sudah ada di keranjang
$produkIndex = -1;
foreach ($_SESSION['keranjang'] as $index => $item) {
    if ($item['id_produk'] == $produk['id_produk_alfin']) {
        $produkIndex = $index;
        break;
    }
}

// Jika produk sudah ada, tambah jumlahnya
if ($produkIndex >= 0) {
    $_SESSION['keranjang'][$produkIndex]['jumlah'] += 1;
    $_SESSION['keranjang'][$produkIndex]['subtotal'] = $_SESSION['keranjang'][$produkIndex]['jumlah'] * $_SESSION['keranjang'][$produkIndex]['harga'];
} else {
    // Jika belum ada, tambah produk baru
    $_SESSION['keranjang'][] = [
        'id_produk' => $produk['id_produk_alfin'],
        'nama_produk' => $produk['nama_produk_alfin'],
        'harga' => $produk['harga_jual_alfin'],
        'jumlah' => 1,
        'subtotal' => $produk['harga_jual_alfin']
    ];
}

echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang']);
?>