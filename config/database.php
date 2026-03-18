<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quan_ly_lo_hang');
define('BASE_URL', 'http://localhost/quan-ly-lo-hang/');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/');

// Khởi động session an toàn (chỉ 1 lần)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['success' => false, 'message' => 'Kết nối DB thất bại: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'modules/auth/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'modules/dashboard/index.php');
        exit;
    }
}

function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', '.') . ' đ';
}

function getCurrentWeek() {
    $date = new DateTime();
    $date->setISODate($date->format('o'), $date->format('W'));
    $monday = $date->format('Y-m-d');
    $date->modify('+6 days');
    $sunday = $date->format('Y-m-d');
    return [
        'start' => $monday,
        'end'   => $sunday,
        'week'  => (int)date('W'),
        'year'  => (int)date('o')
    ];
}
?>