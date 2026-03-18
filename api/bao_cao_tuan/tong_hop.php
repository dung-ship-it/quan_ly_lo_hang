<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];
$tuan = intval($_GET['tuan'] ?? date('W'));
$nam = intval($_GET['nam'] ?? date('Y'));

if (isAdmin()) {
    // Tổng toàn bộ
    $r = $conn->query("SELECT COALESCE(SUM(so_tien),0) as tong FROM ung_tien WHERE trang_thai='da_duyet'");
    $tongUng = $r->fetch_assoc()['tong'];
    $r = $conn->query("SELECT COALESCE(SUM(lh.thue+lh.phi_thc+lh.phi_lenh+lh.mo_tk+lh.kiem+lh.boc_xep_xe_nang+lh.handling+lh.xe_om+lh.xe_bus+lh.chi_ngoai+lh.van_chuyen),0) as tong
        FROM bao_cao_tuan bct JOIN lo_hang lh ON bct.lo_hang_id=lh.id WHERE bct.trang_thai='da_duyet'");
    $tongLam = $r->fetch_assoc()['tong'];
    // Số dư cộng dồn tất cả
    $r = $conn->query("SELECT COALESCE(SUM(so_du_hien_tai),0) as tong FROM so_du");
    $conLai = $r->fetch_assoc()['tong'];

    // Theo từng NV
    $nvData = [];
    $nvList = $conn->query("SELECT id, ho_ten FROM users WHERE vai_tro='nhan_vien' AND trang_thai='hoat_dong'");
    while ($nv = $nvList->fetch_assoc()) {
        $r = $conn->prepare("SELECT COALESCE(SUM(so_tien),0) as tong FROM ung_tien WHERE user_id=? AND trang_thai='da_duyet'");
        $r->bind_param('i', $nv['id']); $r->execute();
        $nvUng = $r->get_result()->fetch_assoc()['tong'];
        $r = $conn->prepare("SELECT COALESCE(SUM(lh.thue+lh.phi_thc+lh.phi_lenh+lh.mo_tk+lh.kiem+lh.boc_xep_xe_nang+lh.handling+lh.xe_om+lh.xe_bus+lh.chi_ngoai+lh.van_chuyen),0) as tong
            FROM bao_cao_tuan bct JOIN lo_hang lh ON bct.lo_hang_id=lh.id WHERE lh.user_id=? AND bct.trang_thai='da_duyet'");
        $r->bind_param('i', $nv['id']); $r->execute();
        $nvLam = $r->get_result()->fetch_assoc()['tong'];
        $r2 = $conn->prepare("SELECT COALESCE(so_du_hien_tai,0) as sd FROM so_du WHERE user_id=?");
        $r2->bind_param('i', $nv['id']); $r2->execute();
        $nvConLai = $r2->get_result()->fetch_assoc()['sd'] ?? 0;
        $nvData[] = ['ho_ten' => $nv['ho_ten'], 'tong_ung' => $nvUng, 'tong_lam' => $nvLam, 'con_lai' => $nvConLai];
    }
    jsonResponse(true, '', ['tong_ung' => $tongUng, 'tong_lam' => $tongLam, 'con_lai' => $conLai, 'theo_nv' => $nvData]);
} else {
    $r = $conn->prepare("SELECT COALESCE(SUM(so_tien),0) as tong FROM ung_tien WHERE user_id=? AND trang_thai='da_duyet'");
    $r->bind_param('i', $userId); $r->execute();
    $tongUng = $r->get_result()->fetch_assoc()['tong'];
    $r = $conn->prepare("SELECT COALESCE(SUM(lh.thue+lh.phi_thc+lh.phi_lenh+lh.mo_tk+lh.kiem+lh.boc_xep_xe_nang+lh.handling+lh.xe_om+lh.xe_bus+lh.chi_ngoai+lh.van_chuyen),0) as tong
        FROM bao_cao_tuan bct JOIN lo_hang lh ON bct.lo_hang_id=lh.id WHERE lh.user_id=? AND bct.trang_thai='da_duyet'");
    $r->bind_param('i', $userId); $r->execute();
    $tongLam = $r->get_result()->fetch_assoc()['tong'];
    $r2 = $conn->prepare("SELECT COALESCE(so_du_hien_tai,0) as sd FROM so_du WHERE user_id=?");
    $r2->bind_param('i', $userId); $r2->execute();
    $conLai = $r2->get_result()->fetch_assoc()['sd'] ?? 0;
    jsonResponse(true, '', ['tong_ung' => $tongUng, 'tong_lam' => $tongLam, 'con_lai' => $conLai]);
}