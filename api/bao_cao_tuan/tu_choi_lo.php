<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
$lyDo = trim($_POST['ly_do'] ?? '');
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
if (!$lyDo) jsonResponse(false, 'Vui lòng nhập lý do từ chối!');

// Lấy lo_hang_id
$r = $conn->prepare("SELECT lo_hang_id FROM bao_cao_tuan WHERE id=?");
$r->bind_param('i', $id); $r->execute();
$bct = $r->get_result()->fetch_assoc();
if (!$bct) jsonResponse(false, 'Không tìm thấy!');

// Cập nhật BCT
$adminId = $_SESSION['user_id'];
$ngay = date('Y-m-d H:i:s');
$stmt = $conn->prepare("UPDATE bao_cao_tuan SET trang_thai='tu_choi', duyet_boi=?, ngay_duyet=?, ly_do_tu_choi=? WHERE id=?");
$stmt->bind_param('issi', $adminId, $ngay, $lyDo, $id);
$stmt->execute();

// Chuyển lô về trạng thái tu_choi + ghi lý do
$stmt2 = $conn->prepare("UPDATE lo_hang SET trang_thai='tu_choi', ly_do_tu_choi=? WHERE id=?");
$stmt2->bind_param('si', $lyDo, $bct['lo_hang_id']);
$stmt2->execute();

// Xoá khỏi BCT
$stmt3 = $conn->prepare("DELETE FROM bao_cao_tuan WHERE id=?");
$stmt3->bind_param('i', $id); $stmt3->execute();

jsonResponse(true, 'Đã từ chối lô hàng! Lô sẽ quay về mục Nhập Lô Hàng (màu vàng).');