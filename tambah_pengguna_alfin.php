<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
    <div class="content-wrapper" style="max-width: 600px;">
        <h2>Tambah Pengguna Baru</h2>

        <form method="post" action="insert_pengguna_alfin.php">
            <div class="form-group">
                <label for="namaAlfin">Nama Lengkap</label>
                <input type="text" id="namaAlfin" name="namaAlfin"
                       placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="usernameAlfin">Username</label>
                <input type="text" id="usernameAlfin" name="usernameAlfin"
                       placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label for="passwordAlfin">Password</label>
                <input type="password" id="passwordAlfin" name="passwordAlfin"
                       placeholder="Masukkan password" required>
            </div>

            <div class="form-group">
                <label for="roleAlfin">Role</label>
                <select id="roleAlfin" name="roleAlfin" required>
                    <option value="">Pilih role</option>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" name="insertPenggunaAlfin" class="btn-primary" style="flex: 1;">Tambah Pengguna</button>
                <button type="button" onclick="window.location.href='crud_pengguna_alfin.php'" class="btn-primary" style="flex: 1;">Batal</button>
            </div>
        </form>
    </div>
</body>

</html>