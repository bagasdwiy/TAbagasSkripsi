<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    $query = "SELECT b.*, s.nama_supplier, 
              DATE_FORMAT(b.last_update_stok, '%d-%m-%Y %H:%i') as tanggal_update 
              FROM barang b 
              LEFT JOIN supplier s ON b.id_supplier = s.id";
              
    $result = $db->query($query);
    $data = [];
    $no = 1;

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'DT_RowIndex' => $no++,
            'nama_barang' => $row['nama_barang'],
            'barcode' => $row['barcode'],
            'harga' => number_format($row['harga'], 0, ',', '.'),
            'stok' => $row['stok'],
            'nama_supplier' => $row['nama_supplier'],
            'tanggal_update' => $row['tanggal_update'],
            'actions' => '<button onclick="editBarang('.$row['id'].')" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                         </button>
                         <button onclick="deleteBarang('.$row['id'].')" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                         </button>'
        ];
    }

    echo json_encode([
        'data' => $data
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}