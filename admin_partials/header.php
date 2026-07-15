<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_admin();

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$page_title     = $page_title ?? 'Dashboard';
$page_subtitle  = $page_subtitle ?? '';
$active         = $active ?? 'dashboard';

$error   = $error ?? '';
$success = $success ?? '';

// Consume any flash message set before a redirect.
$flash = get_flash();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success = $success !== '' ? $success : $flash['message'];
    } else {
        $error = $error !== '' ? $error : $flash['message'];
    }
}

$nav_items = [
    'dashboard' => ['admin_dashboard.php', 'bi-speedometer2', 'Dashboard'],
    'courses'   => ['admin_courses.php',   'bi-book',         'Courses'],
    'notices'   => ['admin_notices.php',   'bi-megaphone',    'Notices'],
    'students'  => ['admin_students.php',  'bi-people',       'Students'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($page_title); ?> | Forces Academy Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">
<div class="admin-shell">

  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-brand">
      <i class="bi bi-shield-lock"></i> Forces Academy
    </div>
    <ul class="admin-nav">
      <?php foreach ($nav_items as $key => $item): ?>
        <li>
          <a href="<?php echo $item[0]; ?>" class="<?php echo $active === $key ? 'active' : ''; ?>">
            <i class="bi <?php echo $item[1]; ?>"></i> <?php echo $item[2]; ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li><div class="admin-nav-divider"></div></li>
      <li><a href="dashboard.php"><i class="bi bi-box-arrow-up-right"></i> Student View</a></li>
      <li><a href="admin_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
  </aside>

  <div class="admin-backdrop" id="adminBackdrop"></div>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="admin-menu-btn" id="adminMenuBtn" type="button" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
      </button>
      <span class="admin-greeting"><i class="bi bi-person-circle"></i> Hello, <?php echo e($admin_username); ?></span>
    </header>

    <main class="admin-content">
      <div class="page-hero mb-4">
        <h2 class="mb-1"><?php echo e($page_title); ?></h2>
        <?php if ($page_subtitle !== ''): ?>
          <p class="text-muted mb-0"><?php echo e($page_subtitle); ?></p>
        <?php endif; ?>
      </div>

      <?php if ($error !== ''): ?>
        <div class="alert alert-danger-custom"><i class="bi bi-exclamation-circle"></i> <?php echo e($error); ?></div>
      <?php endif; ?>
      <?php if ($success !== ''): ?>
        <div class="alert alert-success-custom"><i class="bi bi-check-circle"></i> <?php echo e($success); ?></div>
      <?php endif; ?>
