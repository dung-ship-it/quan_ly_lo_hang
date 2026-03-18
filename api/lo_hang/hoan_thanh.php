<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];
$id = intval($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'ID không hợp lệ!');

// Validate bắt buộc
$khId     = intval($_POST['khach_hang_id'] ?? 0);
$houseBl  = trim($_POST['house_bl']   ?? '');
$congTy   = trim($_POST['cong_ty']    ?? '');
$soToKhai = trim($_POST['so_to_khai'] ?? '');
if (!$khId || !$houseBl || !$congTy || !$soToKhai)
    jsonResponse(false, 'Vui lòng nhập đủ các trường bắt buộc!');

// Validate Phí THC phải có ảnh
$phiThc = floatval($_POST['phi_thc'] ?? 0);
if ($phiThc > 0) {
    $r = $conn->prepare("SELECT COUNT(*) as total FROM lo_hang_anh WHERE lo_hang_id = ?");
    $r->bind_param('i', $id);
    $r->execute();
    if ($r->get_result()->fetch_assoc()['total'] == 0)
        jsonResponse(false, 'Phí THC có giá trị, vui lòng đính kèm ảnh trước khi hoàn thành!');
}

// Validate nhóm vận chuyển
$vanChuyen = floatval($_POST['van_chuyen'] ?? 0);
$congTyVC  = trim($_POST['cong_ty_van_chuyen'] ?? '');
$bienSo    = trim($_POST['bien_so_xe'] ?? '');
$vcCount   = array_sum([
    ($vanChuyen > 0    ? 1 : 0),
    (!empty($congTyVC) ? 1 : 0),
    (!empty($bienSo)   ? 1 : 0),
]);
if ($vcCount > 0 && $vcCount < 3)
    jsonResponse(false, 'Nhóm vận chuyển: Phải điền đủ cả 3 ô hoặc để trống cả 3!');

// Lấy ngày của lô để tính tuần/năm
$rNgay = $conn->prepare("SELECT ngay FROM lo_hang WHERE id = ?");
$rNgay->bind_param('i', $id);
$rNgay->execute();
$loRow = $rNgay->get_result()->fetch_assoc();
if (!$loRow) jsonResponse(false, 'Không tìm thấy lô hàng!');

$ngayLo = $loRow['ngay'];
$dto    = new DateTime($ngayLo);
$tuan   = intval($dto->format('W'));   // ISO week number
$nam    = intval($dto->format('o'));   // ISO year

// ── 1. Cập nhật trạng thái lô hàng ──────────────────────────────────────────
$ngayHT = date('Y-m-d');
$stmtUp = $conn->prepare("UPDATE lo_hang SET trang_thai='hoan_thanh', ngay_hoan_thanh=? WHERE id=?");
$stmtUp->bind_param('si', $ngayHT, $id);
if (!$stmtUp->execute())
    jsonResponse(false, 'Lỗi UPDATE lo_hang: ' . $conn->error);

// ── 2. Kiểm tra bảng bao_cao_tuan tồn tại, nếu chưa thì tạo ─────────────────
$conn->query("CREATE TABLE IF NOT EXISTS `bao_cao_tuan` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `lo_hang_id`    INT NOT NULL,
    `user_id`       INT NOT NULL DEFAULT 0,
    `tuan`          SMALLINT NOT NULL DEFAULT 0,
    `nam`           SMALLINT NOT NULL DEFAULT 0,
    `trang_thai`    VARCHAR(20) NOT NULL DEFAULT 'cho_duyet',
    `duyet_boi`     INT NULL,
    `ngay_duyet`    DATETIME NULL,
    `ly_do_tu_choi` TEXT NULL,
    `ngay_tao`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_lo_tuan` (`lo_hang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── 3. Thêm vào bao_cao_tuan nếu chưa có ─────────────────────────────────────
$rCheck = $conn->prepare("SELECT id FROM bao_cao_tuan WHERE lo_hang_id = ?");
$rCheck->bind_param('i', $id);
$rCheck->execute();

if ($rCheck->get_result()->num_rows === 0) {
    $stmtBCT = $conn->prepare(
        "INSERT INTO bao_cao_tuan (lo_hang_id, user_id, tuan, nam, trang_thai, ngay_tao)
         VALUES (?, ?, ?, ?, 'cho_duyet', NOW())"
    );
    $stmtBCT->bind_param('iiii', $id, $userId, $tuan, $nam);
    if (!$stmtBCT->execute())
        jsonResponse(false, 'Lỗi INSERT bao_cao_tuan: ' . $conn->error);
}

jsonResponse(true, 'Đánh dấu hoàn thành! Lô hàng đã được chuyển vào Báo Cáo Tuần.');