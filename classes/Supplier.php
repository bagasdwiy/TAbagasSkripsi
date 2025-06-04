<?php
class Supplier {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM supplier ORDER BY nama_supplier";  // Sesuaikan dengan nama kolom
            return $this->db->query($query);
        } catch (Exception $e) {
            throw new Exception("Error mengambil data supplier: " . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM supplier WHERE id = ?";
            $result = $this->db->query($query, [$id]);
            return $result->fetch_assoc();
        } catch (Exception $e) {
            throw new Exception("Error mengambil detail supplier: " . $e->getMessage());
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO supplier (nama_supplier, alamat, telepon, email) 
                     VALUES (?, ?, ?, ?)";
            return $this->db->query($query, [
                $data['nama_supplier'],
                $data['alamat'],
                $data['telepon'],
                $data['email']
            ]);
        } catch (Exception $e) {
            throw new Exception("Error membuat supplier: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE supplier 
                     SET nama_supplier = ?, 
                         alamat = ?, 
                         telepon = ?, 
                         email = ?
                     WHERE id = ?";
            return $this->db->query($query, [
                $data['nama_supplier'],
                $data['alamat'],
                $data['telepon'],
                $data['email'],
                $id
            ]);
        } catch (Exception $e) {
            throw new Exception("Error mengupdate supplier: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM supplier WHERE id = ?";
            return $this->db->query($query, [$id]);
        } catch (Exception $e) {
            throw new Exception("Error menghapus supplier: " . $e->getMessage());
        }
    }
}
