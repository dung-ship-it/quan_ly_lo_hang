<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
$ten = trim($_POST['ten_day_du'] ?? '');
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
if (!$ten) jsonResponse(false, 'Vui lòng nhập tên đầy đủ!');
$mst = trim($_POST['mst'] ?? '');
$sdt = trim($_POST['so_dien_thoai'] ?? '');
$email = trim($_POST['email'] ?? '');
$diaChi = trim($_POST['dia_chi'] ?? '');
$ghiChu = trim($_POST['ghi_chu'] ?? '');
$stmt = $conn->prepare("UPDATE khach_hang SET ten_day_du=?,mst=?,so_dien_thoai=?,email=?,dia_chi=?,ghi_chu=? WHERE id=?");
$stmt->bind_param('ssssssi', $ten,$mst,$sdt,$email,$diaChi,$ghiChu,$id);
if ($stmt->execute()) jsonResponse(true, 'Cập nhật khách hàng thành công!');
else jsonResponse(false, 'Lỗi khi cập nhật!');