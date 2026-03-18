<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Nhập Lô Hàng';
$conn = getDB();
$userId = $_SESSION['user_id'];
$ngayChon = $_GET['ngay'] ?? date('Y-m-d');

// Lấy danh sách khách hàng cho dropdown
$khachHangList = [];
$r = $conn->query("SELECT id, ma_khach, ten_day_du FROM khach_hang ORDER BY ma_khach ASC");
while ($row = $r->fetch_assoc()) $khachHangList[] = $row;

include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-box me-2"></i>Nhập Lô Hàng</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 fw-bold small">Ngày:</label>
                <input type="date" id="ngayChon" class="form-control form-control-sm" style="width:150px"
                    value="<?= $ngayChon ?>" onchange="doiNgay(this.value)">
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
                                <th class="col-nv">Nhân Viên</th>
                                <th style="min-width:40px">STT</th>
                                <th class="col-kh">Khách Hàng <span class="text-danger">*</span></th>
                                <th class="col-house">House B/L <span class="text-danger">*</span></th>
                                <th class="col-cong-ty">Công Ty <span class="text-danger">*</span></th>
                                <th class="col-so-tk">Số Tờ Khai <span class="text-danger">*</span></th>
                                <th class="col-tien">Thuế</th>
                                <th class="col-tien">Phí THC</th>
                                <th class="col-tien">Phi Lệnh</th>
                                <th class="col-tien">Mở TK</th>
                                <th class="col-tien">Kiểm</th>
                                <th class="col-giam-sat">Giám Sát</th>
                                <th class="col-tien">Bốc Xếp, Xe Nâng</th>
                                <th class="col-tien">Handling</th>
                                <th class="col-tien">Xe Ôm</th>
                                <th class="col-tien">Xe Bus</th>
                                <th class="col-tien">CHI NGOÀI</th>
                                <th class="col-ly-do">Lý Do Chi Ngoài</th>
                                <th class="col-van-chuyen">Vận Chuyển</th>
                                <th class="col-cong-ty-vc">Công Ty Vận Chuyển</th>
                                <th class="col-bien-so">Biển Số Xe</th>
                                <th class="col-anh">Đính Kèm Ảnh</th>
                                <th class="col-trang-thai">Trạng Thái</th>
                                <th class="col-action">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyLoHang">
                            <tr><td colspan="24" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center bg-light">
                <button class="btn btn-success btn-sm" onclick="themLoHang()">
                    <i class="fas fa-plus me-1"></i>Thêm Lô Hàng
                </button>
                <div id="btnLuuNgayWrapper">
                    <button class="btn btn-primary btn-sm" id="btnLuuNgay" onclick="luuNgay()" style="display:none">
                        <i class="fas fa-save me-1"></i>Lưu Ngày
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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

<script>
const BASE_URL = '<?= BASE_URL ?>';
const IS_ADMIN = <?= isAdmin() ? 'true' : 'false' ?>;
const USER_ID = <?= $userId ?>;
const NGAY_CHON = '<?= $ngayChon ?>';
const KHACH_HANG_LIST = <?= json_encode($khachHangList) ?>;

let loHangData = [];
let hasUnsaved = false;

$(document).ready(function() {
    loadLoHang();
});

function doiNgay(ngay) {
    if (hasUnsaved) {
        showConfirm('Chưa lưu!', 'Bạn có dữ liệu chưa lưu. Bạn có muốn đổi ngày không?', function() {
            window.location.href = '?ngay=' + ngay;
        });
        return;
    }
    window.location.href = '?ngay=' + ngay;
}

function loadLoHang() {
    $.get(BASE_URL + 'api/lo_hang/list.php', { ngay: NGAY_CHON }, function(res) {
        if (!res.success) return;
        loHangData = res.data;
        renderTable();
    }, 'json');
}

function renderTable() {
    let html = '';
    let coTrang = false;
    if (loHangData.length === 0) {
        html = '<tr><td colspan="24" class="text-center py-4 text-muted">Chưa có lô hàng nào. Nhấn "Thêm Lô Hàng" để bắt đầu.</td></tr>';
    } else {
        loHangData.forEach((lh, idx) => {
            const isEditable = lh.trang_thai === 'chua_luu' || lh.trang_thai === 'dang_sua';
            if (lh.trang_thai === 'chua_luu') coTrang = true;
            const rowClass = getTrangThaiClass(lh.trang_thai);
            const canEdit = IS_ADMIN || lh.trang_thai !== 'hoan_thanh';
            html += renderRow(lh, idx, isEditable, rowClass, canEdit);
        });
    }
    $('#tbodyLoHang').html(html);
    hasUnsaved = coTrang;
    $('#btnLuuNgay').toggle(coTrang);
    initFileInputs();
}

function getTrangThaiClass(tt) {
    switch(tt) {
        case 'chua_luu': return 'trang_thai_chua_luu';
        case 'da_luu': return 'trang_thai_da_luu';
        case 'hoan_thanh': return 'trang_thai_hoan_thanh';
        case 'tu_choi': return 'trang_thai_tu_choi';
        default: return '';
    }
}

function renderKhachHangOptions(selectedId) {
    return KHACH_HANG_LIST.map(kh =>
        `<option value="${kh.id}" ${kh.id == selectedId ? 'selected' : ''}>${kh.ma_khach}</option>`
    ).join('');
}

function renderRow(lh, idx, isEditable, rowClass, canEdit) {
    const dis = isEditable ? '' : 'readonly';
    const disSelect = isEditable ? '' : 'disabled';
    const lyDoTuChoi = lh.ly_do_tu_choi ?
        `<div class="ly-do-tu-choi-badge mt-1"><i class="fas fa-exclamation-circle me-1"></i>${lh.ly_do_tu_choi}</div>` : '';

    return `<tr class="lo-hang-row ${rowClass}" data-id="${lh.id || ''}" data-idx="${idx}">
        <td class="col-nv"><span class="badge bg-secondary">${lh.ho_ten || '<?= htmlspecialchars($_SESSION['ho_ten']) ?>'}</span></td>
        <td style="text-align:center; font-weight:bold">${idx+1}</td>
        <td class="col-kh">
            <select class="form-select form-select-sm" name="khach_hang_id" ${disSelect} onchange="onFieldChange(${idx})">
                <option value="">-- Chọn KH --</option>
                ${renderKhachHangOptions(lh.khach_hang_id)}
            </select>
        </td>
        <td class="col-house">
            <input type="text" class="form-control form-control-sm" name="house_bl" value="${lh.house_bl||''}"
                ${dis} onchange="checkTrung(${idx}, 'house_bl', this.value); onFieldChange(${idx})">
        </td>
        <td class="col-cong-ty">
            <input type="text" class="form-control form-control-sm" name="cong_ty" value="${lh.cong_ty||''}" ${dis} onchange="onFieldChange(${idx})">
        </td>
        <td class="col-so-tk">
            <input type="text" class="form-control form-control-sm" name="so_to_khai" value="${lh.so_to_khai||''}"
                ${dis} onchange="checkTrung(${idx}, 'so_to_khai', this.value); onFieldChange(${idx})">
        </td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="thue" value="${lh.thue||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien">
            <input type="number" class="form-control form-control-sm input-tien" name="phi_thc" value="${lh.phi_thc||''}" ${dis} onchange="onPhiThcChange(${idx})">
        </td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="phi_lenh" value="${lh.phi_lenh||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="mo_tk" value="${lh.mo_tk||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="kiem" value="${lh.kiem||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-giam-sat"><input type="text" class="form-control form-control-sm" name="giam_sat" value="${lh.giam_sat||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="boc_xep_xe_nang" value="${lh.boc_xep_xe_nang||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="handling" value="${lh.handling||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="xe_om" value="${lh.xe_om||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="xe_bus" value="${lh.xe_bus||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-tien"><input type="number" class="form-control form-control-sm input-tien" name="chi_ngoai" value="${lh.chi_ngoai||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-ly-do"><input type="text" class="form-control form-control-sm" name="ly_do_chi_ngoai" value="${lh.ly_do_chi_ngoai||''}" ${dis} onchange="onFieldChange(${idx})"></td>
        <td class="col-van-chuyen"><input type="number" class="form-control form-control-sm input-tien" name="van_chuyen" value="${lh.van_chuyen||''}" ${dis} onchange="onVanChuyenChange(${idx})"></td>
        <td class="col-cong-ty-vc"><input type="text" class="form-control form-control-sm" name="cong_ty_van_chuyen" value="${lh.cong_ty_van_chuyen||''}" ${dis} onchange="onVanChuyenChange(${idx})"></td>
        <td class="col-bien-so"><input type="text" class="form-control form-control-sm" name="bien_so_xe" value="${lh.bien_so_xe||''}" ${dis} onchange="onVanChuyenChange(${idx})"></td>
        <td class="col-anh">
            ${isEditable ? `<input type="file" class="form-control form-control-sm file-anh" data-idx="${idx}" multiple accept="image/*">` : ''}
            <div class="mt-1" id="anhPreview_${idx}">
                ${renderAnhDinhKem(lh.anh_list || [], lh.id)}
            </div>
        </td>
        <td class="col-trang-thai text-center">
            <div>
                ${lh.trang_thai === 'hoan_thanh'
                    ? '<span class="badge badge-hoan-thanh"><i class="fas fa-check me-1"></i>Hoàn Thành</span>'
                    : (lh.trang_thai === 'da_luu'
                        ? `<div class="form-check d-inline-block">
                            <input class="form-check-input" type="checkbox" id="ht_${idx}"
                                onchange="tickHoanThanh(${idx}, this.checked)">
                            <label class="form-check-label small" for="ht_${idx}">Hoàn thành</label>
                           </div>`
                        : '<span class="badge badge-chua-hoan-thanh">Chưa lưu</span>')
                }
            </div>
            ${lyDoTuChoi}
        </td>
        <td class="col-action text-center">
            ${renderActionButtons(lh, idx, isEditable, canEdit)}
        </td>
    </tr>`;
}

function renderActionButtons(lh, idx, isEditable, canEdit) {
    if (isEditable) {
        return `<button class="btn btn-danger btn-sm" onclick="xoaLoHang(${idx})">
                    <i class="fas fa-trash"></i>
                </button>`;
    }
    if (lh.trang_thai === 'hoan_thanh') {
        return `<span class="text-muted small">Đã hoàn thành</span>`;
    }
    if (canEdit) {
        return `<button class="btn btn-warning btn-sm me-1" onclick="suaLoHang(${idx})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="xoaLoHang(${idx})">
                    <i class="fas fa-trash"></i>
                </button>`;
    }
    return '';
}

function renderAnhDinhKem(anhList, loId) {
    if (!anhList || anhList.length === 0) return '';
    return anhList.map(a =>
        `<a href="${BASE_URL}assets/uploads/${a.ten_file}" target="_blank" class="me-1">
            <img src="${BASE_URL}assets/uploads/${a.ten_file}" style="width:30px;height:30px;object-fit:cover;border-radius:4px;" title="${a.ten_file}">
         </a>`
    ).join('');
}

function initFileInputs() {
    $('.file-anh').off('change').on('change', function() {
        const idx = $(this).data('idx');
        const files = this.files;
        if (files.length > 0 && loHangData[idx]) {
            loHangData[idx]._newFiles = files;
        }
    });
}

function onFieldChange(idx) {
    if (loHangData[idx] && loHangData[idx].trang_thai !== 'chua_luu') return;
    hasUnsaved = true;
    $('#btnLuuNgay').show();
}

function onPhiThcChange(idx) {
    onFieldChange(idx);
    const val = $(`tr[data-idx="${idx}"] input[name="phi_thc"]`).val();
    if (val && parseFloat(val) > 0) {
        $(`tr[data-idx="${idx}"] input[name="phi_thc"]`).css('border-color', '#ffc107');
    }
}

function onVanChuyenChange(idx) {
    onFieldChange(idx);
}

function checkTrung(idx, field, value) {
    if (!value) return;
    $.get(BASE_URL + 'api/lo_hang/check_trung.php', {
        field, value, ngay: NGAY_CHON, exclude_idx: idx
    }, function(res) {
        if (res.found) {
            $('#alertTrungMsg').html(
                `⚠️ ${field === 'house_bl' ? 'House B/L' : 'Số Tờ Khai'} <strong>"${value}"</strong> đã được nhập ngày <strong>${res.ngay}</strong>. 
                <a href="?ngay=${res.ngay}" class="alert-link">Xem lại ngày đó</a>`
            );
            $('#alertTrung').removeClass('d-none');
            setTimeout(() => $('#alertTrung').addClass('d-none'), 8000);
        }
    }, 'json');
}

function themLoHang() {
    const newLo = {
        id: null, trang_thai: 'chua_luu',
        ho_ten: '<?= htmlspecialchars($_SESSION['ho_ten']) ?>',
        khach_hang_id: '', house_bl: '', cong_ty: '', so_to_khai: '',
        thue: '', phi_thc: '', phi_lenh: '', mo_tk: '', kiem: '',
        giam_sat: '', boc_xep_xe_nang: '', handling: '', xe_om: '', xe_bus: '',
        chi_ngoai: '', ly_do_chi_ngoai: '', van_chuyen: '', cong_ty_van_chuyen: '', bien_so_xe: '',
        anh_list: []
    };
    loHangData.push(newLo);
    renderTable();
}

function suaLoHang(idx) {
    loHangData[idx].trang_thai = 'chua_luu';
    loHangData[idx]._editing = true;
    hasUnsaved = true;
    renderTable();
}

function xoaLoHang(idx) {
    const lh = loHangData[idx];
    if (lh.trang_thai === 'hoan_thanh') return showError('Không thể xoá lô đã hoàn thành!');
    showConfirm('Xoá lô hàng?', 'Bạn có chắc muốn xoá lô hàng này?', function() {
        if (lh.id) {
            showLoading('Đang xoá...');
            apiCall(BASE_URL + 'api/lo_hang/delete.php', { id: lh.id }, function(res) {
                Swal.close();
                if (res.success) { loHangData.splice(idx, 1); renderTable(); }
                else showError(res.message);
            });
        } else {
            loHangData.splice(idx, 1);
            renderTable();
        }
    });
}

function getRowData(idx) {
    const row = $(`tr[data-idx="${idx}"]`);
    return {
        khach_hang_id: row.find('[name="khach_hang_id"]').val(),
        house_bl: row.find('[name="house_bl"]').val(),
        cong_ty: row.find('[name="cong_ty"]').val(),
        so_to_khai: row.find('[name="so_to_khai"]').val(),
        thue: row.find('[name="thue"]').val() || 0,
        phi_thc: row.find('[name="phi_thc"]').val() || 0,
        phi_lenh: row.find('[name="phi_lenh"]').val() || 0,
        mo_tk: row.find('[name="mo_tk"]').val() || 0,
        kiem: row.find('[name="kiem"]').val() || 0,
        giam_sat: row.find('[name="giam_sat"]').val(),
        boc_xep_xe_nang: row.find('[name="boc_xep_xe_nang"]').val() || 0,
        handling: row.find('[name="handling"]').val() || 0,
        xe_om: row.find('[name="xe_om"]').val() || 0,
        xe_bus: row.find('[name="xe_bus"]').val() || 0,
        chi_ngoai: row.find('[name="chi_ngoai"]').val() || 0,
        ly_do_chi_ngoai: row.find('[name="ly_do_chi_ngoai"]').val(),
        van_chuyen: row.find('[name="van_chuyen"]').val() || 0,
        cong_ty_van_chuyen: row.find('[name="cong_ty_van_chuyen"]').val(),
        bien_so_xe: row.find('[name="bien_so_xe"]').val(),
    };
}

function luuNgay() {
    const toSave = [];
    loHangData.forEach((lh, idx) => {
        if (lh.trang_thai === 'chua_luu') {
            const data = getRowData(idx);
            data.id = lh.id;
            data.ngay = NGAY_CHON;
            data._newFiles = lh._newFiles || null;
            toSave.push({ idx, data });
        }
    });
    if (toSave.length === 0) return;

    // Validate
    for (const item of toSave) {
        const d = item.data;
        if (!d.khach_hang_id) return showError(`Dòng ${item.idx+1}: Vui lòng chọn khách hàng!`);
        if (!d.house_bl) return showError(`Dòng ${item.idx+1}: Vui lòng nhập House B/L!`);
        if (!d.cong_ty) return showError(`Dòng ${item.idx+1}: Vui lòng nhập Công Ty!`);
        if (!d.so_to_khai) return showError(`Dòng ${item.idx+1}: Vui lòng nhập Số Tờ Khai!`);
    }

    showLoading('Đang lưu...');
    let saved = 0;
    toSave.forEach(item => {
        const formData = new FormData();
        Object.keys(item.data).forEach(k => {
            if (k !== '_newFiles') formData.append(k, item.data[k] || '');
        });
        if (item.data._newFiles) {
            for (let f of item.data._newFiles) formData.append('anh_files[]', f);
        }
        $.ajax({
            url: BASE_URL + 'api/lo_hang/save.php',
            type: 'POST', data: formData,
            processData: false, contentType: false,
            success: function(res) {
                saved++;
                if (saved === toSave.length) {
                    Swal.close();
                    if (res.success) showSuccess('Lưu thành công!', loadLoHang);
                    else showError(res.message);
                }
            },
            error: function() { Swal.close(); showError('Lỗi khi lưu!'); }
        });
    });
}

function tickHoanThanh(idx, checked) {
    if (!checked) return;
    const lh = loHangData[idx];
    const data = getRowData(idx);
    data.id = lh.id;
    data.ngay = NGAY_CHON;

    showLoading('Đang kiểm tra...');
    apiCall(BASE_URL + 'api/lo_hang/hoan_thanh.php', data, function(res) {
        Swal.close();
        if (res.success) {
            showSuccess('Đánh dấu hoàn thành!', loadLoHang);
        } else {
            $(`#ht_${idx}`).prop('checked', false);
            showError(res.message);
        }
    });
}
</script>
<?php include '../../includes/footer.php'; ?>
