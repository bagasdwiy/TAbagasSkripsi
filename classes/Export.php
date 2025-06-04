<?php
require_once '../vendor/autoload.php'; // Require PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Export {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function toExcel($data, $filename) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $columns = array_keys(reset($data));
        $col = 'A';
        foreach ($columns as $column) {
            $sheet->setCellValue($col . '1', strtoupper($column));
            $col++;
        }
        
        // Set data
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($item as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Create Excel file
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }

    public function toPDF($html, $filename) {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename . ".pdf", array("Attachment" => true));
    }

    public function exportLaporan($tanggal_awal, $tanggal_akhir, $format = 'excel') {
        $query = "SELECT t.tanggal, b.nama_barang, td.jumlah, td.harga, (td.jumlah * td.harga) as subtotal 
                 FROM transaksi t 
                 JOIN transaksi_detail td ON t.id = td.transaksi_id 
                 JOIN barang b ON td.barang_id = b.id 
                 WHERE t.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
                 ORDER BY t.tanggal DESC";
        
        $result = $this->db->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        if ($format == 'excel') {
            $this->toExcel($data, 'laporan_' . date('Y-m-d'));
        } else {
            $html = $this->generateLaporanHTML($data);
            $this->toPDF($html, 'laporan_' . date('Y-m-d'));
        }
    }

    private function generateLaporanHTML($data) {
        $html = '
        <html>
        <head>
            <style>
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid black; padding: 5px; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h2>Laporan Transaksi</h2>
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>';
        
        foreach ($data as $row) {
            $html .= "<tr>
                        <td>{$row['tanggal']}</td>
                        <td>{$row['nama_barang']}</td>
                        <td>{$row['jumlah']}</td>
                        <td>" . formatRupiah($row['harga']) . "</td>
                        <td>" . formatRupiah($row['subtotal']) . "</td>
                    </tr>";
        }
        
        $html .= '</table></body></html>';
        return $html;
    }
} 