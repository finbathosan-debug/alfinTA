<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletePenggunaAlfin'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id === 0) {
        header("Location: pengguna_alfin.php");
        exit;
    }

    $query = mysqli_prepare($koneksiAlfin, "SELECT id_pengguna_alfin FROM pengguna_alfin WHERE id_pengguna_alfin = ? LIMIT 1");
    mysqli_stmt_execute($query, [$id]);
    mysqli_stmt_store_result($query);

    if (mysqli_stmt_num_rows($query) === 0) {
        mysqli_stmt_close($query);
        header("Location: pengguna_alfin.php");
        exit;
    }
    mysqli_stmt_close($query);

    $delete = mysqli_prepare($koneksiAlfin, "DELETE FROM pengguna_alfin WHERE id_pengguna_alfin = ? LIMIT 1");

    if ($delete) {
        if (mysqli_stmt_execute($delete, [$id])) {
            mysqli_stmt_close($delete);
            header("Location: pengguna_alfin.php?success=delete");
            exit;
        } else {
            echo "Gagal menghapus data. Coba lagi.";
            mysqli_stmt_close($delete);
            exit;
        }
    } else {
        echo "Kesalahan server. Coba lagi.";
    }
} else {
    header("Location: pengguna_alfin.php");
    exit;
}
?>
