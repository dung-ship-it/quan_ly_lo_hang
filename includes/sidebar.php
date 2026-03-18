<?php
$currentScript = $_SERVER['SCRIPT_FILENAME'] ?? '';
$currentDir    = basename(dirname($currentScript));
$currentPage   = basename($currentScript);
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-boxes me-2"></i>
        <span>Quản Lý Lô Hàng</span>
    </div>
    <div class="sidebar-user">
        <i class="fas fa-user-circle fa-2x"></i>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($_SESSION['ho_ten'] ?? '') ?></div>
            <div class="small text-white-50"><?= isAdmin() ? 'Quản Trị Viên' : 'Nhân Viên' ?></div>
        </div>
    </div>
    <ul class="sidebar-menu">
        <li class="<?= $currentDir === 'lo_hang' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/lo_hang/index.php">
                <i class="fas fa-box"></i><span>Nhập Lô Hàng</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'khach_hang' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/khach_hang/index.php">
                <i class="fas fa-users"></i><span>Khách Hàng</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'ung_tien' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/ung_tien/index.php">
                <i class="fas fa-money-bill-wave"></i><span>Ứng Tiền</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'bao_cao_tuan' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/bao_cao_tuan/index.php">
                <i class="fas fa-chart-line"></i><span>Báo Cáo Tuần</span>
            </a>
        </li>
        <li class="<?= $currentDir === 'bao_cao_thang' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/bao_cao_thang/index.php">
                <i class="fas fa-calendar-alt"></i><span>Báo Cáo Tháng</span>
            </a>
        </li>
        <?php if (isAdmin()): ?>
        <li class="<?= $currentDir === 'quan_tri' && $currentPage !== 'doi_mat_khau.php' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/quan_tri/index.php">
                <i class="fas fa-cog"></i><span>Quản Trị</span>
            </a>
        </li>
        <?php endif; ?>
        <li class="<?= $currentDir === 'quan_tri' && $currentPage === 'doi_mat_khau.php' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/quan_tri/doi_mat_khau.php">
                <i class="fas fa-key"></i><span>Đổi Mật Khẩu</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>modules/auth/logout.php">
                <i class="fas fa-sign-out-alt"></i><span>Đăng Xuất</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <button id="toggleSidebar" class="btn btn-sm btn-outline-light w-100">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>