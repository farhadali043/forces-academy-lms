<?php
require_once 'config/auth.php';
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit;
}
header('Location: login.php');
exit;
