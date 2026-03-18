<?php
require_once '../../config/database.php';
requireLogin();
$conn = getDB();
$userId = $_SESSION['user_id'];
$thang = intval($_GET['thang'] ?? date('m'));
$nam = intval($_GET['nam'] ?? date('Y'));
$ngay = $_GET['ngay'] ?? '';
$nvId = intval($_GET['nv_id'] ?? 0);

$sql = "SELECT ut.*, u.ho_ten FROM ung_tien ut JOIN users u ON ut.user_id = u.id WHERE ut.thang=? AND ut.nam=?";
$params = [$thang, $nam]; $types = 'ii';

if (!isAdmin()) {
    $sql .= " AND ut.user_id=?";
    $params[] = $userId; $types .= 'i';
} elseif ($nvId) {
    $sql .= " AND ut.user_id=?";
    $params[] = $nvId; $types .= 'i';
}
if ($ngay) { $sql .= " AND ut.ngay=?"; $params[] = $ngay; $types .= 's'; }
$sql .= " ORDER BY ut.ngay DESC, ut.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
jsonResponse(true, '', $data);