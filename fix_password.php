<?php
// Đặt file này vào thư mục gốc, chạy 1 lần rồi xoá
require_once 'config/database.php';
$conn = getDB();
$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password=? WHERE username='admin'");
$stmt->bind_param('s', $hash);
$stmt->execute();
echo "✅ Đã cập nhật mật khẩu admin thành: Admin@123<br>";
echo "Hash: " . $hash . "<br>";
echo "<br><strong>⚠️ Xoá file này ngay sau khi chạy!</strong>";
?>