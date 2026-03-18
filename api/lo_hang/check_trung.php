<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];
$field = $_GET['field'] ?? '';
$value = trim($_GET['value'] ?? '');
$ngay = $_GET['ngay'] ?? date('Y-m-d');

if (!in_array($field, ['house_bl', 'so_to_khai']) || !$value) {
    echo json_encode(['found' => false]); exit;
}

$stmt = $conn->prepare("SELECT ngay FROM lo_hang WHERE user_id=? AND $field=? AND ngay != ? LIMIT 1");
$stmt->bind_param('iss', $userId, $value, $ngay);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if ($row) echo json_encode(['found' => true, 'ngay' => $row['ngay']]);
else echo json_encode(['found' => false]);