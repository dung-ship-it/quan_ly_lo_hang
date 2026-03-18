<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');

// Kiểm tra đã có lô hàng chưa
$r = $conn->prepare("SELECT COUNT(*) as total FROM lo_hang WHERE khach_hang_id = ?");
$r->bind_param('i', $id); $r->execute();
if ($r->get_result()->fetch_assoc()['total'] > 0)
    jsonResponse(false, 'Không thể xoá! Khách hàng đã có lô hàng liên quan.');

// Kiểm tra đã có tiền ứng chưa - (ứng tiền liên kết qua user, không trực tiếp KH, bỏ qua)
$stmt = $conn->prepare("DELETE FROM khach_hang WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) jsonResponse(true, 'Xoá khách hàng thành công!');
else jsonResponse(false, 'Lỗi khi xoá!');