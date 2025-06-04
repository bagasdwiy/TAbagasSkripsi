<?php
require_once '../includes/init.php';

if (!isAjax()) {
    die('Direct access not permitted');
}

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    switch ($action) {
        case 'check_stock':
            $barang_id = (int)$_POST['barang_id'];
            $barang = new Barang();
            $stock = $barang->getStock($barang_id);
            $response = ['success' => true, 'stock' => $stock];
            break;

        case 'get_notifications':
            $notifications = Notification::getInstance()->getNotifications($_SESSION['user_id']);
            $response = ['success' => true, 'notifications' => $notifications];
            break;

        case 'mark_notification_read':
            $notification_id = (int)$_POST['notification_id'];
            $result = Notification::getInstance()->markAsRead($notification_id);
            $response = ['success' => $result];
            break;

        case 'search_product':
            $keyword = $_POST['keyword'];
            $barang = new Barang();
            $results = $barang->search($keyword);
            $response = ['success' => true, 'results' => $results];
            break;

        case 'get_customer_history':
            $customer_id = (int)$_POST['customer_id'];
            $pembeli = new Pembeli();
            $history = $pembeli->getRiwayatTransaksi($customer_id);
            $response = ['success' => true, 'history' => $history];
            break;

        default:
            $response = ['success' => false, 'message' => 'Unknown action'];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response); 