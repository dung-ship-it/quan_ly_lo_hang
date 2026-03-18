<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];
$ngay = $_POST['ngay'] ?? date('Y-m-d');
$soTien = floatval($_POST['so_tien'] ?? 0);
$lyDo = trim($_POST['ly_do'] ?? '');
$ghiChu = trim($_POST['ghi_chu'] ?? '');
if (!$soTien || !$lyDo) jsonResponse(false, 'Vui lòng nhập đủ thông tin!');
$thang = intval(date('m', strtotime($ngay)));
$nam = intval(date('Y', strtotime($ngay)));
$stmt = $conn->prepare("INSERT INTO ung_tien (user_id,ngay,so_tien,ly_do,ghi_chu,trang_thai,thang,nam) VALUES (?,?,?,?,?,'cho_duyet',?,?)");
$stmt->bind_param('isdssii', $userId,$ngay,$soTien,$lyDo,$ghiChu,$thang,$nam);
if ($stmt->execute()) jsonResponse(true, 'Tạo phiếu ứng thành công!');
else jsonResponse(false, 'Lỗi khi tạo phiếu!');