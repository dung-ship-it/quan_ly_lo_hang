<?php
require_once 'config/database.php';
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'modules/dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . 'modules/auth/login.php');
}
exit;
?>