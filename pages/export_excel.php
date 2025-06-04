<?php
require_once '../includes/init.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->setCellValue('A1', 'Laporan Transaksi');
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($tgl_mulai)) . ' - ' . date('d/m/Y', strtotime($tgl_selesai)));
$sheet->mergeCells('A2:I2');

// Table Header
$headers = ['No', 'Tanggal', 'No Transaksi', 'Pembeli', 'No. Telepon', 'Total', 'Bayar', 'Kembalian', 'Kasir'];
foreach($headers as $i => $header) {
    $sheet->setCellValueByColumnAndRow($i + 1, 4, $header);
}

// Table Data
$row = 5;
$no = 1;

while($data = $result->fetch_assoc()) {
    $kembalian = $data['bayar'] - $data['total'];
    
    $sheet->setCellValue('A'.$row, $no++);
    $sheet->setCellValue('B'.$row, date('d/m/Y H:i', strtotime($data['tanggal'])));
    $sheet->setCellValue('C'.$row, $data['id']);
    $sheet->setCellValue('D'.$row, $data['nama_pembeli'] ?: 'Umum');
    $sheet->setCellValue('E'.$row, $data['no_telp'] ?: '-');
    $sheet->setCellValue('F'.$row, $data['total']);
    $sheet->setCellValue('G'.$row, $data['bayar']);
    $sheet->setCellValue('H'.$row, $kembalian);
    $sheet->setCellValue('I'.$row, getUserName($data['user_id']));
    
    // Format angka
    $sheet->getStyle('F'.$row)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('G'.$row)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('H'.$row)->getNumberFormat()->setFormatCode('#,##0');
    
    $row++;
}

// Format
$sheet->getStyle('A1:I1')->getFont()->setBold(true);
$sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A4:I4')->getFont()->setBold(true);
$sheet->getStyle('A4:I'.$row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('F5:H'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Auto size columns
foreach(range('A','I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="laporan_transaksi.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); 