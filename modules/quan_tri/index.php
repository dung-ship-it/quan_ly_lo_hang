<?php
require_once '../../config/database.php';
requireAdmin();
$pageTitle = 'Quản Trị';
$conn = getDB();
include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-cog me-2"></i>Quản Trị Tài Khoản</h5>
            <button class="btn btn-primary btn-sm" onclick="openModalTao()">
                <i class="fas fa-user-plus me-1"></i>Tạo Tài Khoản
            </button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:#2c3e50;color:white;">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Tên Đăng Nhập</th>
                                <th>Họ Tên</th>
                                <th>Email</th>
                                <th>Số Điện Thoại</th>
                                <th>Vai Trò</th>
                                <th>Trạng Thái</th>
                                <th class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyUsers">
                            <tr><td colspan="8" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo/Sửa Tài Khoản -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;">
                <h5 class="modal-title" id="modalUserTitle">
                    <i class="fas fa-user-plus me-2"></i>Tạo Tài Khoản
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="userId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên Đăng Nhập <span class="text-danger">*</span></label>
                        <input type="text" id="uUsername" class="form-control" placeholder="Nhập tên đăng nhập">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Họ Tên <span class="text-danger">*</span></label>
                        <input type="text" id="uHoTen" class="form-control" placeholder="Nhập họ tên">
                    </div>
                    <div class="col-md-6" id="passWrapper">
                        <label class="form-label fw-bold">Mật Khẩu <span class="text-danger">*</span></label>
                        <input type="password" id="uPassword" class="form-control" placeholder="Nhập mật khẩu" minlength="6">
                    </div>
                    <div class="col-md-6" id="passConfirmWrapper">
                        <label class="form-label fw-bold">Xác Nhận Mật Khẩu <span class="text-danger">*</span></label>
                        <input type="password" id="uPasswordConfirm" class="form-control" placeholder="Nhập lại mật khẩu">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" id="uEmail" class="form-control" placeholder="Nhập email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số Điện Thoại</label>
                        <input type="text" id="uSdt" class="form-control" placeholder="Nhập SĐT">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Vai Trò</label>
                        <select id="uVaiTro" class="form-select">
                            <option value="nhan_vien">Nhân Viên</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Trạng Thái</label>
                        <select id="uTrangThai" class="form-select">
                            <option value="hoat_dong">Hoạt Động</option>
                            <option value="khoa">Khoá</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">
                    <i class="fas fa-save me-1"></i>Lưu Lại
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Đổi Mật Khẩu -->
<div class="modal fade" id="modalDoiPass" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Đổi Mật Khẩu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="dpUserId">
                <p class="text-muted small" id="dpUserName"></p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mật Khẩu Mới <span class="text-danger">*</span></label>
                    <input type="password" id="dpNewPass" class="form-control" placeholder="Nhập mật khẩu mới" minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Xác Nhận <span class="text-danger">*</span></label>
                    <input type="password" id="dpConfirmPass" class="form-control" placeholder="Nhập lại mật khẩu">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="button" class="btn btn-warning" onclick="saveDoiPass()">
                    <i class="fas fa-save me-1"></i>Đổi Mật Khẩu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
$(document).ready(function() { loadUsers(); });

function loadUsers() {
    $.get(BASE_URL + 'api/quan_tri/list_user.php', function(res) {
        if (!res.success) return;
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="8" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>';
        } else {
            res.data.forEach((u, i) => {
                html += `<tr>
                    <td class="ps-3">${i+1}</td>
                    <td><strong>${u.username}</strong></td>
                    <td>${u.ho_ten}</td>
                    <td>${u.email||'-'}</td>
                    <td>${u.so_dien_thoai||'-'}</td>
                    <td><span class="badge ${u.vai_tro==='admin'?'bg-danger':'bg-primary'}">${u.vai_tro==='admin'?'Admin':'Nhân Viên'}</span></td>
                    <td><span class="badge ${u.trang_thai==='hoat_dong'?'bg-success':'bg-secondary'}">${u.trang_thai==='hoat_dong'?'Hoạt Động':'Khoá'}</span></td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm me-1" onclick="editUser(${u.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-info btn-sm me-1 text-white" onclick="openDoiPass(${u.id}, '${u.ho_ten}')">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn ${u.trang_thai==='hoat_dong'?'btn-secondary':'btn-success'} btn-sm" onclick="toggleKhoa(${u.id}, '${u.trang_thai}')">
                            <i class="fas ${u.trang_thai==='hoat_dong'?'fa-lock':'fa-unlock'}"></i>
                        </button>
                    </td>
                </tr>`;
            });
        }
        $('#tbodyUsers').html(html);
    }, 'json');
}

function openModalTao() {
    $('#userId').val('');
    $('#uUsername,#uHoTen,#uPassword,#uPasswordConfirm,#uEmail,#uSdt').val('');
    $('#uVaiTro').val('nhan_vien');
    $('#uTrangThai').val('hoat_dong');
    $('#uUsername').prop('readonly', false);
    $('#passWrapper,#passConfirmWrapper').show();
    $('#modalUserTitle').html('<i class="fas fa-user-plus me-2"></i>Tạo Tài Khoản');
    new bootstrap.Modal('#modalUser').show();
}

function editUser(id) {
    $.get(BASE_URL + 'api/quan_tri/get_user.php', { id }, function(res) {
        if (!res.success) return showError(res.message);
        const u = res.data;
        $('#userId').val(u.id);
        $('#uUsername').val(u.username).prop('readonly', true);
        $('#uHoTen').val(u.ho_ten);
        $('#uEmail').val(u.email);
        $('#uSdt').val(u.so_dien_thoai);
        $('#uVaiTro').val(u.vai_tro);
        $('#uTrangThai').val(u.trang_thai);
        $('#uPassword,#uPasswordConfirm').val('');
        $('#passWrapper,#passConfirmWrapper').hide();
        $('#modalUserTitle').html('<i class="fas fa-edit me-2"></i>Sửa Tài Khoản');
        new bootstrap.Modal('#modalUser').show();
    }, 'json');
}

function saveUser() {
    const id = $('#userId').val();
    const hoTen = $('#uHoTen').val().trim();
    const username = $('#uUsername').val().trim();
    if (!hoTen || (!id && !username)) return showError('Vui lòng nhập đủ thông tin!');
    if (!id) {
        const pass = $('#uPassword').val();
        const confirm = $('#uPasswordConfirm').val();
        if (!pass) return showError('Vui lòng nhập mật khẩu!');
        if (pass.length < 6) return showError('Mật khẩu ít nhất 6 ký tự!');
        if (pass !== confirm) return showError('Mật khẩu xác nhận không khớp!');
    }
    const url = id ? BASE_URL + 'api/quan_tri/update_user.php' : BASE_URL + 'api/quan_tri/create_user.php';
    const data = {
        id, username, ho_ten: hoTen,
        password: $('#uPassword').val(),
        password_confirm: $('#uPasswordConfirm').val(),
        email: $('#uEmail').val(),
        so_dien_thoai: $('#uSdt').val(),
        vai_tro: $('#uVaiTro').val(),
        trang_thai: $('#uTrangThai').val()
    };
    showLoading();
    apiCall(url, data, function(res) {
        Swal.close();
        if (res.success) {
            bootstrap.Modal.getInstance('#modalUser').hide();
            showSuccess(res.message, loadUsers);
        } else showError(res.message);
    });
}

function openDoiPass(id, ten) {
    $('#dpUserId').val(id);
    $('#dpUserName').text('Tài khoản: ' + ten);
    $('#dpNewPass,#dpConfirmPass').val('');
    new bootstrap.Modal('#modalDoiPass').show();
}

function saveDoiPass() {
    const id = $('#dpUserId').val();
    const newPass = $('#dpNewPass').val();
    const confirm = $('#dpConfirmPass').val();
    if (!newPass) return showError('Vui lòng nhập mật khẩu mới!');
    if (newPass.length < 6) return showError('Mật khẩu ít nhất 6 ký tự!');
    if (newPass !== confirm) return showError('Mật khẩu xác nhận không khớp!');
    showLoading();
    apiCall(BASE_URL + 'api/quan_tri/change_pass.php', { id, new_password: newPass }, function(res) {
        Swal.close();
        if (res.success) {
            bootstrap.Modal.getInstance('#modalDoiPass').hide();
            showSuccess(res.message);
        } else showError(res.message);
    });
}

function toggleKhoa(id, trangThai) {
    const newStatus = trangThai === 'hoat_dong' ? 'khoa' : 'hoat_dong';
    const msg = newStatus === 'khoa' ? 'Khoá tài khoản này?' : 'Mở khoá tài khoản này?';
    showConfirm(msg, '', function() {
        showLoading();
        apiCall(BASE_URL + 'api/quan_tri/lock_user.php', { id, trang_thai: newStatus }, function(res) {
            Swal.close();
            if (res.success) showSuccess(res.message, loadUsers);
            else showError(res.message);
        });
    });
}
</script>
<?php include '../../includes/footer.php'; ?>