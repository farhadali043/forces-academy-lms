<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$notices = mysqli_query($conn, "SELECT id, title, content, posted_by, created_at FROM notices ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notices | Forces Academy LMS</title>
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
      <a class="nav-link text-white active" href="notices.php">Notices</a>
      <a class="nav-link text-white" href="profile.php">Profile</a>
      <a class="btn btn-logout" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="page-hero mb-4">
    <h2 class="mb-1">All Notices</h2>
    <p class="text-muted mb-0">Important academy announcements in one place.</p>
  </div>

  <div class="card p-4">
    <?php if (mysqli_num_rows($notices) > 0): ?>
      <?php while ($notice = mysqli_fetch_assoc($notices)): ?>
        <div class="notice-item notice-item-large" id="notice-<?php echo (int) $notice['id']; ?>">
          <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
            <div>
              <h5 class="mb-1"><?php echo e($notice['title']); ?></h5>
              <div class="small text-muted">Posted by <?php echo e($notice['posted_by']); ?></div>
            </div>
            <div class="small text-muted"><i class="bi bi-calendar3"></i> <?php echo date('d M Y, h:i A', strtotime($notice['created_at'])); ?></div>
          </div>
          <p class="mb-0 notice-content"><?php echo nl2br(e($notice['content'])); ?></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-megaphone"></i>
        <h5>No notices available</h5>
        <p class="text-muted mb-0">Check back later for new updates from the academy.</p>
      </div>
    <?php endif; ?>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
