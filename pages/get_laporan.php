<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit('Unauthorized');
}

$db = Database::getInstance()->getConnection();

$tanggal_awal = $_POST['tanggal_awal'];
$tanggal_akhir = $_POST['tanggal_akhir'];

$query = "SELECT DATE(tanggal) as tanggal, 
          COUNT(*) as total_transaksi,
          SUM(total) as total_pendapatan
          FROM transaksi 
          WHERE DATE(tanggal) BETWEEN ? AND ?
          GROUP BY DATE(tanggal)
          ORDER BY tanggal DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("ss", $tanggal_awal, $tanggal_akhir);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$no = 1;
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "no" => $no++,
        "tanggal" => tanggalIndo($row['tanggal']),
        "total_transaksi" => $row['total_transaksi'],
        "total_pendapatan" => formatRupiah($row['total_pendapatan']),
        "id" => $row['tanggal']  // Ini akan mengirim format YYYY-MM-DD
    ];
}

echo json_encode([
    "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal" => count($data),
    "recordsFiltered" => count($data),
    "data" => $data
]); 