<?php
require_once 'config/db.php';
require_once 'config/auth.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM admins WHERE username=? OR email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ss', $identity, $identity);
    mysqli_stmt_execute($stmt);
    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: admin_dashboard.php');
        exit;
    }

    $error = 'Invalid admin credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Forces Academy LMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-brand">
      <i class="bi bi-shield-lock"></i>
      <h1>Forces Academy Admin</h1>
      <p>Secure management panel</p>
    </div>

    <h2 class="auth-title">Admin Login</h2>
    <p class="auth-subtitle">Manage courses, notices, and materials</p>

    <?php if ($error): ?>
      <div class="alert alert-danger-custom"><i class="bi bi-exclamation-circle"></i> <?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group-icon mb-3">
        <i class="bi bi-person-badge"></i>
        <input class="form-control" name="identity" placeholder="Username or email" required>
      </div>
      <div class="form-group-icon mb-4">
        <i class="bi bi-lock"></i>
        <input class="form-control" name="password" type="password" placeholder="Password" required>
      </div>
      <button class="btn btn-auth-primary w-100" type="submit">Open Admin Panel <i class="bi bi-arrow-right"></i></button>
    </form>

    <p class="auth-footer">Student login? <a href="login.php">Go back</a></p>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
