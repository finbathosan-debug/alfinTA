<?php
// Koneksi database (gunakan nama variabel yang konsisten di seluruh aplikasi)
$koneksiAlfin = mysqli_connect("localhost", "root", "", "db_taalfin");

// Cek koneksi
if (!$koneksiAlfin) {
	die("Koneksi database gagal: " . mysqli_connect_error());
}

// Pastikan charset modern
mysqli_set_charset($koneksiAlfin, 'utf8mb4');
?>