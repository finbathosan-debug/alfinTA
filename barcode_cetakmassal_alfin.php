<?php
require 'fpdf/fpdf.php';
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: form_login_alfin.php');
    exit;
}

// 1. Ambil SEMUA data produk dari database
$query = mysqli_query($koneksiAlfin, "SELECT * FROM produk_alfin ORDER BY id_produk_alfin DESC");

if (mysqli_num_rows($query) == 0) {
    die("<div style='padding: 20px; font-family: sans-serif;'>Tidak ada data produk untuk dicetak. <br><br><a href='produk_alfin.php'>Kembali</a></div>");
}

// ==========================================================
// PENGATURAN KERTAS A4 DENGAN SPASI & MARGIN SUPER AMAN
// ==========================================================
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Margin Kertas yang sangat aman
$marginLeft = 12.5;
$marginTop = 15.5;

// Jarak (Spasi) antar kotak barcode
$gapX = 3;
$gapY = 4;

// Susunan: 4 ke samping, 9 ke bawah = 36 stiker per halaman
$kolomMaksimal = 4;
$barisMaksimal = 9;

// Ukuran 1 kotak stiker
$lebarKotak = 44;
$tinggiKotak = 26;

$kolomSaatIni = 0;
$barisSaatIni = 0;
$itemCount = 0;

// 2. Lakukan perulangan untuk SETIAP produk di database
while ($row = mysqli_fetch_assoc($query)) {
    $barcode = trim($row['barcode_alfin']);
    $name = $row['nama_produk_alfin'];

    // Lewati jika produk tidak punya barcode
    if (empty($barcode))
        continue;

    // Jika 1 halaman penuh (36 barcode), buat lembar A4 baru
    if ($itemCount > 0 && $itemCount % ($kolomMaksimal * $barisMaksimal) == 0) {
        $pdf->AddPage();
        $kolomSaatIni = 0;
        $barisSaatIni = 0;
    }

    // Hitung posisi X dan Y dengan menambahkan GAP (Spasi)
    $x = $marginLeft + ($kolomSaatIni * ($lebarKotak + $gapX));
    $y = $marginTop + ($barisSaatIni * ($tinggiKotak + $gapY));

    // Buat garis kotak tipis pembatas
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Rect($x, $y, $lebarKotak, $tinggiKotak);

    // Teks Nama Produk (di atas)
    $pdf->SetXY($x, $y + 1);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell($lebarKotak, 4, substr($name, 0, 26), 0, 1, 'C');

    // ==========================================================
    // UNDUH & GAMBAR BARCODE DARI API
    // ==========================================================
    $urlBarcode = 'https://bwipjs-api.metafloor.com/?bcid=code128&text=' . urlencode($barcode) . '&scale=2&includetext=false';
    $tempBarcodeImage = 'temp_bc_' . uniqid() . '.png'; // Nama file unik

    // Kita gunakan teknik error control (@) agar kalau API gagal/lambat, aplikasi tidak error total
    $imageRendered = false;
    if ($imgData = @file_get_contents($urlBarcode)) {
        file_put_contents($tempBarcodeImage, $imgData);
        if (file_exists($tempBarcodeImage)) {
            // Pasang gambar ke PDF
            $pdf->Image($tempBarcodeImage, $x + 4, $y + 6, 36, 12, 'PNG');
            // Langsung hapus file gambar sementara agar memori server tidak penuh
            unlink($tempBarcodeImage);
            $imageRendered = true;
        }
    }

    // Jika gambar gagal dimuat (misal karena koneksi internet putus)
    if (!$imageRendered) {
        $pdf->SetXY($x, $y + 10);
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->Cell($lebarKotak, 4, '(API Timeout)', 0, 1, 'C');
    }

    // Angka Barcode (di bawah)
    $pdf->SetXY($x, $y + 19);
    $pdf->SetFont('Courier', 'B', 8);
    $pdf->Cell($lebarKotak, 4, $barcode, 0, 1, 'C');

    // Geser ke kolom sebelahnya
    $kolomSaatIni++;
    if ($kolomSaatIni >= $kolomMaksimal) {
        $kolomSaatIni = 0; // Balik ke kiri
        $barisSaatIni++;   // Turun 1 baris
    }

    $itemCount++; // Tambah total perhitungan
}

// 3. Tampilkan Hasil PDF Massal
$pdf->Output('Katalog_Barcode_Massal_Alfin.pdf', 'I');
?>