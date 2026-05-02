<?php
include 'koneksi_alfin.php';

// Ambil nilai filter dari GET
$filterNama = isset($_GET['filter_nama']) ? trim($_GET['filter_nama']) : '';

// Buat query dengan filter
if (!empty($filterNama)) {
    $sqlAlfin = "SELECT * FROM produk_alfin WHERE nama_produk_alfin LIKE ?";
    $stmt = mysqli_prepare($koneksiAlfin, $sqlAlfin);
    $searchTerm = "%{$filterNama}%";
    mysqli_stmt_bind_param($stmt, 's', $searchTerm);
    mysqli_stmt_execute($stmt);
    $resultAlfin = mysqli_stmt_get_result($stmt);
} else {
    $sqlAlfin = "SELECT * FROM produk_alfin";
    $resultAlfin = $koneksiAlfin->query($sqlAlfin);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Produk - alfinTA</title>
  <link rel="stylesheet" href="style_alfin.css">
  <script src="js/JsBarcode.all.min.js"></script>
</head>

<body>
  <div class="container">
    <div style="margin-top: 20px;">
      <h2>Data Produk</h2>

      <?php if (isset($_GET['success']) && $_GET['success'] === 'delete'): ?>
        <div class="alert alert-success">
          <p>✅ Data produk berhasil dihapus!</p>
        </div>
      <?php endif; ?>

      <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
        <form method="GET" style="display: flex; gap: 10px; align-items: center; flex: 1;">
          <input type="text" name="filter_nama" placeholder="Cari nama produk..." value="<?php echo htmlspecialchars($filterNama); ?>" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
          <button type="submit" class="btn-primary" style="white-space: nowrap;">Cari</button>
          <?php if (!empty($filterNama)): ?>
            <a href="produk_alfin.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Reset</a>
          <?php endif; ?>
        </form>
      </div>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama Produk</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Kategori</th>
            <th>Barcode</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (!empty($filterNama)) {
              $dataAlfin = $resultAlfin;
          } else {
              $dataAlfin = mysqli_query($koneksiAlfin, "SELECT * FROM produk_alfin");
          }
          
          if (mysqli_num_rows($dataAlfin) > 0) {
              while ($dAlfin = mysqli_fetch_array($dataAlfin)) {
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($dAlfin['id_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($dAlfin['nama_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($dAlfin['harga_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($dAlfin['stok_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($dAlfin['kategori_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                      <svg id="barcode-<?php echo $dAlfin['id_produk_alfin']; ?>" style="width: 180px; height: 70px; display: block; margin: 0 auto;"></svg>
                      <div style="font-size: 12px; color: #475569; margin-top: 6px; word-break: break-all; text-align: center;"><?php echo htmlspecialchars($dAlfin['barcode_alfin'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </td>
                    <td class="flex gap-10" style="flex-wrap: wrap; align-items: center;">
                      <form action="edit_produk_alfin.php" method="GET" style="margin: 0;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($dAlfin['id_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn-edit">Edit</button>
                      </form>
                      <form action="confirm_delete_produk_alfin.php" method="GET" style="margin: 0;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($dAlfin['id_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn-danger">Hapus</button>
                      </form>
                    </td>
                  </tr>
                  <?php
              }
          } else {
              ?>
              <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada produk ditemukan</td>
              </tr>
              <?php
          }
          ?>
        </tbody>
      </table>

      <div style="margin-top: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
        <form action="tambah_produk_alfin.php" method="GET">
          <button type="submit" class="btn-primary" style="flex: 1;">+ Tambah Produk</button>
        </form>
        <form action="dashboard_alfin.php" method="GET">
          <button type="submit" class="btn-primary" style="flex: 1;">← Kembali ke Dashboard</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php
      if (!empty($filterNama)) {
          $dataAlfin2 = mysqli_prepare($koneksiAlfin, "SELECT id_produk_alfin, barcode_alfin FROM produk_alfin WHERE nama_produk_alfin LIKE ?");
          $searchTerm = "%{$filterNama}%";
          mysqli_stmt_bind_param($dataAlfin2, 's', $searchTerm);
          mysqli_stmt_execute($dataAlfin2);
          $dataAlfin2 = mysqli_stmt_get_result($dataAlfin2);
      } else {
          $dataAlfin2 = mysqli_query($koneksiAlfin, "SELECT id_produk_alfin, barcode_alfin FROM produk_alfin");
      }
      
      while ($d = mysqli_fetch_array($dataAlfin2)) {
        echo "JsBarcode('#barcode-" . $d['id_produk_alfin'] . "', " . json_encode($d['barcode_alfin']) . ", { format: 'CODE128', width: 2.2, height: 70, displayValue: false });";
      }
      ?>
    });

    function printBarcodeLabel(barcode, name) {
      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <html>
          <head>
            <title>Barcode Label - ${name}</title>
            <style>
              body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
              .label { display: inline-block; padding: 20px; border: 1px solid #333; }
              .barcode-text { margin-top: 12px; font-size: 18px; font-weight: 700; letter-spacing: 1px; }
            </style>
          </head>
          <body>
            <div class="label">
              <h2 style="margin: 0 0 12px 0;">${name}</h2>
              <svg id="printBarcode" style="width: 320px; height: 100px;"></svg>
              <div class="barcode-text">${barcode}</div>
            </div>
            <script src="js/JsBarcode.all.min.js"><\/script>
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                JsBarcode('#printBarcode', ${JSON.stringify(barcode)}, { format: 'CODE128', width: 2.5, height: 100, displayValue: false });
                window.print();
              });
            <\/script>
          </body>
        </html>
      `);
      printWindow.document.close();
    }
  </script>
</body>

</html>