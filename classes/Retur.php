<?php
class Retur {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        try {
            $this->db->beginTransaction();
    
            // Insert ke tabel retur
            $query = "INSERT INTO retur (id_supplier, user_id, keterangan) 
                     VALUES (?, ?, ?)";
            
            $result = $this->db->query($query, [
                $data['id_supplier'],
                $data['user_id'],
                $data['keterangan']
            ]);
    
            $id_retur = $this->db->lastInsertId();
    
            // Insert detail retur dan update stok
            foreach ($data['items'] as $key => $item) {
                // Upload foto jika ada
                $foto_path = '';
                if (!empty($_FILES['foto']['name'][$key])) {
                    $foto = [
                        'name' => $_FILES['foto']['name'][$key],
                        'type' => $_FILES['foto']['type'][$key],
                        'tmp_name' => $_FILES['foto']['tmp_name'][$key],
                        'error' => $_FILES['foto']['error'][$key],
                        'size' => $_FILES['foto']['size'][$key]
                    ];
                    $foto_path = $this->uploadFoto($foto);
                }
    
                // Insert detail retur
                $query = "INSERT INTO detail_retur 
                         (id_retur, id_barang, jumlah, alasan, foto) 
                         VALUES (?, ?, ?, ?, ?)";
                
                $result = $this->db->query($query, [
                    $id_retur,
                    $item['id_barang'],
                    $item['jumlah'],
                    $item['alasan'],
                    $foto_path
                ]);
    
                // Update stok barang
                $query_update = "UPDATE barang 
                               SET stok = stok - ? 
                               WHERE id = ?";
                
                $this->db->query($query_update, [
                    $item['jumlah'],
                    $item['id_barang']
                ]);
            }
    
            $this->db->commit();
            return $id_retur;
    
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE retur 
                     SET status = ?
                     WHERE id = ?";
            
            $this->db->query($query, [$status, $id]);
            
            if ($this->db->affected_rows() == 0) {
                throw new Exception("Retur tidak ditemukan atau status tidak berubah");
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error mengupdate status: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $query = "SELECT r.*, 
                            s.nama_supplier as nama_supplier,
                            u.username as nama_user,
                            COUNT(d.id) as total_items
                     FROM retur r
                     LEFT JOIN supplier s ON r.id_supplier = s.id
                     LEFT JOIN users u ON r.user_id = u.id
                     LEFT JOIN detail_retur d ON r.id = d.id_retur
                     GROUP BY r.id, r.tanggal, r.id_supplier, r.user_id, r.status, r.keterangan, 
                              s.nama_supplier, u.username
                     ORDER BY r.tanggal DESC";
            
            return $this->db->query($query);
        } catch (Exception $e) {
            throw new Exception("Error mengambil data retur: " . $e->getMessage());
        }
    }

    public function getDetailById($id) {
        try {
            // Ambil data retur
            $query = "SELECT r.*, 
                            s.nama_supplier,
                            u.username as nama_user
                     FROM retur r
                     LEFT JOIN supplier s ON r.id_supplier = s.id
                     LEFT JOIN users u ON r.user_id = u.id
                     WHERE r.id = ?";
            
            $retur = $this->db->query($query, [$id])->fetch_assoc();
            
            if (!$retur) {
                throw new Exception("Retur tidak ditemukan");
            }
    
            // Ambil detail items
            $query = "SELECT d.*, 
                            b.nama_barang,
                            b.barcode
                     FROM detail_retur d
                     LEFT JOIN barang b ON d.id_barang = b.id
                     WHERE d.id_retur = ?";
            
            $items = [];
            $result = $this->db->query($query, [$id]);
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
    
            $retur['items'] = $items;
            return $retur;
    
        } catch (Exception $e) {
            throw new Exception("Error mengambil detail retur: " . $e->getMessage());
        }
    }

    private function uploadFoto($file) {
        try {
            // Validasi file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Error upload file");
            }
    
            if ($file['size'] > 5000000) {
                throw new Exception("File terlalu besar (maksimal 5MB)");
            }
    
            $allowed_types = ['image/jpeg', 'image/png'];
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception("Format file tidak didukung (hanya JPG dan PNG)");
            }
    
            // Buat direktori jika belum ada
            $target_dir = "../uploads/retur/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
    
            // Generate nama file baru
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
    
            // Pindahkan file
            if (!move_uploaded_file($file['tmp_name'], $target_file)) {
                throw new Exception("Gagal memindahkan file");
            }
    
            return $new_filename;
        } catch (Exception $e) {
            throw new Exception("Error upload foto: " . $e->getMessage());
        }
    }
}