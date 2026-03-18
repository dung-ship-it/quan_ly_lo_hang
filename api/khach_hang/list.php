<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM khach_hang WHERE 1=1";
$params = []; $types = '';
if ($search) {
    $sql .= " AND (ma_khach LIKE ? OR ten_day_du LIKE ? OR mst LIKE ? OR so_dien_thoai LIKE ?)";
    $s = "%$search%";
    $params = [$s,$s,$s,$s]; $types = 'ssss';
}
$sql .= " ORDER BY created_at DESC";
if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
jsonResponse(true, '', $data);