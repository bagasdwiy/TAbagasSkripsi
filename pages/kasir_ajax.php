<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

$barang = new Barang();
$transaksi = new Transaksi();

if ($_POST['action'] === 'cari') {
    $keyword = $_POST['keyword'];
    $result = $barang->search($keyword);
    
    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Barang tidak ditemukan']);
    }
}

elseif ($_POST['action'] === 'simpan') {
    try {
        $items = json_decode($_POST['items'], true);
        $total = $_POST['total'];
        $bayar = $_POST['bayar'];
        
        $id_transaksi = $transaksi->create([
            'id_user' => $_SESSION['user_id'], // atau 'user_id' => $_SESSION['user_id']
            'total' => $total,
            'bayar' => $bayar,
            'items' => $items
        ]);
        
        if ($id_transaksi) {
            echo json_encode(['success' => true, 'id_transaksi' => $id_transaksi]);
        } else {
            echo json_encode(['error' => 'Gagal menyimpan transaksi']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}