<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$student_id = (int) $_SESSION['student_id'];

// Keep this student's overdue status current (unpaid & past due date)
$stmt = mysqli_prepare($conn, "UPDATE fees SET status='overdue' WHERE status='pending' AND due_date < CURDATE() AND student_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);

// All fee records for the logged-in student only
$stmt = mysqli_prepare($conn, "SELECT amount, due_date, paid_date, status, description FROM fees WHERE student_id = ? ORDER BY due_date DESC");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$rows = [];
while ($r = mysqli_fetch_assoc($result)) { $rows[] = $r; }

// Summary figures
$totalPending = 0;
$totalPaid    = 0;
foreach ($rows as $r) {
    if ($r['status'] === 'paid') {
        $totalPaid += (float) $r['amount'];
    } else {
        $totalPending += (float) $r['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Fees | Forces Academy LMS</title>
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
      <a class="nav-link text-white active" href="fees.php">Fees</a>
      <a class="nav-link text-white" href="timetable.php">Timetable</a>
      <a class="nav-link text-white" href="notices.php">Notices</a>
      <a class="nav-link text-white" href="profile.php">Profile</a>
      <a class="btn btn-logout" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="page-hero mb-4">
    <h2 class="mb-1">My Fees</h2>
    <p class="text-muted mb-0">Your fee records, due dates and payment status.</p>
  </div>

  <!-- Total pending amount shown prominently at the top -->
  <div class="card p-4 mb-4" style="background:linear-gradient(135deg,#1f2937,#111827);color:#fff;border:none;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <div class="text-uppercase small" style="letter-spacing:.08em;opacity:.75;">Total Pending Amount</div>
        <div style="font-size:2.4rem;font-weight:700;line-height:1.1;">PKR <?php echo number_format($totalPending, 2); ?></div>
      </div>
      <span style="font-size:2.5rem;opacity:.5;"><i class="bi bi-wallet2"></i></span>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="mini-stat-card">
        <span class="mini-stat-icon"><i class="bi bi-check2-circle"></i></span>
        <div><h3 class="record">PKR <?php echo number_format($totalPaid, 2); ?></h3><p>Total Paid</p></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="mini-stat-card">
        <span class="mini-stat-icon"><i class="bi bi-receipt"></i></span>
        <div><h3><?php echo count($rows); ?></h3><p>Fee Records</p></div>
      </div>
    </div>
  </div>

  <?php if (count($rows) > 0): ?>
    <div class="card p-0">
      <div class="table-responsive">
        <table class="table table-modern align-middle">
          <thead>
            <tr>
              <th>Description</th>
              <th class="text-center">Amount</th>
              <th class="text-center">Due Date</th>
              <th class="text-center">Paid Date</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <?php
                $badge = $r['status'] === 'paid' ? 'bg-success'
                       : ($r['status'] === 'overdue' ? 'bg-danger' : 'bg-warning text-dark');
              ?>
              <tr>
                <td class="fw-semibold"><?php echo e($r['description'] ?: '—'); ?></td>
                <td class="text-center record"><?php echo number_format((float) $r['amount'], 2); ?></td>
                <td class="text-center"><?php echo date('d M Y', strtotime($r['due_date'])); ?></td>
                <td class="text-center"><?php echo $r['paid_date'] ? date('d M Y', strtotime($r['paid_date'])) : '—'; ?></td>
                <td class="text-center"><span class="badge <?php echo $badge; ?>"><?php echo e(ucfirst($r['status'])); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="card p-4">
      <div class="empty-state">
        <i class="bi bi-wallet2"></i>
        <h5>No fee records yet</h5>
        <p class="text-muted mb-0">Your fee details will appear here once the office adds them.</p>
      </div>
    </div>
  <?php endif; ?>
</div>
<script src="js/main.js"></script>
</body>
</html>
