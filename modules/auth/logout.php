<?php
require_once '../../config/database.php';
session_destroy();
header('Location: ' . BASE_URL . 'modules/auth/login.php');
exit;
?>