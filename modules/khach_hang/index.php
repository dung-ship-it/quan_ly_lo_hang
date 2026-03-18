<?php
require_once '../../config/database.php';
requireAdmin();
$pageTitle = 'Khách Hàng';
include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-users me-2"></i>Quản Lý Khách Hàng</h5>
            <button class="btn btn-primary btn-sm" onclick="openModalTao()">
                <i class="fas fa-plus me-1"></i>Thêm Khách Hàng
            </button>
        </div>

        <!-- Tìm kiếm -->
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="🔍 Tìm theo mã KH, tên, MST, SĐT...">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary btn-sm" onclick="loadKhachHang()">
                            <i class="fas fa-search me-1"></i>Tìm
                        </button>
                        <button class="btn btn-outline-danger btn-sm ms-1" onclick="$('#searchInput').val(''); loadKhachHang();">
                            <i class="fas fa-times me-1"></i>Xoá lọc
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tableKhachHang">
                        <thead style="background:#2c3e50; color:white;">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Mã Khách</th>
                                <th>Tên Đầy Đủ</th>
                                <th>MST</th>
                                <th>Địa Chỉ</th>
                                <th>Số Điện Thoại</th>
                                <th>Email</th>
                                <th>Ghi Chú</th>
                                <th class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKhachHang">
                            <tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo/Sửa Khách Hàng -->
<div class="modal fade" id="modalKhachHang" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2); color:white;">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-user-plus me-2"></i>Thêm Khách Hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="khId">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mã Khách <span class="text-danger">*</span></label>
                        <input type="text" id="maKhach" class="form-control" placeholder="Tự động sinh" readonly style="background:#f8f9fa;">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Tên Đầy Đủ <span class="text-danger">*</span></label>
                        <input type="text" id="tenDayDu" class="form-control" placeholder="Nhập tên khách hàng">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mã Số Thuế</label>
                        <input type="text" id="mst" class="form-control" placeholder="Nhập MST">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Số Điện Thoại</label>
                        <input type="text" id="sdt" class="form-control" placeholder="Nhập SĐT">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" id="email" class="form-control" placeholder="Nhập email">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa Chỉ</label>
                        <input type="text" id="diaChi" class="form-control" placeholder="Nhập địa chỉ">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Ghi Chú</label>
                        <textarea id="ghiChu" class="form-control" rows="2" placeholder="Nhập ghi chú..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
                <button type="button" class="btn btn-primary" onclick="saveKhachHang()">
                    <i class="fas fa-save me-1"></i>Lưu Lại
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() { loadKhachHang(); });

function loadKhachHang() {
    const search = $('#searchInput').val();
    $.get('../../api/khach_hang/list.php', { search }, function(res) {
        if (!res.success) return;
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="9" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>';
        } else {
            res.data.forEach((kh, i) => {
                html += `<tr>
                    <td class="ps-3">${i+1}</td>
                    <td><span class="badge bg-primary">${kh.ma_khach}</span></td>
                    <td class="fw-bold">${kh.ten_day_du}</td>
                    <td>${kh.mst || '-'}</td>
                    <td>${kh.dia_chi || '-'}</td>
                    <td>${kh.so_dien_thoai || '-'}</td>
                    <td>${kh.email || '-'}</td>
                    <td>${kh.ghi_chu || '-'}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm me-1" onclick="editKhachHang(${kh.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteKhachHang(${kh.id}, '${kh.ten_day_du}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
        }
        $('#tbodyKhachHang').html(html);
    }, 'json');
}

function openModalTao() {
    $('#khId').val('');
    $('#maKhach').val('Tự động sinh');
    $('#tenDayDu,#mst,#sdt,#email,#diaChi,#ghiChu').val('');
    $('#modalTitle').html('<i class="fas fa-user-plus me-2"></i>Thêm Khách Hàng');
    new bootstrap.Modal('#modalKhachHang').show();
}

function editKhachHang(id) {
    $.get('../../api/khach_hang/get.php', { id }, function(res) {
        if (!res.success) return showError(res.message);
        const kh = res.data;
        $('#khId').val(kh.id);
        $('#maKhach').val(kh.ma_khach);
        $('#tenDayDu').val(kh.ten_day_du);
        $('#mst').val(kh.mst);
        $('#sdt').val(kh.so_dien_thoai);
        $('#email').val(kh.email);
        $('#diaChi').val(kh.dia_chi);
        $('#ghiChu').val(kh.ghi_chu);
        $('#modalTitle').html('<i class="fas fa-edit me-2"></i>Sửa Khách Hàng');
        new bootstrap.Modal('#modalKhachHang').show();
    }, 'json');
}

function saveKhachHang() {
    const id = $('#khId').val();
    const ten = $('#tenDayDu').val().trim();
    if (!ten) return showError('Vui lòng nhập tên đầy đủ!');
    const data = {
        id, ten_day_du: ten,
        mst: $('#mst').val(),
        so_dien_thoai: $('#sdt').val(),
        email: $('#email').val(),
        dia_chi: $('#diaChi').val(),
        ghi_chu: $('#ghiChu').val()
    };
    const url = id ? '../../api/khach_hang/update.php' : '../../api/khach_hang/create.php';
    showLoading();
    apiCall(url, data, function(res) {
        Swal.close();
        if (res.success) {
            bootstrap.Modal.getInstance('#modalKhachHang').hide();
            showSuccess(res.message, loadKhachHang);
        } else showError(res.message);
    });
}

function deleteKhachHang(id, ten) {
    showConfirm('Xoá khách hàng?', `Bạn có chắc muốn xoá "${ten}"?`, function() {
        showLoading('Đang xoá...');
        apiCall('../../api/khach_hang/delete.php', { id }, function(res) {
            Swal.close();
            if (res.success) showSuccess(res.message, loadKhachHang);
            else showError(res.message);
        });
    });
}

$('#searchInput').on('keyup', function(e) { if (e.key === 'Enter') loadKhachHang(); });
</script>
<?php include '../../includes/footer.php'; ?>