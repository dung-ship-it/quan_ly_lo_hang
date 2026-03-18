<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Đổi Mật Khẩu';
$conn = getDB();
$userId = $_SESSION['user_id'];
include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-key me-2"></i>Đổi Mật Khẩu</h5>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;">
                        <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Đổi Mật Khẩu Của Bạn</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật Khẩu Hiện Tại <span class="text-danger">*</span></label>
                            <input type="password" id="oldPass" class="form-control" placeholder="Nhập mật khẩu hiện tại">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật Khẩu Mới <span class="text-danger">*</span></label>
                            <input type="password" id="newPass" class="form-control" placeholder="Nhập mật khẩu mới" minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Xác Nhận Mật Khẩu Mới <span class="text-danger">*</span></label>
                            <input type="password" id="confirmPass" class="form-control" placeholder="Nhập lại mật khẩu mới">
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary" onclick="doiMatKhau()">
                                <i class="fas fa-save me-2"></i>Lưu Mật Khẩu Mới
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const BASE_URL = '<?= BASE_URL ?>';
function doiMatKhau() {
    const oldPass = $('#oldPass').val();
    const newPass = $('#newPass').val();
    const confirm = $('#confirmPass').val();
    if (!oldPass || !newPass || !confirm) return showError('Vui lòng nhập đủ thông tin!');
    if (newPass.length < 6) return showError('Mật khẩu mới ít nhất 6 ký tự!');
    if (newPass !== confirm) return showError('Mật khẩu xác nhận không khớp!');
    showLoading('Đang lưu...');
    apiCall(BASE_URL + 'api/quan_tri/doi_mat_khau_ca_nhan.php', {
        old_password: oldPass,
        new_password: newPass
    }, function(res) {
        Swal.close();
        if (res.success) showSuccess(res.message, function() { window.location.reload(); });
        else showError(res.message);
    });
}
</script>
<?php include '../../includes/footer.php'; ?>