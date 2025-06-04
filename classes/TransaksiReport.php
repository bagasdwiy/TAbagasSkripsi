<?php
class TransaksiReport {
    private $db;
    private $tgl_mulai;
    private $tgl_selesai;

    public function __construct() {
        global $db;
        $this->db = $db;
        $this->tgl_mulai = date('Y-m-d');
        $this->tgl_selesai = date('Y-m-d');
    }

    public function setDateRange($start, $end) {
        $this->tgl_mulai = $start;
        $this->tgl_selesai = $end;
    }

    private function getData() {
        $query = "SELECT t.*, p.nama_pembeli, p.no_telp 
                 FROM transaksi t 
                 LEFT JOIN pembeli p ON t.id_pembeli = p.id 
                 WHERE t.jenis = 'jual' 
                 AND DATE(t.tanggal) BETWEEN ? AND ?
                 ORDER BY t.tanggal DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $this->tgl_mulai, $this->tgl_selesai);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function exportPDF() {
        require_once '../vendor/autoload.php';

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);

        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Laporan Transaksi', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 10, 'Periode: ' . date('d/m/Y', strtotime($this->tgl_mulai)) . ' - ' . date('d/m/Y', strtotime($this->tgl_selesai)), 0, 1, 'C');
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
        $result = $this->getData();
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

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(array_sum(array_slice($w, 0, 5)), 7, 'Total Penjualan:', 1, 0, 'R');
        $pdf->Cell($w[5], 7, formatRupiah($total_penjualan), 1, 0, 'R');
        $pdf->Cell(array_sum(array_slice($w, 6)), 7, '', 1, 1, 'C');

        $pdf->Output('laporan_transaksi.pdf', 'D');
    }

    public function exportExcel() {
        require_once '../vendor/autoload.php';
    
        $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Header
        $sheet->setCellValue('A1', 'Laporan Transaksi');
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($this->tgl_mulai)) . ' - ' . date('d/m/Y', strtotime($this->tgl_selesai)));
        $sheet->mergeCells('A2:I2');
    
        // Table Header
        $headers = ['No', 'Tanggal', 'No Transaksi', 'Pembeli', 'No. Telepon', 'Total', 'Bayar', 'Kembalian', 'Kasir'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        
        foreach($headers as $i => $header) {
            $sheet->setCellValue($columns[$i].'4', $header);
        }
    
        // Table Data
        $row = 5;
        $result = $this->getData();
        $no = 1;
        $total_penjualan = 0;
    
        while($data = $result->fetch_assoc()) {
            $kembalian = $data['bayar'] - $data['total'];
            $total_penjualan += $data['total'];
            
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
    
        // Total
        $lastRow = $row;
        $sheet->setCellValue('A'.$lastRow, 'Total Penjualan:');
        $sheet->mergeCells('A'.$lastRow.':E'.$lastRow);
        $sheet->setCellValue('F'.$lastRow, $total_penjualan);
        $sheet->getStyle('F'.$lastRow)->getNumberFormat()->setFormatCode('#,##0');
    
        // Format
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:I2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:I4')->getFont()->setBold(true);
        $sheet->getStyle('A4:I'.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('F5:H'.$lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    
        // Auto size columns
        foreach($columns as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    
        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="laporan_transaksi.xlsx"');
        header('Cache-Control: max-age=0');
    
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}