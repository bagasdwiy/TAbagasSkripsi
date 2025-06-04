<?php
class Transaksi {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Mengambil transaksi berdasarkan periode tanggal
     */
    public function getByPeriode($start_date, $end_date) {
        try {
            $query = "SELECT t.id, 
                            t.user_id,
                            t.total,
                            t.bayar,
                            t.tanggal,
                            u.username as nama_user,
                            COUNT(d.id) as total_items,
                            SUM(d.jumlah) as total_quantity
                     FROM transaksi t
                     LEFT JOIN users u ON t.user_id = u.id
                     LEFT JOIN detail_transaksi d ON t.id = d.id_transaksi
                     WHERE DATE(t.tanggal) >= ? 
                     AND DATE(t.tanggal) <= ?
                     GROUP BY t.id
                     ORDER BY t.tanggal DESC";
            
            return $this->db->query($query, [$start_date, $end_date]);
        } catch (Exception $e) {
            throw new Exception("Error mengambil data transaksi: " . $e->getMessage());
        }
    }

    /**
     * Membuat transaksi baru
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            // Validasi data
            if (empty($data['items'])) {
                throw new Exception("Tidak ada item dalam transaksi");
            }

            if (!isset($data['bayar']) || $data['bayar'] < $data['total']) {
                throw new Exception("Pembayaran kurang dari total");
            }

            // Insert ke tabel transaksi
            $query = "INSERT INTO transaksi (user_id, total, bayar, tanggal) 
                     VALUES (?, ?, ?, NOW())";
            
            $result = $this->db->query($query, [
                $data['user_id'],
                $data['total'],
                $data['bayar']
            ]);

            if (!$result) {
                throw new Exception("Gagal menyimpan transaksi");
            }

            $id_transaksi = $this->db->lastInsertId();

            // Insert ke tabel detail_transaksi
            foreach ($data['items'] as $item) {
                // Validasi stok
                $stok_query = "SELECT id, stok, nama_barang FROM barang WHERE id = ? FOR UPDATE";
                $stok_result = $this->db->query($stok_query, [$item['id']])->fetch_assoc();
                
                if (!$stok_result) {
                    throw new Exception("Barang dengan ID {$item['id']} tidak ditemukan");
                }

                if ($stok_result['stok'] < $item['jumlah']) {
                    throw new Exception("Stok tidak mencukupi untuk {$stok_result['nama_barang']}");
                }

                // Insert detail transaksi
                $query = "INSERT INTO detail_transaksi 
                         (id_transaksi, id_barang, jumlah, harga, subtotal) 
                         VALUES (?, ?, ?, ?, ?)";
                
                $result = $this->db->query($query, [
                    $id_transaksi,
                    $item['id'],
                    $item['jumlah'],
                    $item['harga'],
                    ($item['jumlah'] * $item['harga']) // Hitung ulang subtotal untuk keamanan
                ]);

                if (!$result) {
                    throw new Exception("Gagal menyimpan detail transaksi");
                }

                // Update stok barang
                $query = "UPDATE barang 
                         SET stok = stok - ?,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE id = ?";
                
                $result = $this->db->query($query, [
                    $item['jumlah'],
                    $item['id']
                ]);

                if (!$result) {
                    throw new Exception("Gagal update stok barang");
                }
            }

            $this->db->commit();
            return $id_transaksi;

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Error transaksi: " . $e->getMessage());
        }
    }

    /**
     * Mengambil detail transaksi berdasarkan ID
     */
    public function getById($id) {
        try {
            $query = "SELECT t.id, 
                            t.user_id,
                            t.total,
                            t.bayar,
                            t.tanggal,
                            u.username as nama_user,
                            COUNT(d.id) as total_items,
                            SUM(d.jumlah) as total_quantity
                     FROM transaksi t
                     LEFT JOIN users u ON t.user_id = u.id
                     LEFT JOIN detail_transaksi d ON t.id = d.id_transaksi
                     WHERE t.id = ?
                     GROUP BY t.id";
            
            $result = $this->db->query($query, [$id]);
            if (!$result || $result->num_rows === 0) {
                throw new Exception("Transaksi tidak ditemukan");
            }
            return $result->fetch_assoc();
        } catch (Exception $e) {
            throw new Exception("Error mengambil detail transaksi: " . $e->getMessage());
        }
    }

    /**
     * Mengambil detail items transaksi
     */
    public function getDetailById($id) {
        try {
            $query = "SELECT d.*, 
                            b.nama_barang, 
                            b.barcode,
                            b.harga as harga_sekarang
                     FROM detail_transaksi d
                     LEFT JOIN barang b ON d.id_barang = b.id
                     WHERE d.id_transaksi = ?
                     ORDER BY d.id ASC";
            
            $result = $this->db->query($query, [$id]);
            if (!$result) {
                throw new Exception("Detail transaksi tidak ditemukan");
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception("Error mengambil detail items: " . $e->getMessage());
        }
    }

    /**
     * Membatalkan transaksi
     */
    public function cancel($id) {
        try {
            $this->db->beginTransaction();

            // Cek transaksi exists
            $trans = $this->getById($id);
            if (!$trans) {
                throw new Exception("Transaksi tidak ditemukan");
            }

            // Ambil detail transaksi
            $details = $this->getDetailById($id);
            if (!$details) {
                throw new Exception("Detail transaksi tidak ditemukan");
            }

            // Kembalikan stok
            while ($item = $details->fetch_assoc()) {
                $query = "UPDATE barang 
                         SET stok = stok + ?,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE id = ?";
                
                $result = $this->db->query($query, [
                    $item['jumlah'],
                    $item['id_barang']
                ]);

                if (!$result) {
                    throw new Exception("Gagal mengembalikan stok barang");
                }
            }

            // Hapus detail transaksi
            $query = "DELETE FROM detail_transaksi WHERE id_transaksi = ?";
            $result = $this->db->query($query, [$id]);

            if (!$result) {
                throw new Exception("Gagal menghapus detail transaksi");
            }

            // Hapus transaksi
            $query = "DELETE FROM transaksi WHERE id = ?";
            $result = $this->db->query($query, [$id]);

            if (!$result) {
                throw new Exception("Gagal menghapus transaksi");
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Error membatalkan transaksi: " . $e->getMessage());
        }
    }

    /**
     * Mengambil laporan penjualan per produk
     */
    public function getLaporanProduk($start_date, $end_date) {
        try {
            $query = "SELECT b.id,
                            b.nama_barang,
                            b.barcode,
                            SUM(d.jumlah) as total_terjual,
                            AVG(d.harga) as rata_harga,
                            SUM(d.subtotal) as total_penjualan,
                            COUNT(DISTINCT d.id_transaksi) as total_transaksi
                     FROM detail_transaksi d
                     JOIN transaksi t ON d.id_transaksi = t.id
                     JOIN barang b ON d.id_barang = b.id
                     WHERE DATE(t.tanggal) >= ?
                     AND DATE(t.tanggal) <= ?
                     GROUP BY b.id
                     ORDER BY total_terjual DESC";
            
            return $this->db->query($query, [$start_date, $end_date]);
        } catch (Exception $e) {
            throw new Exception("Error mengambil laporan produk: " . $e->getMessage());
        }
    }
}