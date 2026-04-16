<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: produk_alfin.php?error=invalid");
    exit;
}

$stmt = mysqli_prepare($koneksiAlfin, "SELECT * FROM produk_alfin WHERE id_produk_alfin = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$produk) {
    header("Location: produk_alfin.php?error=notfound");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
    <div class="content-wrapper" style="max-width: 600px;">
        <h2>Edit Produk</h2>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'required'): ?>
            <div class="alert alert-danger">
                <p>Semua field wajib diisi.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
            <div class="alert alert-danger">
                <p>Data tidak valid.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="edit_aksi_produk_alfin.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($produk['id_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="namaProdukAlfin">Nama Produk</label>
                <input type="text" id="namaProdukAlfin" name="namaProdukAlfin" placeholder="Masukkan nama produk" value="<?php echo htmlspecialchars($produk['nama_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="form-group">
                <label for="hargaAlfin">Harga Jual</label>
                <input type="number" id="hargaAlfin" name="hargaAlfin" placeholder="Masukkan harga produk" value="<?php echo htmlspecialchars($produk['harga_jual_alfin'], ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="deskripsiAlfin">Kategori</label>
                <textarea id="deskripsiAlfin" name="deskripsiAlfin" placeholder="Masukkan kategori produk" required><?php echo htmlspecialchars($produk['kategori_alfin'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="form-group">
                <label for="barcodeAlfin">Barcode</label>
                <input type="text" id="barcodeAlfin" name="barcodeAlfin" placeholder="Masukkan barcode produk" value="<?php echo htmlspecialchars($produk['barcode_alfin'], ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Simpan Perubahan</button>
                <a href="produk_alfin.php" class="btn-secondary" style="flex: 1; text-align: center; line-height: 1.5;">Batal</a>
            </div>
        </form>
    </div>
</body>

</html>
