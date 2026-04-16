<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header("Location: produk_alfin.php");
    exit;
}

$query = mysqli_prepare($koneksiAlfin, "SELECT id_produk_alfin, nama_produk_alfin FROM produk_alfin WHERE id_produk_alfin = ? LIMIT 1");
mysqli_stmt_execute($query, [$id]);
$result = mysqli_stmt_get_result($query);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($query);
    header("Location: produk_alfin.php");
    exit;
}

$produk = mysqli_fetch_assoc($result);
mysqli_stmt_close($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Produk - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
    <div class="content-wrapper" style="max-width: 500px;">
        <h2>Konfirmasi Hapus Produk</h2>

        <div class="alert alert-warning">
            <strong>⚠️ Peringatan!</strong><br>
            Anda akan menghapus produk berikut:
        </div>

        <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); margin: 20px 0; border: 1px solid var(--border-color);">
            <p><strong>Nama Produk:</strong> <?php echo htmlspecialchars($produk['nama_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px;">
            Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin ingin menghapus produk ini?
        </p>

        <form method="post" action="hapus_aksi_produk_alfin.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($produk['id_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="deleteProdukAlfin" value="1">

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-danger" style="flex: 1;">Ya, Hapus Produk</button>
                <a href="produk_alfin.php" class="btn-secondary" style="flex: 1; text-align: center; line-height: 1.5;">Batal</a>
            </div>
        </form>
    </div>
</body>

</html>