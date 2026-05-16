<?php
include 'koneksi_alfin.php';
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = (int)$_POST['id_produk'];
    $jumlah = (int)$_POST['jumlah'];
    $keterangan = trim($_POST['keterangan']);
    if ($id_produk && $jumlah > 0) {
        // Kurangi stok produk
        $query = mysqli_prepare($koneksiAlfin, "UPDATE produk_alfin SET stok_alfin = stok_alfin - ? WHERE id_produk_alfin = ? AND stok_alfin >= ?");
        mysqli_stmt_bind_param($query, 'iii', $jumlah, $id_produk, $jumlah);
        mysqli_stmt_execute($query);
        if (mysqli_stmt_affected_rows($query) > 0) {
            // Catat riwayat barang keluar
            $query2 = mysqli_prepare($koneksiAlfin, "INSERT INTO barang_keluar_alfin (id_produk_alfin, jumlah_keluar, tanggal_keluar, keterangan) VALUES (?, ?, NOW(), ?)");
            mysqli_stmt_bind_param($query2, 'iis', $id_produk, $jumlah, $keterangan);
            mysqli_stmt_execute($query2);
            mysqli_stmt_close($query2);
            header("Location: barang_keluar_alfin.php?success=1");
            exit;
        } else {
            $error = 'Stok tidak cukup!';
        }
        mysqli_stmt_close($query);
    }
}

// Ambil semua produk untuk autocomplete
$produk = mysqli_query($koneksiAlfin, "SELECT id_produk_alfin, nama_produk_alfin, stok_alfin FROM produk_alfin ORDER BY nama_produk_alfin ASC");
$produkList = [];
while ($row = mysqli_fetch_assoc($produk)) {
    $produkList[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Keluar - alfinTA</title>
    <link rel="stylesheet" href="style_alfin.css">
    <style>
        /* ── Page Layout ── */
        .bk-page {
            min-height: 100vh;
            background: #f0f4f8;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 48px 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .bk-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 560px;
            overflow: hidden;
        }

        /* ── Card Header ── */
        .bk-header {
            background: linear-gradient(135deg, #f7f7f7 0%, #f7f7f7 100%);
            padding: 28px 32px;
            color: #fff;
        }

        .bk-header .bk-icon {
            font-size: 2rem;
            margin-bottom: 8px;
            display: block;
        }

        .bk-header h2 {
            margin: 0 0 4px 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .bk-header p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.85;
        }

        /* ── Form Body ── */
        .bk-body {
            padding: 32px;
        }

        /* ── Alerts ── */
        .bk-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.92rem;
            font-weight: 500;
        }

        .bk-alert.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .bk-alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* ── Form Groups ── */
        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group label span.required {
            color: #ef4444;
            margin-left: 2px;
        }

        /* ── Autocomplete Wrapper ── */
        .autocomplete-wrapper {
            position: relative;
        }

        .autocomplete-wrapper .search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
            pointer-events: none;
        }

        .autocomplete-input {
            width: 100%;
            padding: 12px 14px 12px 38px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #111827;
            background: #f9fafb;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box;
        }

        .autocomplete-input:focus {
            outline: none;
            border-color: #ef4444;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.12);
        }

        .autocomplete-input.has-value {
            border-color: #ef4444;
            background: #fff;
        }

        /* ── Dropdown List ── */
        .autocomplete-list {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            max-height: 240px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .autocomplete-list.visible {
            display: block;
        }

        .autocomplete-item {
            padding: 11px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.15s;
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .autocomplete-item:hover,
        .autocomplete-item.active {
            background: #fff1f2;
        }

        .autocomplete-item.stok-kosong {
            opacity: 0.5;
            pointer-events: none;
        }

        .autocomplete-item .item-name {
            font-size: 0.92rem;
            color: #111827;
            font-weight: 500;
        }

        .autocomplete-item .item-name mark {
            background: #fecdd3;
            color: #9f1239;
            border-radius: 2px;
            padding: 0 1px;
        }

        .autocomplete-item .item-stock {
            font-size: 0.78rem;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .autocomplete-item .item-stock.low {
            background: #fee2e2;
            color: #991b1b;
        }

        .autocomplete-no-result {
            padding: 14px 16px;
            font-size: 0.88rem;
            color: #9ca3af;
            text-align: center;
        }

        /* ── Selected Badge ── */
        .selected-badge {
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #fee2e2;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #991b1b;
            font-weight: 500;
        }

        .selected-badge.visible {
            display: flex;
        }

        .selected-badge .clear-btn {
            margin-left: auto;
            cursor: pointer;
            background: none;
            border: none;
            color: #991b1b;
            font-size: 1rem;
            line-height: 1;
            padding: 0;
            opacity: 0.7;
        }

        .selected-badge .clear-btn:hover {
            opacity: 1;
        }

        /* ── Regular Inputs ── */
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #111827;
            background: #f9fafb;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #ef4444;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.12);
        }

        /* ── Submit Button ── */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #b91c1c, #ef4444);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            letter-spacing: 0.3px;
            margin-top: 4px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(239,68,68,0.35);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* ── Back Link ── */
        .bk-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
            font-size: 0.88rem;
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s;
        }

        .bk-back:hover {
            color: #ef4444;
        }
    </style>
</head>
<body>
<div class="bk-page">
    <div class="bk-card">

        <!-- Header -->
        <div class="bk-header">
            <span class="bk-icon">📤</span>
            <h2>Barang Keluar</h2>
            <p>Kurangi stok produk dari inventaris</p>
        </div>

        <!-- Body -->
        <div class="bk-body">

            <?php if (isset($_GET['success'])): ?>
                <div class="bk-alert success">
                    ✅ Barang keluar berhasil dicatat!
                </div>
            <?php elseif (isset($error)): ?>
                <div class="bk-alert error">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="formBarangKeluar">

                <!-- Produk Autocomplete -->
                <div class="form-group">
                    <label>Produk <span class="required">*</span></label>
                    <input type="hidden" name="id_produk" id="id_produk" required>
                    <div class="autocomplete-wrapper">
                        <input
                            type="text"
                            id="produkSearch"
                            class="autocomplete-input"
                            placeholder="Ketik nama produk untuk mencari..."
                            autocomplete="off"
                        />
                        <div class="autocomplete-list" id="autocompleteList"></div>
                    </div>
                    <div class="selected-badge" id="selectedBadge">
                        <span>✓</span>
                        <span id="selectedName"></span>
                        <button type="button" class="clear-btn" id="clearBtn" title="Hapus pilihan">✕</button>
                    </div>
                </div>

                <!-- Jumlah Keluar -->
                <div class="form-group">
                    <label>Jumlah Keluar <span class="required">*</span></label>
                    <input type="number" name="jumlah" min="1" required
                           class="form-input" placeholder="Masukkan jumlah barang keluar" />
                </div>

                <!-- Keterangan -->
                <div class="form-group">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan"
                           class="form-input" placeholder="Opsional — contoh: Pengiriman ke toko cabang" />
                </div>

                <button type="submit" class="btn-submit">
                    − Tambah Barang Keluar
                </button>

            </form>

            <a href="dashboard_alfin.php" class="bk-back">← Kembali ke Dashboard</a>

        </div>
    </div>
</div>

<script>
// Data produk dari PHP
const produkData = <?php echo json_encode($produkList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

const searchInput   = document.getElementById('produkSearch');
const hiddenInput   = document.getElementById('id_produk');
const dropdownList  = document.getElementById('autocompleteList');
const selectedBadge = document.getElementById('selectedBadge');
const selectedName  = document.getElementById('selectedName');
const clearBtn      = document.getElementById('clearBtn');

let activeIndex = -1;

// Highlight kata yang cocok
function highlight(text, query) {
    if (!query) return text;
    const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>');
}

// Render dropdown
function renderList(query) {
    const q = query.trim().toLowerCase();
    const filtered = q
        ? produkData.filter(p => p.nama_produk_alfin.toLowerCase().includes(q))
        : produkData;

    dropdownList.innerHTML = '';
    activeIndex = -1;

    if (filtered.length === 0) {
        dropdownList.innerHTML = '<div class="autocomplete-no-result">Produk tidak ditemukan</div>';
    } else {
        filtered.forEach((p, idx) => {
            const stok = parseInt(p.stok_alfin);
            const isEmpty = stok === 0;
            const isLow   = stok > 0 && stok <= 5;

            const item = document.createElement('div');
            item.className = 'autocomplete-item' + (isEmpty ? ' stok-kosong' : '');
            item.dataset.id   = p.id_produk_alfin;
            item.dataset.name = p.nama_produk_alfin;
            item.dataset.idx  = idx;

            const stockClass = isLow ? 'item-stock low' : 'item-stock';
            const stockLabel = isEmpty ? 'Stok habis' : `Stok: ${stok.toLocaleString('id-ID')}`;

            item.innerHTML = `
                <span class="item-name">${highlight(p.nama_produk_alfin, query)}</span>
                <span class="${stockClass}">${stockLabel}</span>
            `;

            if (!isEmpty) {
                item.addEventListener('mousedown', () => selectItem(p));
            }
            dropdownList.appendChild(item);
        });
    }

    dropdownList.classList.add('visible');
}

// Pilih produk
function selectItem(p) {
    hiddenInput.value = p.id_produk_alfin;
    searchInput.value = '';
    searchInput.classList.add('has-value');
    selectedName.textContent = p.nama_produk_alfin;
    selectedBadge.classList.add('visible');
    dropdownList.classList.remove('visible');
}

// Clear pilihan
function clearSelection() {
    hiddenInput.value = '';
    searchInput.value = '';
    searchInput.classList.remove('has-value');
    selectedBadge.classList.remove('visible');
    searchInput.focus();
}

// Keyboard navigation
function updateActive(items) {
    items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
    if (activeIndex >= 0) items[activeIndex].scrollIntoView({ block: 'nearest' });
}

searchInput.addEventListener('input', () => renderList(searchInput.value));
searchInput.addEventListener('focus', () => renderList(searchInput.value));

searchInput.addEventListener('keydown', (e) => {
    const items = [...dropdownList.querySelectorAll('.autocomplete-item:not(.stok-kosong)')];
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(activeIndex + 1, items.length - 1);
        updateActive(items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        updateActive(items);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (activeIndex >= 0) {
            const el = items[activeIndex];
            selectItem({ id_produk_alfin: el.dataset.id, nama_produk_alfin: el.dataset.name });
        }
    } else if (e.key === 'Escape') {
        dropdownList.classList.remove('visible');
    }
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.autocomplete-wrapper')) {
        dropdownList.classList.remove('visible');
    }
});

clearBtn.addEventListener('click', clearSelection);

// Validasi: pastikan produk dipilih sebelum submit
document.getElementById('formBarangKeluar').addEventListener('submit', (e) => {
    if (!hiddenInput.value) {
        e.preventDefault();
        searchInput.focus();
        searchInput.style.borderColor = '#ef4444';
        searchInput.style.boxShadow = '0 0 0 3px rgba(239,68,68,0.15)';
        setTimeout(() => {
            searchInput.style.borderColor = '';
            searchInput.style.boxShadow = '';
        }, 2000);
    }
});
</script>

</body>
</html>
