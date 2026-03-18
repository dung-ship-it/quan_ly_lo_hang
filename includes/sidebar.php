<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-boxes me-2"></i>
        <span>Quản Lý Lô Hàng</span>
    </div>
    <ul class="sidebar-menu">

        <!-- Dashboard -->
        <li class="<?= $currentPage === 'index.php' && $currentDir === 'dashboard' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/dashboard/index.php">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>

        <!-- Lô Hàng -->
        <li class="<?= $currentDir === 'lo_hang' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/lo_hang/index.php">
                <i class="fas fa-boxes"></i><span>Lô Hàng</span>
            </a>
        </li>

        <!-- Khách Hàng -->
        <li class="<?= $currentDir === 'khach_hang' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/khach_hang/index.php">
                <i class="fas fa-users"></i><span>Khách Hàng</span>
            </a>
        </li>

        <!-- Ứng Tiền -->
        <li class="<?= $currentDir === 'ung_tien' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/ung_tien/index.php">
                <i class="fas fa-money-bill-wave"></i><span>Ứng Tiền</span>
            </a>
        </li>
<!-- Báo Cáo Tuần -->
        <li class="<?= $currentDir === 'bao_cao_tuan' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/bao_cao_tuan/index.php">
                <i class="fas fa-chart-line"></i><span>Báo Cáo Tuần</span>
            </a>
        </li>
        <!-- Báo Cáo Tháng -->
        <li class="<?= $currentDir === 'bao_cao_thang' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/bao_cao_thang/index.php">
                <i class="fas fa-chart-bar"></i><span>Báo Cáo Tháng</span>
            </a>
        </li>

       
        <?php if (isAdmin()): ?>
        <!-- Quản Trị (chỉ admin) -->
        <li class="<?= $currentDir === 'quan_tri' && $currentPage !== 'doi_mat_khau.php' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/quan_tri/list_user.php">
                <i class="fas fa-user-cog"></i><span>Quản Trị</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Đổi Mật Khẩu -->
        <li class="<?= $currentDir === 'quan_tri' && $currentPage === 'doi_mat_khau.php' ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>modules/quan_tri/doi_mat_khau.php">
                <i class="fas fa-key"></i><span>Đổi Mật Khẩu</span>
            </a>
        </li>

        <!-- Đăng Xuất -->
        <li>
            <a href="<?= BASE_URL ?>modules/auth/logout.php">
                <i class="fas fa-sign-out-alt"></i><span>Đăng Xuất</span>
            </a>
        </li>

    </ul>
</div>