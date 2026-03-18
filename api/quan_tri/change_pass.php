<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
$newPass = $_POST['new_password'] ?? '';
// Chỉ admin mới đổi được pass của người khác
if (!isAdmin() && $id != $_SESSION['user_id']) jsonResponse(false, 'Không có quyền!');
if (!$id || !$newPass) jsonResponse(false, 'Dữ liệu không hợp lệ!');
if (strlen($newPass) < 6) jsonResponse(false, 'Mật khẩu ít nhất 6 ký tự!');
$hash = password_hash($newPass, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$stmt->bind_param('si', $hash, $id);
if ($stmt->execute()) jsonResponse(true, 'Đổi mật khẩu thành công!');
else jsonResponse(false, 'Lỗi khi đổi mật khẩu!');