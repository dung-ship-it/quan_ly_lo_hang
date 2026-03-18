<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
$lyDo = trim($_POST['ly_do'] ?? '');
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
if (!$lyDo) jsonResponse(false, 'Vui lòng nhập lý do từ chối!');
$adminId = $_SESSION['user_id'];
$ngayDuyet = date('Y-m-d H:i:s');
$stmt = $conn->prepare("UPDATE ung_tien SET trang_thai='tu_choi', duyet_boi=?, ngay_duyet=?, ly_do_tu_choi=? WHERE id=?");
$stmt->bind_param('issi', $adminId, $ngayDuyet, $lyDo, $id);
if ($stmt->execute()) jsonResponse(true, 'Đã từ chối phiếu ứng!');
else jsonResponse(false, 'Lỗi khi từ chối!');