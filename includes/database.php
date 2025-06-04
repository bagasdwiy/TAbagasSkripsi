<?php
class Database {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8");
    }

    public function query($sql, $params = []) {
        try {
            if (!empty($params)) {
                $stmt = $this->conn->prepare($sql);
                
                if ($stmt === false) {
                    throw new Exception("Error preparing statement: " . $this->conn->error);
                }

                // Generate type string for bind_param
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 'b';
                    }
                }

                // Bind parameters
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }

                // Execute statement
                $stmt->execute();
                
                // Get result
                $result = $stmt->get_result();
                
                $stmt->close();
                
                return $result;
            } else {
                return $this->conn->query($sql);
            }
        } catch (Exception $e) {
            throw new Exception("Query error: " . $e->getMessage());
        }
    }

    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollback();
    }

    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    public function close() {
        $this->conn->close();
    }
} 