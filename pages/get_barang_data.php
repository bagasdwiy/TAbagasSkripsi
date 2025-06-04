<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

// Parameters dari DataTables
$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];
$search = $_POST['search']['value'];
$order_column = $_POST['order'][0]['column'];
$order_dir = $_POST['order'][0]['dir'];

// Mapping kolom untuk ordering
$columns = array(
    0 => 'b.nama_barang',
    1 => 'b.barcode',
    2 => 'b.harga',
    3 => 'b.stok',
    4 => 's.nama_supplier'
);

// Base query
$query = "FROM barang b 
          LEFT JOIN supplier s ON b.id_supplier = s.id";

// Where clause
$where = "";
if (!empty($search)) {
    $where = " WHERE b.nama_barang LIKE ? 
               OR b.barcode LIKE ?
               OR b.harga LIKE ?
               OR b.stok LIKE ?
               OR s.nama_supplier LIKE ?";
}

// Count total records
$sql_count = "SELECT COUNT(*) as count " . $query;
$count_stmt = $conn->prepare($sql_count);
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['count'];

// Count filtered records
if (!empty($where)) {
    $sql_count_filtered = "SELECT COUNT(*) as count " . $query . $where;
    $count_filtered_stmt = $conn->prepare($sql_count_filtered);
    $search_param = "%$search%";
    $count_filtered_stmt->bind_param("sssss", 
        $search_param, $search_param, $search_param, $search_param, $search_param);
    $count_filtered_stmt->execute();
    $filtered_records = $count_filtered_stmt->get_result()->fetch_assoc()['count'];
} else {
    $filtered_records = $total_records;
}

// Fetch data
$sql = "SELECT b.*, s.nama_supplier " . $query . $where;

// Add ordering
if (isset($columns[$order_column])) {
    $sql .= " ORDER BY " . $columns[$order_column] . " " . $order_dir;
}

// Add limit
$sql .= " LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!empty($where)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssssii", 
        $search_param, $search_param, $search_param, $search_param, $search_param,
        $start, $length);
} else {
    $stmt->bind_param("ii", $start, $length);
}

$stmt->execute();
$result = $stmt->get_result();
$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = array(
        "id" => $row['id'],
        "nama_barang" => htmlspecialchars($row['nama_barang']),
        "barcode" => htmlspecialchars($row['barcode'] ?? ''),
        "harga" => $row['harga'],
        "stok" => $row['stok'],
        "nama_supplier" => htmlspecialchars($row['nama_supplier'] ?? '-')
    );
}

// Response
$response = array(
    "draw" => intval($draw),
    "recordsTotal" => intval($total_records),
    "recordsFiltered" => intval($filtered_records),
    "data" => $data
);

header('Content-Type: application/json');
echo json_encode($response); 