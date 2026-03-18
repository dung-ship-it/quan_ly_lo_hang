<?php
/**
 * CRON JOB - Chạy hàng đêm lúc 00:01
 * Mục đích:
 * 1. Chuyển lô hoàn thành hôm qua vào Báo Cáo Tuần
 * 2. Cập nhật ngày cho lô chưa hoàn thành sang hôm nay
 *
 * Cài đặt cron (Linux):
 * 1 0 * * * php /path/to/quan-ly-lo-hang/cron/chuyen_lo_hang.php
 *
 * Windows XAMPP - Dùng Task Scheduler:
 * Action: php C:\xampp\htdocs\quan-ly-lo-hang\cron\chuyen_lo_hang.php
 * Trigger: Hàng ngày lúc 00:01
 */
require_once __DIR__ . '/../config/database.php';
$conn = getDB();
$hom_nay = date('Y-m-d');
$hom_qua = date('Y-m-d', strtotime('-1 day'));

echo "[" . date('Y-m-d H:i:s') . "] Bắt đầu chạy cron...\n";

// =============================================
// BƯỚC 1: Chuyển lô HOÀN THÀNH vào Báo Cáo Tuần
// =============================================
$dto = new DateTime($hom_qua);
$tuan = intval($dto->format('W'));
$nam = intval($dto->format('o')); // ISO year
$dto->setISODate($nam, $tuan);
$ngayBD = $dto->format('Y-m-d');
$dto->modify('+6 days');
$ngayKT = $dto->format('Y-m-d');

$stmt = $conn->prepare("
    SELECT id FROM lo_hang
    WHERE trang_thai = 'hoan_thanh'
    AND ngay_hoan_thanh = ?
    AND id NOT IN (SELECT lo_hang_id FROM bao_cao_tuan)
");
$stmt->bind_param('s', $hom_qua);
$stmt->execute();
$loHoanThanh = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$demChuyen = 0;
foreach ($loHoanThanh as $lo) {
    $stmt2 = $conn->prepare("
        INSERT INTO bao_cao_tuan (lo_hang_id, tuan_so, nam, ngay_bat_dau, ngay_ket_thuc, trang_thai)
        VALUES (?, ?, ?, ?, ?, 'cho_duyet')
    ");
    $stmt2->bind_param('iiiss', $lo['id'], $tuan, $nam, $ngayBD, $ngayKT);
    if ($stmt2->execute()) {
        $demChuyen++;
        echo "[OK] Chuyển lô ID {$lo['id']} vào Báo Cáo Tuần $tuan/$nam\n";
    }
}
echo "[INFO] Đã chuyển $demChuyen lô hoàn thành vào Báo Cáo Tuần.\n";

// =============================================
// BƯỚC 2: Cập nhật ngày cho lô CHƯA HOÀN THÀNH
// =============================================
$stmt3 = $conn->prepare("
    UPDATE lo_hang
    SET ngay = ?
    WHERE trang_thai IN ('da_luu', 'tu_choi')
    AND ngay < ?
");
$stmt3->bind_param('ss', $hom_nay, $hom_nay);
$stmt3->execute();
$demCapNhat = $stmt3->affected_rows;
echo "[INFO] Đã cập nhật ngày cho $demCapNhat lô chưa hoàn thành sang hôm nay ($hom_nay).\n";

// =============================================
// BƯỚC 3: Reset STT theo ngày mới
// =============================================
$stmt4 = $conn->prepare("
    SELECT user_id, COUNT(*) as total
    FROM lo_hang
    WHERE ngay = ? AND trang_thai IN ('da_luu', 'tu_choi')
    GROUP BY user_id
");
$stmt4->bind_param('s', $hom_nay);
$stmt4->execute();
$users = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($users as $u) {
    $stmtLo = $conn->prepare("
        SELECT id FROM lo_hang
        WHERE ngay = ? AND user_id = ? AND trang_thai IN ('da_luu', 'tu_choi')
        ORDER BY id ASC
    ");
    $stmtLo->bind_param('si', $hom_nay, $u['user_id']);
    $stmtLo->execute();
    $loList = $stmtLo->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($loList as $idx => $loItem) {
        $stt = $idx + 1;
        $stmtStt = $conn->prepare("UPDATE lo_hang SET stt = ? WHERE id = ?");
        $stmtStt->bind_param('ii', $stt, $loItem['id']);
        $stmtStt->execute();
    }
}
echo "[INFO] Đã reset STT cho các lô ngày hôm nay.\n";

echo "[" . date('Y-m-d H:i:s') . "] Hoàn thành cron!\n";