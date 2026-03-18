<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];

$tuan = intval($_GET['tuan'] ?? date('W'));
$nam  = intval($_GET['nam']  ?? date('Y'));

// Tính ngày bắt đầu và kết thúc tuần
$dto = new DateTime();
$dto->setISODate($nam, $tuan);
$ngayBD = $dto->format('Y-m-d');
$dto->modify('+6 days');
$ngayKT = $dto->format('Y-m-d');

// Chỉ lấy lô đã hoàn thành (trang_thai = 'hoan_thanh' hoặc đã vào bao_cao_tuan)
// Lấy các lô hàng trong tuần này đã được đưa vào bao_cao_tuan
if (isAdmin()) {
    $stmt = $conn->prepare("
        SELECT
            bct.id          AS bct_id,
            lh.id           AS id,
            lh.ngay,
            lh.house_bl,
            lh.cong_ty,
            lh.so_to_khai,
            lh.thue,
            lh.phi_thc,
            lh.phi_lenh,
            lh.mo_tk,
            lh.kiem,
            lh.giam_sat,
            lh.boc_xep_xe_nang,
            lh.handling,
            lh.xe_om,
            lh.xe_bus,
            lh.chi_ngoai,
            lh.ly_do_chi_ngoai,
            lh.van_chuyen,
            lh.cong_ty_van_chuyen,
            lh.bien_so_xe,
            lh.ly_do_tu_choi,
            bct.trang_thai  AS trang_thai_duyet,
            u.ho_ten,
            kh.ma_khach,
            0               AS co_nhieu_nv
        FROM bao_cao_tuan bct
        JOIN lo_hang lh  ON bct.lo_hang_id = lh.id
        JOIN users   u   ON lh.user_id     = u.id
        JOIN khach_hang kh ON lh.khach_hang_id = kh.id
        WHERE lh.ngay BETWEEN ? AND ?
        ORDER BY lh.ngay ASC, lh.stt ASC
    ");
    $stmt->bind_param('ss', $ngayBD, $ngayKT);
} else {
    $stmt = $conn->prepare("
        SELECT
            bct.id          AS bct_id,
            lh.id           AS id,
            lh.ngay,
            lh.house_bl,
            lh.cong_ty,
            lh.so_to_khai,
            lh.thue,
            lh.phi_thc,
            lh.phi_lenh,
            lh.mo_tk,
            lh.kiem,
            lh.giam_sat,
            lh.boc_xep_xe_nang,
            lh.handling,
            lh.xe_om,
            lh.xe_bus,
            lh.chi_ngoai,
            lh.ly_do_chi_ngoai,
            lh.van_chuyen,
            lh.cong_ty_van_chuyen,
            lh.bien_so_xe,
            lh.ly_do_tu_choi,
            bct.trang_thai  AS trang_thai_duyet,
            u.ho_ten,
            kh.ma_khach,
            0               AS co_nhieu_nv
        FROM bao_cao_tuan bct
        JOIN lo_hang lh  ON bct.lo_hang_id = lh.id
        JOIN users   u   ON lh.user_id     = u.id
        JOIN khach_hang kh ON lh.khach_hang_id = kh.id
        WHERE lh.ngay BETWEEN ? AND ?
          AND lh.user_id = ?
        ORDER BY lh.ngay ASC, lh.stt ASC
    ");
    $stmt->bind_param('ssi', $ngayBD, $ngayKT, $userId);
}

$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Gắn danh sách ảnh cho từng lô
foreach ($rows as &$row) {
    $ra = $conn->prepare("SELECT ten_file, duong_dan FROM lo_hang_anh WHERE lo_hang_id = ?");
    $ra->bind_param('i', $row['id']);
    $ra->execute();
    $row['anh_list'] = $ra->get_result()->fetch_all(MYSQLI_ASSOC);
    // nhan_vien_list dùng cho cột Nhân Viên
    $row['nhan_vien_list'] = $row['ho_ten'];
}
unset($row);

jsonResponse(true, '', $rows);