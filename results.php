<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$student_id = (int) $_SESSION['student_id'];

// Results for the logged-in student only
$stmt = mysqli_prepare($conn, "SELECT subject, marks, total_marks, grade, exam_type FROM results WHERE student_id = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);

// Small summary for the header
$rows = [];
while ($r = mysqli_fetch_assoc($results)) { $rows[] = $r; }
$totalMarks = array_sum(array_column($rows, 'marks'));
$totalMax   = array_sum(array_column($rows, 'total_marks'));
$overall    = $totalMax > 0 ? round(($totalMarks / $totalMax) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Results | Forces Academy LMS</title>
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
      <a class="nav-link text-white active" href="results.php">Results</a>
      <a class="nav-link text-white" href="notices.php">Notices</a>
      <a class="nav-link text-white" href="profile.php">Profile</a>
      <a class="btn btn-logout" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="page-hero mb-4">
    <h2 class="mb-1">My Results</h2>
    <p class="text-muted mb-0">Your exam and quiz results across all subjects.</p>
  </div>

  <?php if (count($rows) > 0): ?>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="mini-stat-card">
          <span class="mini-stat-icon"><i class="bi bi-list-check"></i></span>
          <div><h3><?php echo count($rows); ?></h3><p>Subjects</p></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mini-stat-card">
          <span class="mini-stat-icon"><i class="bi bi-bar-chart-line"></i></span>
          <div><h3 class="record"><?php echo $totalMarks; ?>/<?php echo $totalMax; ?></h3><p>Total Marks</p></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mini-stat-card">
          <span class="mini-stat-icon"><i class="bi bi-percent"></i></span>
          <div><h3 class="record"><?php echo $overall; ?>%</h3><p>Overall</p></div>
        </div>
      </div>
    </div>

    <div class="card p-0">
      <div class="table-responsive">
        <table class="table table-modern align-middle">
          <thead>
            <tr>
              <th>Subject</th>
              <th>Exam Type</th>
              <th class="text-center">Marks</th>
              <th class="text-center">Total</th>
              <th class="text-center">Percentage</th>
              <th class="text-center">Grade</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <?php $pct = $r['total_marks'] > 0 ? round(($r['marks'] / $r['total_marks']) * 100, 1) : 0; ?>
              <tr>
                <td class="fw-semibold"><?php echo e($r['subject']); ?></td>
                <td><?php echo e($r['exam_type']); ?></td>
                <td class="text-center record"><?php echo (int) $r['marks']; ?></td>
                <td class="text-center record"><?php echo (int) $r['total_marks']; ?></td>
                <td class="text-center record"><?php echo $pct; ?>%</td>
                <td class="text-center"><span class="grade-pill"><?php echo e($r['grade']); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="card p-4">
      <div class="empty-state">
        <i class="bi bi-graph-up"></i>
        <h5>No results published yet</h5>
        <p class="text-muted mb-0">Your results will appear here once they are released.</p>
      </div>
    </div>
  <?php endif; ?>
</div>
<script src="js/main.js"></script>
</body>
</html>
