<?php
// 1. SET TIMEZONE GLOBAL (Mencakup semua file yang include koneksi ini)
date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db = "db_taalfin"; // Sesuaikan dengan nama DB Anda

$koneksiAlfin = mysqli_connect($host, $user, $pass, $db);

if (!$koneksiAlfin) {
	die("Koneksi gagal: " . mysqli_connect_error());
}
?>