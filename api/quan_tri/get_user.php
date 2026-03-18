<?php
require_once '../../config/database.php';
requireAdmin();
$id = intval($_GET['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
$conn = getDB();
$stmt = $conn->prepare("SELECT id,username,ho_ten,email,so_dien_thoai,vai_tro,trang_thai FROM users WHERE id=?");
$stmt->bind_param('i', $id); $stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) jsonResponse(false, 'Không tìm thấy tài khoản!');
jsonResponse(true, '', $data);