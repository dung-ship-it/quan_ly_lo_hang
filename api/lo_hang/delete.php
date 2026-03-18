<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');

$r = $conn->prepare("SELECT trang_thai, user_id FROM lo_hang WHERE id=?");
$r->bind_param('i', $id); $r->execute();
$lh = $r->get_result()->fetch_assoc();
if (!$lh) jsonResponse(false, 'Không tìm thấy lô hàng!');
if ($lh['trang_thai'] === 'hoan_thanh') jsonResponse(false, 'Không thể xoá lô đã hoàn thành!');
if (!isAdmin() && $lh['user_id'] != $_SESSION['user_id']) jsonResponse(false, 'Bạn không có quyền xoá lô này!');

$stmt = $conn->prepare("DELETE FROM lo_hang WHERE id=?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) jsonResponse(true, 'Xoá lô hàng thành công!');
else jsonResponse(false, 'Lỗi khi xoá!');