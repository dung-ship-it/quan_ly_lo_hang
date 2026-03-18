<?php
require_once '../../config/database.php';
requireLogin();
$pageTitle = 'Báo Cáo Tháng';
$conn = getDB();
$userId = $_SESSION['user_id'];
$thangChon = intval($_GET['thang'] ?? date('m'));
$namChon = intval($_GET['nam'] ?? date('Y'));
include '../../includes/header.php';
?>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="top-navbar">
            <h5 class="page-title"><i class="fas fa-calendar-alt me-2"></i>Báo Cáo Tháng</h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select id="thangLoc" class="form-select form-select-sm" style="width:110px">
                    <?php for($i=1;$i<=12;$i++): ?>
                    <option value="<?=$i?>" <?=$i==$thangChon?'selected':''?>>Tháng <?=$i?></option>
                    <?php endfor; ?>
                </select>
                <select id="namLoc" class="form-select form-select-sm" style="width:90px">
                    <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
                    <option value="<?=$y?>" <?=$y==$namChon?'selected':''?>><?=$y?></option>
                    <?php endfor; ?>
                </select>
                <select id="tuanLoc" class="form-select form-select-sm" style="width:110px">
                    <option value="">-- Tất cả tuần --</option>
                    <?php for($w=1;$w<=53;$w++): ?>
                    <option value="<?=$w?>">Tuần <?=$w?></option>
                    <?php endfor; ?>
                </select>
                <input type="date" id="ngayLoc" class="form-control form-control-sm" style="width:150px" placeholder="Lọc theo ngày">
                <?php if(isAdmin()): ?>
                <select id="nvLoc" class="form-select form-select-sm" style="width:150px">
                    <option value="">-- Tất cả NV --</option>
                    <?php
                    $nvs = $conn->query("SELECT id, ho_ten FROM users WHERE vai_tro='nhan_vien' ORDER BY ho_ten");
                    while($nv = $nvs->fetch_assoc()):
                    ?>
                    <option value="<?=$nv['id']?>"><?=htmlspecialchars($nv['ho_ten'])?></option>
                    <?php endwhile; ?>
                </select>
                <?php endif; ?>
                <select id="khLoc" class="form-select form-select-sm" style="width:150px">
                    <option value="">-- Tất cả KH --</option>
                    <?php
                    $khs = $conn->query("SELECT id, ma_khach, ten_day_du FROM khach_hang ORDER BY ma_khach");
                    while($kh = $khs->fetch_assoc()):
                    ?>
                    <option value="<?=$kh['id']?>"><?=htmlspecialchars($kh['ma_khach'].' - '.$kh['ten_day_du'])?></option>
                    <?php endwhile; ?>
                </select>
                <button class="btn btn-primary btn-sm" onclick="loadBaoCaoThang()">
                    <i class="fas fa-search me-1"></i>Lọc
                </button>
                <button class="btn btn-success btn-sm" onclick="xuatExcel()">
                    <i class="fas fa-file-excel me-1"></i>Xuất Excel
                </button>
            </div>
        </div>

        <!-- Cards thống kê -->
        <div id="summaryCards" class="mb-3"></div>

        <!-- Bảng -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table table-bordered table-lo-hang mb-0" id="tableBCThang">
                        <thead>
                            <tr>
                                <th>Ngày</th>
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
                            </tr>
                        </thead>
                        <tbody id="tbodyBCThang">
                            <tr><td colspan="23" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i>Đang tải...
                            </td></tr>
                        </tbody>
                        <tfoot id="tfootBCThang"></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const IS_ADMIN = <?= isAdmin() ? 'true' : 'false' ?>;

$(document).ready(function() {
    loadBaoCaoThang();
    loadSummaryCards();
});

function getParams() {
    return {
        thang: $('#thangLoc').val(), nam: $('#namLoc').val(),
        tuan: $('#tuanLoc').val(), ngay: $('#ngayLoc').val(),
        nv_id: $('#nvLoc').val() || '', kh_id: $('#khLoc').val() || ''
    };
}

function loadBaoCaoThang() {
    $('#tbodyBCThang').html('<tr><td colspan="23" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Đang tải...</td></tr>');
    $.get(BASE_URL + 'api/bao_cao_thang/list.php', getParams(), function(res) {
        if (!res.success) {
            $('#tbodyBCThang').html('<tr><td colspan="23" class="text-center py-4 text-danger">Lỗi tải dữ liệu!</td></tr>');
            return;
        }
        let html = '', tongRow = {};
        const moneyFields = ['thue','phi_thc','phi_lenh','mo_tk','kiem','boc_xep_xe_nang','handling','xe_om','xe_bus','chi_ngoai','van_chuyen'];
        moneyFields.forEach(f => tongRow[f] = 0);

        if (res.data.length === 0) {
            html = '<tr><td colspan="23" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>';
        } else {
            res.data.forEach(lo => {
                moneyFields.forEach(f => tongRow[f] += parseFloat(lo[f]||0));
                html += `<tr class="trang_thai_hoan_thanh">
                    <td>${lo.ngay}</td>
                    <td><span class="badge bg-secondary">${lo.nhan_vien}</span></td>
                    <td>${lo.stt}</td>
                    <td>${lo.ma_khach}</td>
                    <td>${lo.house_bl}</td>
                    <td>${lo.cong_ty}</td>
                    <td>${lo.so_to_khai}</td>
                    <td>${formatMoney(lo.thue)}</td>
                    <td>${formatMoney(lo.phi_thc)}</td>
                    <td>${formatMoney(lo.phi_lenh)}</td>
                    <td>${formatMoney(lo.mo_tk)}</td>
                    <td>${formatMoney(lo.kiem)}</td>
                    <td>${lo.giam_sat||'-'}</td>
                    <td>${formatMoney(lo.boc_xep_xe_nang)}</td>
                    <td>${formatMoney(lo.handling)}</td>
                    <td>${formatMoney(lo.xe_om)}</td>
                    <td>${formatMoney(lo.xe_bus)}</td>
                    <td>${formatMoney(lo.chi_ngoai)}</td>
                    <td>${lo.ly_do_chi_ngoai||'-'}</td>
                    <td>${formatMoney(lo.van_chuyen)}</td>
                    <td>${lo.cong_ty_van_chuyen||'-'}</td>
                    <td>${lo.bien_so_xe||'-'}</td>
                    <td>${renderAnh(lo.anh_list)}</td>
                </tr>`;
            });
        }
        $('#tbodyBCThang').html(html);

        // Tổng cộng cuối bảng
        let footHtml = `<tr style="background:#2c3e50;color:white;font-weight:bold;">
            <td colspan="7" class="text-end pe-3">TỔNG CỘNG:</td>
            <td>${formatMoney(tongRow.thue)}</td>
            <td>${formatMoney(tongRow.phi_thc)}</td>
            <td>${formatMoney(tongRow.phi_lenh)}</td>
            <td>${formatMoney(tongRow.mo_tk)}</td>
            <td>${formatMoney(tongRow.kiem)}</td>
            <td>-</td>
            <td>${formatMoney(tongRow.boc_xep_xe_nang)}</td>
            <td>${formatMoney(tongRow.handling)}</td>
            <td>${formatMoney(tongRow.xe_om)}</td>
            <td>${formatMoney(tongRow.xe_bus)}</td>
            <td>${formatMoney(tongRow.chi_ngoai)}</td>
            <td>-</td>
            <td>${formatMoney(tongRow.van_chuyen)}</td>
            <td colspan="3">-</td>
        </tr>`;
        $('#tfootBCThang').html(footHtml);
        loadSummaryCards();
    }, 'json').fail(function() {
        $('#tbodyBCThang').html('<tr><td colspan="23" class="text-center py-4 text-danger">Không thể kết nối API!</td></tr>');
    });
}

function loadSummaryCards() {
    $.get(BASE_URL + 'api/bao_cao_thang/tong_hop.php', getParams(), function(res) {
        if (!res.success) return;
        const d = res.data;
        let html = '<div class="summary-cards">';
        html += `
            <div class="summary-card ung">
                <div class="sc-label">Tổng Tiền Ứng (Tháng)</div>
                <div class="sc-value">${formatMoney(d.tong_ung)}</div>
            </div>
            <div class="summary-card lam">
                <div class="sc-label">Tổng Tiền Làm (Tháng)</div>
                <div class="sc-value">${formatMoney(d.tong_lam)}</div>
            </div>
            <div class="summary-card con-lai">
                <div class="sc-label">Còn Lại (Cộng Dồn)</div>
                <div class="sc-value">${formatMoney(d.con_lai)}</div>
            </div>`;
        if (IS_ADMIN && d.theo_nv) {
            d.theo_nv.forEach(nv => {
                html += `
                <div style="width:100%;border-top:1px dashed #dee2e6;padding-top:8px;margin-top:5px;">
                    <strong class="small text-muted">👤 ${nv.ho_ten}</strong>
                </div>
                <div class="summary-card ung">
                    <div class="sc-label">Tiền Ứng - ${nv.ho_ten}</div>
                    <div class="sc-value">${formatMoney(nv.tong_ung)}</div>
                </div>
                <div class="summary-card lam">
                    <div class="sc-label">Tiền Làm - ${nv.ho_ten}</div>
                    <div class="sc-value">${formatMoney(nv.tong_lam)}</div>
                </div>
                <div class="summary-card con-lai">
                    <div class="sc-label">Còn Lại - ${nv.ho_ten}</div>
                    <div class="sc-value">${formatMoney(nv.con_lai)}</div>
                </div>`;
            });
        }
        html += '</div>';
        $('#summaryCards').html(html);
    }, 'json');
}

function renderAnh(anhList) {
    if (!anhList || anhList.length === 0) return '-';
    return anhList.map(a =>
        `<a href="${BASE_URL}assets/uploads/${a.ten_file}" target="_blank">
            <img src="${BASE_URL}assets/uploads/${a.ten_file}" style="width:28px;height:28px;object-fit:cover;border-radius:3px;">
         </a>`
    ).join('');
}

function xuatExcel() {
    const params = new URLSearchParams(getParams());
    window.open(BASE_URL + 'api/bao_cao_thang/xuat_excel.php?' + params.toString(), '_blank');
}
</script>
<?php include '../../includes/footer.php'; ?>