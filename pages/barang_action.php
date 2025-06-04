<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');
$barang = new Barang();

try {
    switch ($_POST['action']) {
        case 'create':
            $data = [
                'nama_barang' => $_POST['nama_barang'],
                'barcode' => $_POST['barcode'],
                'harga' => $_POST['harga'],
                'stok' => $_POST['stok'],
                'id_supplier' => $_POST['id_supplier']
            ];
            
            $result = $barang->create($data);
            echo json_encode($result);
            break;

        case 'get_by_barcode':
            $result = $barang->getByBarcode($_POST['barcode']);
            echo json_encode($result);
            break;

        case 'get_by_nama':
            $result = $barang->getByNama($_POST['nama_barang']);
            echo json_encode($result);
            break;

        case 'get_by_id':
            $result = $barang->getById($_POST['id']);
            echo json_encode($result);
            break;
            
        case 'delete':
            $result = $barang->delete($_POST['id']);
            echo json_encode($result);
            break;
        
        case 'update':
                $data = [
                    'nama_barang' => $_POST['nama_barang'],
                    'barcode' => $_POST['barcode'],
                    'harga' => $_POST['harga'],
                    'stok' => $_POST['stok'],
                    'id_supplier' => $_POST['id_supplier']
                ];
                
                $result = $barang->update($_POST['id'], $data);
                echo json_encode($result);
                break;
    

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}