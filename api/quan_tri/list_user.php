<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$result = $conn->query("SELECT id,username,ho_ten,email,so_dien_thoai,vai_tro,trang_thai FROM users ORDER BY vai_tro DESC, ho_ten ASC");
$data = $result->fetch_all(MYSQLI_ASSOC);
jsonResponse(true, '', $data);