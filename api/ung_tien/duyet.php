<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
$adminId = $_SESSION['user_id'];
$ngayDuyet = date('Y-m-d H:i:s');
$stmt = $conn->prepare("UPDATE ung_tien SET trang_thai='da_duyet', duyet_boi=?, ngay_duyet=?, ly_do_tu_choi=NULL WHERE id=?");
$stmt->bind_param('isi', $adminId, $ngayDuyet, $id);
if ($stmt->execute()) {
    // Cập nhật số dư
    $r = $conn->prepare("SELECT user_id, so_tien FROM ung_tien WHERE id=?");
    $r->bind_param('i', $id); $r->execute();
    $ut = $r->get_result()->fetch_assoc();
    // Upsert bảng so_du
    $stmt2 = $conn->prepare("INSERT INTO so_du (user_id, tong_ung, so_du_hien_tai) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE tong_ung=tong_ung+VALUES(tong_ung), so_du_hien_tai=tong_ung-tong_lam");
    $stmt2->bind_param('idd', $ut['user_id'], $ut['so_tien'], $ut['so_tien']);
    $stmt2->execute();
    jsonResponse(true, 'Duyệt phiếu ứng thành công!');
} else jsonResponse(false, 'Lỗi khi duyệt!');