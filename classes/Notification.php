<?php
class Notification {
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function checkStokMenipis() {
        $query = "SELECT * FROM barang WHERE stok < " . MIN_STOK;
        $result = $this->db->query($query);
        $notifications = [];
        
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'type' => 'warning',
                'message' => "Stok {$row['nama_barang']} tinggal {$row['stok']} item"
            ];
        }
        
        return $notifications;
    }

    public function addNotification($type, $message, $user_id = null) {
        $type = $this->db->escape($type);
        $message = $this->db->escape($message);
        $user_id = $user_id ? (int)$user_id : 'NULL';
        $query = "INSERT INTO notifications (type, message, user_id, created_at) 
                 VALUES ('$type', '$message', $user_id, NOW())";
        return $this->db->query($query);
    }

    public function getNotifications($user_id = null) {
        $where = $user_id ? "WHERE user_id = " . (int)$user_id . " OR user_id IS NULL" : "";
        return $this->db->query(
            "SELECT * FROM notifications $where 
             ORDER BY created_at DESC LIMIT 10"
        );
    }

    public function markAsRead($id) {
        $id = (int)$id;
        return $this->db->query(
            "UPDATE notifications SET read_at = NOW() WHERE id = $id"
        );
    }
} 