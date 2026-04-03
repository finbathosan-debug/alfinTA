<?php
include 'koneksi_alfin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registerAlfin'])) {
    $namaAlfin = trim($_POST['namaAlfin'] ?? '');
    $usernameAlfin = trim($_POST['usernameAlfin'] ?? '');
    $passwordAlfin = $_POST['passwordAlfin'] ?? '';
    $roleAlfin = trim($_POST['roleAlfin'] ?? '');

    if ($namaAlfin === '' || $usernameAlfin === '' || $passwordAlfin === '') {
        echo "Nama, username dan password harus diisi.";
        exit;
    }

    // Cek apakah username sudah dipakai
    $check = mysqli_prepare($koneksiAlfin, "SELECT 1 FROM pengguna_alfin WHERE username_alfin = ? LIMIT 1");
    mysqli_stmt_bind_param($check, 's', $usernameAlfin);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    if (mysqli_stmt_num_rows($check) > 0) {
        echo "Username sudah terdaftar. Pilih username lain.";
        mysqli_stmt_close($check);
        exit;
    }
    mysqli_stmt_close($check);

    // Validasi role
    $allowedRoles = ['admin', 'kasir'];
    if ($roleAlfin === '' || !in_array($roleAlfin, $allowedRoles, true)) {
        echo "Role tidak valid. Pilih 'admin' atau 'kasir'.";
        exit;
    }

    // Hash password
    $passwordHash = password_hash($passwordAlfin, PASSWORD_DEFAULT);
    // Simpan user baru dengan prepared statement (simpan juga nama dan role)
    $insert = mysqli_prepare($koneksiAlfin, "INSERT INTO pengguna_alfin (nama_pengguna_alfin, username_alfin, password_alfin, role_alfin) VALUES (?, ?, ?, ?)");
    if ($insert) {
        mysqli_stmt_bind_param($insert, 'ssss', $namaAlfin, $usernameAlfin, $passwordHash, $roleAlfin);
        if (mysqli_stmt_execute($insert)) {
            mysqli_stmt_close($insert);
            // Redirect ke halaman login setelah registrasi sukses
            header("Location: form_login_alfin.php");
            exit;
        } else {
            echo "Gagal mendaftar. Coba lagi.";
            mysqli_stmt_close($insert);
        }
    } else {
        // Jika prepare gagal kemungkinan kolom nama_alfin/role_alfin belum ada di tabel
        echo "Kesalahan server (prepare gagal). Pastikan tabel pengguna_alfin memiliki kolom 'nama_alfin' dan 'role_alfin'.";
    }
}
?>