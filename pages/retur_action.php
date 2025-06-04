<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    exit('Unauthorized');
}

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            try {
                $db->begin_transaction(); // Mulai transaction
        
                $id_barang = $_POST['id_barang'];
                $jumlah = $_POST['jumlah'];
                $alasan = $_POST['alasan'];
        
                // Validasi input
                if (empty($id_barang) || empty($jumlah) || empty($alasan)) {
                    throw new Exception("Semua field harus diisi");
                }
        
                // Cek stok barang
                $check_stok = "SELECT stok FROM barang WHERE id = ?";
                $stmt_check = $db->prepare($check_stok);
                $stmt_check->bind_param("i", $id_barang);
                $stmt_check->execute();
                $result = $stmt_check->get_result();
                $barang = $result->fetch_assoc();
        
                if ($barang['stok'] < $jumlah) {
                    throw new Exception("Stok tidak mencukupi");
                }
        
                // Insert data retur
                $query = "INSERT INTO retur (id_barang, jumlah, alasan, tanggal, status) 
                         VALUES (?, ?, ?, NOW(), 'pending')";
                $stmt = $db->prepare($query);
                $stmt->bind_param("iis", $id_barang, $jumlah, $alasan);
                
                if ($stmt->execute()) {
                    // Update stok barang
                    $update_stok = "UPDATE barang SET stok = stok - ? WHERE id = ?";
                    $stmt_update = $db->prepare($update_stok);
                    $stmt_update->bind_param("ii", $jumlah, $id_barang);
                    
                    if ($stmt_update->execute()) {
                        $db->commit(); // Commit transaction
                        echo json_encode([
                            'success' => true,
                            'message' => 'Retur berhasil ditambahkan dan stok diperbarui'
                        ]);
                    } else {
                        throw new Exception("Gagal memperbarui stok");
                    }
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                $db->rollback(); // Rollback jika terjadi error
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menambahkan retur: ' . $e->getMessage()
                ]);
            }
            break;

        case 'get':
            try {
                $id = $_POST['id'];
                $query = "SELECT * FROM retur WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
                
                if ($data) {
                    echo json_encode([
                        'success' => true,
                        'data' => $data
                    ]);
                } else {
                    throw new Exception("Retur tidak ditemukan");
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        case 'update':
            try {
                $id = $_POST['id'];
                $id_barang = $_POST['id_barang'];
                $jumlah = $_POST['jumlah'];
                $alasan = $_POST['alasan'];
                $status = $_POST['status'];

                // Validasi input
                if (empty($id_barang) || empty($jumlah) || empty($alasan) || empty($status)) {
                    throw new Exception("Semua field harus diisi");
                }

                $query = "UPDATE retur 
                         SET id_barang = ?, jumlah = ?, alasan = ?, status = ? 
                         WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("iissi", $id_barang, $jumlah, $alasan, $status, $id);
                
                if ($stmt->execute()) {
                    // Jika status disetujui, kurangi stok barang
                    if ($status === 'disetujui') {
                        $updateStok = "UPDATE barang 
                                     SET stok = stok - ? 
                                     WHERE id = ?";
                        $stmtStok = $db->prepare($updateStok);
                        $stmtStok->bind_param("ii", $jumlah, $id_barang);
                        $stmtStok->execute();
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => 'Retur berhasil diupdate'
                    ]);
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal mengupdate retur: ' . $e->getMessage()
                ]);
            }
            break;

        case 'delete':
            try {
                $id = $_POST['id'];
                
                // Cek status retur sebelum menghapus
                $checkQuery = "SELECT status FROM retur WHERE id = ?";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bind_param("i", $id);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $retur = $result->fetch_assoc();

                if ($retur['status'] === 'disetujui') {
                    throw new Exception("Tidak dapat menghapus retur yang sudah disetujui");
                }

                $query = "DELETE FROM retur WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Retur berhasil dihapus'
                    ]);
                } else {
                    throw new Exception($stmt->error);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Gagal menghapus retur: ' . $e->getMessage()
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
} 