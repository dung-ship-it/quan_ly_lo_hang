<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];
$oldPass = $_POST['old_password'] ?? '';
$newPass = $_POST['new_password'] ?? '';
if (!$oldPass || !$newPass) jsonResponse(false, 'Vui lòng nhập đủ thông tin!');
if (strlen($newPass) < 6) jsonResponse(false, 'Mật khẩu mới ít nhất 6 ký tự!');

// Kiểm tra mật khẩu cũ
$stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
$stmt->bind_param('i', $userId); $stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user || !password_verify($oldPass, $user['password']))
    jsonResponse(false, 'Mật khẩu hiện tại không đúng!');

$hash = password_hash($newPass, PASSWORD_DEFAULT);
$stmt2 = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$stmt2->bind_param('si', $hash, $userId);
if ($stmt2->execute()) jsonResponse(true, 'Đổi mật khẩu thành công!');
else jsonResponse(false, 'Lỗi khi đổi mật khẩu!');