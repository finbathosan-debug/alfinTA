<?php
include 'koneksi_alfin.php';

$sqlAlfin = "SELECT * FROM produk_alfin";
$resultAlfin = $koneksiAlfin->query($sqlAlfin);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Produk - alfinTA</title>
  <link rel="stylesheet" href="style_alfin.css">
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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
          $dataAlfin = mysqli_query($koneksiAlfin, "SELECT * FROM produk_alfin");
          while ($dAlfin = mysqli_fetch_array($dataAlfin)) {
            ?>
            <tr>
              <td><?php echo htmlspecialchars($dAlfin['id_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($dAlfin['nama_produk_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($dAlfin['harga_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($dAlfin['stok_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($dAlfin['kategori_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><svg id="barcode-<?php echo $dAlfin['id_produk_alfin']; ?>" style="width: 120px; height: 50px;"></svg></td>
              <td class="flex gap-10">
                <a href="edit_produk_alfin.php?id=<?php echo $dAlfin['id_produk_alfin']; ?>" class="btn-edit">Edit</a>
                <a href="confirm_delete_produk_alfin.php?id=<?php echo $dAlfin['id_produk_alfin']; ?>" class="btn-danger">Hapus</a>
              </td>
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
      $dataAlfin2 = mysqli_query($koneksiAlfin, "SELECT id_produk_alfin, barcode_alfin FROM produk_alfin");
      while ($d = mysqli_fetch_array($dataAlfin2)) {
        $barcodeValue = addslashes($d['barcode_alfin']);
        echo "JsBarcode('#barcode-" . $d['id_produk_alfin'] . "', '" . $barcodeValue . "', { format: 'CODE128', width: 1.5, height: 50, displayValue: false });";
      }
      ?>
    });
  </script>
</body>

</html>