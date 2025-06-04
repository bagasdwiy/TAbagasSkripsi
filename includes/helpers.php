<?php
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function tanggalIndo($tanggal) {
    if (empty($tanggal)) return '';
    
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    try {
        $date = new DateTime($tanggal);
        $tgl = $date->format('d');
        $bln = $date->format('n');
        $thn = $date->format('Y');
        
        return $tgl . ' ' . $bulan[$bln] . ' ' . $thn;
    } catch (Exception $e) {
        return $tanggal; // Return original if parsing fails
    }
}
function generateBarcode() {
    return date('Ymd') . rand(1000, 9999);
}

function flashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function redirectTo($path) {
    header("Location: " . SITE_URL . $path);
    exit;
}

function generatePDF($html, $filename) {
    $export = new Export();
    return $export->toPDF($html, $filename);
}

function generateExcel($data, $filename) {
    $export = new Export();
    return $export->toExcel($data, $filename);
}

function validateImage($file) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return false;
    }
    
    if ($file['size'] > 5000000) { // 5MB max
        return false;
    }
    
    return true;
}

function uploadImage($file, $destination) {
    if (validateImage($file)) {
        $filename = uniqid() . '_' . basename($file['name']);
        $target = $destination . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return $filename;
        }
    }
    return false;
}

function deleteImage($filename, $destination) {
    $file = $destination . $filename;
    if (file_exists($file)) {
        unlink($file);
        return true;
    }
    return false;
} 

// Tambahkan fungsi isLoggedIn di awal file
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
// Tambahkan fungsi baru untuk laporan
function getUserName($user_id) {
    global $db;
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    return $user ? htmlspecialchars($user['username']) : '-';
}