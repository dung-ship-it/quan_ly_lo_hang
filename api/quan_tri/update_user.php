<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
$hoTen = trim($_POST['ho_ten'] ?? '');
$email = trim($_POST['email'] ?? '');
$sdt = trim($_POST['so_dien_thoai'] ?? '');
$vaiTro = $_POST['vai_tro'] ?? 'nhan_vien';
$trangThai = $_POST['trang_thai'] ?? 'hoat_dong';
if (!$id || !$hoTen) jsonResponse(false, 'Vui lòng nhập đủ thông tin!');
$stmt = $conn->prepare("UPDATE users SET ho_ten=?,email=?,so_dien_thoai=?,vai_tro=?,trang_thai=? WHERE id=?");
$stmt->bind_param('sssssi', $hoTen,$email,$sdt,$vaiTro,$trangThai,$id);
if ($stmt->execute()) jsonResponse(true, 'Cập nhật tài khoản thành công!');
else jsonResponse(false, 'Lỗi khi cập nhật!');