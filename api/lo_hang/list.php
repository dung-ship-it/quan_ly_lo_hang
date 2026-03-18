<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$ngay = $_GET['ngay'] ?? date('Y-m-d');
$userId = $_SESSION['user_id'];

if (isAdmin()) {
    $stmt = $conn->prepare("
        SELECT lh.*, u.ho_ten, kh.ma_khach, kh.ten_day_du
        FROM lo_hang lh
        JOIN users u ON lh.user_id = u.id
        JOIN khach_hang kh ON lh.khach_hang_id = kh.id
        WHERE lh.ngay = ? OR (lh.trang_thai IN ('da_luu') AND lh.ngay <= ?)
        ORDER BY lh.stt ASC
    ");
    $stmt->bind_param('ss', $ngay, $ngay);
} else {
    $stmt = $conn->prepare("
        SELECT lh.*, u.ho_ten, kh.ma_khach, kh.ten_day_du
        FROM lo_hang lh
        JOIN users u ON lh.user_id = u.id
        JOIN khach_hang kh ON lh.khach_hang_id = kh.id
        WHERE lh.user_id = ? AND (lh.ngay = ? OR (lh.trang_thai = 'da_luu' AND lh.ngay <= ?))
        ORDER BY lh.stt ASC
    ");
    $stmt->bind_param('iss', $userId, $ngay, $ngay);
}
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    // Lấy ảnh đính kèm
    $stmtAnh = $conn->prepare("SELECT * FROM lo_hang_anh WHERE lo_hang_id = ?");
    $stmtAnh->bind_param('i', $row['id']);
    $stmtAnh->execute();
    $anhResult = $stmtAnh->get_result();
    $row['anh_list'] = [];
    while ($anh = $anhResult->fetch_assoc()) $row['anh_list'][] = $anh;
    $data[] = $row;
}
jsonResponse(true, '', $data);