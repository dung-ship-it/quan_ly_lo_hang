<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$ten = trim($_POST['ten_day_du'] ?? '');
if (!$ten) jsonResponse(false, 'Vui lòng nhập tên đầy đủ!');

// Tự động sinh mã KH
$r = $conn->query("SELECT ma_khach FROM khach_hang ORDER BY id DESC LIMIT 1");
$lastMa = $r->fetch_assoc()['ma_khach'] ?? 'KH000';
$soThu = intval(substr($lastMa, 2)) + 1;
$maKhach = 'KH' . str_pad($soThu, 3, '0', STR_PAD_LEFT);

$mst = trim($_POST['mst'] ?? '');
$sdt = trim($_POST['so_dien_thoai'] ?? '');
$email = trim($_POST['email'] ?? '');
$diaChi = trim($_POST['dia_chi'] ?? '');
$ghiChu = trim($_POST['ghi_chu'] ?? '');
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO khach_hang (ma_khach,ten_day_du,mst,so_dien_thoai,email,dia_chi,ghi_chu,created_by) VALUES (?,?,?,?,?,?,?,?)");
$stmt->bind_param('sssssssi', $maKhach,$ten,$mst,$sdt,$email,$diaChi,$ghiChu,$userId);
if ($stmt->execute()) jsonResponse(true, "Thêm khách hàng thành công! Mã: $maKhach");
else jsonResponse(false, 'Lỗi khi thêm khách hàng!');