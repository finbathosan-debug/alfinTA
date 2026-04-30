<?php
require('fpdf/fpdf.php');

include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: transaksi_penjualan_alfin.php");
    exit;
}

$idTransaksi = (int)$_GET['id'];

// Ambil data transaksi
$queryTransaksi = mysqli_prepare($koneksiAlfin, "SELECT t.id_transaksi_alfin, t.tanggal_alfin, t.total_alfin, p.nama_pengguna_alfin FROM transaksi_alfin t JOIN pengguna_alfin p ON t.id_pengguna_alfin = p.id_pengguna_alfin WHERE t.id_transaksi_alfin = ?");
mysqli_stmt_bind_param($queryTransaksi, 'i', $idTransaksi);
mysqli_stmt_execute($queryTransaksi);
$resultTransaksi = mysqli_stmt_get_result($queryTransaksi);
$transaksi = mysqli_fetch_assoc($resultTransaksi);
mysqli_stmt_close($queryTransaksi);

if (!$transaksi) {
    header("Location: transaksi_penjualan_alfin.php");
    exit;
}

// Ambil detail transaksi
$queryDetail = mysqli_prepare($koneksiAlfin, "SELECT d.jumlah_alfin, d.subtotal_alfin, pr.nama_produk_alfin, pr.harga_alfin FROM detail_transaksi_alfin d JOIN produk_alfin pr ON d.id_produk_alfin = pr.id_produk_alfin WHERE d.id_transaksi_alfin = ?");
mysqli_stmt_bind_param($queryDetail, 'i', $idTransaksi);
mysqli_stmt_execute($queryDetail);
$resultDetail = mysqli_stmt_get_result($queryDetail);
$details = [];
while ($row = mysqli_fetch_assoc($resultDetail)) {
    $details[] = $row;
}
mysqli_stmt_close($queryDetail);

// Buat PDF
class StrukPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'STRUK BELANJA', 0, 1, 'C');
        $this->Ln(2);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, 'Terima Kasih Atas Kunjungan Anda!', 0, 1, 'C');
        $this->Cell(0, 5, 'alfinTA - Sistem Point of Sale', 0, 1, 'C');
    }
}

// Hitung tinggi kertas struk berdasarkan jumlah item
$rowHeight = 5;
$detailCount = count($details);
$pageHeight = max(110, 72 + ($detailCount * $rowHeight));

// Inisialisasi PDF
$pdf = new StrukPDF('P', 'mm', array(80, $pageHeight)); // Ukuran kertas thermal 80mm dengan tinggi dinamis
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

// Header struk
$pdf->Cell(0, 5, 'ID Transaksi: ' . $transaksi['id_transaksi_alfin'], 0, 1, 'L');
$pdf->Cell(0, 5, 'Tanggal: ' . date('d/m/Y H:i', strtotime($transaksi['tanggal_alfin'])), 0, 1, 'L');
$pdf->Cell(0, 5, 'Kasir: ' . $transaksi['nama_pengguna_alfin'], 0, 1, 'L');
$pdf->Ln(2);

// Garis pemisah
$pdf->Cell(0, 0, '', 'T', 1);
$pdf->Ln(1);

// Header tabel
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, 'Produk', 0, 0, 'L');
$pdf->Cell(10, 5, 'Qty', 0, 0, 'C');
$pdf->Cell(15, 5, 'Harga', 0, 0, 'R');
$pdf->Cell(15, 5, 'Total', 0, 1, 'R');

// Garis pemisah
$pdf->Cell(0, 0, '', 'T', 1);
$pdf->Ln(1);

// Detail produk
$pdf->SetFont('Arial', '', 8);
foreach ($details as $detail) {
    $namaProduk = substr($detail['nama_produk_alfin'], 0, 20); // Potong nama jika terlalu panjang
    $pdf->Cell(35, 4, $namaProduk, 0, 0, 'L');
    $pdf->Cell(10, 4, $detail['jumlah_alfin'], 0, 0, 'C');
    $pdf->Cell(15, 4, 'Rp ' . number_format($detail['harga_alfin'], 0, ',', '.'), 0, 0, 'R');
    $pdf->Cell(15, 4, 'Rp ' . number_format($detail['subtotal_alfin'], 0, ',', '.'), 0, 1, 'R');
}

$pdf->Ln(2);

// Total
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(60, 5, 'TOTAL:', 0, 0, 'R');
$pdf->Cell(15, 5, 'Rp ' . number_format($transaksi['total_alfin'], 0, ',', '.'), 0, 1, 'R');

// Output PDF
$pdf->Output('struk_' . $transaksi['id_transaksi_alfin'] . '.pdf', 'I');
?>