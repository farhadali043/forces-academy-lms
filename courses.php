<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$courses = mysqli_query($conn, "
    SELECT c.id, c.course_name, c.description, c.teacher_name, c.created_at, COUNT(cm.id) AS material_count
    FROM courses c
    LEFT JOIN course_materials cm ON cm.course_id = c.id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Courses | Forces Academy LMS</title>
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
      <a class="nav-link text-white active" href="courses.php">Courses</a>
      <a class="nav-link text-white" href="assignments.php">Assignments</a>
      <a class="nav-link text-white" href="results.php">Results</a>
      <a class="nav-link text-white" href="notices.php">Notices</a>
      <a class="nav-link text-white" href="profile.php">Profile</a>
      <a class="btn btn-logout" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="page-hero mb-4">
    <h2 class="mb-1">All Courses</h2>
    <p class="text-muted mb-0">Open any course to view its details and materials.</p>
  </div>

  <div class="row g-4">
    <?php if (mysqli_num_rows($courses) > 0): ?>
      <?php while ($course = mysqli_fetch_assoc($courses)): ?>
        <div class="col-md-6 col-xl-4">
          <a class="course-link-wrap" href="course_detail.php?id=<?php echo (int) $course['id']; ?>">
            <div class="card course-card p-4 h-100">
              <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <h5 class="mb-0"><?php echo e($course['course_name']); ?></h5>
                <span class="badge subtle-badge"><?php echo (int) $course['material_count']; ?> materials</span>
              </div>
              <p class="text-muted small mb-3"><?php echo e($course['description']); ?></p>
              <div class="mt-auto">
                <div class="small text-muted mb-2"><i class="bi bi-person-workspace"></i> Teacher: <?php echo e($course['teacher_name']); ?></div>
                <div class="small text-muted mb-3"><i class="bi bi-calendar-event"></i> Added: <?php echo date('d M Y', strtotime($course['created_at'])); ?></div>
                <span class="course-open-text">View course details <i class="bi bi-arrow-right"></i></span>
              </div>
            </div>
          </a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="card p-4 text-center">
          <h5>No courses available</h5>
          <p class="text-muted mb-0">Admin can add courses from the admin panel.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
