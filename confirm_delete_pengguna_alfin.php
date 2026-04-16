<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header("Location: pengguna_alfin.php");
    exit;
}

$query = mysqli_prepare($koneksiAlfin, "SELECT id_pengguna_alfin, nama_pengguna_alfin FROM pengguna_alfin WHERE id_pengguna_alfin = ? LIMIT 1");
mysqli_stmt_bind_param($query, 'i', $id);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($query);
    header("Location: pengguna_alfin.php");
    exit;
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Pengguna - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
    <div class="content-wrapper" style="max-width: 500px;">
        <h2>Konfirmasi Hapus Pengguna</h2>

        <div class="alert alert-warning">
            <strong>⚠️ Peringatan!</strong><br>
            Anda akan menghapus pengguna berikut:
        </div>

        <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); margin: 20px 0; border: 1px solid var(--border-color);">
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($user['nama_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px;">
            Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin ingin menghapus pengguna ini?
        </p>

        <form method="post" action="hapus_aksi_pengguna_alfin.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="deletePenggunaAlfin" value="1">

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-danger" style="flex: 1;">Ya, Hapus Pengguna</button>
                <a href="pengguna_alfin.php" class="btn-secondary" style="flex: 1; text-align: center; line-height: 1.5;">Batal</a>
            </div>
        </form>
    </div>
</body>

</html>