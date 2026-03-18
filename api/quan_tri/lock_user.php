<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
$trangThai = $_POST['trang_thai'] ?? 'khoa';
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
if ($id == $_SESSION['user_id']) jsonResponse(false, 'Không thể khoá chính tài khoản của mình!');
$stmt = $conn->prepare("UPDATE users SET trang_thai=? WHERE id=?");
$stmt->bind_param('si', $trangThai, $id);
if ($stmt->execute()) jsonResponse(true, $trangThai === 'khoa' ? 'Đã khoá tài khoản!' : 'Đã mở khoá tài khoản!');
else jsonResponse(false, 'Lỗi khi cập nhật!');