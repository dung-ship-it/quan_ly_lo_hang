<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$username = trim($_POST['username'] ?? '');
$hoTen = trim($_POST['ho_ten'] ?? '');
$password = $_POST['password'] ?? '';
$email = trim($_POST['email'] ?? '');
$sdt = trim($_POST['so_dien_thoai'] ?? '');
$vaiTro = $_POST['vai_tro'] ?? 'nhan_vien';
$trangThai = $_POST['trang_thai'] ?? 'hoat_dong';
if (!$username || !$hoTen || !$password) jsonResponse(false, 'Vui lòng nhập đủ thông tin!');
if (strlen($password) < 6) jsonResponse(false, 'Mật khẩu ít nhất 6 ký tự!');
// Kiểm tra username trùng
$r = $conn->prepare("SELECT id FROM users WHERE username=?");
$r->bind_param('s', $username); $r->execute();
if ($r->get_result()->num_rows > 0) jsonResponse(false, 'Tên đăng nhập đã tồn tại!');
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username,password,ho_ten,email,so_dien_thoai,vai_tro,trang_thai) VALUES (?,?,?,?,?,?,?)");
$stmt->bind_param('sssssss', $username,$hash,$hoTen,$email,$sdt,$vaiTro,$trangThai);
if ($stmt->execute()) jsonResponse(true, 'Tạo tài khoản thành công!');
else jsonResponse(false, 'Lỗi khi tạo tài khoản!');