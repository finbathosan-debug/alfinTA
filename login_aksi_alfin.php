<?php
include 'koneksi_alfin.php';
session_start();

// Hanya terima POST dari form login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loginAlfin'])) {
    $usernameAlfin = trim($_POST['usernameAlfin'] ?? '');
    $passwordAlfin = $_POST['passwordAlfin'] ?? '';

    if ($usernameAlfin === '' || $passwordAlfin === '') {
        echo "Username dan password harus diisi.";
        exit;
    }

    // Gunakan prepared statement untuk menghindari SQL injection
    $dataAlfin = mysqli_prepare($koneksiAlfin, "SELECT id_pengguna_alfin, username_alfin, password_alfin FROM pengguna_alfin WHERE username_alfin = ? LIMIT 1");
    if ($dataAlfin) {
        mysqli_stmt_bind_param($dataAlfin, 's', $usernameAlfin);
        mysqli_stmt_execute($dataAlfin);
        mysqli_stmt_store_result($dataAlfin);

        if (mysqli_stmt_num_rows($dataAlfin) === 1) {
            mysqli_stmt_bind_result($dataAlfin, $dbUserId, $dbUsername, $dbPasswordHash);
            mysqli_stmt_fetch($dataAlfin);

            // Verifikasi password yang tersimpan hash
            if (password_verify($passwordAlfin, $dbPasswordHash)) {
                session_regenerate_id(true);
                $_SESSION['login'] = true;
                $_SESSION['user'] = $dbUsername;
                $_SESSION['user_id'] = $dbUserId;
                header("Location: dashboard_alfin.php");
                exit;
            }
        }

        mysqli_stmt_close($dataAlfin);
    }

    echo "Username atau Password Anda Salah.";
}
?>