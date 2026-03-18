<?php
require_once '../../config/database.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$conn   = getDB();
$userId = $_SESSION['user_id'];
$id     = intval($_POST['id'] ?? 0);
$ngay   = $_POST['ngay'] ?? date('Y-m-d');

// Validate bắt buộc
$khId     = intval($_POST['khach_hang_id'] ?? 0);
$houseBl  = trim($_POST['house_bl'] ?? '');
$congTy   = trim($_POST['cong_ty'] ?? '');
$soToKhai = trim($_POST['so_to_khai'] ?? '');
if (!$khId || !$houseBl || !$congTy || !$soToKhai)
    jsonResponse(false, 'Vui lòng nhập đủ các trường bắt buộc!');

// Validate nhóm vận chuyển
$vanChuyen = floatval($_POST['van_chuyen'] ?? 0);
$congTyVC  = trim($_POST['cong_ty_van_chuyen'] ?? '');
$bienSo    = trim($_POST['bien_so_xe'] ?? '');
$vcFields  = [($vanChuyen > 0 ? 1 : 0), (!empty($congTyVC) ? 1 : 0), (!empty($bienSo) ? 1 : 0)];
$vcCount   = array_sum($vcFields);
if ($vcCount > 0 && $vcCount < 3)
    jsonResponse(false, 'Nhóm vận chuyển: Nếu điền 1 ô thì phải điền đủ cả 3 ô!');

// STT trong ngày
if (!$id) {
    $r = $conn->prepare("SELECT COALESCE(MAX(stt),0)+1 as next_stt FROM lo_hang WHERE ngay = ?");
    $r->bind_param('s', $ngay); $r->execute();
    $stt = $r->get_result()->fetch_assoc()['next_stt'];
    $r->close();
} else {
    $r = $conn->prepare("SELECT stt FROM lo_hang WHERE id = ?");
    $r->bind_param('i', $id); $r->execute();
    $stt = $r->get_result()->fetch_assoc()['stt'];
    $r->close();
}

$thue          = floatval($_POST['thue'] ?? 0);
$phiThc        = floatval($_POST['phi_thc'] ?? 0);
$phiLenh       = floatval($_POST['phi_lenh'] ?? 0);
$moTk          = floatval($_POST['mo_tk'] ?? 0);
$kiem          = floatval($_POST['kiem'] ?? 0);
$giamSat       = trim($_POST['giam_sat'] ?? '');
$bocXep        = floatval($_POST['boc_xep_xe_nang'] ?? 0);
$handling      = floatval($_POST['handling'] ?? 0);
$xeOm          = floatval($_POST['xe_om'] ?? 0);
$xeBus         = floatval($_POST['xe_bus'] ?? 0);
$chiNgoai      = floatval($_POST['chi_ngoai'] ?? 0);
$lyDoChiNgoai  = trim($_POST['ly_do_chi_ngoai'] ?? '');

if ($id) {
    // UPDATE: i s s s d d d d d s d d d d d s d s s i = 20 params
    $stmt = $conn->prepare(
        "UPDATE lo_hang SET khach_hang_id=?,house_bl=?,cong_ty=?,so_to_khai=?,
         thue=?,phi_thc=?,phi_lenh=?,mo_tk=?,kiem=?,giam_sat=?,
         boc_xep_xe_nang=?,handling=?,xe_om=?,xe_bus=?,chi_ngoai=?,
         ly_do_chi_ngoai=?,van_chuyen=?,cong_ty_van_chuyen=?,bien_so_xe=?,
         trang_thai='da_luu',ly_do_tu_choi=NULL
         WHERE id=?"
    );
    // 20 biến: i s s s  d d d d d  s  d d d d d  s  d  s  s  i
    $stmt->bind_param(
        'isssdddddsdddddsdsssi',
        $khId,$houseBl,$congTy,$soToKhai,
        $thue,$phiThc,$phiLenh,$moTk,$kiem,
        $giamSat,
        $bocXep,$handling,$xeOm,$xeBus,$chiNgoai,
        $lyDoChiNgoai,
        $vanChuyen,$congTyVC,$bienSo,
        $id
    );
} else {
    // INSERT: i s i i  s s s  d d d d d  s  d d d d  d  s  d  s  s = 22 params
    $stmt = $conn->prepare(
        "INSERT INTO lo_hang
         (user_id,ngay,stt,khach_hang_id,house_bl,cong_ty,so_to_khai,
          thue,phi_thc,phi_lenh,mo_tk,kiem,giam_sat,
          boc_xep_xe_nang,handling,xe_om,xe_bus,chi_ngoai,ly_do_chi_ngoai,
          van_chuyen,cong_ty_van_chuyen,bien_so_xe,trang_thai)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'da_luu')"
    );
    // 22 biến: i s i i  s s s  d d d d d  s  d d d d  d  s  d  s  s
    $stmt->bind_param(
        'isiisssdddddsdddddsdss',
        $userId,$ngay,$stt,$khId,
        $houseBl,$congTy,$soToKhai,
        $thue,$phiThc,$phiLenh,$moTk,$kiem,
        $giamSat,
        $bocXep,$handling,$xeOm,$xeBus,$chiNgoai,
        $lyDoChiNgoai,
        $vanChuyen,$congTyVC,$bienSo
    );
}

if (!$stmt->execute()) jsonResponse(false, 'Lỗi khi lưu: ' . $conn->error);
$loId = $id ?: $conn->insert_id;
$stmt->close();

// Upload ảnh
if (!empty($_FILES['anh_files']['name'][0])) {
    if ($phiThc <= 0) jsonResponse(false, 'Chỉ đính kèm ảnh khi có Phí THC!');
    $uploadDir = UPLOAD_PATH;
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    foreach ($_FILES['anh_files']['tmp_name'] as $k => $tmp) {
        if ($_FILES['anh_files']['error'][$k] === 0) {
            $ext     = pathinfo($_FILES['anh_files']['name'][$k], PATHINFO_EXTENSION);
            $newName = 'thc_' . $loId . '_' . time() . '_' . $k . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $newName)) {
                $stmtAnh  = $conn->prepare("INSERT INTO lo_hang_anh (lo_hang_id, ten_file, duong_dan) VALUES (?,?,?)");
                $duongDan = UPLOAD_URL . $newName;
                $stmtAnh->bind_param('iss', $loId, $newName, $duongDan);
                $stmtAnh->execute();
                $stmtAnh->close();
            }
        }
    }
}
jsonResponse(true, 'Lưu thành công!', ['id' => $loId]);