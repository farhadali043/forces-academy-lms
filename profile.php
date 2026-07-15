<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$student_id = (int) $_SESSION['student_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $roll_number = trim($_POST['roll_number'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $email === '' || $roll_number === '' || $class === '') {
        $error = 'All profile fields are required.';
    } elseif ($new_password !== '' && $new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } else {
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM students WHERE (email=? OR roll_number=?) AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($check_stmt, 'ssi', $email, $roll_number, $student_id);
        mysqli_stmt_execute($check_stmt);
        $exists = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($exists) > 0) {
            $error = 'Email or roll number is already in use by another student.';
        } else {
            if ($new_password !== '') {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = mysqli_prepare($conn, "UPDATE students SET full_name=?, email=?, roll_number=?, class=?, password=? WHERE id=?");
                mysqli_stmt_bind_param($update_stmt, 'sssssi', $full_name, $email, $roll_number, $class, $hashed_password, $student_id);
            } else {
                $update_stmt = mysqli_prepare($conn, "UPDATE students SET full_name=?, email=?, roll_number=?, class=? WHERE id=?");
                mysqli_stmt_bind_param($update_stmt, 'ssssi', $full_name, $email, $roll_number, $class, $student_id);
            }

            if (mysqli_stmt_execute($update_stmt)) {
                $_SESSION['student_name'] = $full_name;
                $success = 'Profile updated successfully.';
            } else {
                $error = 'Unable to update profile right now. Please try again.';
            }
        }
    }
}

$stmt = mysqli_prepare($conn, "SELECT full_name, email, roll_number, class, created_at FROM students WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Session points to a student that no longer exists (e.g. DB re-imported).
if (!$student) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | Forces Academy LMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container-fluid px-4">
    <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php"><i class="bi bi-shield-shaded"></i> Forces Academy LMS</a>
    <div class="navbar-nav ms-auto flex-row gap-2 align-items-center">
      <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
      <a class="nav-link text-white" href="courses.php">Courses</a>
      <a class="nav-link text-white" href="assignments.php">Assignments</a>
      <a class="nav-link text-white" href="results.php">Results</a>
      <a class="nav-link text-white" href="notices.php">Notices</a>
      <a class="nav-link text-white active" href="profile.php">Profile</a>
      <a class="btn btn-logout" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card p-4 h-100 profile-card">
        <div class="profile-avatar mb-3"><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></div>
        <h4 class="mb-1"><?php echo e($student['full_name']); ?></h4>
        <p class="text-muted mb-3">Student Profile</p>
        <ul class="list-unstyled profile-meta">
          <li><i class="bi bi-envelope"></i> <?php echo e($student['email']); ?></li>
          <li><i class="bi bi-person-vcard"></i> Roll No: <?php echo e($student['roll_number']); ?></li>
          <li><i class="bi bi-mortarboard"></i> Class: <?php echo e($student['class']); ?></li>
          <li><i class="bi bi-calendar3"></i> Joined: <?php echo date('d M Y', strtotime($student['created_at'])); ?></li>
        </ul>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card p-4">
        <div class="section-header mb-3">
          <h5 class="section-title mb-0"><i class="bi bi-pencil-square"></i> Edit Profile</h5>
          <a class="section-link" href="dashboard.php">Back to dashboard</a>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger-custom"><i class="bi bi-exclamation-circle"></i> <?php echo e($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success-custom"><i class="bi bi-check-circle"></i> <?php echo e($success); ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input class="form-control form-control-solid" type="text" name="full_name" value="<?php echo e($student['full_name']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input class="form-control form-control-solid" type="email" name="email" value="<?php echo e($student['email']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Roll Number</label>
              <input class="form-control form-control-solid" type="text" name="roll_number" value="<?php echo e($student['roll_number']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Class</label>
              <input class="form-control form-control-solid" type="text" name="class" value="<?php echo e($student['class']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">New Password</label>
              <input class="form-control form-control-solid" type="password" name="new_password" placeholder="Leave blank to keep current password">
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirm New Password</label>
              <input class="form-control form-control-solid" type="password" name="confirm_password" placeholder="Repeat new password">
            </div>
          </div>
          <button class="btn btn-auth-primary mt-4" type="submit"><i class="bi bi-save"></i> Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
