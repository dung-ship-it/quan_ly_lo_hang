<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Nhập Lô Hàng';
$conn   = getDB();
$userId = $_SESSION['user_id'];
$ngayChon = $_GET['ngay'] ?? date('Y-m-d');

$khachHangList = [];
$r = $conn->query("SELECT id, ma_khach, ten_day_du FROM khach_hang ORDER BY ma_khach ASC");
while ($row = $r->fetch_assoc()) $khachHangList[] = $row;

include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">

        <!-- Top Navbar: Tiêu đề + Ngày + Nút Lưu Ngày -->
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-box me-2"></i>Nhập Lô Hàng</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 fw-bold small text-nowrap">Ngày:</label>
                <input type="date" id="ngayChon" class="form-control form-control-sm"
                    style="width:150px" value="<?= $ngayChon ?>" onchange="doiNgay(this.value)">
                <button class="btn btn-primary btn-sm" id="btnLuuNgay" onclick="luuNgay()" style="display:none;white-space:nowrap">
                    <i class="fas fa-save me-1"></i>Lưu Ngày
                </button>
            </div>
        </div>

        <!-- Cảnh báo trùng -->
        <div id="alertTrung" class="alert alert-warning d-none mb-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="alertTrungMsg"></span>
        </div>

        <!-- Bảng lô hàng -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table table-bordered table-lo-hang mb-0" id="tableLoHang">
                        <thead>
                            <tr>
                                <th class="col-nv"    style="min-width:100px">Nhân Viên</th>
                                <th                  style="min-width:40px">STT</th>
                                <th class="col-kh"   style="min-width:130px">Khách Hàng <span class="text-danger">*</span></th>
                                <th class="col-house" style="min-width:160px">House B/L <span class="text-danger">*</span></th>
                                <th class="col-cong-ty" style="min-width:160px">Công Ty <span class="text-danger">*</span></th>
                                <th class="col-so-tk" style="min-width:150px">Số Tờ Khai <span class="text-danger">*</span></th>
                                <th class="col-tien" style="min-width:100px">Thuế</th>
                                <th class="col-tien" style="min-width:100px">Phí THC</th>
                                <th class="col-tien" style="min-width:100px">Phi Lệnh</th>
                                <th class="col-tien" style="min-width:90px">Mở TK</th>
                                <th class="col-tien" style="min-width:90px">Kiểm</th>
                                <th class="col-giam-sat" style="min-width:120px">Giám Sát</th>
                                <th class="col-tien" style="min-width:140px">Bốc Xếp, Xe Nâng</th>
                                <th class="col-tien" style="min-width:100px">Handling</th>
                                <th class="col-tien" style="min-width:90px">Xe Ôm</th>
                                <th class="col-tien" style="min-width:90px">Xe Bus</th>
                                <th class="col-tien" style="min-width:110px">CHI NGOÀI</th>
                                <th class="col-ly-do" style="min-width:150px">Lý Do Chi Ngoài</th>
                                <th class="col-van-chuyen" style="min-width:110px">Vận Chuyển</th>
                                <th class="col-cong-ty-vc" style="min-width:160px">Công Ty Vận Chuyển</th>
                                <th class="col-bien-so" style="min-width:120px">Biển Số Xe</th>
                                <th class="col-anh" style="min-width:130px">Đính Kèm Ảnh</th>
                                <th class="col-trang-thai" style="min-width:130px">Trạng Thái</th>
                                <th class="col-action" style="min-width:110px">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLoHang">
                            <tr><td colspan="24" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center bg-light py-2">
                <button class="btn btn-success btn-sm" onclick="themLoHang()">
                    <i class="fas fa-plus me-1"></i>Thêm Lô Hàng
                </button>
            </div>
        </div>

    </div><!-- end main-content -->
</div><!-- end wrapper -->

<!-- Modal xem ảnh -->
<div class="modal fade" id="modalAnh" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ảnh Đính Kèm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalAnhBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script src="<?= BASE_URL ?>assets/js/lo_hang.js"></script>
<script>
const BASE_URL        = '<?= BASE_URL ?>';
const IS_ADMIN        = <?= isAdmin() ? 'true' : 'false' ?>;
const USER_ID         = <?= $userId ?>;
const NGAY_CHON       = '<?= $ngayChon ?>';
const HO_TEN_SESSION  = '<?= htmlspecialchars($_SESSION['ho_ten']) ?>';
const KHACH_HANG_LIST = <?= json_encode($khachHangList) ?>;

$(document).ready(function () {
    loadLoHang();
});
</script>
</body>
</html>