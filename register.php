<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$error='';
if($_SERVER['REQUEST_METHOD']=='POST'){
$full_name=trim($_POST['full_name'] ?? ''); $email=trim($_POST['email'] ?? '');
$password=$_POST['password'] ?? ''; $confirm=$_POST['confirm_password'] ?? '';
$roll_number=trim($_POST['roll_number'] ?? ''); $class=trim($_POST['class'] ?? '');
if(empty($full_name)||empty($email)||empty($password)||empty($roll_number)||empty($class))
$error='All fields are required.';
elseif($password!==$confirm) $error='Passwords do not match.';
else{
$hashed=password_hash($password,PASSWORD_DEFAULT);
$sql="INSERT INTO students(full_name,email,password,roll_number,class) VALUES(?,?,?,?,?)";
$stmt=mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,'sssss',$full_name,$email,$hashed,$roll_number,$class);
if(mysqli_stmt_execute($stmt)){ header('Location: login.php?registered=1'); exit; }
else $error='Registration failed. Email or roll number may already be in use.';
}}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Forces Academy LMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
  <div class="auth-card auth-card-wide">
    <div class="auth-brand">
      <i class="bi bi-shield-shaded"></i>
      <h1>Forces Academy</h1>
      <p>Learning Management System</p>
    </div>

    <h2 class="auth-title">Create Account</h2>
    <p class="auth-subtitle">Register to access your courses and notices</p>

    <?php if($error): ?>
      <div class="alert alert-danger-custom"><i class="bi bi-exclamation-circle"></i> <?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="form-group-icon">
            <i class="bi bi-person"></i>
            <input class="form-control" name="full_name" placeholder="Full Name" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group-icon">
            <i class="bi bi-envelope"></i>
            <input class="form-control" name="email" type="email" placeholder="Email address" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group-icon">
            <i class="bi bi-lock"></i>
            <input class="form-control" name="password" type="password" placeholder="Password" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group-icon">
            <i class="bi bi-lock-fill"></i>
            <input class="form-control" name="confirm_password" type="password" placeholder="Confirm Password" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group-icon">
            <i class="bi bi-person-vcard"></i>
            <input class="form-control" name="roll_number" placeholder="Roll Number" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group-icon">
            <i class="bi bi-mortarboard"></i>
            <input class="form-control" name="class" placeholder="Class" required>
          </div>
        </div>
      </div>
      <button class="btn btn-auth-primary w-100 mt-4" type="submit">Create Account <i class="bi bi-arrow-right"></i></button>
    </form>

    <div class="auth-extra-links">
      <a href="login.php">Student login</a>
      <a href="admin_login.php">Admin login</a>
    </div>

    <p class="auth-footer">Already registered? <a href="login.php">Login</a></p>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
