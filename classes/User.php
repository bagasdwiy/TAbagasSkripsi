<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($username, $password) {
        $username = $this->db->real_escape_string($username);
        $password = md5($password); // Hash password dengan MD5
        
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                return true;
            }
        }
        return false;
    }


    public function logout() {
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
