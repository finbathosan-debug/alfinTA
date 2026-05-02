<?php
include 'koneksi_alfin.php';

// Ambil nilai filter dari GET
$filterNama = isset($_GET['filter_nama']) ? trim($_GET['filter_nama']) : '';

// Buat query dengan filter
if (!empty($filterNama)) {
    $sqlAlfin = "SELECT * FROM pengguna_alfin WHERE nama_pengguna_alfin LIKE ?";
    $stmt = mysqli_prepare($koneksiAlfin, $sqlAlfin);
    $searchTerm = "%{$filterNama}%";
    mysqli_stmt_bind_param($stmt, 's', $searchTerm);
    mysqli_stmt_execute($stmt);
    $resultAlfin = mysqli_stmt_get_result($stmt);
} else {
    $sqlAlfin = "SELECT * FROM pengguna_alfin";
    $resultAlfin = $koneksiAlfin->query($sqlAlfin);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pengguna - alfinTA</title>
  <link rel="stylesheet" href="style_alfin.css">
</head>

<body>
  <div class="container">
    <div style="margin-top: 20px;">
      <h2>Data Pengguna</h2>

      <?php if (isset($_GET['success']) && $_GET['success'] === 'delete'): ?>
        <div class="alert alert-success">
          <p>✅ Data pengguna berhasil dihapus!</p>
        </div>
      <?php endif; ?>

      <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
        <form method="GET" style="display: flex; gap: 10px; align-items: center; flex: 1;">
          <input type="text" name="filter_nama" placeholder="Cari nama pengguna..." value="<?php echo htmlspecialchars($filterNama); ?>" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
          <button type="submit" class="btn-primary" style="white-space: nowrap;">Cari</button>
          <?php if (!empty($filterNama)): ?>
            <a href="pengguna_alfin.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Reset</a>
          <?php endif; ?>
        </form>
      </div>

      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (!empty($filterNama)) {
              $dataAlfin = $resultAlfin;
          } else {
              $dataAlfin = mysqli_query($koneksiAlfin, "SELECT * FROM pengguna_alfin");
          }
          
          if (mysqli_num_rows($dataAlfin) > 0) {
              while ($dAlfin = mysqli_fetch_array($dataAlfin)) {
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($dAlfin['id_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($dAlfin['nama_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($dAlfin['username_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($dAlfin['role_alfin'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="flex gap-10" style="flex-wrap: wrap; align-items: center;">
                      <form action="edit_alfin.php" method="GET" style="margin: 0;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($dAlfin['id_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn-edit">Edit</button>
                      </form>
                      <form action="confirm_delete_pengguna_alfin.php" method="GET" style="margin: 0;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($dAlfin['id_pengguna_alfin'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn-danger">Hapus</button>
                      </form>
                    </td>
                  </tr>
                  <?php
              }
          } else {
              ?>
              <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">Tidak ada pengguna ditemukan</td>
              </tr>
              <?php
          }
          ?>
        </tbody>
      </table>

      <div style="margin-top: 30px; display: flex; gap: 10px; flex-wrap: wrap;">
        <form action="tambah_pengguna_alfin.php" method="GET" style="margin: 0;">
          <button type="submit" class="btn-primary">+ Tambah Pengguna</button>
        </form>
        <form action="dashboard_alfin.php" method="GET" style="margin: 0;">
          <button type="submit" class="btn-secondary">← Kembali ke Dashboard</button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>