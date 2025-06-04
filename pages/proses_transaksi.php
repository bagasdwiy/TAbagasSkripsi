<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->begin_transaction();
        
        $items = json_decode($_POST['items'], true);
        $total = $_POST['total'];
        $bayar = $_POST['bayar'];
        
        // Insert ke tabel transaksi
        $query = "INSERT INTO transaksi (total, bayar, tanggal) VALUES (?, ?, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bind_param("dd", $total, $bayar);
        $stmt->execute();
        
        $transaksi_id = $db->insert_id;
        
        // Insert detail transaksi dan update stok
        foreach ($items as $item) {
            // Insert detail transaksi
            $query = "INSERT INTO detail_transaksi (id_transaksi, id_barang, jumlah, harga, subtotal) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bind_param("iiidi", $transaksi_id, $item['id'], $item['jumlah'], 
                            $item['harga'], $item['subtotal']);
            $stmt->execute();
            
            // Update stok barang
            $query = "UPDATE barang SET stok = stok - ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bind_param("ii", $item['jumlah'], $item['id']);
            $stmt->execute();
        }
        
        $db->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 