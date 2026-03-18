<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$id = intval($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');

// Validate bắt buộc
$khId = intval($_POST['khach_hang_id'] ?? 0);
$houseBl = trim($_POST['house_bl'] ?? '');
$congTy = trim($_POST['cong_ty'] ?? '');
$soToKhai = trim($_POST['so_to_khai'] ?? '');
if (!$khId || !$houseBl || !$congTy || !$soToKhai)
    jsonResponse(false, 'Vui lòng nhập đủ các trường bắt buộc!');

// Validate PHI THC phải có ảnh
$phiThc = floatval($_POST['phi_thc'] ?? 0);
if ($phiThc > 0) {
    $r = $conn->prepare("SELECT COUNT(*) as total FROM lo_hang_anh WHERE lo_hang_id = ?");
    $r->bind_param('i', $id); $r->execute();
    if ($r->get_result()->fetch_assoc()['total'] == 0)
        jsonResponse(false, 'Phí THC có giá trị, vui lòng đính kèm ảnh trước khi hoàn thành!');
}

// Validate nhóm vận chuyển
$vanChuyen = floatval($_POST['van_chuyen'] ?? 0);
$congTyVC = trim($_POST['cong_ty_van_chuyen'] ?? '');
$bienSo = trim($_POST['bien_so_xe'] ?? '');
$vcCount = array_sum([($vanChuyen > 0 ? 1 : 0), (!empty($congTyVC) ? 1 : 0), (!empty($bienSo) ? 1 : 0)]);
if ($vcCount > 0 && $vcCount < 3)
    jsonResponse(false, 'Nhóm vận chuyển: Phải điền đủ cả 3 ô hoặc để trống cả 3!');

$ngayHT = date('Y-m-d');
$stmt = $conn->prepare("UPDATE lo_hang SET trang_thai='hoan_thanh', ngay_hoan_thanh=? WHERE id=?");
$stmt->bind_param('si', $ngayHT, $id);
if ($stmt->execute()) jsonResponse(true, 'Đánh dấu hoàn thành thành công!');
else jsonResponse(false, 'Lỗi khi cập nhật!');