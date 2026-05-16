<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Tambah produk (Otomatis Qty 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barcode'])) {
    $barcode = trim($_POST['barcode']);
    if (!empty($barcode)) {
        $query = mysqli_prepare($koneksiAlfin, "SELECT * FROM produk_alfin WHERE barcode_alfin = ?");
        mysqli_stmt_bind_param($query, 's', $barcode);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);
        if ($produk = mysqli_fetch_assoc($result)) {
            $found = false;
            foreach ($_SESSION['keranjang'] as &$item) {
                if ($item['id_produk'] == $produk['id_produk_alfin']) {
                    if ($item['jumlah'] + 1 <= $produk['stok_alfin']) {
                        $item['jumlah']++;
                        $item['subtotal'] = $item['jumlah'] * $produk['harga_alfin'];
                        $found = true;
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Stok tidak mencukupi!']);
                        exit;
                    }
                    break;
                }
            }
            if (!$found) {
                if ($produk['stok_alfin'] > 0) {
                    $_SESSION['keranjang'][] = [
                        'id_produk' => $produk['id_produk_alfin'],
                        'nama' => $produk['nama_produk_alfin'],
                        'harga' => $produk['harga_alfin'],
                        'jumlah' => 1,
                        'subtotal' => $produk['harga_alfin']
                    ];
                } else {
                    echo json_encode(['success' => false, 'message' => 'Stok kosong!']);
                    exit;
                }
            }
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        exit;
    }
}

// Update Qty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $index = (int) $_POST['index'];
    $qty = (int) $_POST['quantity'];
    
    if (isset($_SESSION['keranjang'][$index])) {
        $id_produk = $_SESSION['keranjang'][$index]['id_produk'];
        $qStok = mysqli_query($koneksiAlfin, "SELECT stok_alfin FROM produk_alfin WHERE id_produk_alfin = $id_produk");
        $dStok = mysqli_fetch_assoc($qStok);
        $stok_real = $dStok['stok_alfin'];

        if ($qty > $stok_real) {
            echo json_encode(['success' => false, 'message' => 'Stok hanya tersedia: ' . $stok_real]);
        } elseif ($qty > 0) {
            $_SESSION['keranjang'][$index]['jumlah'] = $qty;
            $_SESSION['keranjang'][$index]['subtotal'] = $qty * $_SESSION['keranjang'][$index]['harga'];
            echo json_encode(['success' => true]);
        }
        exit;
    }
}

$total = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total += $item['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
    <style>
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 10px; border-bottom: 1px solid #ddd; gap: 10px; }
        .qty-control { display: flex; align-items: center; gap: 5px; background: #f1f5f9; padding: 5px; border-radius: 8px; border: 1px solid #cbd5e1; }
        .btn-qty { width: 32px; height: 32px; border-radius: 6px; border: none; background: #6366f1; color: white; font-weight: bold; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .input-qty-modern { width: 70px !important; height: 32px !important; border: none !important; background: transparent !important; text-align: center !important; font-size: 16px !important; font-weight: bold !important; color: #000 !important; outline: none !important; }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="margin-top:20px;">Transaksi Penjualan</h2>

        <div style="margin-bottom: 20px; display: flex; gap: 10px; background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <input type="text" id="barcode-input" placeholder="Scan barcode produk..." autofocus onkeydown="if(event.key==='Enter'){addByBarcode();}" style="flex:1; padding:12px; font-size: 16px;">
            <button onclick="addByBarcode()" class="btn-primary" style="padding: 0 20px;">Tambah</button>
        </div>

        <div id="cart" style="background:white; border-radius:8px; border:1px solid #e2e8f0; padding:10px; margin-bottom:20px;">
            <?php if (empty($_SESSION['keranjang'])): ?>
                <p style="text-align: center; padding: 20px; color: #64748b; margin: 0;">Keranjang masih kosong</p>
            <?php else: ?>
                <?php foreach ($_SESSION['keranjang'] as $index => $item): ?>
                    <div class="cart-item">
                        <div style="flex:2;">
                            <strong><?= htmlspecialchars($item['nama']) ?></strong><br>
                            <span style="font-size: 13px; color: #64748b;"> Rp <?= number_format($item['harga']) ?></span>
                        </div>
                        <div class="qty-control">
                            <button type="button" class="btn-qty" onclick="changeQty(<?= $index ?>, -1)">−</button>
                            <input type="number" id="input-qty-<?= $index ?>" class="input-qty-modern" value="<?= $item['jumlah'] ?>" min="1" onchange="updateQuantity(<?= $index ?>, this.value)">
                            <button type="button" class="btn-qty" onclick="changeQty(<?= $index ?>, 1)">+</button>
                        </div>
                        <div style="flex:1; text-align:right; font-weight: bold; font-size: 16px; min-width: 100px;"> Rp <?= number_format($item['subtotal']) ?> </div>
                        <button class="btn-danger" onclick="removeFromCart(<?= $index ?>)" style="min-width:unset; padding:6px 12px; font-weight: bold;">X</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="background:#f9fafb; padding:20px; border-radius:8px; border:1px solid #e2e8f0; margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span>Subtotal Barang:</span>
                <strong>Rp <?= number_format($total) ?></strong>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <label>Diskon (%):</label>
                <div style="text-align:right;">
                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 5px;">
                        <input type="number" id="diskon-persen" value="0" min="0" max="100" oninput="hitungDiskon()" style="width: 70px !important; height: 35px !important; text-align: center !important; font-weight: bold !important; border: 1px solid #cbd5e1 !important; border-radius: 4px !important;">
                        <strong>%</strong>
                    </div>
                    <div style="font-size:12px; color:#ef4444; margin-top:4px; font-weight: bold;">(Potongan: -Rp <span id="diskon-rupiah-text">0</span>)</div>
                </div>
            </div>
            <hr style="border:none; border-top:2px dashed #cbd5e1; margin:15px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; color:#4f46e5;">
                <strong style="font-size:20px;">Total Bayar:</strong>
                <strong style="font-size:24px;">Rp <span id="grand-total-text"><?= number_format($total) ?></span></strong>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 40px;">
            <form action="dashboard_alfin.php" method="GET" style="flex: 1; margin: 0;">
                <button type="submit" class="btn-secondary" style="width: 100%; height: 45px;">← Kembali</button>
            </form>
            <form action="proses_transaksi_alfin.php" method="POST" style="flex: 2; margin: 0;">
                <input type="hidden" name="persen_final" id="persen-hidden" value="0">
                <input type="hidden" name="diskon_final" id="diskon-rupiah-hidden" value="0">
                <button type="submit" class="btn-primary" style="width:100%; height: 45px; font-weight: bold; background: #10b981;">Proses Pembayaran</button>
            </form>
        </div>
    </div>

    <script>
        function changeQty(index, delta) {
            const input = document.getElementById('input-qty-' + index);
            let newQty = (parseInt(input.value) || 1) + delta;
            if (newQty >= 1) updateQuantity(index, newQty);
        }

        function updateQuantity(index, qty) {
            fetch('', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'update_quantity=1&index=' + index + '&quantity=' + qty })
            .then(res => res.json())
            .then(data => {
                if (!data.success) alert(data.message);
                location.reload();
            });
        }

        function hitungDiskon() {
            const subtotal = <?= $total ?>;
            let persen = parseInt(document.getElementById('diskon-persen').value) || 0;
            if (persen > 100) persen = 100;
            const diskonRupiah = Math.round((persen / 100) * subtotal);
            const grandTotal = subtotal - diskonRupiah;
            document.getElementById('diskon-rupiah-text').innerText = diskonRupiah.toLocaleString('id-ID');
            document.getElementById('grand-total-text').innerText = grandTotal.toLocaleString('id-ID');
            document.getElementById('persen-hidden').value = persen;
            document.getElementById('diskon-rupiah-hidden').value = diskonRupiah;
        }

        window.onload = hitungDiskon;

        function addByBarcode() {
            const barcode = document.getElementById('barcode-input').value.trim();
            if (!barcode) return;
            fetch('', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'barcode=' + encodeURIComponent(barcode) })
            .then(res => res.json())
            .then(data => { 
                if (data.success) location.reload(); 
                else alert(data.message); 
                document.getElementById('barcode-input').value = '';
            });
        }

        function removeFromCart(index) {
            fetch('hapus_dari_keranjang_alfin.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'index=' + index })
            .then(() => location.reload());
        }
    </script>
</body>
</html>