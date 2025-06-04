<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$db = Database::getInstance()->getConnection();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'search_barcode':
            case 'search_barang': // Tambahkan case untuk pencarian nama barang
                try {
                    $keyword = $_POST['barcode'] ?? $_POST['keyword'] ?? '';
                    error_log("Searching for: " . $keyword); // Debug log
                    
                    // Ubah query untuk mendukung pencarian dengan nama atau barcode
                    $query = "SELECT * FROM barang 
                             WHERE barcode LIKE ? 
                             OR nama_barang LIKE ? 
                             AND stok > 0";
                    
                    $search = "%{$keyword}%";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param("ss", $search, $search);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $data = [];
                        while ($row = $result->fetch_assoc()) {
                            $data[] = [
                                'id' => $row['id'],
                                'barcode' => $row['barcode'],
                                'nama_barang' => $row['nama_barang'],
                                'harga' => (float)$row['harga'],
                                'stok' => (int)$row['stok']
                            ];
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'data' => count($data) == 1 ? $data[0] : $data
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Barang tidak ditemukan'
                        ]);
                    }
                    
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
                break;

                case 'simpan_transaksi':
                    try {
                        $db->begin_transaction();
                
                        $total = $_POST['total'];
                        $bayar = $_POST['bayar'];
                        $id_pembeli = $_POST['id_pembeli'] ?: null; // Tambahkan ini
                        $items = json_decode($_POST['items'], true);
                
                        // Validasi
                        if (empty($items)) {
                            throw new Exception("Keranjang kosong");
                        }
                
                        if ($bayar < $total) {
                            throw new Exception("Pembayaran kurang");
                        }
                
                        // Insert transaksi
                        $query = "INSERT INTO transaksi (tanggal, total, bayar, jenis, user_id, id_pembeli) 
                                 VALUES (NOW(), ?, ?, 'jual', ?, ?)"; // Tambahkan id_pembeli
                        $stmt = $db->prepare($query);
                        $stmt->bind_param("ddii", 
                            $total, 
                            $bayar, 
                            $_SESSION['user_id'],
                            $id_pembeli // Tambahkan ini
                        );
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Gagal menyimpan transaksi");
                        }
                
                        $id_transaksi = $db->insert_id;
                
                        // Insert detail transaksi dan update stok
                        foreach ($items as $item) {
                            // Cek stok
                            $query = "SELECT stok FROM barang WHERE id = ?";
                            $stmt = $db->prepare($query);
                            $stmt->bind_param("i", $item['id_barang']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $barang = $result->fetch_assoc();
                
                            if (!$barang) {
                                throw new Exception("Barang tidak ditemukan");
                            }
                
                            if ($barang['stok'] < $item['jumlah']) {
                                throw new Exception("Stok tidak mencukupi");
                            }
                
                            // Insert detail
                            $query = "INSERT INTO detail_transaksi (id_transaksi, id_barang, jumlah, harga) 
                                     VALUES (?, ?, ?, ?)";
                            $stmt = $db->prepare($query);
                            $stmt->bind_param("iiid", 
                                $id_transaksi, 
                                $item['id_barang'], 
                                $item['jumlah'], 
                                $item['harga']
                            );
                            
                            if (!$stmt->execute()) {
                                throw new Exception("Gagal menyimpan detail transaksi");
                            }
                
                            // Update stok
                            $query = "UPDATE barang 
                                     SET stok = stok - ? 
                                     WHERE id = ?";
                            $stmt = $db->prepare($query);
                            $stmt->bind_param("ii", 
                                $item['jumlah'], 
                                $item['id_barang']
                            );
                            
                            if (!$stmt->execute()) {
                                throw new Exception("Gagal update stok");
                            }
                        }
                
                        $db->commit();
                
                        echo json_encode([
                            'success' => true,
                            'message' => 'Transaksi berhasil disimpan',
                            'id_transaksi' => $id_transaksi
                        ]);
                
                    } catch (Exception $e) {
                        $db->rollback();
                        error_log($e->getMessage());
                        echo json_encode([
                            'success' => false,
                            'message' => $e->getMessage()
                        ]);
                    }
                    break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
} 