<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

$defaultBarcode = 'BC' . substr(str_shuffle('0123456789'), 0, 12);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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
                <label for="stokAlfin">Stok</label>
                <input type="number" id="stokAlfin" name="stokAlfin" placeholder="Masukkan stok produk" min="0" required>
            </div>

            <div class="form-group">
                <label for="kategoriAlfin">Kategori</label>
                <textarea id="kategoriAlfin" name="kategoriAlfin" placeholder="Masukkan kategori produk" required></textarea>
            </div>

            <div class="form-group">
                <label>Barcode (Otomatis)</label>
                <input type="hidden" id="barcodeAlfin" name="barcodeAlfin" value="<?php echo htmlspecialchars($defaultBarcode, ENT_QUOTES, 'UTF-8'); ?>">
                <div id="barcodeDisplay" style="text-align: center; margin-top: 10px;">
                    <svg id="barcode"></svg>
                    <p id="barcodeText" style="font-family: monospace; font-size: 12px; margin-top: 5px;"></p>
                    <button type="button" id="printBarcodeButton" class="btn-secondary" style="margin-top: 10px;">Cetak Barcode</button>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn-primary" style="flex: 1;">Simpan Produk</button>
                <a href="produk_alfin.php" class="btn-secondary" style="flex: 1; text-align: center; line-height: 1.5;">Batal</a>
            </div>
        </form>

        <form id="printBarcodeForm" action="barcode_label_pdf.php" method="GET" target="_blank" style="display: none;">
            <input type="hidden" name="barcode" id="printBarcodeValue" value="<?php echo htmlspecialchars($defaultBarcode, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="name" id="printBarcodeName" value="">
        </form>
    </div>

    <script>
        // Generate barcode on load
        document.addEventListener('DOMContentLoaded', function() {
            const barcodeInput = document.getElementById('barcodeAlfin');
            const barcodeSvg = document.getElementById('barcode');
            const barcodeText = document.getElementById('barcodeText');

            function updateBarcode() {
                const value = barcodeInput.value;
                if (value) {
                    JsBarcode(barcodeSvg, value, {
                        format: "CODE128",
                        width: 2,
                        height: 60,
                        displayValue: false
                    });
                    barcodeText.textContent = value;
                } else {
                    barcodeSvg.innerHTML = '';
                    barcodeText.textContent = '';
                }
            }

            updateBarcode(); // Initial generate
            const productNameInput = document.getElementById('namaProdukAlfin');
            const printBarcodeName = document.getElementById('printBarcodeName');
            const printBarcodeValue = document.getElementById('printBarcodeValue');
            const printBarcodeButton = document.getElementById('printBarcodeButton');
            const printBarcodeForm = document.getElementById('printBarcodeForm');

            printBarcodeName.value = productNameInput.value.trim() || 'Produk';
            printBarcodeValue.value = barcodeInput.value;

            productNameInput.addEventListener('input', function() {
                printBarcodeName.value = this.value.trim() || 'Produk';
            });

            printBarcodeButton.addEventListener('click', function() {
                printBarcodeValue.value = barcodeInput.value;
                printBarcodeName.value = productNameInput.value.trim() || 'Produk';
                printBarcodeForm.submit();
            });
        });
    </script>
</body>

</html>