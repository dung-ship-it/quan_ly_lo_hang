<?php
require_once '../../config/database.php';
requireAdmin();
$conn = getDB();
$loId = intval($_GET['lo_id'] ?? 0);
if (!$loId) jsonResponse(false, 'ID không hợp lệ!');
$stmt = $conn->prepare("
    SELECT u.ho_ten, lh.thue, lh.phi_thc, lh.phi_lenh, lh.mo_tk, lh.kiem,
           lh.giam_sat, lh.boc_xep_xe_nang, lh.handling, lh.xe_om, lh.xe_bus,
           lh.chi_ngoai, lh.van_chuyen
    FROM lo_hang lh JOIN users u ON lh.user_id = u.id
    WHERE lh.house_bl = (SELECT house_bl FROM lo_hang WHERE id=?)
       OR lh.so_to_khai = (SELECT so_to_khai FROM lo_hang WHERE id=?)
");
$stmt->bind_param('ii', $loId, $loId); $stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
jsonResponse(true, '', $data);