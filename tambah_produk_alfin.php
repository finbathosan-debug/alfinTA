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
    <title>Tambah Produk - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
    <div class="content-wrapper" style="max-width: 600px;">
        <h2>Tambah Produk Baru</h2>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'required'): ?>
            <div class="alert alert-danger">
                <p>Semua field wajib diisi.</p>
            </div>
        <?php endif; ?>

        <form method="post" action="tambah_aksi_produk_alfin.php">
            <div class="form-group">
                <label for="namaProdukAlfin">Nama Produk</label>
                <input type="text" id="namaProdukAlfin" name="namaProdukAlfin" placeholder="Masukkan nama produk" required>
            </div>

            <div class="form-group">
                <label for="hargaAlfin">Harga</label>
                <input type="number" id="hargaAlfin" name="hargaAlfin" placeholder="Masukkan harga produk" min="0" required>
            </div>

            <div class="form-group">
                <label for="deskripsiAlfin">Kategori</label>
                <textarea id="deskripsiAlfin" name="deskripsiAlfin" placeholder="Masukkan kategori produk" required></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Simpan Produk</button>
                <a href="produk_alfin.php" class="btn-secondary" style="flex: 1; text-align: center; line-height: 1.5;">Batal</a>
            </div>
        </form>
    </div>
</body>

</html>