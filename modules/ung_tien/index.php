<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Thông Tin Ứng Tiền';
$conn = getDB();
$userId = $_SESSION['user_id'];
$thangChon = intval($_GET['thang'] ?? date('m'));
$namChon = intval($_GET['nam'] ?? date('Y'));
$ngayChon = $_GET['ngay'] ?? '';
include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-money-bill-wave me-2"></i>Thông Tin Ứng Tiền</h5>
            <button class="btn btn-primary btn-sm" onclick="openModalTao()">
                <i class="fas fa-plus me-1"></i>Tạo Phiếu Ứng
            </button>
        </div>

        <!-- Bộ lọc -->
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold small">Tháng:</label>
                    </div>
                    <div class="col-auto">
                        <select id="thangLoc" class="form-select form-select-sm" style="width:100px">
                            <?php for($i=1;$i<=12;$i++): ?>
                            <option value="<?=$i?>" <?=$i==$thangChon?'selected':''?>>Tháng <?=$i?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select id="namLoc" class="form-select form-select-sm" style="width:90px">
                            <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
                            <option value="<?=$y?>" <?=$y==$namChon?'selected':''?>><?=$y?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold small">Ngày:</label>
                        <input type="date" id="ngayLoc" class="form-control form-control-sm" style="width:150px" value="<?=$ngayChon?>">
                    </div>
                    <?php if(isAdmin()): ?>
                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold small">Nhân viên:</label>
                        <select id="nvLoc" class="form-select form-select-sm" style="width:150px">
                            <option value="">-- Tất cả --</option>
                            <?php
                            $nvList = $conn->query("SELECT id, ho_ten FROM users WHERE vai_tro='nhan_vien' AND trang_thai='hoat_dong' ORDER BY ho_ten");
                            while($nv = $nvList->fetch_assoc()):
                            ?>
                            <option value="<?=$nv['id']?>"><?=htmlspecialchars($nv['ho_ten'])?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" onclick="loadUngTien()">
                            <i class="fas fa-search me-1"></i>Lọc
                        </button>
                        <button class="btn btn-outline-secondary btn-sm ms-1" onclick="resetLoc()">
                            <i class="fas fa-times me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:#2c3e50; color:white;">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Nhân Viên</th>
                                <th>Ngày</th>
                                <th>Số Tiền Ứng</th>
                                <th>Lý Do</th>
                                <th>Ghi Chú</th>
                                <th>Trạng Thái</th>
                                <th>Lý Do Từ Chối</th>
                                <th class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyUngTien">
                            <tr><td colspan="9" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tạo Phiếu Ứng -->
<div class="modal fade" id="modalUngTien" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Tạo Phiếu Ứng Tiền</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="utId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Ngày Ứng <span class="text-danger">*</span></label>
                    <input type="date" id="utNgay" class="form-control" value="<?=date('Y-m-d')?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Số Tiền Ứng <span class="text-danger">*</span></label>
                    <input type="number" id="utSoTien" class="form-control" placeholder="Nhập số tiền" min="1">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Lý Do <span class="text-danger">*</span></label>
                    <textarea id="utLyDo" class="form-control" rows="2" placeholder="Nhập lý do ứng tiền..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ghi Chú</label>
                    <textarea id="utGhiChu" class="form-control" rows="2" placeholder="Ghi chú thêm..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Đóng
                </button>
                <button type="button" class="btn btn-primary" onclick="saveUngTien()">
                    <i class="fas fa-save me-1"></i>Lưu Lại
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Từ Chối -->
<div class="modal fade" id="modalTuChoi" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Từ Chối Phiếu Ứng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tcId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Lý Do Từ Chối <span class="text-danger">*</span></label>
                    <textarea id="tcLyDo" class="form-control" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="button" class="btn btn-danger" onclick="confirmTuChoi()">
                    <i class="fas fa-times me-1"></i>Xác Nhận Từ Chối
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const IS_ADMIN = <?= isAdmin() ? 'true' : 'false' ?>;

$(document).ready(function() { loadUngTien(); });

function loadUngTien() {
    const params = {
        thang: $('#thangLoc').val(),
        nam: $('#namLoc').val(),
        ngay: $('#ngayLoc').val(),
        nv_id: IS_ADMIN ? ($('#nvLoc').val() || '') : ''
    };
    $.get(BASE_URL + 'api/ung_tien/list.php', params, function(res) {
        if (!res.success) return;
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="9" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>';
        } else {
            res.data.forEach((ut, i) => {
                const badgeClass = ut.trang_thai === 'da_duyet' ? 'badge-da-duyet' :
                                   (ut.trang_thai === 'tu_choi' ? 'badge-tu-choi' : 'badge-cho-duyet');
                const badgeText = ut.trang_thai === 'da_duyet' ? 'Đã Duyệt' :
                                  (ut.trang_thai === 'tu_choi' ? 'Từ Chối' : 'Chờ Duyệt');
                const lyDoTuChoi = ut.ly_do_tu_choi ?
                    `<span class="ly-do-tu-choi-badge"><i class="fas fa-exclamation-circle me-1"></i>${ut.ly_do_tu_choi}</span>` : '-';

                html += `<tr>
                    <td class="ps-3">${i+1}</td>
                    <td><span class="badge bg-secondary">${ut.ho_ten}</span></td>
                    <td>${ut.ngay}</td>
                    <td class="fw-bold text-success">${formatMoney(ut.so_tien)}</td>
                    <td>${ut.ly_do}</td>
                    <td>${ut.ghi_chu || '-'}</td>
                    <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                    <td>${lyDoTuChoi}</td>
                    <td class="text-center">${renderUTActions(ut)}</td>
                </tr>`;
            });
        }
        $('#tbodyUngTien').html(html);
    }, 'json');
}

function renderUTActions(ut) {
    let html = '';
    if (ut.trang_thai === 'cho_duyet') {
        // Nhân viên sửa
        html += `<button class="btn btn-warning btn-sm me-1" onclick="editUngTien(${ut.id})">
                    <i class="fas fa-edit"></i>
                 </button>`;
        // Admin duyệt/từ chối
        if (IS_ADMIN) {
            html += `<button class="btn btn-success btn-sm me-1" onclick="duyetUngTien(${ut.id})">
                        <i class="fas fa-check me-1"></i>Duyệt
                     </button>
                     <button class="btn btn-danger btn-sm" onclick="openTuChoi(${ut.id})">
                        <i class="fas fa-times me-1"></i>Từ Chối
                     </button>`;
        }
    } else if (ut.trang_thai === 'da_duyet' && IS_ADMIN) {
        html += `<button class="btn btn-warning btn-sm" onclick="editUngTien(${ut.id})">
                    <i class="fas fa-edit"></i>
                 </button>`;
    }
    return html || '<span class="text-muted small">-</span>';
}

function openModalTao() {
    $('#utId').val('');
    $('#utNgay').val('<?=date('Y-m-d')?>');
    $('#utSoTien,#utLyDo,#utGhiChu').val('');
    new bootstrap.Modal('#modalUngTien').show();
}

function editUngTien(id) {
    $.get(BASE_URL + 'api/ung_tien/get.php', { id }, function(res) {
        if (!res.success) return showError(res.message);
        const ut = res.data;
        $('#utId').val(ut.id);
        $('#utNgay').val(ut.ngay);
        $('#utSoTien').val(ut.so_tien);
        $('#utLyDo').val(ut.ly_do);
        $('#utGhiChu').val(ut.ghi_chu);
        new bootstrap.Modal('#modalUngTien').show();
    }, 'json');
}

function saveUngTien() {
    const ngay = $('#utNgay').val();
    const soTien = $('#utSoTien').val();
    const lyDo = $('#utLyDo').val().trim();
    if (!ngay || !soTien || !lyDo) return showError('Vui lòng nhập đủ thông tin!');
    if (parseFloat(soTien) <= 0) return showError('Số tiền phải lớn hơn 0!');
    const id = $('#utId').val();
    const url = id ? BASE_URL + 'api/ung_tien/update.php' : BASE_URL + 'api/ung_tien/create.php';
    showLoading();
    apiCall(url, { id, ngay, so_tien: soTien, ly_do: lyDo, ghi_chu: $('#utGhiChu').val() }, function(res) {
        Swal.close();
        if (res.success) {
            bootstrap.Modal.getInstance('#modalUngTien').hide();
            showSuccess(res.message, loadUngTien);
        } else showError(res.message);
    });
}

function duyetUngTien(id) {
    showConfirm('Duyệt phiếu ứng?', 'Bạn xác nhận duyệt phiếu ứng này?', function() {
        showLoading('Đang duyệt...');
        apiCall(BASE_URL + 'api/ung_tien/duyet.php', { id }, function(res) {
            Swal.close();
            if (res.success) showSuccess(res.message, loadUngTien);
            else showError(res.message);
        });
    });
}

function openTuChoi(id) {
    $('#tcId').val(id);
    $('#tcLyDo').val('');
    new bootstrap.Modal('#modalTuChoi').show();
}

function confirmTuChoi() {
    const id = $('#tcId').val();
    const lyDo = $('#tcLyDo').val().trim();
    if (!lyDo) return showError('Vui lòng nhập lý do từ chối!');
    showLoading('Đang xử lý...');
    apiCall(BASE_URL + 'api/ung_tien/tu_choi.php', { id, ly_do: lyDo }, function(res) {
        Swal.close();
        if (res.success) {
            bootstrap.Modal.getInstance('#modalTuChoi').hide();
            showSuccess(res.message, loadUngTien);
        } else showError(res.message);
    });
}

function resetLoc() {
    $('#thangLoc').val(<?=date('m')?>);
    $('#namLoc').val(<?=date('Y')?>);
    $('#ngayLoc').val('');
    if (IS_ADMIN) $('#nvLoc').val('');
    loadUngTien();
}
</script>
<?php include '../../includes/footer.php'; ?>