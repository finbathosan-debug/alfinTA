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
                <label for="hargaAlfin">Harga</label>
                <input type="number" id="hargaAlfin" name="hargaAlfin" placeholder="Masukkan harga produk" value="<?php echo htmlspecialchars($produk['harga_alfin'], ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="stokAlfin">Stok</label>
                <input type="number" id="stokAlfin" name="stokAlfin" placeholder="Masukkan stok produk" value="<?php echo htmlspecialchars($produk['stok_alfin'], ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="kategoriAlfin">Kategori</label>
                <textarea id="kategoriAlfin" name="kategoriAlfin" placeholder="Masukkan kategori produk" required><?php echo htmlspecialchars($produk['kategori_alfin'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="form-group">
                <label>Barcode</label>
                <input type="hidden" id="barcodeAlfin" name="barcodeAlfin" value="<?php echo htmlspecialchars($produk['barcode_alfin'], ENT_QUOTES, 'UTF-8'); ?>">
                <div id="barcodeDisplay" style="text-align: center; margin-top: 10px;">
                    <svg id="barcode"></svg>
                    <p id="barcodeText" style="font-family: monospace; font-size: 12px; margin-top: 5px;"></p>
                    <button type="button" onclick="printBarcode()" class="btn-secondary" style="margin-top: 10px;">Cetak Barcode</button>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Simpan Perubahan</button>
                <a href="produk_alfin.php" class="btn-secondary" style="flex: 1; text-align: center; line-height: 1.5;">Batal</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        // Generate barcode on load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof JsBarcode === 'undefined') {
                console.error('JsBarcode library not loaded');
                return;
            }

            const barcodeInput = document.getElementById('barcodeAlfin');
            const barcodeSvg = document.getElementById('barcode');
            const barcodeText = document.getElementById('barcodeText');

            function updateBarcode() {
                const value = barcodeInput.value;
                if (value) {
                    try {
                        JsBarcode(barcodeSvg, value, {
                            format: "CODE128",
                            width: 2,
                            height: 60,
                            displayValue: false
                        });
                        barcodeText.textContent = value;
                    } catch (error) {
                        console.error('Error generating barcode:', error);
                        barcodeText.textContent = 'Error generating barcode';
                    }
                } else {
                    barcodeSvg.innerHTML = '';
                    barcodeText.textContent = '';
                }
            }

            updateBarcode(); // Initial generate
        });

        function printBarcode() {
            const barcodeSvg = document.getElementById('barcode');
            const barcodeText = document.getElementById('barcodeText').textContent;
            if (!barcodeSvg || !barcodeText) {
                alert('Barcode belum dihasilkan.');
                return;
            }

            const printWindow = window.open('', '_blank');
            const svgContent = barcodeSvg.outerHTML;
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Barcode</title>
                    <style>
                        body { text-align: center; font-family: Arial, sans-serif; margin: 20px; }
                        svg { margin: 20px auto; display: block; }
                        p { font-size: 14px; font-family: monospace; }
                    </style>
                </head>
                <body>
                    <h2>Barcode Produk</h2>
                    ${svgContent}
                    <p>${barcodeText}</p>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
</body>

</html>
