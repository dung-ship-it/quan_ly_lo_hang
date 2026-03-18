<?php
require_once '../../config/database.php';
$message = '';
$messageType = '';
$step = $_GET['step'] ?? '1';
$token = $_GET['token'] ?? '';

// Bước 2: Kiểm tra token hợp lệ
if ($step === '2' && $token) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $step = '1';
        $message = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!';
        $messageType = 'danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDB();
    // Bước 1: Nhập username để lấy token
    if (isset($_POST['action']) && $_POST['action'] === 'get_token') {
        $username = trim($_POST['username'] ?? '');
        $stmt = $conn->prepare("SELECT id, ho_ten FROM users WHERE username = ? AND trang_thai = 'hoat_dong'");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt2 = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $stmt2->bind_param('ssi', $token, $expires, $user['id']);
            $stmt2->execute();
            // Trong thực tế sẽ gửi email, ở đây hiển thị link trực tiếp
            $resetLink = BASE_URL . "modules/auth/forgot_password.php?step=2&token=" . $token;
            $message = 'Link đặt lại mật khẩu: <a href="' . $resetLink . '">' . $resetLink . '</a><br><small>(Hết hạn sau 1 giờ)</small>';
            $messageType = 'success';
        } else {
            $message = 'Không tìm thấy tài khoản hoặc tài khoản đã bị khoá!';
            $messageType = 'danger';
        }
    }
    // Bước 2: Đặt lại mật khẩu
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        if ($newPass !== $confirmPass) {
            $message = 'Mật khẩu xác nhận không khớp!';
            $messageType = 'danger';
            $step = '2';
        } elseif (strlen($newPass) < 6) {
            $message = 'Mật khẩu phải có ít nhất 6 ký tự!';
            $messageType = 'danger';
            $step = '2';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            if ($user) {
                $hashedPass = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $stmt2->bind_param('si', $hashedPass, $user['id']);
                $stmt2->execute();
                $message = 'Đặt lại mật khẩu thành công! <a href="login.php">Đăng nhập ngay</a>';
                $messageType = 'success';
                $step = 'done';
            } else {
                $message = 'Token không hợp lệ hoặc đã hết hạn!';
                $messageType = 'danger';
                $step = '1';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .card { border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); border: none; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0 !important; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header text-center py-4">
                    <i class="fas fa-key fa-2x mb-2"></i>
                    <h5 class="mb-0">Quên Mật Khẩu</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
                    <?php endif; ?>
                    <?php if ($step === '1'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="get_token">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập của bạn" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Lấy Link Đặt Lại
                            </button>
                        </div>
                    </form>
                    <?php elseif ($step === '2'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Nhập mật khẩu mới" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Xác nhận mật khẩu</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required minlength="6">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Đặt Lại Mật Khẩu
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-muted small"><i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>