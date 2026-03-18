<?php
require_once '../../config/database.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'modules/dashboard/index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT id, username, password, ho_ten, vai_tro, trang_thai FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            if ($user['trang_thai'] === 'khoa') {
                $error = 'Tài khoản đã bị khoá. Vui lòng liên hệ admin!';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['ho_ten'] = $user['ho_ten'];
                $_SESSION['vai_tro'] = $user['vai_tro'];
                header('Location: ' . BASE_URL . 'modules/dashboard/index.php');
                exit;
            }
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Quản Lý Lô Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); border: none; }
        .login-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0; padding: 30px; text-align: center; }
        .btn-login { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px; font-size: 16px; }
        .btn-login:hover { opacity: 0.9; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.25rem rgba(102,126,234,0.25); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card">
                <div class="login-header">
                    <i class="fas fa-boxes fa-3x mb-3"></i>
                    <h4 class="mb-0">Quản Lý Lô Hàng</h4>
                    <small>Hệ thống quản lý chi phí</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên đăng nhập</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Nhập mật khẩu" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-login text-white">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="forgot_password.php" class="text-muted small">
                                <i class="fas fa-key me-1"></i>Quên mật khẩu?
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') { pwd.type = 'text'; icon.classList.replace('fa-eye', 'fa-eye-slash'); }
    else { pwd.type = 'password'; icon.classList.replace('fa-eye-slash', 'fa-eye'); }
}
</script>
</body>
</html>