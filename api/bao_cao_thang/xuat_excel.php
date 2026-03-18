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

// Lấy dữ liệu
$sql = "SELECT lh.ngay, u.ho_ten, lh.stt, kh.ma_khach, lh.house_bl, lh.cong_ty, lh.so_to_khai,
        lh.thue, lh.phi_thc, lh.phi_lenh, lh.mo_tk, lh.kiem, lh.giam_sat,
        lh.boc_xep_xe_nang, lh.handling, lh.xe_om, lh.xe_bus, lh.chi_ngoai,
        lh.ly_do_chi_ngoai, lh.van_chuyen, lh.cong_ty_van_chuyen, lh.bien_so_xe
        FROM bao_cao_thang bct
        JOIN lo_hang lh ON bct.lo_hang_id = lh.id
        JOIN khach_hang kh ON lh.khach_hang_id = kh.id
        JOIN users u ON lh.user_id = u.id
        WHERE bct.thang=? AND bct.nam=?";
$params = [$thang, $nam]; $types = 'ii';
if (!isAdmin()) { $sql .= " AND lh.user_id=?"; $params[] = $userId; $types .= 'i'; }
elseif ($nvId) { $sql .= " AND lh.user_id=?"; $params[] = $nvId; $types .= 'i'; }
if ($ngay) { $sql .= " AND lh.ngay=?"; $params[] = $ngay; $types .= 's'; }
if ($khId) { $sql .= " AND lh.khach_hang_id=?"; $params[] = $khId; $types .= 'i'; }
$sql .= " ORDER BY lh.ngay, lh.stt";
$stmt = $conn->prepare($sql); $stmt->bind_param($types, ...$params);
$stmt->execute(); $result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Xuất Excel bằng HTML table (không cần Composer)
$tenFile = "BaoCaoThang_{$thang}_{$nam}.xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$tenFile\"");
header("Cache-Control: max-age=0");
echo "\xEF\xBB\xBF"; // UTF-8 BOM
?>
<html><head><meta charset="utf-8"></head><body>
<table border="1">
    <tr>
        <td colspan="22" style="font-size:16pt;font-weight:bold;text-align:center;background:#667eea;color:white;">
            BÁO CÁO THÁNG <?=$thang?>/<?=$nam?>
            <?php if($nvId): ?>
            <?php $nvR = $conn->prepare("SELECT ho_ten FROM users WHERE id=?"); $nvR->bind_param('i',$nvId); $nvR->execute(); echo ' - NV: '.($nvR->get_result()->fetch_assoc()['ho_ten']??''); ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr style="background:#2c3e50;color:white;font-weight:bold;">
        <td>Ngày</td><td>Nhân Viên</td><td>STT</td><td>Mã KH</td>
        <td>House B/L</td><td>Công Ty</td><td>Số Tờ Khai</td>
        <td>Thuế</td><td>Phí THC</td><td>Phi Lệnh</td><td>Mở TK</td>
        <td>Kiểm</td><td>Giám Sát</td><td>Bốc Xếp, Xe Nâng</td>
        <td>Handling</td><td>Xe Ôm</td><td>Xe Bus</td>
        <td>Chi Ngoài</td><td>Lý Do Chi Ngoài</td>
        <td>Vận Chuyển</td><td>Cty Vận Chuyển</td><td>Biển Số Xe</td>
    </tr>
    <?php
    $tongRow = array_fill_keys(['thue','phi_thc','phi_lenh','mo_tk','kiem','boc_xep_xe_nang','handling','xe_om','xe_bus','chi_ngoai','van_chuyen'], 0);
    foreach($rows as $r):
        foreach($tongRow as $k=>$v) $tongRow[$k] += floatval($r[$k]??0);
    ?>
    <tr>
        <td><?=$r['ngay']?></td>
        <td><?=htmlspecialchars($r['ho_ten'])?></td>
        <td><?=$r['stt']?></td>
        <td><?=htmlspecialchars($r['ma_khach'])?></td>
        <td><?=htmlspecialchars($r['house_bl'])?></td>
        <td><?=htmlspecialchars($r['cong_ty'])?></td>
        <td><?=htmlspecialchars($r['so_to_khai'])?></td>
        <td><?=number_format($r['thue'],0,'.',',')?></td>
        <td><?=number_format($r['phi_thc'],0,'.',',')?></td>
        <td><?=number_format($r['phi_lenh'],0,'.',',')?></td>
        <td><?=number_format($r['mo_tk'],0,'.',',')?></td>
        <td><?=number_format($r['kiem'],0,'.',',')?></td>
        <td><?=htmlspecialchars($r['giam_sat']??'')?></td>
        <td><?=number_format($r['boc_xep_xe_nang'],0,'.',',')?></td>
        <td><?=number_format($r['handling'],0,'.',',')?></td>
        <td><?=number_format($r['xe_om'],0,'.',',')?></td>
        <td><?=number_format($r['xe_bus'],0,'.',',')?></td>
        <td><?=number_format($r['chi_ngoai'],0,'.',',')?></td>
        <td><?=htmlspecialchars($r['ly_do_chi_ngoai']??'')?></td>
        <td><?=number_format($r['van_chuyen'],0,'.',',')?></td>
        <td><?=htmlspecialchars($r['cong_ty_van_chuyen']??'')?></td>
        <td><?=htmlspecialchars($r['bien_so_xe']??'')?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:#2c3e50;color:white;font-weight:bold;">
        <td colspan="7">TỔNG CỘNG</td>
        <td><?=number_format($tongRow['thue'],0,'.',',')?></td>
        <td><?=number_format($tongRow['phi_thc'],0,'.',',')?></td>
        <td><?=number_format($tongRow['phi_lenh'],0,'.',',')?></td>
        <td><?=number_format($tongRow['mo_tk'],0,'.',',')?></td>
        <td><?=number_format($tongRow['kiem'],0,'.',',')?></td>
        <td>-</td>
        <td><?=number_format($tongRow['boc_xep_xe_nang'],0,'.',',')?></td>
        <td><?=number_format($tongRow['handling'],0,'.',',')?></td>
        <td><?=number_format($tongRow['xe_om'],0,'.',',')?></td>
        <td><?=number_format($tongRow['xe_bus'],0,'.',',')?></td>
        <td><?=number_format($tongRow['chi_ngoai'],0,'.',',')?></td>
        <td>-</td>
        <td><?=number_format($tongRow['van_chuyen'],0,'.',',')?></td>
        <td colspan="2">-</td>
    </tr>
</table>
</body></html>