<?php
require_once '../../config/database.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$conn      = getDB();
$field     = $_GET['field'] ?? '';
$value     = trim($_GET['value'] ?? '');
$excludeId = intval($_GET['exclude_id'] ?? 0);

if (!in_array($field, ['house_bl', 'so_to_khai']) || !$value) {
    echo json_encode(['found' => false]);
    exit;
}

if ($excludeId > 0) {
    $stmt = $conn->prepare("SELECT id, ngay FROM lo_hang WHERE $field = ? AND id != ? LIMIT 1");
    $stmt->bind_param('si', $value, $excludeId);
} else {
    $stmt = $conn->prepare("SELECT id, ngay FROM lo_hang WHERE $field = ? LIMIT 1");
    $stmt->bind_param('s', $value);
}

$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
    echo json_encode(['found' => true, 'ngay' => $row['ngay'], 'id' => $row['id']]);
} else {
    echo json_encode(['found' => false]);
}