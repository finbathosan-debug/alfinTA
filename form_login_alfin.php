<?php
include 'koneksi_alfin.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>
<body>
    <div class="content-wrapper">
        <h2>Login</h2>
        <form method="post" action="login_aksi_alfin.php">
            <div class="form-group">
                <label for="usernameAlfin">Username</label>
                <input type="text" id="usernameAlfin" name="usernameAlfin" placeholder="Masukkan username kamu" required>
            </div>
            <div class="form-group">
                <label for="passwordAlfin">Password</label>
                <input type="password" id="passwordAlfin" name="passwordAlfin" placeholder="Masukkan password kamu" required>
            </div>
            <button type="submit" name="loginAlfin" class="btn-primary">Login</button>
        </form>
    </div>
</body>
</html>