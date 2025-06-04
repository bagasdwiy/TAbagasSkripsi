<?php
require_once '../includes/init.php';
require_once '../vendor/autoload.php';

if (!isLoggedIn()) {
    exit('Unauthorized');
}

$tgl_mulai = $_GET['start_date'] ?? date('Y-m-d');
$tgl_selesai = $_GET['end_date'] ?? date('Y-m-d');

// Query untuk data
$query = "SELECT t.*, p.nama_pembeli, p.no_telp 
          FROM transaksi t 
          LEFT JOIN pembeli p ON t.id_pembeli = p.id 
          WHERE t.jenis = 'jual' 
          AND DATE(t.tanggal) BETWEEN ? AND ?
          ORDER BY t.tanggal DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
$stmt->execute();
$result = $stmt->get_result();

// Buat PDF
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);

$pdf->AddPage();

// Header
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Laporan Transaksi', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 10, 'Periode: ' . date('d/m/Y', strtotime($tgl_mulai)) . ' - ' . date('d/m/Y', strtotime($tgl_selesai)), 0, 1, 'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('helvetica', 'B', 10);
$w = [10, 35, 25, 35, 25, 30, 30, 30, 25];
$header = ['No', 'Tanggal', 'No Transaksi', 'Pembeli', 'No. Telepon', 'Total', 'Bayar', 'Kembalian', 'Kasir'];

foreach($header as $i => $h) {
    $pdf->Cell($w[$i], 7, $h, 1, 0, 'C');
}
$pdf->Ln();

// Table Data
$pdf->SetFont('helvetica', '', 9);
$no = 1;
$total_penjualan = 0;

while($row = $result->fetch_assoc()) {
    $kembalian = $row['bayar'] - $row['total'];
    $total_penjualan += $row['total'];
    
    $pdf->Cell($w[0], 7, $no++, 1, 0, 'C');
    $pdf->Cell($w[1], 7, date('d/m/Y H:i', strtotime($row['tanggal'])), 1, 0, 'L');
    $pdf->Cell($w[2], 7, $row['id'], 1, 0, 'L');
    $pdf->Cell($w[3], 7, $row['nama_pembeli'] ?: 'Umum', 1, 0, 'L');
    $pdf->Cell($w[4], 7, $row['no_telp'] ?: '-', 1, 0, 'L');
    $pdf->Cell($w[5], 7, formatRupiah($row['total']), 1, 0, 'R');
    $pdf->Cell($w[6], 7, formatRupiah($row['bayar']), 1, 0, 'R');
    $pdf->Cell($w[7], 7, formatRupiah($kembalian), 1, 0, 'R');
    $pdf->Cell($w[8], 7, getUserName($row['user_id']), 1, 1, 'L');
}

$pdf->Output('laporan_transaksi.pdf', 'D'); 