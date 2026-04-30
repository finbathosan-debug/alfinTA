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

// Fungsi untuk menambah produk ke keranjang berdasarkan barcode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barcode'])) {
    $barcode = trim($_POST['barcode']);
    if (!empty($barcode)) {
        $query = mysqli_prepare($koneksiAlfin, "SELECT * FROM produk_alfin WHERE barcode_alfin = ?");
        mysqli_stmt_bind_param($query, 's', $barcode);
        mysqli_stmt_execute($query);
        $result = mysqli_stmt_get_result($query);
        if ($produk = mysqli_fetch_assoc($result)) {
            $message = 'Produk ditambahkan ke keranjang';
            mysqli_stmt_close($query);
        } else {
            mysqli_stmt_close($query);

            $queryPartial = mysqli_prepare($koneksiAlfin, "SELECT * FROM produk_alfin WHERE barcode_alfin LIKE CONCAT('%', ?, '%')");
            mysqli_stmt_bind_param($queryPartial, 's', $barcode);
            mysqli_stmt_execute($queryPartial);
            $resultPartial = mysqli_stmt_get_result($queryPartial);

            $partialMatches = [];
            while ($row = mysqli_fetch_assoc($resultPartial)) {
                $partialMatches[] = $row;
            }

            if (count($partialMatches) === 1) {
                $produk = $partialMatches[0];
                $message = 'Produk ditambahkan ke keranjang (cocok sebagian barcode)';
            } elseif (count($partialMatches) > 1) {
                mysqli_stmt_close($queryPartial);
                echo json_encode(['success' => false, 'message' => 'Barcode tidak unik. Gunakan barcode lengkap atau scan ulang.']);
                exit;
            } else {
                mysqli_stmt_close($queryPartial);
                echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
                exit;
            }

            mysqli_stmt_close($queryPartial);
        }

        // Cek apakah produk sudah ada di keranjang
        $found = false;
        foreach ($_SESSION['keranjang'] as &$item) {
            if ($item['id_produk'] == $produk['id_produk_alfin']) {
                $item['jumlah']++;
                $item['subtotal'] = $item['jumlah'] * $produk['harga_alfin'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['keranjang'][] = [
                'id_produk' => $produk['id_produk_alfin'],
                'nama' => $produk['nama_produk_alfin'],
                'harga' => $produk['harga_alfin'],
                'jumlah' => 1,
                'subtotal' => $produk['harga_alfin']
            ];
        }
        echo json_encode(['success' => true, 'message' => $message]);
        exit;
    }
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $index = (int)$_POST['index'];
    $quantity = (int)$_POST['quantity'];
    if (isset($_SESSION['keranjang'][$index]) && $quantity > 0) {
        $_SESSION['keranjang'][$index]['jumlah'] = $quantity;
        $_SESSION['keranjang'][$index]['subtotal'] = $quantity * $_SESSION['keranjang'][$index]['harga'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    exit;
}

// Hitung total
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
    <script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
    <style>
        #scanner-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
        }
        #scanner-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 200px;
            border: 2px solid red;
            background: transparent;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .cart-item input {
            width: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Transaksi Penjualan</h2>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'transaction'): ?>
            <div class="alert alert-success">
                <p>✅ Transaksi berhasil! ID Transaksi: <?php echo htmlspecialchars($_GET['id']); ?></p>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'transaction_failed'): ?>
            <div class="alert alert-error">
                <p>❌ Transaksi gagal! Silakan coba lagi.</p>
            </div>
        <?php elseif (isset($_GET['error']) && $_GET['error'] === 'empty_cart'): ?>
            <div class="alert alert-error">
                <p>❌ Keranjang kosong! Tambahkan produk terlebih dahulu.</p>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 20px;">
            <input type="text" id="barcode-input" placeholder="Masukkan barcode atau nama produk">
            <button onclick="scanBarcode()">Scan Barcode</button>
            <button onclick="addByBarcode()">Tambah</button>
        </div>

        <div id="scanner-container">
            <div class="scanner-overlay"></div>
            <div id="interactive" class="viewport"></div>
            <button onclick="stopScanning()" style="position: absolute; top: 10px; right: 10px; background: red; color: white;">Tutup</button>
        </div>

        <h3>Keranjang Belanja</h3>
        <div id="cart">
            <?php if (empty($_SESSION['keranjang'])): ?>
                <p>Keranjang kosong</p>
            <?php else: ?>
                <?php foreach ($_SESSION['keranjang'] as $index => $item): ?>
                    <div class="cart-item">
                        <span><?php echo htmlspecialchars($item['nama']); ?> (Rp <?php echo number_format($item['harga']); ?>)</span>
                        <label>Jumlah:</label> <input type="number" value="<?php echo $item['jumlah']; ?>" min="1" onchange="updateQuantity(<?php echo $index; ?>, this.value)">
                        <span>Rp <?php echo number_format($item['subtotal']); ?></span>
                        <button onclick="removeFromCart(<?php echo $index; ?>)">Hapus</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <strong>Total: Rp <span id="total"><?php echo number_format($total); ?></span></strong>
        </div>

        <form action="proses_transaksi_alfin.php" method="POST" style="margin-top: 20px;">
            <button type="submit" <?php echo empty($_SESSION['keranjang']) ? 'disabled' : ''; ?>>Proses Transaksi</button>
        </form>

        <form action="dashboard_alfin.php" method="GET" style="margin-top: 20px;">
            <button type="submit" name="back">Kembali ke Dashboard</button>
        </form>
    </div>

    <script>
        let scannerActive = false;

        function scanBarcode() {
            document.getElementById('scanner-container').style.display = 'block';
            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: document.querySelector('#interactive'),
                    constraints: {
                        width: 1024,
                        height: 768,
                        facingMode: "environment"
                    }
                },
                locator: {
                    patchSize: "medium",
                    halfSample: true
                },
                numOfWorkers: 2,
                decoder: {
                    readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader", "upc_reader"]
                },
                locate: true
            }, function(err) {
                if (err) {
                    console.log(err);
                    return;
                }
                Quagga.start();
                scannerActive = true;
            });

            Quagga.onDetected(function(result) {
                const code = result.codeResult.code ? result.codeResult.code.trim() : '';
                console.log('Barcode terdeteksi:', code, result);
                if (!code) {
                    return;
                }
                document.getElementById('barcode-input').value = code;
                stopScanning();
                addByBarcode();
            });
        }

        function stopScanning() {
            if (scannerActive) {
                Quagga.stop();
                scannerActive = false;
            }
            document.getElementById('scanner-container').style.display = 'none';
        }

        function addByBarcode() {
            const barcode = document.getElementById('barcode-input').value.trim();
            if (!barcode) return;

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'barcode=' + encodeURIComponent(barcode)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produk ditemukan: ' + data.message);
                    location.reload();
                } else {
                    alert('Produk tidak ditemukan. Barcode terbaca: ' + barcode + '\nPesan: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateQuantity(index, quantity) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'update_quantity=1&index=' + index + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function removeFromCart(index) {
            fetch('hapus_dari_keranjang_alfin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'index=' + index
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html>