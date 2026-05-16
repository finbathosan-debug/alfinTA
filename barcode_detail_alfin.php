<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: form_login_alfin.php');
    exit;
}

// Tangkap barcode atau nama dari URL (GET)
$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';
$nama = isset($_GET['nama']) ? trim($_GET['nama']) : '';

if (empty($barcode) && empty($nama)) {
    header('Location: produk_alfin.php');
    exit;
}

// Ambil data produk - coba cari berdasarkan barcode dulu
$produk = null;
if (!empty($barcode)) {
    $query = mysqli_prepare($koneksiAlfin, "SELECT id_produk_alfin, nama_produk_alfin, barcode_alfin, stok_alfin FROM produk_alfin WHERE barcode_alfin = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 's', $barcode);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
    $produk = mysqli_fetch_assoc($result);
    mysqli_stmt_close($query);
}

// Fallback: Jika barcode tidak ketemu atau kosong, coba cari berdasarkan nama produk
if (!$produk && !empty($nama)) {
    $query = mysqli_prepare($koneksiAlfin, "SELECT id_produk_alfin, nama_produk_alfin, barcode_alfin, stok_alfin FROM produk_alfin WHERE nama_produk_alfin = ? LIMIT 1");
    mysqli_stmt_bind_param($query, 's', $nama);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
    $produk = mysqli_fetch_assoc($result);
    mysqli_stmt_close($query);
}

if (!$produk) {
    $searchTerm = !empty($barcode) ? htmlspecialchars($barcode) : htmlspecialchars($nama);
    die("<div style='padding: 20px; font-family: sans-serif;'><h3>❌ Produk tidak ditemukan</h3>Pencarian untuk: <strong>" . $searchTerm . "</strong> tidak menghasilkan data. <br><a href='produk_alfin.php'>← Kembali ke Daftar Produk</a></div>");
}

$jumlahStok = (int) $produk['stok_alfin'];
$barcodesArray = [];

// Jika stok habis (0), kita tetap tampilkan 1 sebagai sampel/preview
if ($jumlahStok <= 0) {
    $jumlahStok = 1;
    $peringatan = "⚠️ Stok produk ini sedang kosong (0). Menampilkan 1 barcode sebagai sampel preview.";
}

// Buat array sebanyak jumlah stok
for ($i = 0; $i < $jumlahStok; $i++) {
    $barcodesArray[] = [
        'barcode' => $produk['barcode_alfin'],
        'nama' => $produk['nama_produk_alfin']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Barcode -
        <?php echo htmlspecialchars($produk['nama_produk_alfin']); ?>
    </title>
    <link rel="stylesheet" href="style_alfin.css">
    <script src="js/JsBarcode.all.min.js"></script>
    <style>
        body {
            background-color: #f8fafc;
        }

        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .barcode-card {
            background: white;
            border: 1px solid #cbd5e1;
            padding: 20px 10px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .barcode-card h4 {
            font-size: 14px;
            margin-bottom: 12px;
            color: #0f172a;
        }

        .barcode-card svg {
            width: 100%;
            height: auto;
            max-height: 80px;
        }

        .barcode-card code {
            display: block;
            margin-top: 10px;
            font-family: monospace;
            font-size: 13px;
            color: #475569;
            font-weight: bold;
        }
        /* CSS Khusus untuk Tombol Kembali */
        .btn-kembali {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background-color: #f8fafc;
            color: #475569;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-kembali:hover {
            background-color: #e2e8f0;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .header-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 1200px; padding-top: 30px;">

        <div class="header-box">
            <div>
                <h2 style="margin-bottom: 5px; color: #6366f1;">Preview Barcode:
                    <?php echo htmlspecialchars($produk['nama_produk_alfin']); ?>
                </h2>
                <p style="margin: 0; color: #64748b;">
                    Menampilkan <strong>
                        <?php echo (isset($peringatan)) ? "1 Sampel" : $jumlahStok . " Label"; ?>
                    </strong> (sesuai jumlah stok)
                </p>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="produk_alfin.php" class="btn-kembali">← Kembali</a>

                <form action="barcode_label_pdf.php" method="GET" target="_blank" style="margin: 0;">
                    <input type="hidden" name="barcode"
                        value="<?php echo htmlspecialchars($produk['barcode_alfin']); ?>">
                    <input type="hidden" name="name"
                        value="<?php echo htmlspecialchars($produk['nama_produk_alfin']); ?>">
                    <input type="hidden" name="qty" value="<?php echo $jumlahStok; ?>">
                    <button type="submit" class="btn-primary" style="background: #10b981; border: none;">🖨️ Cetak PDF
                        (FPDF)</button>
                </form>
            </div>
        </div>

        <?php if (isset($peringatan)): ?>
            <div class="alert alert-warning" style="background: #fffbeb; color: #b45309; border-color: #fde68a;">
                <?php echo $peringatan; ?>
            </div>
        <?php endif; ?>

        <div class="barcode-grid">
            <?php foreach ($barcodesArray as $index => $item): ?>
                <div class="barcode-card">
                    <h4>
                        <?php echo htmlspecialchars(substr($item['nama'], 0, 30)); ?>
                    </h4>
                    <svg id="barcode-<?php echo $index; ?>"></svg>
                    <code><?php echo htmlspecialchars($item['barcode']); ?></code>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php foreach ($barcodesArray as $index => $item): ?>
                    try {
                    JsBarcode('#barcode-<?php echo $index; ?>', 
                            <?php echo json_encode($item['barcode']); ?>,
                    {
                        format: 'CODE128',
                        width: 2,
                        height: 60,
                        displayValue: false,
                        margin: 0
                    }
                        );
                    } catch (error) {
                console.error('Error generating barcode:', error);
            }
            <?php endforeach; ?>
        });
    </script>
</body>

</html>