<?php
class Barang {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        try {
            // Cek apakah barang sudah ada berdasarkan barcode atau nama
            $check_query = "SELECT id FROM barang WHERE barcode = ? OR nama_barang = ?";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->bind_param("ss", $data['barcode'], $data['nama_barang']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                // Jika barang sudah ada, update stok
                $barang = $result->fetch_assoc();
                return $this->updateStok($barang['id'], $data);
            }

            // Jika barang belum ada, insert baru
            $query = "INSERT INTO barang (nama_barang, barcode, harga, stok, id_supplier, last_update_stok) 
                     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssdii", 
                $data['nama_barang'],
                $data['barcode'],
                $data['harga'],
                $data['stok'],
                $data['id_supplier']
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Barang baru berhasil ditambahkan'
                ];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateStok($id, $data) {
        try {
            $query = "UPDATE barang 
                     SET stok = stok + ?,
                         harga = ?,
                         id_supplier = ?,
                         last_update_stok = CURRENT_TIMESTAMP
                     WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("idii",
                $data['stok'],
                $data['harga'],
                $data['id_supplier'],
                $id
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Stok barang berhasil ditambahkan'
                ];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getByBarcode($barcode) {
        $query = "SELECT * FROM barang WHERE barcode = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => true,
                'data' => $result->fetch_assoc()
            ];
        }
        return [
            'success' => false,
            'message' => 'Barang tidak ditemukan'
        ];
    }

    public function getByNama($nama) {
        $query = "SELECT * FROM barang WHERE nama_barang = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => true,
                'data' => $result->fetch_assoc()
            ];
        }
        return [
            'success' => false,
            'message' => 'Barang tidak ditemukan'
        ];
    }

    public function getAll() {
        $query = "SELECT b.*, s.nama_supplier 
                 FROM barang b 
                 LEFT JOIN supplier s ON b.id_supplier = s.id";
        $result = $this->db->query($query);
        return $result;
    }
    public function getById($id) {
        $query = "SELECT * FROM barang WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => true,
                'data' => $result->fetch_assoc()
            ];
        }
        return [
            'success' => false,
            'message' => 'Barang tidak ditemukan'
        ];
    }
    
    public function delete($id) {
        try {
            $query = "DELETE FROM barang WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Barang berhasil dihapus'
                ];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
