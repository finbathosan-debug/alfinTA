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

// --- QUERY SUMMARY ---
$queryBarangTerjual = "SELECT SUM(dt.jumlah_alfin) as total_barang FROM transaksi_alfin t JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin " . $dateFilter;
$resultBarang = mysqli_query($koneksiAlfin, $queryBarangTerjual);
$totalBarangTerjual = mysqli_fetch_assoc($resultBarang)['total_barang'] ?? 0;

$queryTotalTransaksi = "SELECT COUNT(*) as total_transaksi FROM transaksi_alfin t " . $dateFilter;
$resultTransaksi = mysqli_query($koneksiAlfin, $queryTotalTransaksi);
$totalTransaksi = mysqli_fetch_assoc($resultTransaksi)['total_transaksi'] ?? 0;

$queryTotalPendapatan = "SELECT SUM(t.total_alfin) as total_pendapatan FROM transaksi_alfin t " . $dateFilter;
$resultPendapatan = mysqli_query($koneksiAlfin, $queryTotalPendapatan);
$totalPendapatan = mysqli_fetch_assoc($resultPendapatan)['total_pendapatan'] ?? 0;

$queryTotalDiskon = "SELECT SUM(t.diskon_alfin) as total_diskon FROM transaksi_alfin t " . $dateFilter;
$resultDiskon = mysqli_query($koneksiAlfin, $queryTotalDiskon);
$totalDiskon = mysqli_fetch_assoc($resultDiskon)['total_diskon'] ?? 0;

// --- QUERY: AMBIL SELURUH TRANSAKSI + HARGA JUAL ---
$querySemuaTransaksi = "
    SELECT 
        t.id_transaksi_alfin, 
        t.tanggal_alfin, 
        t.total_alfin, 
        t.diskon_alfin,
        SUM(dt.jumlah_alfin) as total_barang,
        GROUP_CONCAT(p.nama_produk_alfin SEPARATOR ', ') as daftar_produk,
        GROUP_CONCAT(p.harga_alfin SEPARATOR ',') as daftar_harga
    FROM transaksi_alfin t 
    LEFT JOIN detail_transaksi_alfin dt ON t.id_transaksi_alfin = dt.id_transaksi_alfin 
    LEFT JOIN produk_alfin p ON dt.id_produk_alfin = p.id_produk_alfin
    " . $dateFilter . "
    GROUP BY t.id_transaksi_alfin, t.tanggal_alfin, t.total_alfin, t.diskon_alfin
    ORDER BY t.tanggal_alfin DESC
";
$resultSemuaTransaksi = mysqli_query($koneksiAlfin, $querySemuaTransaksi);

// Membuat Class FPDF Kustom untuk Footer
class PDF extends FPDF {
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(128,128,128);
        $this->Cell(0,10,'Dicetak pada: ' . date('d/m/Y H:i') . ' WIB | Halaman '.$this->PageNo().' dari {nb}',0,0,'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);

// --- KOP SURAT ---
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(37, 99, 235); // Warna Biru Primary
$pdf->Cell(0, 8, 'TOKO ALFIN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(50, 50, 50);
$pdf->Cell(0, 5, 'Sistem Kasir & Manajemen Penjualan (alfinTA)', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 5, 'Jl. Pakuhaji No. 123, Kota Cimahi | Telp: 0812-3456-7890', 0, 1, 'C');
$pdf->Ln(3);

// Garis Pemisah Kop
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// --- JUDUL LAPORAN ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(15, 23, 42); // Dark slate
$pdf->Cell(0, 8, 'LAPORAN DAFTAR TRANSAKSI', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 116, 139);
$pdf->Cell(0, 5, 'Periode: ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate)), 0, 1, 'C');
$pdf->Ln(8);

// --- 1. RINGKASAN STATISTIK ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(241, 245, 249); // Background abu muda
$pdf->SetTextColor(15, 23, 42);
$pdf->Cell(0, 8, '  RINGKASAN STATISTIK', 0, 1, 'L', true);
$pdf->Ln(3);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 6, 'Total Barang Terjual', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, ': ' . number_format($totalBarangTerjual, 0, ',', '.') . ' Item', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 6, 'Total Transaksi', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, ': ' . number_format($totalTransaksi, 0, ',', '.') . ' Transaksi', 0, 1);

// --- 2. TABEL DAFTAR SELURUH TRANSAKSI ---
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(241, 245, 249);
$pdf->SetTextColor(15, 23, 42);
$pdf->Cell(0, 8, '  DAFTAR SELURUH TRANSAKSI', 0, 1, 'L', true);
$pdf->Ln(2);

// Header Tabel Transaksi (Lebar total 180mm)
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(37, 99, 235); // Background header tabel biru
$pdf->SetTextColor(255, 255, 255); // Teks putih
$pdf->SetDrawColor(200, 200, 200); // Garis abu-abu
$pdf->SetLineWidth(0.2);

$pdf->Cell(6, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(12, 8, 'Struk', 1, 0, 'C', true);
$pdf->Cell(20, 8, 'Waktu', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'Nama Produk', 1, 0, 'C', true);
$pdf->Cell(23, 8, 'Hrg Jual(Rp)', 1, 0, 'C', true);
$pdf->Cell(10, 8, 'Qty', 1, 0, 'C', true);
$pdf->Cell(23, 8, 'Awal (Rp)', 1, 0, 'C', true);
$pdf->Cell(23, 8, 'Diskon(Rp)', 1, 0, 'C', true);
$pdf->Cell(28, 8, 'Total (Rp)', 1, 1, 'C', true);

// Isi Tabel Transaksi
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(50, 50, 50);
$no = 1;

if (mysqli_num_rows($resultSemuaTransaksi) > 0) {
    while ($row = mysqli_fetch_assoc($resultSemuaTransaksi)) {
        $pdf->Cell(6, 7, $no++, 1, 0, 'C');
        $pdf->Cell(12, 7, '#' . $row['id_transaksi_alfin'], 1, 0, 'C');
        
        // Menampilkan Tanggal dan Jam
        $waktu_tr = strtotime($row['tanggal_alfin']);
        $tampil_waktu = (date('H:i', $waktu_tr) != '00:00') ? date('d/m/y H:i', $waktu_tr) : date('d/m/y', $waktu_tr);
        $pdf->Cell(20, 7, $tampil_waktu, 1, 0, 'C');
        
        // Nama produk
        $daftar_produk = !empty($row['daftar_produk']) ? $row['daftar_produk'] : '-';
        if(strlen($daftar_produk) > 20) {
            $daftar_produk = substr($daftar_produk, 0, 17) . '...';
        }
        $pdf->Cell(35, 7, $daftar_produk, 1, 0, 'L');
        
        // Harga Jual
        $daftar_harga_str = '-';
        if (!empty($row['daftar_harga'])) {
            $harga_arr = explode(',', $row['daftar_harga']);
            $harga_formatted = array_map(function($h) {
                return number_format((int)$h, 0, ',', '.');
            }, $harga_arr);
            $daftar_harga_str = implode(', ', $harga_formatted);
        }
        if(strlen($daftar_harga_str) > 13) {
            $daftar_harga_str = substr($daftar_harga_str, 0, 11) . '..';
        }
        $pdf->SetTextColor(14, 165, 233); 
        $pdf->Cell(23, 7, $daftar_harga_str, 1, 0, 'R');
        $pdf->SetTextColor(50, 50, 50); 
        
        // Jumlah Item
        $pdf->Cell(10, 7, $row['total_barang'], 1, 0, 'C');

        // Menghitung Harga Awal
        $harga_awal = $row['total_alfin'] + $row['diskon_alfin'];
        $pdf->Cell(23, 7, number_format($harga_awal, 0, ',', '.'), 1, 0, 'R');
        
        // Menampilkan Diskon
        if ($row['diskon_alfin'] > 0) {
            $pdf->SetTextColor(220, 38, 38); 
            $pdf->Cell(23, 7, '-' . number_format($row['diskon_alfin'], 0, ',', '.'), 1, 0, 'R');
            $pdf->SetTextColor(50, 50, 50); 
        } else {
            $pdf->SetTextColor(150, 150, 150); 
            $pdf->Cell(23, 7, '-', 1, 0, 'C');
            $pdf->SetTextColor(50, 50, 50); 
        }
        
        // Total Belanja
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(21, 128, 61); 
        $pdf->Cell(28, 7, number_format($row['total_alfin'], 0, ',', '.'), 1, 1, 'R');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(50, 50, 50);
    }

    // ==========================================
    // BARIS GRAND TOTAL DI BAWAH TABEL DATA
    // ==========================================
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(241, 245, 249); // Warna background abu-abu muda
    $pdf->SetTextColor(15, 23, 42); // Warna teks dark blue
    
    // Lebar kolom 1 sampai 6 digabungkan = 6 + 12 + 20 + 35 + 23 + 10 = 106mm
    $pdf->Cell(106, 8, 'GRAND TOTAL KESELURUHAN', 1, 0, 'R', true);
    
    // Total Harga Awal Keseluruhan (Pendapatan Bersih + Diskon)
    $totalAwalSemua = $totalPendapatan + $totalDiskon;
    $pdf->Cell(23, 8, number_format($totalAwalSemua, 0, ',', '.'), 1, 0, 'R', true);
    
    // Total Diskon Keseluruhan
    $pdf->SetTextColor(220, 38, 38); // Merah untuk diskon
    $pdf->Cell(23, 8, '-' . number_format($totalDiskon, 0, ',', '.'), 1, 0, 'R', true);
    
    // Total Pendapatan Bersih Keseluruhan
    $pdf->SetTextColor(21, 128, 61); // Hijau untuk pendapatan
    $pdf->Cell(28, 8, number_format($totalPendapatan, 0, ',', '.'), 1, 1, 'R', true);
    
    $pdf->SetTextColor(50, 50, 50); // Kembalikan ke warna default

} else {
    $pdf->Cell(180, 8, 'Tidak ada data transaksi pada periode ini.', 1, 1, 'C');
}

// ==========================================
// BAGIAN TANDA TANGAN (KANAN BAWAH)
// ==========================================
$pdf->Ln(15); // Jarak vertikal dari tabel ke tanda tangan

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(15, 23, 42);

// Total lebar area adalah 180mm.
// Kita buat kolom ttd lebar 60mm di kanan, jadi digeser 120mm ke kanan.
$pdf->Cell(120); 
$pdf->Cell(60, 5, 'Cimahi, ' . date('d/m/Y'), 0, 1, 'C'); // Tanggal Cetak

$pdf->Cell(120);
$pdf->Cell(60, 5, 'Mengetahui, Alpin', 0, 1, 'C');

$pdf->Ln(20); // Spasi kosong untuk tempat tanda tangan

$pdf->Cell(120);
$pdf->SetFont('Arial', 'B', 10);

// MENGAMBIL USERNAME DARI SESSION LOGIN
$username_admin = isset($_SESSION['username_alfin']) ? $_SESSION['username_alfin'] : 'Alpin';

$pdf->Cell(60, 5, '( ' . strtoupper($username_admin) . ' )', 0, 1, 'C');

$pdf->Output('laporan_transaksi_' . $startDate . '_sampai_' . $endDate . '.pdf', 'I');
?>