<?php
require 'fpdf/fpdf.php';
session_start();
include 'koneksi_alfin.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: form_login_alfin.php');
    exit;
}

function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$defaultEndDate = date('Y-m-d');
$defaultStartDate = date('Y-m-d', strtotime('-6 days'));

$startDate = isset($_GET['start_date']) && isValidDate($_GET['start_date']) ? $_GET['start_date'] : $defaultStartDate;
$endDate = isset($_GET['end_date']) && isValidDate($_GET['end_date']) ? $_GET['end_date'] : $defaultEndDate;

if ($startDate > $endDate) {
    $temp = $startDate;
    $startDate = $endDate;
    $endDate = $temp;
}

$dateFilter = "WHERE DATE(t.tanggal_alfin) BETWEEN '" . $startDate . "' AND '" . $endDate . "'";

$queryBarangTerjual = "SELECT SUM(dt.jumlah_alfin) as total_barang FROM transaksi_alfin t JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin " . $dateFilter;
$resultBarang = mysqli_query($koneksiAlfin, $queryBarangTerjual);
$totalBarangTerjual = mysqli_fetch_assoc($resultBarang)['total_barang'] ?? 0;

$queryTotalTransaksi = "SELECT COUNT(*) as total_transaksi FROM transaksi_alfin t " . $dateFilter;
$resultTransaksi = mysqli_query($koneksiAlfin, $queryTotalTransaksi);
$totalTransaksi = mysqli_fetch_assoc($resultTransaksi)['total_transaksi'] ?? 0;

$queryTotalPendapatan = "SELECT SUM(t.total_alfin) as total_pendapatan FROM transaksi_alfin t " . $dateFilter;
$resultPendapatan = mysqli_query($koneksiAlfin, $queryTotalPendapatan);
$totalPendapatan = mysqli_fetch_assoc($resultPendapatan)['total_pendapatan'] ?? 0;

$queryDetailPenjualan = "
    SELECT
        p.nama_produk_alfin,
        p.kategori_alfin,
        SUM(dt.jumlah_alfin) as jumlah_terjual,
        SUM(dt.subtotal_alfin) as total_pendapatan_produk,
        COUNT(DISTINCT dt.id_transaksi_alfin) as jumlah_transaksi
    FROM detail_transaksi_alfin dt
    JOIN produk_alfin p ON dt.id_produk_alfin = p.id_produk_alfin
    JOIN transaksi_alfin t ON dt.id_transaksi_alfin = t.id_transaksi_alfin
    " . $dateFilter . "
    GROUP BY p.id_produk_alfin, p.nama_produk_alfin, p.kategori_alfin
    ORDER BY jumlah_terjual DESC
";
$resultDetail = mysqli_query($koneksiAlfin, $queryDetailPenjualan);

$queryPenjualanHarian = "
    SELECT
        DATE(t.tanggal_alfin) as tanggal,
        COUNT(t.id_transaksi_alfin) as jumlah_transaksi,
        SUM(t.total_alfin) as total_pendapatan,
        SUM(dt.jumlah_alfin) as total_barang
    FROM transaksi_alfin t
    LEFT JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin
    " . $dateFilter . "
    GROUP BY DATE(t.tanggal_alfin)
    ORDER BY tanggal DESC
";
$resultHarian = mysqli_query($koneksiAlfin, $queryPenjualanHarian);

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, 'Toko Alfin', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'alfinTA - Aplikasi Kasir & Laporan Penjualan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Jl. Pakuhaji No. 123, Kota Cimahi', 0, 1, 'C');
$pdf->Ln(5);

// Garis pemisah
$pdf->SetDrawColor(0, 0, 0);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'Laporan Penjualan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Periode: ' . date('d/m/Y', strtotime($startDate)) . ' - ' . date('d/m/Y', strtotime($endDate)), 0, 1, 'C');
$pdf->Cell(0, 6, 'Tanggal Cetak: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Ringkasan Statistik', 0, 1);
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Total Barang Terjual: ' . number_format($totalBarangTerjual, 0, ',', '.') . ' item', 0, 1);
$pdf->Cell(0, 6, 'Total Transaksi: ' . number_format($totalTransaksi, 0, ',', '.') . ' transaksi', 0, 1);
$pdf->Cell(0, 6, 'Total Pendapatan: Rp ' . number_format($totalPendapatan, 0, ',', '.'), 0, 1);
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Detail Penjualan per Produk', 0, 1);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(55, 6, 'Produk', 1, 0, 'L');
$pdf->Cell(25, 6, 'Terjual', 1, 0, 'C');
$pdf->Cell(25, 6, 'Transaksi', 1, 0, 'C');
$pdf->Cell(40, 6, 'Pendapatan', 1, 1, 'R');

$pdf->SetFont('Arial', '', 9);
while ($row = mysqli_fetch_assoc($resultDetail)) {
    $pdf->Cell(55, 6, substr($row['nama_produk_alfin'], 0, 25), 1, 0, 'L');
    $pdf->Cell(25, 6, number_format($row['jumlah_terjual'], 0, ',', '.'), 1, 0, 'C');
    $pdf->Cell(25, 6, number_format($row['jumlah_transaksi'], 0, ',', '.'), 1, 0, 'C');
    $pdf->Cell(40, 6, 'Rp ' . number_format($row['total_pendapatan_produk'], 0, ',', '.'), 1, 1, 'R');
}

$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Penjualan Harian', 0, 1);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, 'Tanggal', 1, 0, 'L');
$pdf->Cell(35, 6, 'Transaksi', 1, 0, 'C');
$pdf->Cell(35, 6, 'Barang', 1, 0, 'C');
$pdf->Cell(40, 6, 'Pendapatan', 1, 1, 'R');

$pdf->SetFont('Arial', '', 9);
while ($row = mysqli_fetch_assoc($resultHarian)) {
    $pdf->Cell(40, 6, date('d/m/Y', strtotime($row['tanggal'])), 1, 0, 'L');
    $pdf->Cell(35, 6, number_format($row['jumlah_transaksi'], 0, ',', '.'), 1, 0, 'C');
    $pdf->Cell(35, 6, number_format($row['total_barang'], 0, ',', '.'), 1, 0, 'C');
    $pdf->Cell(40, 6, 'Rp ' . number_format($row['total_pendapatan'], 0, ',', '.'), 1, 1, 'R');
}

$pdf->Output('laporan_penjualan_' . $startDate . '_sampai_' . $endDate . '.pdf', 'I');
