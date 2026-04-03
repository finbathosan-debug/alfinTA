<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insertPenggunaAlfin'])) {
    $namaAlfin = trim($_POST['namaAlfin'] ?? '');
    $usernameAlfin = trim($_POST['usernameAlfin'] ?? '');
    $passwordAlfin = $_POST['passwordAlfin'] ?? '';
    $roleAlfin = trim($_POST['roleAlfin'] ?? '');

    if ($namaAlfin === '' || $usernameAlfin === '' || $passwordAlfin === '' || $roleAlfin === '') {
        echo "Semua field harus diisi.";
        exit;
    }

    $check = mysqli_prepare($koneksiAlfin, "SELECT 1 FROM pengguna_alfin WHERE username_alfin = ? LIMIT 1");
    mysqli_stmt_execute($check, [$usernameAlfin]);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        echo "Username sudah terdaftar. Pilih username lain.";
        mysqli_stmt_close($check);
        exit;
    }
    mysqli_stmt_close($check);

    $allowedRoles = ['admin', 'kasir'];
    if (!in_array($roleAlfin, $allowedRoles, true)) {
        echo "Role tidak valid.";
        exit;
    }

    $passwordHash = password_hash($passwordAlfin, PASSWORD_DEFAULT);
    $insert = mysqli_prepare($koneksiAlfin, "INSERT INTO pengguna_alfin (nama_pengguna_alfin, username_alfin, password_alfin, role_alfin) VALUES (?, ?, ?, ?)");

    if ($insert) {
        if (mysqli_stmt_execute($insert, [$namaAlfin, $usernameAlfin, $passwordHash, $roleAlfin])) {
            mysqli_stmt_close($insert);
            header("Location: crud_pengguna_alfin.php?success=insert");
            exit;
        } else {
            echo "Gagal menambah data. Coba lagi.";
            mysqli_stmt_close($insert);
        }
    } else {
        echo "Kesalahan server. Coba lagi.";
    }
} else {
    header("Location: crud_pengguna_alfin.php");
    exit;
}
?>
