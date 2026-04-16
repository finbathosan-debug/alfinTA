<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteProdukAlfin'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id === 0) {
        header("Location: produk_alfin.php");
        exit;
    }

    $query = mysqli_prepare($koneksiAlfin, "SELECT id_produk_alfin FROM produk_alfin WHERE id_produk_alfin = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 'i', $id);
    mysqli_stmt_execute($query);
    mysqli_stmt_store_result($query);

    if (mysqli_stmt_num_rows($query) === 0) {
        mysqli_stmt_close($query);
        header("Location: produk_alfin.php");
        exit;
    }
    mysqli_stmt_close($query);

    $delete = mysqli_prepare($koneksiAlfin, "DELETE FROM produk_alfin WHERE id_produk_alfin = ? LIMIT 1");
    mysqli_stmt_bind_param($delete, 'i', $id);

    if ($delete && mysqli_stmt_execute($delete)) {
        mysqli_stmt_close($delete);
        header("Location: produk_alfin.php?success=delete");
        exit;
    }

    if ($delete) {
        mysqli_stmt_close($delete);
    }

    echo "Gagal menghapus data. Coba lagi.";
    exit;
} else {
    header("Location: produk_alfin.php");
    exit;
}
?>