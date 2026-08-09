<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$student_id = (int) $_SESSION['student_id'];

$stmt = mysqli_prepare($conn, "SELECT full_name, class FROM students WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Session points to a student that no longer exists (e.g. DB re-imported).
if (!$student) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$class = $student['class'];

// Same fixed lists as the admin page, so the grid always lines up.
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$time_slots = [
    '08:00 - 09:00', '09:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00',
    '12:00 - 01:00', '01:00 - 02:00', '02:00 - 03:00', '03:00 - 04:00',
];

// Pull this student's class timetable and index it by [time_slot][day].
$stmt = mysqli_prepare($conn, "SELECT day, time_slot, subject, teacher FROM timetable WHERE class = ?");
mysqli_stmt_bind_param($stmt, 's', $class);
mysqli_stmt_execute($stmt);
$rows = mysqli_stmt_get_result($stmt);

$grid = [];
$hasAnyEntry = false;
while ($r = mysqli_fetch_assoc($rows)) {
    $grid[$r['time_slot']][$r['day']] = $r;
    $hasAnyEntry = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Timetable | Forces Academy LMS</title>
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
      <a class="nav-link text-white" href="fees.php">Fees</a>
      <a class="nav-link text-white active" href="timetable.php">Timetable</a>
      <a class="nav-link text-white" href="notices.php">Notices</a>
      <a class="nav-link text-white" href="profile.php">Profile</a>
      <a class="btn btn-logout" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="page-hero mb-4">
    <h2 class="mb-1">Weekly Timetable</h2>
    <p class="text-muted mb-0">
      Class: <span class="badge subtle-badge"><?php echo e($class); ?></span>
    </p>
  </div>

  <?php if ($hasAnyEntry): ?>
    <div class="card p-3">
      <div class="table-responsive">
        <table class="table table-modern align-middle text-center mb-0">
          <thead>
            <tr>
              <th class="text-start" style="min-width:120px;">Time</th>
              <?php foreach ($days as $d): ?>
                <th><?php echo e($d); ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($time_slots as $slot): ?>
              <?php if (!isset($grid[$slot])) continue; // hide empty rows entirely ?>
              <tr>
                <td class="text-start record"><?php echo e($slot); ?></td>
                <?php foreach ($days as $d): ?>
                  <td>
                    <?php if (isset($grid[$slot][$d])): ?>
                      <div class="fw-semibold"><?php echo e($grid[$slot][$d]['subject']); ?></div>
                      <div class="small text-muted"><?php echo e($grid[$slot][$d]['teacher']); ?></div>
                    <?php else: ?>
                      <span class="text-muted small">&mdash;</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="card p-4">
      <div class="empty-state">
        <i class="bi bi-calendar3"></i>
        <h5>No timetable published yet</h5>
        <p class="text-muted mb-0">Your class timetable will appear here once the admin adds it.</p>
      </div>
    </div>
  <?php endif; ?>
</div>
<script src="js/main.js"></script>
</body>
</html>
