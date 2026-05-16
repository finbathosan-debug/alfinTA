<?php
require 'fpdf/fpdf.php';
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: form_login_alfin.php');
    exit;
}

// 1. Ambil semua produk yang stoknya lebih dari 0
$query = "SELECT id_produk_alfin, nama_produk_alfin, barcode_alfin, stok_alfin FROM produk_alfin WHERE stok_alfin > 0 ORDER BY nama_produk_alfin ASC";
$result = mysqli_query($koneksiAlfin, $query);

if (!$result) {
    die('Error: ' . mysqli_error($koneksiAlfin));
}

if (mysqli_num_rows($result) == 0) {
    die("<div style='padding: 20px; font-family: sans-serif;'>Tidak ada data produk dengan stok > 0 untuk dicetak. <br><br><a href='produk_alfin.php'>Kembali</a></div>");
}

// ==========================================================
// PENGATURAN KERTAS A4 DENGAN SPASI & MARGIN SUPER AMAN
// ==========================================================
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Margin Kertas yang sangat aman (anti kepotong jepitan printer)
$marginLeft = 12.5;
$marginTop = 15.5;
$gapX = 3;
$gapY = 4;
$kolomMaksimal = 4;
$barisMaksimal = 9;
$lebarKotak = 44;
$tinggiKotak = 26;

$kolomSaatIni = 0;
$barisSaatIni = 0;
$itemCount = 0;

// 2. Lakukan perulangan untuk SETIAP produk di database
while ($row = mysqli_fetch_assoc($result)) {
    $barcode = trim($row['barcode_alfin']);
    $name = $row['nama_produk_alfin'];
    $jumlahStok = (int) $row['stok_alfin'];

    // Lewati jika produk tidak punya barcode atau stoknya minus/0
    if (empty($barcode) || $jumlahStok <= 0)
        continue;

    // ==========================================================
    // OPTIMASI: UNDUH GAMBAR API HANYA 1 KALI PER PRODUK
    // ==========================================================
    $urlBarcode = 'https://bwipjs-api.metafloor.com/?bcid=code128&text=' . urlencode($barcode) . '&scale=2&includetext=false';
    $tempBarcodeImage = 'temp_bc_' . uniqid() . '.png';

    $imageRendered = false;
    if ($imgData = @file_get_contents($urlBarcode)) {
        file_put_contents($tempBarcodeImage, $imgData);
        if (file_exists($tempBarcodeImage)) {
            $imageRendered = true;
        }
    }

    // 3. Lakukan perulangan cetak SEBANYAK JUMLAH STOK produk tersebut
    for ($i = 0; $i < $jumlahStok; $i++) {
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

        // Pasang Gambar Barcode
        if ($imageRendered) {
            $pdf->Image($tempBarcodeImage, $x + 4, $y + 6, 36, 12, 'PNG');
        } else {
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

        $itemCount++; // Tambah total perhitungan keseluruhan
    } // Akhir loop stok

    // Langsung hapus file gambar sementara setelah produk ini selesai diproses
    // agar memori hardisk tidak penuh
    if (file_exists($tempBarcodeImage)) {
        unlink($tempBarcodeImage);
    }
} // Akhir loop produk

// 4. Tampilkan Hasil PDF Massal
$pdf->Output('Cetak_Massal_Stok_Alfin.pdf', 'I');
?>