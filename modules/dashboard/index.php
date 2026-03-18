<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Dashboard - Quản Lý Lô Hàng';

$conn   = getDB();
$userId = $_SESSION['user_id'];
$hoTen  = $_SESSION['ho_ten'] ?? 'Người Dùng'; // ← Fix lỗi undefined key
$thang  = date('m');
$nam    = date('Y');

if (isAdmin()) {
    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam");
    $tongLo = $r->fetch_assoc()['total'];

    $r = $conn->query("SELECT COALESCE(SUM(thue+phi_thc+phi_lenh+mo_tk+kiem+boc_xep_xe_nang+handling+xe_om+xe_bus+chi_ngoai+van_chuyen),0) as total
        FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND trang_thai='hoan_thanh'");
    $tongChiPhi = $r->fetch_assoc()['total'];

    $r = $conn->query("SELECT COALESCE(SUM(so_tien),0) as total FROM ung_tien WHERE thang=$thang AND nam=$nam AND trang_thai='da_duyet'");
    $tongUng = $r->fetch_assoc()['total'];

    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND trang_thai='hoan_thanh'");
    $loHoanThanh = $r->fetch_assoc()['total'];

    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND trang_thai IN ('chua_luu','da_luu')");
    $loChuaHoanThanh = $r->fetch_assoc()['total'];
} else {
    $r = $conn->prepare("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=? AND YEAR(ngay)=? AND user_id=?");
    $r->bind_param('iii', $thang, $nam, $userId); $r->execute();
    $tongLo = $r->get_result()->fetch_assoc()['total'];

    $r = $conn->prepare("SELECT COALESCE(SUM(thue+phi_thc+phi_lenh+mo_tk+kiem+boc_xep_xe_nang+handling+xe_om+xe_bus+chi_ngoai+van_chuyen),0) as total
        FROM lo_hang WHERE MONTH(ngay)=? AND YEAR(ngay)=? AND user_id=? AND trang_thai='hoan_thanh'");
    $r->bind_param('iii', $thang, $nam, $userId); $r->execute();
    $tongChiPhi = $r->get_result()->fetch_assoc()['total'];

    $r = $conn->prepare("SELECT COALESCE(SUM(so_tien),0) as total FROM ung_tien WHERE thang=? AND nam=? AND user_id=? AND trang_thai='da_duyet'");
    $r->bind_param('iii', $thang, $nam, $userId); $r->execute();
    $tongUng = $r->get_result()->fetch_assoc()['total'];

    $r = $conn->prepare("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=? AND YEAR(ngay)=? AND user_id=? AND trang_thai='hoan_thanh'");
    $r->bind_param('iii', $thang, $nam, $userId); $r->execute();
    $loHoanThanh = $r->get_result()->fetch_assoc()['total'];

    $r = $conn->prepare("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=? AND YEAR(ngay)=? AND user_id=? AND trang_thai IN ('chua_luu','da_luu')");
    $r->bind_param('iii', $thang, $nam, $userId); $r->execute();
    $loChuaHoanThanh = $r->get_result()->fetch_assoc()['total'];
}
$conLai = $tongUng - $tongChiPhi;

include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">

        <!-- Top navbar -->
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h5>
            <div class="text-muted small">
                <i class="fas fa-clock me-1"></i><span id="clock"></span>
            </div>
        </div>

        <!-- Banner chào mừng -->
        <div class="card mb-4 border-0 shadow-sm"
             style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;border-radius:12px;">
            <div class="card-body py-4 text-center">
                <h4 class="mb-1">🎉 CHÀO MỪNG, <?= htmlspecialchars($hoTen) ?>!</h4>
                <p class="mb-0 opacity-75"><i class="fas fa-calendar me-1"></i><span id="clock2"></span></p>
                <p class="mb-0 mt-1"><i class="fas fa-star me-1"></i>Chúc bạn một ngày làm việc tốt lành!</p>
            </div>
        </div>

        <!-- Hàng 1: 4 thẻ KPI -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                            <div class="stat-label">Tổng Số Lô Hàng</div>
                            <div class="stat-value"><?= number_format($tongLo) ?></div>
                            <small class="text-muted">Trong tháng <?= $thang ?>/<?= $nam ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c)">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div>
                            <div class="stat-label">Tổng Chi Phí</div>
                            <div class="stat-value" style="font-size:16px"><?= formatMoney($tongChiPhi) ?></div>
                            <small class="text-muted">Trong tháng <?= $thang ?>/<?= $nam ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe)">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <div class="stat-label">Tổng Tiền Ứng</div>
                            <div class="stat-value" style="font-size:16px"><?= formatMoney($tongUng) ?></div>
                            <small class="text-muted">Trong tháng <?= $thang ?>/<?= $nam ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon"
                            style="background:<?= $conLai >= 0
                                ? 'linear-gradient(135deg,#43e97b,#38f9d7)'
                                : 'linear-gradient(135deg,#f5576c,#f093fb)' ?>">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <div class="stat-label">Còn Lại</div>
                            <div class="stat-value" style="font-size:16px; color:<?= $conLai >= 0 ? '#28a745' : '#dc3545' ?>">
                                <?= ($conLai < 0 ? '-' : '') . formatMoney(abs($conLai)) ?>
                            </div>
                            <small class="text-muted">Ứng - Chi Phí</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hàng 2: 2 thẻ lô hàng -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#43e97b,#38f9d7)">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="stat-label">Số Lô Hàng Đã Hoàn Thành</div>
                            <div class="stat-value"><?= number_format($loHoanThanh) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#ff9a9e,#fad0c4)">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <div class="stat-label">Số Lô Hàng Chưa Hoàn Thành</div>
                            <div class="stat-value"><?= number_format($loChuaHoanThanh) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function updateClock() {
    const now  = new Date();
    const days = ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'];
    const str  = `${days[now.getDay()]}, ${String(now.getDate()).padStart(2,'0')}/${String(now.getMonth()+1).padStart(2,'0')}/${now.getFullYear()} - ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;
    const el1 = document.getElementById('clock');
    const el2 = document.getElementById('clock2');
    if (el1) el1.textContent = str;
    if (el2) el2.textContent = str;
}
updateClock();
setInterval(updateClock, 1000);
</script>
<?php include '../../includes/footer.php'; ?>