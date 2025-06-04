<?php
class Pembeli {
    private $db;
    private $id;
    private $nama_pembeli;
    private $no_telp;
    private $alamat;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Setter methods
    public function setNamaPembeli($nama) {
        $this->nama_pembeli = $this->sanitize($nama);
    }
    
    public function setNoTelp($no_telp) {
        $this->no_telp = $this->sanitize($no_telp);
    }
    
    public function setAlamat($alamat) {
        $this->alamat = $this->sanitize($alamat);
    }
    
    // Getter methods
    public function getId() {
        return $this->id;
    }
    
    public function getNamaPembeli() {
        return $this->nama_pembeli;
    }
    
    public function getNoTelp() {
        return $this->no_telp;
    }
    
    public function getAlamat() {
        return $this->alamat;
    }
    
    // Helper method untuk sanitasi input
    private function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    
    // Method untuk validasi data
    private function validate() {
        if (empty($this->nama_pembeli)) {
            throw new Exception("Nama pembeli tidak boleh kosong");
        }
        if (empty($this->no_telp)) {
            throw new Exception("Nomor telepon tidak boleh kosong");
        }
        if (empty($this->alamat)) {
            throw new Exception("Alamat tidak boleh kosong");
        }
    }
    
    public function create($data) {
        try {
            $this->setNamaPembeli($data['nama_pembeli']);
            $this->setNoTelp($data['no_telp']);
            $this->setAlamat($data['alamat']);
            
            $this->validate();
            
            $query = "INSERT INTO pembeli (nama_pembeli, no_telp, alamat) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sss", 
                $this->nama_pembeli,
                $this->no_telp,
                $this->alamat
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Data pembeli berhasil ditambahkan'
                ];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan pembeli: ' . $e->getMessage()
            ];
        }
    }
    
    public function update($id, $data) {
        try {
            $this->id = $id;
            $this->setNamaPembeli($data['nama_pembeli']);
            $this->setNoTelp($data['no_telp']);
            $this->setAlamat($data['alamat']);
            
            $this->validate();
            
            $query = "UPDATE pembeli SET nama_pembeli = ?, no_telp = ?, alamat = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssi", 
                $this->nama_pembeli,
                $this->no_telp,
                $this->alamat,
                $this->id
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Data pembeli berhasil diupdate'
                ];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate pembeli: ' . $e->getMessage()
            ];
        }
    }
    
    public function delete($id) {
        try {
            $this->id = $id;
            $query = "DELETE FROM pembeli WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $this->id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Data pembeli berhasil dihapus'
                ];
            }
            throw new Exception($stmt->error);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghapus pembeli: ' . $e->getMessage()
            ];
        }
    }

    public function getById($id) {
        $this->id = $id;
        $query = "SELECT * FROM pembeli WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $this->setNamaPembeli($data['nama_pembeli']);
            $this->setNoTelp($data['no_telp']);
            $this->setAlamat($data['alamat']);
            
            return [
                'success' => true,
                'data' => [
                    'id' => $this->id,
                    'nama_pembeli' => $this->getNamaPembeli(),
                    'no_telp' => $this->getNoTelp(),
                    'alamat' => $this->getAlamat()
                ]
            ];
        }
        return [
            'success' => false,
            'message' => 'Pembeli tidak ditemukan'
        ];
    }
    
    public function getAll() {
        $query = "SELECT * FROM pembeli ORDER BY nama_pembeli ASC";
        return $this->db->query($query);
    }
} 