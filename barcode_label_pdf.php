<?php
require 'fpdf/fpdf.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: form_login_alfin.php');
    exit;
}

$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';
$name = isset($_GET['name']) ? trim($_GET['name']) : 'Produk';

if ($barcode === '') {
    echo 'Barcode tidak tersedia.';
    exit;
}

$pdf = new FPDF('P', 'mm', [80, 80]);
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(8, 8, 8);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'LABEL BARCODE', 0, 1, 'C');
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(0, 6, substr($name, 0, 30), 0, 'C');
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, 'Kode: ' . $barcode, 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetFont('Courier', 'B', 18);
$pdf->Cell(0, 12, $barcode, 0, 1, 'C');

$pdf->Ln(4);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'alfinTA - Cetak Barcode', 0, 1, 'C');

$pdf->Output('barcode_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $barcode) . '.pdf', 'I');
