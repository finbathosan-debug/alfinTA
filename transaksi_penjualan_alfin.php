<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Hitung total keranjang
$totalKeranjang = 0;
$jumlahItem = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $totalKeranjang += $item['subtotal'];
    $jumlahItem += $item['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <div style="margin-top: 20px;">
            <h2>Transaksi Penjualan</h2>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'transaction'): ?>
                <div class="alert alert-success">
                    <p>✅ Transaksi berhasil! ID Transaksi: <?php echo htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <p>
                        <?php
                        if ($_GET['error'] === 'empty_cart') {
                            echo '❌ Keranjang kosong, tidak bisa memproses transaksi!';
                        } elseif ($_GET['error'] === 'transaction_failed') {
                            echo '❌ Transaksi gagal diproses, silakan coba lagi!';
                        } else {
                            echo '❌ Terjadi kesalahan!';
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">

                <!-- Panel Kiri: Input Barcode dan Keranjang -->
                <div>

                    <!-- Input Barcode -->
                    <div class="card" style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px;">
                        <h3>Scan Barcode Produk</h3>
                        <form id="formBarcode" method="post">
                            <div class="form-group">
                                <label for="barcodeInput">Barcode:</label>
                                <input type="text" id="barcodeInput" name="barcode" placeholder="Scan barcode atau ketik manual" autocomplete="off" required>
                                <small style="color: var(--text-secondary);">Fokus ke input ini dan scan barcode</small>
                            </div>
                            <button type="submit" class="btn-primary">Tambah ke Keranjang</button>
                        </form>
                    </div>

                    <!-- Keranjang Belanja -->
                    <div class="card" style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <h3>Keranjang Belanja (<?php echo $jumlahItem; ?> item)</h3>

                        <?php if (empty($_SESSION['keranjang'])): ?>
                            <p style="text-align: center; color: var(--text-secondary); margin: 20px 0;">Keranjang kosong</p>
                        <?php else: ?>
                            <div style="max-height: 400px; overflow-y: auto;">
                                <table style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Jumlah</th>
                                            <th>Harga</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['keranjang'] as $index => $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['nama_produk'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo $item['jumlah']; ?></td>
                                                <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <button onclick="hapusDariKeranjang(<?php echo $index; ?>)" class="btn-danger" style="padding: 5px 10px; font-size: 12px;">Hapus</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- Panel Kanan: Ringkasan dan Pembayaran -->
                <div>

                    <!-- Ringkasan Pembelian -->
                    <div class="card" style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px;">
                        <h3>Ringkasan Pembelian</h3>
                        <div style="margin: 20px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span>Total Item:</span>
                                <strong><?php echo $jumlahItem; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; border-top: 1px solid var(--border-color); padding-top: 10px;">
                                <span>Total Bayar:</span>
                                <strong style="color: var(--primary-color);">Rp <?php echo number_format($totalKeranjang, 0, ',', '.'); ?></strong>
                            </div>
                        </div>

                        <?php if (!empty($_SESSION['keranjang'])): ?>
                            <form method="post" action="proses_transaksi_alfin.php">
                                <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 16px;">
                                    💳 Proses Pembayaran
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Tombol Navigasi -->
                    <div class="card" style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <a href="dashboard_alfin.php" class="btn-secondary" style="width: 100%; text-align: center; display: block; margin-bottom: 10px;">← Kembali ke Dashboard</a>
                        <button onclick="clearKeranjang()" class="btn-danger" style="width: 100%;">🗑️ Kosongkan Keranjang</button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Fokus ke input barcode saat halaman load
            $('#barcodeInput').focus();

            // Submit form barcode
            $('#formBarcode').on('submit', function(e) {
                e.preventDefault();
                const barcode = $('#barcodeInput').val().trim();

                if (barcode === '') {
                    alert('Masukkan barcode produk!');
                    return;
                }

                // Kirim AJAX request untuk menambah ke keranjang
                $.post('tambah_ke_keranjang_alfin.php', { barcode: barcode }, function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Reload halaman untuk update keranjang
                            location.reload();
                        } else {
                            alert(data.message || 'Produk tidak ditemukan!');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan!');
                    }
                });

                // Clear input dan fokus kembali
                $('#barcodeInput').val('').focus();
            });

            // Deteksi barcode scanner (input cepat)
            let barcodeBuffer = '';
            let lastKeyTime = Date.now();

            $('#barcodeInput').on('keydown', function(e) {
                const currentTime = Date.now();
                const timeDiff = currentTime - lastKeyTime;

                // Jika jeda antar karakter < 50ms, kemungkinan barcode scanner
                if (timeDiff < 50) {
                    barcodeBuffer += String.fromCharCode(e.which);
                } else {
                    barcodeBuffer = String.fromCharCode(e.which);
                }

                lastKeyTime = currentTime;

                // Jika Enter atau jeda > 100ms, proses barcode
                setTimeout(() => {
                    if (barcodeBuffer.length > 0) {
                        $('#barcodeInput').val(barcodeBuffer);
                        $('#formBarcode').submit();
                        barcodeBuffer = '';
                    }
                }, 100);
            });
        });

        function hapusDariKeranjang(index) {
            if (confirm('Yakin ingin menghapus item ini dari keranjang?')) {
                $.post('hapus_dari_keranjang_alfin.php', { index: index }, function(response) {
                    location.reload();
                });
            }
        }

        function clearKeranjang() {
            if (confirm('Yakin ingin mengosongkan seluruh keranjang?')) {
                $.post('hapus_dari_keranjang_alfin.php', { clear: true }, function(response) {
                    location.reload();
                });
            }
        }
    </script>
</body>

</html>