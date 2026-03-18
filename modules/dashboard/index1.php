<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Dashboard - Quản Lý Lô Hàng';
$conn = getDB();
$userId = $_SESSION['user_id'];
$thang = date('m');
$nam = date('Y');

// Thống kê tháng hiện tại
if (isAdmin()) {
    // Tổng lô hàng trong tháng
    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam");
    $tongLo = $r->fetch_assoc()['total'];
    // Tổng chi phí
    $r = $conn->query("SELECT COALESCE(SUM(thue+phi_thc+phi_lenh+mo_tk+kiem+boc_xep_xe_nang+handling+xe_om+xe_bus+chi_ngoai+van_chuyen),0) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND trang_thai='hoan_thanh'");
    $tongChiPhi = $r->fetch_assoc()['total'];
    // Tổng tiền ứng đã duyệt
    $r = $conn->query("SELECT COALESCE(SUM(so_tien),0) as total FROM ung_tien WHERE thang=$thang AND nam=$nam AND trang_thai='da_duyet'");
    $tongUng = $r->fetch_assoc()['total'];
    // Lô hoàn thành
    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND trang_thai='hoan_thanh'");
    $loHoanThanh = $r->fetch_assoc()['total'];
    // Lô chưa hoàn thành
    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND trang_thai IN ('chua_luu','da_luu')");
    $loChuaHoanThanh = $r->fetch_assoc()['total'];
} else {
    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND user_id=$userId");
    $tongLo = $r->fetch_assoc()['total'];
    $r = $conn->query("SELECT COALESCE(SUM(thue+phi_thc+phi_lenh+mo_tk+kiem+boc_xep_xe_nang+handling+xe_om+xe_bus+chi_ngoai+van_chuyen),0) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND user_id=$userId AND trang_thai='hoan_thanh'");
    $tongChiPhi = $r->fetch_assoc()['total'];
    $r = $conn->query("SELECT COALESCE(SUM(so_tien),0) as total FROM ung_tien WHERE thang=$thang AND nam=$nam AND user_id=$userId AND trang_thai='da_duyet'");
    $tongUng = $r->fetch_assoc()['total'];
    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND user_id=$userId AND trang_thai='hoan_thanh'");
    $loHoanThanh = $r->fetch_assoc()['total'];
    $r = $conn->query("SELECT COUNT(*) as total FROM lo_hang WHERE MONTH(ngay)=$thang AND YEAR(ngay)=$nam AND user_id=$userId AND trang_thai IN ('chua_luu','da_luu')");
    $loChuaHoanThanh = $r->fetch_assoc()['total'];
}
$conLai = $tongUng - $tongChiPhi;
include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h5>
            <div class="text-muted small"><i class="fas fa-clock me-1"></i><span id="clock"></span></div>
        </div>

        <!-- Lời chào -->
        <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg,#667eea,#764ba2); color:white; border-radius:12px;">
            <div class="card-body py-4 text-center">
                <h4 class="mb-1">🎉 CHÀO MỪNG, <?= htmlspecialchars($_SESSION['ho_ten']) ?>!</h4>
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
                            <div class="stat-value" style="font-size:18px"><?= formatMoney($tongChiPhi) ?></div>
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
                            <div class="stat-value" style="font-size:18px"><?= formatMoney($tongUng) ?></div>
                            <small class="text-muted">Trong tháng <?= $thang ?>/<?= $nam ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:<?= $conLai >= 0 ? 'linear-gradient(135deg,#43e97b,#38f9d7)' : 'linear-gradient(135deg,#f5576c,#f093fb)' ?>">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <div class="stat-label">Còn Lại</div>
                            <div class="stat-value" style="font-size:18px; color:<?= $conLai >= 0 ? '#28a745' : '#dc3545' ?>">
                                <?= formatMoney(abs($conLai)) ?>
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
function updateClock2() {
    const now = new Date();
    const days = ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'];
    const str = `${days[now.getDay()]}, ${String(now.getDate()).padStart(2,'0')}/${String(now.getMonth()+1).padStart(2,'0')}/${now.getFullYear()} - ${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')}`;
    document.getElementById('clock') && (document.getElementById('clock').textContent = str);
    document.getElementById('clock2') && (document.getElementById('clock2').textContent = str);
}
updateClock2(); setInterval(updateClock2, 1000);
</script>
<?php include '../../includes/footer.php'; ?>