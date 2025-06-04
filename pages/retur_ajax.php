<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json');

$retur = new Retur();

if ($_POST['action'] == 'update_status') {
    try {
        if (empty($_POST['id']) || empty($_POST['status'])) {
            throw new Exception("Parameter tidak lengkap");
        }

        $allowed_status = ['pending', 'diproses', 'selesai', 'ditolak'];
        if (!in_array($_POST['status'], $allowed_status)) {
            throw new Exception("Status tidak valid");
        }

        $retur->updateStatus($_POST['id'], $_POST['status']);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
} 