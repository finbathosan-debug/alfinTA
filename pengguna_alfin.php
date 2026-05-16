<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: form_login_alfin.php");
  exit;
}

// Ambil nilai filter dari GET
$filterNama = isset($_GET['filter_nama']) ? trim($_GET['filter_nama']) : '';

// PAGINATION
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Hitung total data
if (!empty($filterNama)) {
  $sqlCount = "SELECT COUNT(*) as total FROM pengguna_alfin WHERE nama_pengguna_alfin LIKE ?";
  $stmtCount = mysqli_prepare($koneksiAlfin, $sqlCount);
  $searchTerm = "%{$filterNama}%";
  mysqli_stmt_bind_param($stmtCount, 's', $searchTerm);
  mysqli_stmt_execute($stmtCount);
  $resultCount = mysqli_stmt_get_result($stmtCount);
  $totalRows = mysqli_fetch_assoc($resultCount)['total'];
  mysqli_stmt_close($stmtCount);

  $sqlAlfin = "SELECT * FROM pengguna_alfin WHERE nama_pengguna_alfin LIKE ? LIMIT ?, ?";
  $stmt = mysqli_prepare($koneksiAlfin, $sqlAlfin);
  mysqli_stmt_bind_param($stmt, 'sii', $searchTerm, $offset, $perPage);
  mysqli_stmt_execute($stmt);
  $resultAlfin = mysqli_stmt_get_result($stmt);
} else {
  $sqlCount = "SELECT COUNT(*) as total FROM pengguna_alfin";
  $resultCount = mysqli_query($koneksiAlfin, $sqlCount);
  $totalRows = mysqli_fetch_assoc($resultCount)['total'];

  $sqlAlfin = "SELECT * FROM pengguna_alfin LIMIT $offset, $perPage";
  $resultAlfin = mysqli_query($koneksiAlfin, $sqlAlfin);
}

$totalPages = ceil($totalRows / $perPage);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pengguna - alfinTA</title>
  <link rel="stylesheet" href="style_alfin.css">

  <style>
    .user-header-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
      background: #f9fafb;
      padding: 15px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
    }

    .user-search-form {
      display: flex;
      gap: 10px;
      flex: 1;
      max-width: 400px;
    }

    .user-search-input {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s;
    }

    .user-search-input:focus {
      border-color: #6366f1;
    }

    .user-table-container {
      overflow-x: auto;
      background: white;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      margin-bottom: 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .user-styled-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 700px;
    }

    .user-styled-table thead tr {
      background-color: #f1f5f9;
      color: #475569;
      text-align: left;
      border-bottom: 2px solid #e2e8f0;
    }

    .user-styled-table th,
    .user-styled-table td {
      padding: 15px;
      border-bottom: 1px solid #e2e8f0;
      vertical-align: middle;
    }

    .user-styled-table tbody tr:hover {
      background-color: #f8fafc;
    }

    /* Badge Role */
    .role-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: inline-block;
    }

    .role-admin {
      background: #fee2e2;
      color: #ef4444;
    }

    .role-kasir {
      background: #dcfce3;
      color: #10b981;
    }

    /* Tombol Aksi */
    .action-btns {
      display: flex;
      gap: 8px;
    }

    .btn-sm {
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 13px;
      font-weight: bold;
      color: white;
      transition: opacity 0.2s;
    }

    .btn-sm:hover {
      opacity: 0.8;
    }

    .btn-edit {
      background-color: #f59e0b;
    }

    .btn-delete {
      background-color: #ef4444;
    }

    /* Paginasi */
    .user-pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 20px;
      margin-bottom: 30px;
    }

    .user-page-link {
      padding: 8px 14px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      text-decoration: none;
      color: #334155;
      font-weight: 600;
      transition: all 0.2s;
      background: white;
    }

    .user-page-link:hover {
      background: #f1f5f9;
    }

    .user-page-link.active {
      background: #6366f1;
      color: white;
      border-color: #6366f1;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2 style="margin-top: 20px; margin-bottom: 20px;">Kelola Pengguna</h2>

    <div class="user-header-actions">
      <form method="GET" action="" class="user-search-form">
        <input type="text" name="filter_nama" class="user-search-input" placeholder="Cari nama pengguna..."
          value="<?php echo htmlspecialchars($filterNama); ?>">
        <button type="submit" class="btn-primary" style="padding: 10px 20px;">Cari</button>
        <?php if (!empty($filterNama)): ?>
          <a href="pengguna_alfin.php" class="btn-secondary" style="padding: 10px 20px; text-decoration: none;">Reset</a>
        <?php endif; ?>
      </form>

      <div>
        <a href="tambah_pengguna_alfin.php" class="btn-primary"
          style="padding: 12px 20px; text-decoration: none; font-weight: bold;">+ Tambah Pengguna</a>
      </div>
    </div>

    <div class="user-table-container">
      <table class="user-styled-table">
        <thead>
          <tr>
            <th style="width: 50px; text-align: center;">No</th>
            <th>Nama Lengkap</th>
            <th>Username</th>
            <th>Role</th>
            <th style="text-align: center; width: 150px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (mysqli_num_rows($resultAlfin) > 0) {
            $no = $offset + 1;
            while ($d = mysqli_fetch_assoc($resultAlfin)) {
              // Penentuan warna badge role
              $roleClass = strtolower($d['role_alfin']) === 'admin' ? 'role-admin' : 'role-kasir';
              ?>
              <tr>
                <td style="text-align: center; color: #64748b; font-weight: bold;"><?php echo $no++; ?></td>
                <td><strong
                    style="color: #0f172a; font-size: 15px;"><?php echo htmlspecialchars($d['nama_pengguna_alfin']); ?></strong>
                </td>
                <td style="color: #475569;">@<?php echo htmlspecialchars($d['username_alfin']); ?></td>
                <td>
                  <span class="role-badge <?php echo $roleClass; ?>">
                    <?php echo htmlspecialchars($d['role_alfin']); ?>
                  </span>
                </td>
                <td>
                  <div class="action-btns" style="justify-content: center;">
                    <a href="edit_alfin.php?id=<?php echo $d['id_pengguna_alfin']; ?>" class="btn-sm btn-edit">Edit</a>
                    <a href="confirm_delete_pengguna_alfin.php?id=<?php echo $d['id_pengguna_alfin']; ?>"
                      class="btn-sm btn-delete">Hapus</a>
                  </div>
                </td>
              </tr>
              <?php
            }
          } else {
            ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 30px; color: #64748b; font-size: 15px;">
                <em>Tidak ada data pengguna ditemukan.</em>
              </td>
            </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="user-pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php
          $params = $_GET;
          $params['page'] = $i;
          $url = '?' . http_build_query($params);
          $activeClass = ($i == $page) ? 'active' : '';
          ?>
          <a href="<?php echo $url; ?>" class="user-page-link <?php echo $activeClass; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <div style="margin-bottom: 40px;">
      <a href="dashboard_alfin.php" class="btn-secondary"
        style="padding: 12px 20px; text-decoration: none; display: inline-block;">← Kembali ke Dashboard</a>
    </div>
  </div>
</body>

</html>