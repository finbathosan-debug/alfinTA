<?php
include 'koneksi_alfin.php';

// Ambil nilai filter dari GET
$filterNama = isset($_GET['filter_nama']) ? trim($_GET['filter_nama']) : '';

// PAGINATION - 5 data per halaman
$perPage = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Hitung total data
if (!empty($filterNama)) {
  $sqlCount = "SELECT COUNT(*) as total FROM produk_alfin WHERE nama_produk_alfin LIKE ?";
  $stmtCount = mysqli_prepare($koneksiAlfin, $sqlCount);
  $searchTerm = "%{$filterNama}%";
  mysqli_stmt_bind_param($stmtCount, 's', $searchTerm);
  mysqli_stmt_execute($stmtCount);
  $resultCount = mysqli_stmt_get_result($stmtCount);
  $totalRows = mysqli_fetch_assoc($resultCount)['total'];
  mysqli_stmt_close($stmtCount);
  
  $sqlAlfin = "SELECT * FROM produk_alfin WHERE nama_produk_alfin LIKE ? LIMIT ?, ?";
  $stmt = mysqli_prepare($koneksiAlfin, $sqlAlfin);
  mysqli_stmt_bind_param($stmt, 'sii', $searchTerm, $offset, $perPage);
  mysqli_stmt_execute($stmt);
  $resultAlfin = mysqli_stmt_get_result($stmt);
  mysqli_stmt_close($stmt);
} else {
  $sqlCount = "SELECT COUNT(*) as total FROM produk_alfin";
  $resultCount = $koneksiAlfin->query($sqlCount);
  $totalRows = mysqli_fetch_assoc($resultCount)['total'];
  
  $sqlAlfin = "SELECT * FROM produk_alfin LIMIT $offset, $perPage";
  $resultAlfin = $koneksiAlfin->query($sqlAlfin);
}
// Hitung total halaman
$totalPages = ceil($totalRows / $perPage);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Produk - alfinTA</title>
  <script src="js/JsBarcode.all.min.js"></script>
  
  <style>
    :root {
      --primary: #6366f1;
      --primary-hover: #4f46e5;
      --secondary: #f1f5f9;
      --secondary-hover: #e2e8f0;
      --danger: #ef4444;
      --danger-hover: #dc2626;
      --warning: #f59e0b;
      --warning-hover: #d97706;
      --success: #10b981;
      --info: #0ea5e9;
      --info-hover: #0284c7;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --border: #e2e8f0;
      --bg-body: #f8fafc;
      --card-bg: #ffffff;
      --radius: 12px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
      background-color: var(--bg-body);
      font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      color: var(--text-main);
      line-height: 1.6;
    }

    .app-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px;
    }

    /* Header Section */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }
    .page-title {
      font-size: 28px;
      font-weight: 700;
      color: var(--primary);
    }
    .page-subtitle {
      font-size: 14px;
      color: var(--text-muted);
      margin-top: 5px;
    }

    /* Card Panels */
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px;
      margin-bottom: 25px;
    }
    
    .panel-card {
      background: var(--card-bg);
      border-radius: var(--radius);
      padding: 20px;
      border: 1px solid var(--border);
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
      position: relative;
    }

    .panel-title {
      font-size: 15px;
      font-weight: 600;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Forms & Inputs */
    .form-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    
    /* Buttons */
    .btn {
      padding: 10px 18px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
      white-space: nowrap;
    }
    .btn-primary { background: var(--primary); color: white; }
    .btn-primary:hover { background: var(--primary-hover); }
    
    .btn-secondary { background: var(--secondary); color: var(--text-main); border: 1px solid var(--border); }
    .btn-secondary:hover { background: var(--secondary-hover); }
    
    .btn-info { background: var(--info); color: white; }
    .btn-info:hover { background: var(--info-hover); }
    
    .btn-outline-info { background: transparent; color: var(--info); border: 1px solid var(--info); }
    .btn-outline-info:hover { background: var(--info); color: white; }

    .btn-sm { padding: 6px 12px; font-size: 13px; border-radius: 6px; }
    .btn-warning { background: var(--warning); color: white; }
    .btn-warning:hover { background: var(--warning-hover); }
    .btn-danger { background: var(--danger); color: white; }
    .btn-danger:hover { background: var(--danger-hover); }

    /* Floating Search Results */
    .dropdown-results {
      position: absolute;
      top: 100%;
      left: 20px;
      right: 20px;
      z-index: 50;
      background: white;
      border: 1px solid var(--info);
      border-radius: 8px;
      margin-top: 5px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      max-height: 250px;
      overflow-y: auto;
      display: none;
    }
    .dropdown-item {
      padding: 12px 15px;
      border-bottom: 1px solid var(--border);
      cursor: pointer;
      transition: background 0.2s;
    }
    .dropdown-item:hover { background: #f0f9ff; }
    .dropdown-item:last-child { border-bottom: none; }

    /* Table Styling */
    .table-container {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
      border: 1px solid var(--border);
      overflow-x: auto;
      margin-bottom: 25px;
    }
    .modern-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 800px;
    }
    .modern-table th {
      background: #f8fafc;
      color: var(--text-muted);
      font-weight: 600;
      font-size: 13px;
      text-transform: uppercase;
      padding: 15px;
      text-align: left;
      border-bottom: 2px solid var(--border);
    }
    .modern-table td {
      padding: 15px;
      vertical-align: middle;
      border-bottom: 1px solid var(--border);
      font-size: 14px;
    }
    .modern-table tbody tr:hover { background: #fcfcfc; }
    
    /* Badges */
    .badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 700;
      display: inline-block;
    }
    .badge-success { background: #dcfce3; color: #15803d; }
    .badge-danger { background: #fee2e2; color: #b91c1c; }

    /* Barcode Box */
    .barcode-box {
      background: white;
      padding: 5px;
      border-radius: 6px;
      border: 1px solid var(--border);
      display: inline-block;
      text-align: center;
    }

    /* Pagination */
    .pagination-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      margin-bottom: 40px;
    }
    .pagination {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .page-link {
      padding: 8px 14px;
      background: white;
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text-main);
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.2s;
    }
    .page-link:hover:not(.disabled) {
      border-color: var(--primary);
      color: var(--primary);
    }
    .page-link.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    .page-link.disabled {
      opacity: 0.5;
      cursor: not-allowed;
      background: var(--bg-body);
    }

    /* Alert */
    .alert {
      padding: 15px;
      border-radius: 8px;
      background: #dcfce3;
      color: #15803d;
      border: 1px solid #86efac;
      margin-bottom: 20px;
      font-weight: 500;
    }
  </style>
</head>

<body>
  <div class="app-container">
    
    <div class="page-header">
      <div>
        <h2 class="page-title">📦 Kelola Produk</h2>
        <p class="page-subtitle">Manajemen data produk, pemantauan stok, dan cetak barcode pintar.</p>
      </div>
      <a href="dashboard_alfin.php" class="btn btn-secondary">← Kembali ke Dashboard</a>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'delete'): ?>
      <div class="alert">
        ✅ <strong>Berhasil!</strong> Data produk telah dihapus dari sistem.
      </div>
    <?php endif; ?>

    <div class="dashboard-grid">
      
      <div class="panel-card" style="border-top: 4px solid var(--primary);">
        <div class="panel-title" style="color: var(--primary);">🔍 Cari Produk</div>
        <form method="GET" action="" style="display: flex; gap: 10px;">
          <input type="text" name="filter_nama" class="form-control" placeholder="Ketik nama produk..." value="<?php echo htmlspecialchars($filterNama); ?>">
          <button type="submit" class="btn btn-primary">Cari</button>
          <?php if (!empty($filterNama)): ?>
            <a href="produk_alfin.php" class="btn btn-secondary">Reset</a>
          <?php endif; ?>
        </form>
      </div>

      <div class="panel-card" style="border-top: 4px solid var(--info); background: #f8fafc;">
        <div class="panel-title" style="color: var(--info);">🖨️ Cetak Barcode (Pilih Produk)</div>
        <div style="display: flex; gap: 10px;">
          <input type="text" id="barcodeToSearch" class="form-control" style="border-color: #bae6fd;" placeholder="Cari untuk dicetak...">
          <button type="button" onclick="printAllBarcodes()" class="btn btn-outline-info">Massal</button>
        </div>
        <div id="barcodeSearchResults" class="dropdown-results"></div>
      </div>

      <div class="panel-card" style="border-top: 4px solid var(--success); display: flex; flex-direction: column; justify-content: center;">
        <div class="panel-title" style="color: var(--success); margin-bottom: 10px;">✨ Produk Baru</div>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Tambahkan item baru ke dalam inventaris minimarket.</p>
        <a href="tambah_produk_alfin.php" class="btn btn-primary" style="background: var(--success); width: 100%;">+ Tambah Data Produk</a>
      </div>

    </div>

    <div class="table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th style="width: 60px; text-align: center;">ID</th>
            <th>Nama Produk</th>
            <th>Harga Jual</th>
            <th style="text-align: center;">Stok</th>
            <th style="text-align: center;">Barcode ID</th>
            <th style="text-align: center; width: 160px;">Tindakan</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (mysqli_num_rows($resultAlfin) > 0) {
            while ($dAlfin = mysqli_fetch_array($resultAlfin)) {
              // Logika Badge Stok (Merah jika <= 5)
              $stokSisa = (int)$dAlfin['stok_alfin'];
              $stokClass = $stokSisa <= 5 ? 'badge-danger' : 'badge-success';
              ?>
              <tr>
                <td style="text-align: center; color: var(--text-muted); font-weight: 600;">
                  <?php echo htmlspecialchars($dAlfin['id_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?>
                </td>
                
                <td>
                  <strong style="color: var(--text-main); font-size: 15px; display: block; margin-bottom: 4px;">
                    <?php echo htmlspecialchars($dAlfin['nama_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?>
                  </strong>
                  <span style="font-size: 12px; color: var(--text-muted); background: var(--bg-body); padding: 3px 8px; border-radius: 4px;">
                    Kategori: <?php echo htmlspecialchars($dAlfin['kategori_alfin'], ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </td>
                
                <td style="font-weight: 700; color: var(--success); font-size: 15px;">
                  Rp <?php echo number_format($dAlfin['harga_alfin'], 0, ',', '.'); ?>
                </td>
                
                <td style="text-align: center;">
                  <span class="badge <?php echo $stokClass; ?>" title="Sisa Stok Saat Ini">
                    <?php echo $stokSisa; ?> Unit
                  </span>
                </td>
                
                <td style="text-align: center;">
                  <div class="barcode-box">
                    <svg id="barcode-<?php echo $dAlfin['id_produk_alfin']; ?>" style="width: 110px; height: 35px; display: block; margin: 0 auto;"></svg>
                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px; font-family: monospace; font-weight: bold;">
                      <?php echo htmlspecialchars($dAlfin['barcode_alfin'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                  </div>
                </td>
                
                <td style="text-align: center;">
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    <a href="edit_produk_alfin.php?id=<?php echo $dAlfin['id_produk_alfin']; ?>" class="btn btn-sm btn-warning">✏️ Edit</a> 
                    <a href="confirm_delete_produk_alfin.php?id=<?php echo $dAlfin['id_produk_alfin']; ?>" class="btn btn-sm btn-danger">🗑️ Hapus</a>
                  </div>
                </td>
              </tr>
              <?php
            }
          } else {
            ?>
            <tr>
              <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 24px; margin-bottom: 10px;">📦</div>
                <em>Tidak ada data produk yang ditemukan dalam sistem.</em>
              </td>
            </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalRows > 0): ?>
      <div class="pagination-wrapper">
        <div class="pagination">
          
          <?php if ($page > 1): ?>
            <?php
            $params = $_GET;
            $params['page'] = $page - 1;
            $urlPrev = '?' . http_build_query($params);
            ?>
            <a href="<?php echo $urlPrev; ?>" class="page-link">← Prev</a>
          <?php else: ?>
            <span class="page-link disabled">← Prev</span>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php
            $params = $_GET;
            $params['page'] = $i;
            $url = '?' . http_build_query($params);
            $activeClass = ($i == $page) ? 'active' : '';
            ?>
            <a href="<?php echo $url; ?>" class="page-link <?php echo $activeClass; ?>">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <?php
            $params = $_GET;
            $params['page'] = $page + 1;
            $urlNext = '?' . http_build_query($params);
            ?>
            <a href="<?php echo $urlNext; ?>" class="page-link">Next →</a>
          <?php else: ?>
            <span class="page-link disabled">Next →</span>
          <?php endif; ?>

        </div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
          Halaman <?php echo $page; ?> dari <?php echo $totalPages; ?> &bull; Menampilkan maksimal 5 data per halaman
        </div>
      </div>
    <?php endif; ?>

  </div>

  <script>
    // Fitur Search Dropdown Melayang
    const searchInput = document.getElementById('barcodeToSearch');
    const resultsDiv = document.getElementById('barcodeSearchResults');

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();

        if (keyword.length < 1) {
          resultsDiv.style.display = 'none';
          return;
        }

        const tableRows = document.querySelectorAll('.modern-table tbody tr');
        let html = '';

        tableRows.forEach(row => {
          // Ambil nama produk hanya dari element strong (tanpa kategori)
          const nameEl = row.cells[1]?.querySelector('strong');
          const namaProduk = nameEl ? nameEl.textContent.trim() : '';
          
          // Ambil barcode dari kolom 5 (cari di .barcode-box div yang bukan SVG)
          let barcode = '';
          const barcodeBox = row.cells[4]?.querySelector('.barcode-box');
          if (barcodeBox) {
            const divs = barcodeBox.querySelectorAll('div');
            divs.forEach(div => {
              if (div.textContent && div.textContent.trim() && !div.querySelector('svg')) {
                barcode = div.textContent.trim();
              }
            });
          }

          // Filter berdasarkan nama produk
          if (namaProduk.toLowerCase().includes(keyword)) {
            html += `<div class="dropdown-item" data-barcode="${barcode}" data-nama="${namaProduk}" style="cursor: pointer; padding: 12px; border-bottom: 1px solid #e2e8f0; hover-effect" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='white'">
              <strong style="color: #0ea5e9;">${namaProduk}</strong><br>
              <span style="color: #64748b; font-size: 12px; font-family: monospace;">📊 ${barcode || 'N/A'}</span>
            </div>`;
          }
        });

        if (html) {
          resultsDiv.innerHTML = html;
          resultsDiv.style.display = 'block';

          // Tambah event listener ke setiap dropdown item
          const dropdownItems = resultsDiv.querySelectorAll('.dropdown-item');
          dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
              e.stopPropagation();
              const barcode = this.getAttribute('data-barcode');
              const nama = this.getAttribute('data-nama');
              selectProductForPrint(barcode, nama);
            });
          });
        } else {
          resultsDiv.innerHTML = '<div style="padding: 15px; color: #ef4444; font-size: 13px; text-align: center;">❌ Produk tidak ditemukan</div>';
          resultsDiv.style.display = 'block';
        }
      });

      // Menutup dropdown saat klik di luar (tapi tidak menutup saat klik dropdown item)
      document.addEventListener('click', function(e) {
        if (e.target !== searchInput && !resultsDiv.contains(e.target)) {
          resultsDiv.style.display = 'none';
        }
      });
    }

    function selectProductForPrint(barcode, productName) {
      if (!barcode && !productName) {
        alert('⚠️ Data produk tidak valid!');
        return;
      }
      searchInput.value = productName;
      resultsDiv.style.display = 'none';
      printBarcodeModal(barcode, productName);
    }

    function printBarcodeModal(barcode, productName) {
      // Jika tidak ada parameter, ambil dari search input
      if (!barcode && !productName) {
        const searchValue = searchInput.value.trim().toLowerCase();
        
        if (!searchValue) {
          alert('⚠️ Silakan ketik nama produk terlebih dahulu!');
          return;
        }

        // Cari produk di tabel
        const tableRows = document.querySelectorAll('.modern-table tbody tr');
        let foundBarcode = '';
        let foundName = '';

        tableRows.forEach(row => {
          // Ambil nama produk hanya dari element strong (tanpa kategori)
          const nameEl = row.cells[1]?.querySelector('strong');
          const namaProduk = nameEl ? nameEl.textContent.trim() : '';
          
          if (namaProduk.toLowerCase().includes(searchValue)) {
            let barcode = '';
            const barcodeBox = row.cells[4]?.querySelector('.barcode-box');
            if (barcodeBox) {
              const divs = barcodeBox.querySelectorAll('div');
              divs.forEach(div => {
                if (div.textContent && div.textContent.trim() && !div.querySelector('svg')) {
                  barcode = div.textContent.trim();
                }
              });
            }

            foundBarcode = barcode;
            foundName = namaProduk;
          }
        });

        if (!foundName) {
          alert('⚠️ Produk "' + searchInput.value + '" tidak ditemukan di halaman ini!');
          return;
        }

        barcode = foundBarcode;
        productName = foundName;
      }

      // Prioritas: gunakan barcode jika ada, fallback ke nama produk
      if (barcode) {
        window.location.href = 'barcode_detail_alfin.php?barcode=' + encodeURIComponent(barcode) + '&nama=' + encodeURIComponent(productName);
      } else if (productName) {
        window.location.href = 'barcode_detail_alfin.php?nama=' + encodeURIComponent(productName);
      } else {
        alert('⚠️ Silakan ketik atau pilih produk terlebih dahulu!');
        return;
      }
    }

    function printAllBarcodes() {
      window.location.href = 'barcode_massal_alfin.php';
    }

    // Render Barcode SVG Otomatis di Tabel
    document.addEventListener('DOMContentLoaded', function () {
      <?php
      if (!empty($filterNama)) {
        $dataAlfin2 = mysqli_prepare($koneksiAlfin, "SELECT id_produk_alfin, barcode_alfin FROM produk_alfin WHERE nama_produk_alfin LIKE ? LIMIT ? OFFSET ?");
        $searchTerm = "%{$filterNama}%";
        mysqli_stmt_bind_param($dataAlfin2, 'sii', $searchTerm, $perPage, $offset);
        mysqli_stmt_execute($dataAlfin2);
        $dataAlfin2 = mysqli_stmt_get_result($dataAlfin2);
      } else {
        $dataAlfin2 = mysqli_query($koneksiAlfin, "SELECT id_produk_alfin, barcode_alfin FROM produk_alfin LIMIT $perPage OFFSET $offset");
      }

      while ($d = mysqli_fetch_array($dataAlfin2)) {
        echo "try { JsBarcode('#barcode-" . $d['id_produk_alfin'] . "', " . json_encode($d['barcode_alfin']) . ", { format: 'CODE128', width: 1.8, height: 35, margin: 0, displayValue: false }); } catch(e) { console.error('Gagal memuat barcode', e) };\n";
      }
      ?>
    });
  </script>
</body>
</html>