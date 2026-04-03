<?php
include 'koneksi_alfin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>
<body>
    <div class="content-wrapper">
        <h2>Form Registrasi</h2>
        <form method="post" action="register_aksi_alfin.php">
            <div class="form-group">
                <label for="namaAlfin">Nama Lengkap</label>
                <input type="text" id="namaAlfin" name="namaAlfin" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="form-group">
                <label for="usernameAlfin">Username</label>
                <input type="text" id="usernameAlfin" name="usernameAlfin" placeholder="Masukkan username" required>
            </div>
            <div class="form-group">
                <label for="passwordAlfin">Password</label>
                <input type="password" id="passwordAlfin" name="passwordAlfin" placeholder="Masukkan password" required>
            </div>
            <div class="form-group">
                <label for="roleAlfin">Role</label>
                <select id="roleAlfin" name="roleAlfin" required>
                    <option value="">Pilih role</option>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                </select>
            </div>
            <button type="submit" name="registerAlfin" class="btn-primary">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="form_login_alfin.php">Login di sini</a></p>
    </div>
</body>
</html>