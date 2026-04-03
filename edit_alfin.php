<?php
include 'koneksi_alfin.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header("Location: crud_pengguna_alfin.php");
    exit;
}

// Ambil data pengguna dari database
$query = mysqli_prepare($koneksiAlfin, "SELECT id_pengguna_alfin, nama_pengguna_alfin, username_alfin, role_alfin FROM pengguna_alfin WHERE id_pengguna_alfin = ? LIMIT 1");
mysqli_stmt_bind_param($query, 'i', $id);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);

if (mysqli_num_rows($result) === 0) {
    header("Location: crud_pengguna_alfin.php");
    exit;
}

$pengguna = mysqli_fetch_assoc($result);
mysqli_stmt_close($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
    <div class="content-wrapper" style="max-width: 600px;">
        <h2>Edit Data Pengguna</h2>

        <form method="post" action="update_alfin.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($pengguna['id_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="namaAlfin">Nama Lengkap</label>
                <input type="text" id="namaAlfin" name="namaAlfin" 
                       value="<?php echo htmlspecialchars($pengguna['nama_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?>" 
                       placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="usernameAlfin">Username</label>
                <input type="text" id="usernameAlfin" name="usernameAlfin" 
                       value="<?php echo htmlspecialchars($pengguna['username_alfin'], ENT_QUOTES, 'UTF-8'); ?>" 
                       placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label for="passwordAlfin">Password</label>
                <input type="password" id="passwordAlfin" name="passwordAlfin" 
                       placeholder="Kosongkan jika tidak ingin mengubah password">
                <small style="color: #666; margin-top: 5px; display: block;">* Opsional - biarkan kosong jika tidak ingin mengubah</small>
            </div>

            <div class="form-group">
                <label for="roleAlfin">Role</label>
                <select id="roleAlfin" name="roleAlfin" required>
                    <option value="">Pilih role</option>
                    <option value="admin" <?php echo $pengguna['role_alfin'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="kasir" <?php echo $pengguna['role_alfin'] === 'kasir' ? 'selected' : ''; ?>>Kasir</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" name="updateAlfin" class="btn-primary" style="flex: 1;">Simpan Perubahan</button>
                <a href="crud_pengguna_alfin.php" class="btn-secondary" style="flex: 1; text-align: center; line-height: 1.5;">Batal</a>
            </div>
        </form>
    </div>
</body>

</html>