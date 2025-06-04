<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['barcode'])) {
    $db = Database::getInstance()->getConnection();
    $barcode = $db->real_escape_string($_POST['barcode']);
    
    $query = "SELECT * FROM barang WHERE barcode = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $barang = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'barang' => $barang
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Barang tidak ditemukan'
        ]);
    }
} 