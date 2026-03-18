<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];
$thang = intval($_GET['thang'] ?? date('m'));
$nam = intval($_GET['nam'] ?? date('Y'));
$tuan = intval($_GET['tuan'] ?? 0);
$ngay = $_GET['ngay'] ?? '';
$nvId = intval($_GET['nv_id'] ?? 0);
$khId = intval($_GET['kh_id'] ?? 0);

$sql = "SELECT lh.*, kh.ma_khach, kh.ten_day_du,
        CASE WHEN ? > 0 THEN u.ho_ten
             ELSE GROUP_CONCAT(DISTINCT u2.ho_ten ORDER BY u2.ho_ten SEPARATOR ', ')
        END as nhan_vien
        FROM bao_cao_thang bct
        JOIN lo_hang lh ON bct.lo_hang_id = lh.id
        JOIN khach_hang kh ON lh.khach_hang_id = kh.id
        JOIN users u ON lh.user_id = u.id
        LEFT JOIN lo_hang lh2 ON (lh2.house_bl = lh.house_bl OR lh2.so_to_khai = lh.so_to_khai)
        LEFT JOIN users u2 ON lh2.user_id = u2.id
        WHERE bct.thang=? AND bct.nam=?";

$params = [$nvId, $thang, $nam];
$types = 'iii';

if (!isAdmin()) {
    $sql .= " AND lh.user_id=?";
    $params[] = $userId; $types .= 'i';
} elseif ($nvId) {
    $sql .= " AND lh.user_id=?";
    $params[] = $nvId; $types .= 'i';
}
if ($tuan) {
    $dto = new DateTime(); $dto->setISODate($nam, $tuan);
    $ngayBD = $dto->format('Y-m-d');
    $dto->modify('+6 days'); $ngayKT = $dto->format('Y-m-d');
    $sql .= " AND lh.ngay BETWEEN ? AND ?";
    $params[] = $ngayBD; $params[] = $ngayKT; $types .= 'ss';
}
if ($ngay) { $sql .= " AND lh.ngay=?"; $params[] = $ngay; $types .= 's'; }
if ($khId) { $sql .= " AND lh.khach_hang_id=?"; $params[] = $khId; $types .= 'i'; }
$sql .= " GROUP BY lh.id ORDER BY lh.ngay ASC, lh.stt ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $stmtAnh = $conn->prepare("SELECT * FROM lo_hang_anh WHERE lo_hang_id=?");
    $stmtAnh->bind_param('i', $row['id']); $stmtAnh->execute();
    $row['anh_list'] = $stmtAnh->get_result()->fetch_all(MYSQLI_ASSOC);
    $data[] = $row;
}
jsonResponse(true, '', $data);