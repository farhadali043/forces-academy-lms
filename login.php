<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$error = '';

if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $sql = "SELECT id, full_name, password FROM students WHERE email=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id'] = (int) $student['id'];
        $_SESSION['student_name'] = $student['full_name'];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Forces Academy LMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-brand">
      <i class="bi bi-shield-shaded"></i>
      <h1>Forces Academy</h1>
      <p>Learning Management System</p>
    </div>

    <h2 class="auth-title">Welcome Back</h2>
    <p class="auth-subtitle">Sign in to continue to your dashboard</p>

    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success-custom"><i class="bi bi-check-circle"></i> Registration successful. Please login.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger-custom"><i class="bi bi-exclamation-circle"></i> <?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-group-icon mb-3">
        <i class="bi bi-envelope"></i>
        <input class="form-control" name="email" type="email" placeholder="Email address" required>
      </div>
      <div class="form-group-icon mb-4">
        <i class="bi bi-lock"></i>
        <input class="form-control" name="password" type="password" placeholder="Password" required>
      </div>
      <button class="btn btn-auth-primary w-100" type="submit">Login <i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="auth-extra-links">
      <a href="register.php">Create student account</a>
      <a href="admin_login.php">Open admin panel</a>
    </div>

    <p class="auth-footer">No account? <a href="register.php">Create one</a></p>
    <p class="text-center text-muted mt-2 mb-0" style="font-size:.76rem;letter-spacing:.04em;">Project by Farhad Ali</p>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
