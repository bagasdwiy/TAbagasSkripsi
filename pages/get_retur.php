<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit('Unauthorized');
}

$db = Database::getInstance()->getConnection();

// Parameters dari DataTables
$draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
$start = isset($_POST['start']) ? $_POST['start'] : 0;
$length = isset($_POST['length']) ? $_POST['length'] : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Query untuk total records tanpa filter
$recordsTotalQuery = "SELECT COUNT(*) as total FROM retur";
$recordsTotalStmt = $db->prepare($recordsTotalQuery);
$recordsTotalStmt->execute();
$recordsTotalResult = $recordsTotalStmt->get_result();
$recordsTotal = $recordsTotalResult->fetch_assoc()['total'];

// Query untuk filtered records
$query = "SELECT r.*, b.nama_barang 
          FROM retur r 
          LEFT JOIN barang b ON r.id_barang = b.id
          WHERE b.nama_barang LIKE ? 
          OR r.alasan LIKE ? 
          OR r.status LIKE ?
          ORDER BY r.tanggal DESC
          LIMIT ?, ?";

// Hitung total filtered records
$searchTerm = "%$search%";
$countQuery = "SELECT COUNT(*) as total 
               FROM retur r 
               LEFT JOIN barang b ON r.id_barang = b.id
               WHERE b.nama_barang LIKE ? 
               OR r.alasan LIKE ? 
               OR r.status LIKE ?";

$countStmt = $db->prepare($countQuery);
$countStmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$countStmt->execute();
$countResult = $countStmt->get_result();
$recordsFiltered = $countResult->fetch_assoc()['total'];

// Get data
$stmt = $db->prepare($query);
$stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $start, $length);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Format tanggal
    $tanggal = date('d/m/Y H:i', strtotime($row['tanggal']));
    
    // Format status dengan badge
    $status_badge = '';
    switch($row['status']) {
        case 'pending':
            $status_badge = '<span class="badge badge-warning">Pending</span>';
            break;
        case 'disetujui':
            $status_badge = '<span class="badge badge-success">Disetujui</span>';
            break;
        case 'ditolak':
            $status_badge = '<span class="badge badge-danger">Ditolak</span>';
            break;
    }

    $data[] = [
        "id" => $row['id'],
        "tanggal" => $tanggal,
        "nama_barang" => htmlspecialchars($row['nama_barang']),
        "jumlah" => $row['jumlah'],
        "alasan" => htmlspecialchars($row['alasan']),
        "status" => $row['status'],
        "actions" => '
            <button onclick="editRetur(' . $row['id'] . ')" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteRetur(' . $row['id'] . ')" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i>
            </button>'
    ];
}

echo json_encode([
    "draw" => intval($draw),
    "recordsTotal" => intval($recordsTotal),
    "recordsFiltered" => intval($recordsFiltered),
    "data" => $data
]); 