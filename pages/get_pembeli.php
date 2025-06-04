<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit('Unauthorized');
}

$pembeli = new Pembeli();
$result = $pembeli->getAll();

$data = [];
$no = 1;

while ($row = $result->fetch_assoc()) {
    $actions = '<button type="button" onclick="editPembeli('.$row['id'].')" class="btn btn-sm btn-primary">Edit</button> ';
    $actions .= '<button type="button" onclick="deletePembeli('.$row['id'].')" class="btn btn-sm btn-danger">Hapus</button>';
    
    $data[] = [
        'DT_RowIndex' => $no++,
        'nama_pembeli' => htmlspecialchars($row['nama_pembeli']),
        'no_telp' => htmlspecialchars($row['no_telp']),
        'alamat' => htmlspecialchars($row['alamat']),
        'actions' => $actions
    ];
}

header('Content-Type: application/json');
echo json_encode(['data' => $data]);