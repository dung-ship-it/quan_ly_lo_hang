<?php
require_once '../../config/database.php';
requireAdmin();
$id = intval($_GET['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
$conn = getDB();
$stmt = $conn->prepare("SELECT * FROM khach_hang WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) jsonResponse(false, 'Không tìm thấy khách hàng!');
jsonResponse(true, '', $data);