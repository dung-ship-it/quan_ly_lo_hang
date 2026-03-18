<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Báo Cáo Tuần';
$conn = getDB();
$userId = $_SESSION['user_id'];

// Tính tuần hiện tại
$week = getCurrentWeek();
$tuanChon = intval($_GET['tuan'] ?? $week['week']);
$namChon = intval($_GET['nam'] ?? $week['year']);

// Tính ngày bắt đầu/kết thúc của tuần được chọn
$dto = new DateTime();
$dto->setISODate($namChon, $tuanChon);
$ngayBD = $dto->format('Y-m-d');
$dto->modify('+6 days');
$ngayKT = $dto->format('Y-m-d');

include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-calendar-week me-2"></i>Báo Cáo Tuần</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 fw-bold small">Tuần:</label>
                <select id="tuanChon" class="form-select form-select-sm" style="width:100px" onchange="doiTuan()">
                    <?php for($w=1;$w<=53;$w++): ?>
                    <option value="<?=$w?>" <?=$w==$tuanChon?'selected':''?>>Tuần <?=$w?></option>
                    <?php endfor; ?>
                </select>
                <select id="namChon" class="form-select form-select-sm" style="width:90px" onchange="doiTuan()">
                    <?php for($y=date('Y');$y>=date('Y')-2;$y--): ?>
                    <option value="<?=$y?>" <?=$y==$namChon?'selected':''?>><?=$y?></option>
                    <?php endfor; ?>
                </select>
                <span class="text-muted small">(<?=$ngayBD?> → <?=$ngayKT?>)</span>
            </div>
        </div>

        <!-- Cards thống kê -->
        <div id="summaryCards" class="mb-3"></div>

        <!-- Bảng lô hàng -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table table-bordered table-lo-hang mb-0" id="tableBCTuan">
                        <thead>
                            <tr>
                                <th>Nhân Viên</th>
                                <th>STT</th>
                                <th>Khách Hàng</th>
                                <th>House B/L</th>
                                <th>Công Ty</th>
                                <th>Số Tờ Khai</th>
                                <th>Thuế</th>
                                <th>Phí THC</th>
                                <th>Phi Lệnh</th>
                                <th>Mở TK</th>
                                <th>Kiểm</th>
                                <th>Giám Sát</th>
                                <th>Bốc Xếp, Xe Nâng</th>
                                <th>Handling</th>
                                <th>Xe Ôm</th>
                                <th>Xe Bus</th>
                                <th>CHI NGOÀI</th>
                                <th>Lý Do Chi Ngoài</th>
                                <th>Vận Chuyển</th>
                                <th>Công Ty Vận Chuyển</th>
                                <th>Biển Số Xe</th>
                                <th>Đính Kèm Ảnh</th>
                                <th>Trạng Thái</th>
                                <?php if(isAdmin()): ?>
                                <th>Thao Tác</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="tbodyBCTuan">
                            <tr><td colspan="24" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chi Tiết NV trong lô -->
<div class="modal fade" id="modalChiTiet" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:white;">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Chi Tiết Nhân Viên Trong Lô</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalChiTietBody"></div>
        </div>
    </div>
</div>

<!-- Modal Từ Chối Lô -->
<div class="modal fade" id="modalTuChoiLo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Từ Chối Lô Hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tcLoId">
                <div class="mb-3">
                    <label class="form-label fw-bold">Lý Do Từ Chối <span class="text-danger">*</span></label>
                    <textarea id="tcLoLyDo" class="form-control" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                <button type="button" class="btn btn-danger" onclick="confirmTuChoiLo()">
                    <i class="fas fa-times me-1"></i>Xác Nhận Từ Chối
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const IS_ADMIN = <?= isAdmin() ? 'true' : 'false' ?>;
const USER_ID = <?= $userId ?>;

$(document).ready(function() {
    loadBaoCaoTuan();
    loadSummaryCards();
});

function doiTuan() {
    const tuan = $('#tuanChon').val();
    const nam = $('#namChon').val();
    window.location.href = `?tuan=${tuan}&nam=${nam}`;
}

function loadBaoCaoTuan() {
    const tuan = $('#tuanChon').val();
    const nam = $('#namChon').val();
    $.get(BASE_URL + 'api/bao_cao_tuan/list.php', { tuan, nam }, function(res) {
        if (!res.success) {
            $('#tbodyBCTuan').html('<tr><td colspan="24" class="text-center py-4 text-danger">Lỗi tải dữ liệu!</td></tr>');
            return;
        }
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="24" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>';
        } else {
            res.data.forEach((lo, i) => {
                const rowClass = lo.trang_thai_duyet === 'da_duyet' ? 'trang_thai_hoan_thanh' :
                                 (lo.trang_thai_duyet === 'tu_choi' ? 'trang_thai_tu_choi' : '');
                const badgeClass = lo.trang_thai_duyet === 'da_duyet' ? 'badge-da-duyet' :
                                   (lo.trang_thai_duyet === 'tu_choi' ? 'badge-tu-choi' : 'badge-cho-duyet');
                const badgeText = lo.trang_thai_duyet === 'da_duyet' ? 'Đã Duyệt' :
                                  (lo.trang_thai_duyet === 'tu_choi' ? 'Từ Chối' : 'Chờ Duyệt');
                const lyDoTC = lo.ly_do_tu_choi ?
                    `<div class="ly-do-tu-choi-badge mt-1">${lo.ly_do_tu_choi}</div>` : '';

                html += `<tr class="lo-hang-row ${rowClass}">
                    <td>
                        <span class="badge bg-secondary">${lo.nhan_vien_list || lo.ho_ten}</span>
                        ${IS_ADMIN && lo.co_nhieu_nv ? `<button class="btn btn-link btn-sm p-0 ms-1" onclick="xemChiTiet(${lo.id})"><i class="fas fa-info-circle"></i></button>` : ''}
                    </td>
                    <td>${i+1}</td>
                    <td>${lo.ma_khach}</td>
                    <td>${lo.house_bl}</td>
                    <td>${lo.cong_ty}</td>
                    <td>${lo.so_to_khai}</td>
                    <td>${formatMoney(lo.thue)}</td>
                    <td>${formatMoney(lo.phi_thc)}</td>
                    <td>${formatMoney(lo.phi_lenh)}</td>
                    <td>${formatMoney(lo.mo_tk)}</td>
                    <td>${formatMoney(lo.kiem)}</td>
                    <td>${lo.giam_sat || '-'}</td>
                    <td>${formatMoney(lo.boc_xep_xe_nang)}</td>
                    <td>${formatMoney(lo.handling)}</td>
                    <td>${formatMoney(lo.xe_om)}</td>
                    <td>${formatMoney(lo.xe_bus)}</td>
                    <td>${formatMoney(lo.chi_ngoai)}</td>
                    <td>${lo.ly_do_chi_ngoai || '-'}</td>
                    <td>${formatMoney(lo.van_chuyen)}</td>
                    <td>${lo.cong_ty_van_chuyen || '-'}</td>
                    <td>${lo.bien_so_xe || '-'}</td>
                    <td>${renderAnh(lo.anh_list)}</td>
                    <td>
                        <span class="badge ${badgeClass}">${badgeText}</span>
                        ${lyDoTC}
                    </td>
                    ${IS_ADMIN ? `<td class="text-center">${renderAdminActions(lo)}</td>` : ''}
                </tr>`;
            });
        }
        $('#tbodyBCTuan').html(html);
    }, 'json').fail(function() {
        $('#tbodyBCTuan').html('<tr><td colspan="24" class="text-center py-4 text-danger">Không thể kết nối API!</td></tr>');
    });
}

function renderAdminActions(lo) {
    if (lo.trang_thai_duyet === 'da_duyet') {
        return `<button class="btn btn-warning btn-sm" onclick="suaLoBCT(${lo.id})">
                    <i class="fas fa-edit"></i>
                </button>`;
    }
    return `<button class="btn btn-success btn-sm me-1" onclick="duyetLo(${lo.bct_id})">
                <i class="fas fa-check me-1"></i>Duyệt
            </button>
            <button class="btn btn-danger btn-sm" onclick="openTuChoiLo(${lo.bct_id})">
                <i class="fas fa-times me-1"></i>Từ Chối
            </button>`;
}

function renderAnh(anhList) {
    if (!anhList || anhList.length === 0) return '-';
    return anhList.map(a =>
        `<a href="${BASE_URL}assets/uploads/${a.ten_file}" target="_blank">
            <img src="${BASE_URL}assets/uploads/${a.ten_file}" style="width:28px;height:28px;object-fit:cover;border-radius:3px;">
         </a>`
    ).join('');
}

function loadSummaryCards() {
    const tuan = $('#tuanChon').val();
    const nam = $('#namChon').val();
    $.get(BASE_URL + 'api/bao_cao_tuan/tong_hop.php', { tuan, nam }, function(res) {
        if (!res.success) return;
        const d = res.data;
        let html = '<div class="summary-cards">';

        html += `
            <div class="summary-card ung">
                <div class="sc-label">Tổng Tiền Ứng (Tuần)</div>
                <div class="sc-value">${formatMoney(d.tong_ung)}</div>
            </div>
            <div class="summary-card lam">
                <div class="sc-label">Tổng Tiền Làm (Tuần)</div>
                <div class="sc-value">${formatMoney(d.tong_lam)}</div>
            </div>
            <div class="summary-card con-lai">
                <div class="sc-label">Còn Lại (Cộng Dồn)</div>
                <div class="sc-value">${formatMoney(d.con_lai)}</div>
            </div>`;

        if (IS_ADMIN && d.theo_nv) {
            d.theo_nv.forEach(nv => {
                html += `<div style="width:100%;border-top:1px dashed #dee2e6;padding-top:10px;margin-top:5px;">
                    <strong class="small text-muted">👤 ${nv.ho_ten}</strong>
                </div>
                <div class="summary-card ung">
                    <div class="sc-name">${nv.ho_ten}</div>
                    <div class="sc-label">Tiền Ứng</div>
                    <div class="sc-value">${formatMoney(nv.tong_ung)}</div>
                </div>
                <div class="summary-card lam">
                    <div class="sc-name">${nv.ho_ten}</div>
                    <div class="sc-label">Tiền Làm</div>
                    <div class="sc-value">${formatMoney(nv.tong_lam)}</div>
                </div>
                <div class="summary-card con-lai">
                    <div class="sc-name">${nv.ho_ten}</div>
                    <div class="sc-label">Còn Lại</div>
                    <div class="sc-value">${formatMoney(nv.con_lai)}</div>
                </div>`;
            });
        }
        html += '</div>';
        $('#summaryCards').html(html);
    }, 'json');
}

function duyetLo(bctId) {
    showConfirm('Duyệt lô hàng?', 'Lô hàng sẽ được chuyển sang Báo Cáo Tháng!', function() {
        showLoading('Đang duyệt...');
        apiCall(BASE_URL + 'api/bao_cao_tuan/duyet_lo.php', { id: bctId }, function(res) {
            Swal.close();
            if (res.success) { showSuccess(res.message, function(){ loadBaoCaoTuan(); loadSummaryCards(); }); }
            else showError(res.message);
        });
    });
}

function openTuChoiLo(bctId) {
    $('#tcLoId').val(bctId);
    $('#tcLoLyDo').val('');
    new bootstrap.Modal('#modalTuChoiLo').show();
}

function confirmTuChoiLo() {
    const id = $('#tcLoId').val();
    const lyDo = $('#tcLoLyDo').val().trim();
    if (!lyDo) return showError('Vui lòng nhập lý do từ chối!');
    showLoading('Đang xử lý...');
    apiCall(BASE_URL + 'api/bao_cao_tuan/tu_choi_lo.php', { id, ly_do: lyDo }, function(res) {
        Swal.close();
        if (res.success) {
            bootstrap.Modal.getInstance('#modalTuChoiLo').hide();
            showSuccess(res.message, function(){ loadBaoCaoTuan(); loadSummaryCards(); });
        } else showError(res.message);
    });
}

function xemChiTiet(loId) {
    $.get(BASE_URL + 'api/bao_cao_tuan/chi_tiet_nv.php', { lo_id: loId }, function(res) {
        if (!res.success) return;
        let html = '<div class="table-responsive"><table class="table table-bordered table-sm">';
        html += '<thead style="background:#2c3e50;color:white;"><tr><th>Nhân Viên</th><th>Thuế</th><th>Phí THC</th><th>Phi Lệnh</th><th>Mở TK</th><th>Kiểm</th><th>Giám Sát</th><th>Bốc Xếp</th><th>Handling</th><th>Xe Ôm</th><th>Xe Bus</th><th>Chi Ngoài</th><th>Vận Chuyển</th></tr></thead><tbody>';
        res.data.forEach(nv => {
            html += `<tr>
                <td><strong>${nv.ho_ten}</strong></td>
                <td>${formatMoney(nv.thue)}</td><td>${formatMoney(nv.phi_thc)}</td>
                <td>${formatMoney(nv.phi_lenh)}</td><td>${formatMoney(nv.mo_tk)}</td>
                <td>${formatMoney(nv.kiem)}</td><td>${nv.giam_sat||'-'}</td>
                <td>${formatMoney(nv.boc_xep_xe_nang)}</td><td>${formatMoney(nv.handling)}</td>
                <td>${formatMoney(nv.xe_om)}</td><td>${formatMoney(nv.xe_bus)}</td>
                <td>${formatMoney(nv.chi_ngoai)}</td><td>${formatMoney(nv.van_chuyen)}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        $('#modalChiTietBody').html(html);
        new bootstrap.Modal('#modalChiTiet').show();
    }, 'json');
}

function suaLoBCT(loId) {
    window.location.href = BASE_URL + 'modules/lo_hang/index.php?edit_lo=' + loId;
}
</script>
<?php include '../../includes/footer.php'; ?>