<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');
$pembeli = new Pembeli();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $data = [
                    'nama_pembeli' => $_POST['nama_pembeli'],
                    'no_telp' => $_POST['no_telp'],
                    'alamat' => $_POST['alamat']
                ];
                
                $result = $pembeli->create($data);
                echo json_encode($result);
                break;

            case 'update':
                $data = [
                    'nama_pembeli' => $_POST['nama_pembeli'],
                    'no_telp' => $_POST['no_telp'],
                    'alamat' => $_POST['alamat']
                ];
                
                $result = $pembeli->update($_POST['id'], $data);
                echo json_encode($result);
                break;

            case 'delete':
                $result = $pembeli->delete($_POST['id']);
                echo json_encode($result);
                break;

            case 'get_by_id':
                $result = $pembeli->getById($_POST['id']);
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
}
