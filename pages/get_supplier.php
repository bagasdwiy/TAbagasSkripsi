<?php
require_once '../includes/init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

global $conn;

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $query = "SELECT * FROM supplier WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
        exit;
    }
    
    $result = $stmt->get_result();
    $supplier = $result->fetch_assoc();
    
    echo json_encode($supplier);
} 