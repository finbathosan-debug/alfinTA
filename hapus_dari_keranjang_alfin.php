<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

if (isset($_POST['clear'])) {
    // Kosongkan seluruh keranjang
    $_SESSION['keranjang'] = [];
    echo json_encode(['success' => true, 'message' => 'Keranjang dikosongkan']);
    exit;
}

if (isset($_POST['index'])) {
    $index = intval($_POST['index']);

    if (isset($_SESSION['keranjang'][$index])) {
        unset($_SESSION['keranjang'][$index]);
        // Reindex array
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
        echo json_encode(['success' => true, 'message' => 'Produk dihapus dari keranjang']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan di keranjang']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
?>