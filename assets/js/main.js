// =============================================
// MAIN JS - Quản Lý Lô Hàng
// =============================================

$(document).ready(function () {
    // Cập nhật đồng hồ realtime
    updateClock();
    setInterval(updateClock, 1000);

    // Format số tiền khi nhập
    $(document).on('blur', '.input-tien', function () {
        let val = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(val);
    });
});

// Đồng hồ thời gian thực
function updateClock() {
    const now = new Date();
    const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    const dayName = days[now.getDay()];
    const date = now.getDate().toString().padStart(2, '0');
    const month = (now.getMonth() + 1).toString().padStart(2, '0');
    const year = now.getFullYear();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    const timeStr = `${dayName}, ${date}/${month}/${year} - ${hours}:${minutes}:${seconds}`;
    if ($('#clock').length) $('#clock').text(timeStr);
}

// Format tiền VND
function formatMoney(amount) {
    if (!amount) return '0 đ';
    return parseInt(amount).toLocaleString('vi-VN') + ' đ';
}

// Parse số từ string
function parseMoney(str) {
    return parseInt(str.replace(/[^0-9]/g, '')) || 0;
}

// Hiển thị loading
function showLoading(msg = 'Đang xử lý...') {
    Swal.fire({ title: msg, allowOutsideClick: false, didOpen: () => Swal.showLoading() });
}

// Hiển thị thành công
function showSuccess(msg, callback) {
    Swal.fire({ icon: 'success', title: 'Thành công!', text: msg, timer: 2000, showConfirmButton: false })
        .then(() => { if (callback) callback(); });
}

// Hiển thị lỗi
function showError(msg) {
    Swal.fire({ icon: 'error', title: 'Lỗi!', text: msg });
}

// Hiển thị xác nhận
function showConfirm(title, text, callback) {
    Swal.fire({
        title: title, text: text,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xác nhận', cancelButtonText: 'Huỷ'
    }).then((result) => { if (result.isConfirmed && callback) callback(); });
}

// AJAX helper
function apiCall(url, data, callback) {
    $.ajax({
        url: url, type: 'POST',
        data: data, dataType: 'json',
        success: function (res) { if (callback) callback(res); },
        error: function () { showError('Có lỗi xảy ra. Vui lòng thử lại!'); }
    });
}