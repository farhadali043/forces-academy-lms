<?php
require_once 'config/auth.php';
session_destroy();
header('Location: admin_login.php');
exit;
