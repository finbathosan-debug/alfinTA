<?php
// 1. SET TIMEZONE KE WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

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

$idTransaksi = (int) $_GET['id'];

// 2. Ambil data transaksi dengan penanganan waktu yang lebih baik
$queryTransaksi = mysqli_prepare($koneksiAlfin, "SELECT t.id_transaksi_alfin, t.tanggal_alfin, t.diskon_persen_alfin, t.diskon_alfin, t.total_alfin, p.nama_pengguna_alfin FROM transaksi_alfin t JOIN pengguna_alfin p ON t.id_pengguna_alfin = p.id_pengguna_alfin WHERE t.id_transaksi_alfin = ?");
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

// Custom Class FPDF untuk Struk
class StrukPDF extends FPDF
{
    function DrawZigZag($y, $top = true)
    {
        $this->SetDrawColor(200, 200, 200);
        $w = 80;
        $step = 2;
        $h = 1.5;
        $currentX = 0;
        while ($currentX < $w) {
            if ($top) {
                $this->Line($currentX, $y, $currentX + ($step / 2), $y + $h);
                $this->Line($currentX + ($step / 2), $y + $h, $currentX + $step, $y);
            } else {
                $this->Line($currentX, $y, $currentX + ($step / 2), $y - $h);
                $this->Line($currentX + ($step / 2), $y - $h, $currentX + $step, $y);
            }
            $currentX += $step;
        }
    }

    function Header()
    {
        $this->DrawZigZag(0, true);
        $this->Ln(3);
        $this->SetFont('Courier', 'B', 14);
        $this->Cell(0, 6, 'TOKO ALFIN', 0, 1, 'C');
        $this->SetFont('Courier', '', 8);
        $this->Cell(0, 4, 'Jl. Pakuhaji No. 123, Cimahi', 0, 1, 'C');
        $this->Cell(0, 4, 'Telp: 0812-3456-7890', 0, 1, 'C');
        $this->Cell(0, 4, '====================================', 0, 1, 'C');
        $this->Ln(1);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Courier', '', 8);
        $this->Cell(0, 4, '====================================', 0, 1, 'C');
        $this->Cell(0, 4, 'TERIMA KASIH ATAS KUNJUNGAN ANDA', 0, 1, 'C');
        $this->DrawZigZag($this->GetPageHeight(), false);
    }
}

// Tinggi dinamis
$pageHeight = 110 + (count($details) * 5);
if ($transaksi['diskon_alfin'] > 0) $pageHeight += 20;

$pdf = new StrukPDF('P', 'mm', array(80, $pageHeight));
$pdf->SetMargins(5, 2, 5);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// 3. Tampilkan Data Transaksi (Perbaikan Jam)
$pdf->SetFont('Courier', '', 8);
$pdf->Ln(1);
$pdf->Cell(20, 4, 'No. Struk :', 0, 0);
$pdf->Cell(0, 4, '#' . $transaksi['id_transaksi_alfin'], 0, 1);

$pdf->Cell(20, 4, 'Tanggal   :', 0, 0);
// Mengambil tanggal dan waktu SEKARANG secara langsung
$pdf->Cell(0, 4, date('d/m/Y H:i'), 0, 1);

$pdf->Cell(20, 4, 'Kasir     :', 0, 0);
$pdf->Cell(0, 4, strtoupper($transaksi['nama_pengguna_alfin'] ?? 'KASIR'), 0, 1);
$pdf->Ln(1);
$pdf->Cell(0, 4, '------------------------------------', 0, 1, 'C');

// ... (Looping Detail Barang Tetap Sama)
foreach ($details as $detail) {
    $nama = substr($detail['nama_produk_alfin'], 0, 20);
    $pdf->Cell(35, 4, $nama, 0, 0);
    $pdf->Cell(8, 4, $detail['jumlah_alfin'], 0, 0, 'C');
    $pdf->Cell(27, 4, number_format($detail['subtotal_alfin'], 0, ',', '.'), 0, 1, 'R');
}

$pdf->Ln(2);
$pdf->SetFont('Courier', 'B', 10);
$pdf->Cell(40, 7, 'TOTAL AKHIR', 0, 0, 'R');
$pdf->Cell(30, 7, 'Rp ' . number_format($transaksi['total_alfin'], 0, ',', '.'), 0, 1, 'R');

$pdf->Output('I', 'Struk_' . $transaksi['id_transaksi_alfin'] . '.pdf');