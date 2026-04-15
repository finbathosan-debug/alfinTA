<?php
session_start();

// Proteksi: jika belum login, arahkan ke form login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>
<body>
    <header>
        <div class="container">
            <h1 style="margin: 0; font-size: 28px;">alfinTA</h1>
            <div class="user-info">
                <span>Selamat Datang, <strong><?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <a href="logout_alfin.php" class="btn-danger" style="padding: 8px 16px; font-size: 14px; margin: 0;">Logout</a>
            </div>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <h2>Dashboard Utama</h2>
        <p>Pilih menu di bawah untuk mengelola aplikasi</p>
        <div class="flex-center" style="margin-top: 30px;">
            <a href="pengguna_alfin.php" class="btn-primary" style="padding: 15px 40px; font-size: 16px;">Kelola Pengguna</a>
        </div>
        <div class="flex-center" style="margin-top: 30px;">
            <a href="produk_alfin.php" class="btn-primary" style="padding: 15px 40px; font-size: 16px;">Kelola Produk</a>
        </div>
    </div>
</body>
</html>