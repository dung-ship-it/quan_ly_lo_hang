<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');
$adminId = $_SESSION['user_id'];
$ngayDuyet = date('Y-m-d H:i:s');

// Lấy thông tin BCT
$r = $conn->prepare("SELECT * FROM bao_cao_tuan WHERE id=?");
$r->bind_param('i', $id); $r->execute();
$bct = $r->get_result()->fetch_assoc();
if (!$bct) jsonResponse(false, 'Không tìm thấy!');

// Cập nhật trạng thái duyệt
$stmt = $conn->prepare("UPDATE bao_cao_tuan SET trang_thai='da_duyet', duyet_boi=?, ngay_duyet=?, ly_do_tu_choi=NULL WHERE id=?");
$stmt->bind_param('isi', $adminId, $ngayDuyet, $id);
$stmt->execute();

// Thêm vào báo cáo tháng
$r2 = $conn->prepare("SELECT ngay FROM lo_hang WHERE id=?");
$r2->bind_param('i', $bct['lo_hang_id']); $r2->execute();
$lo = $r2->get_result()->fetch_assoc();
$thang = intval(date('m', strtotime($lo['ngay'])));
$nam = intval(date('Y', strtotime($lo['ngay'])));

// Kiểm tra đã có chưa
$r3 = $conn->prepare("SELECT id FROM bao_cao_thang WHERE lo_hang_id=? AND thang=? AND nam=?");
$r3->bind_param('iii', $bct['lo_hang_id'], $thang, $nam); $r3->execute();
if ($r3->get_result()->num_rows === 0) {
    $stmt2 = $conn->prepare("INSERT INTO bao_cao_thang (lo_hang_id, thang, nam) VALUES (?,?,?)");
    $stmt2->bind_param('iii', $bct['lo_hang_id'], $thang, $nam);
    $stmt2->execute();
}

// Cập nhật số dư nhân viên
$r4 = $conn->prepare("SELECT user_id, thue+phi_thc+phi_lenh+mo_tk+kiem+boc_xep_xe_nang+handling+xe_om+xe_bus+chi_ngoai+van_chuyen as tong_phi FROM lo_hang WHERE id=?");
$r4->bind_param('i', $bct['lo_hang_id']); $r4->execute();
$loData = $r4->get_result()->fetch_assoc();
if ($loData) {
    $stmt3 = $conn->prepare("INSERT INTO so_du (user_id, tong_lam, so_du_hien_tai) VALUES (?,?,0-?)
        ON DUPLICATE KEY UPDATE tong_lam=tong_lam+VALUES(tong_lam), so_du_hien_tai=tong_ung-tong_lam");
    $stmt3->bind_param('idd', $loData['user_id'], $loData['tong_phi'], $loData['tong_phi']);
    $stmt3->execute();
}
jsonResponse(true, 'Duyệt lô hàng thành công! Đã chuyển sang Báo Cáo Tháng.');