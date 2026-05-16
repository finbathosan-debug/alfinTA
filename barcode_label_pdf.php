<?php
require 'fpdf/fpdf.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: form_login_alfin.php');
    exit;
}

// Tangkap parameter dari URL
$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';
$name = isset($_GET['name']) ? trim($_GET['name']) : 'Produk';
$qty = isset($_GET['qty']) ? (int) $_GET['qty'] : 1;

if ($qty < 1) {
    $qty = 1;
}

if ($barcode === '') {
    echo 'Barcode tidak tersedia.';
    exit;
}

// ==========================================================
// OPTIMASI: Unduh gambar dari API 1 KALI SAJA
// ==========================================================
$urlBarcode = 'https://bwipjs-api.metafloor.com/?bcid=code128&text=' . urlencode($barcode) . '&scale=2&includetext=false';
$tempBarcodeImage = 'temp_barcode_' . time() . '.png';
file_put_contents($tempBarcodeImage, file_get_contents($urlBarcode));


// ==========================================================
// PENGATURAN KERTAS A4 DENGAN SPASI & MARGIN SUPER AMAN
// ==========================================================
// Ukuran Kertas A4: Lebar 210mm x Tinggi 297mm
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Margin Kertas yang sangat aman dari jepitan printer
$marginLeft = 12.5;
$marginTop = 15.5;

// Jarak (Spasi) antar kotak barcode
$gapX = 3; // Spasi horizontal (menyamping)
$gapY = 4; // Spasi vertikal (ke bawah)

// Susunan: 4 ke samping, 9 ke bawah = 36 stiker per halaman
$kolomMaksimal = 4;
$barisMaksimal = 9;

// Ukuran 1 kotak stiker
$lebarKotak = 44;
$tinggiKotak = 26;

$kolomSaatIni = 0;
$barisSaatIni = 0;

for ($i = 0; $i < $qty; $i++) {
    // Jika 1 halaman penuh (36 barcode), buat lembar A4 baru
    if ($i > 0 && $i % ($kolomMaksimal * $barisMaksimal) == 0) {
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

    // 1. Teks Nama Produk (di atas)
    $pdf->SetXY($x, $y + 1);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell($lebarKotak, 4, substr($name, 0, 26), 0, 1, 'C');

    // 2. Gambar Barcode Asli (Posisinya di tengah kotak secara presisi)
    // Lebar gambar 36, lebar kotak 44 -> sisa 8 (dibagi 2 jadi margin sisinya 4)
    $pdf->Image($tempBarcodeImage, $x + 4, $y + 6, 36, 12, 'PNG');

    // 3. Angka Barcode (di bawah)
    $pdf->SetXY($x, $y + 19);
    $pdf->SetFont('Courier', 'B', 8);
    $pdf->Cell($lebarKotak, 4, $barcode, 0, 1, 'C');

    // Geser ke kolom sebelahnya
    $kolomSaatIni++;
    if ($kolomSaatIni >= $kolomMaksimal) {
        $kolomSaatIni = 0; // Balik ke kiri
        $barisSaatIni++;   // Turun 1 baris
    }
}

// Hapus file gambar sementara setelah selesai
if (file_exists($tempBarcodeImage)) {
    unlink($tempBarcodeImage);
}

// Tampilkan PDF
$pdf->Output('barcode_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $barcode) . '.pdf', 'I');
?>