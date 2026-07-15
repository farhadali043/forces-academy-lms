<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$course_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($course_id <= 0) {
    header('Location: courses.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, course_name, description, teacher_name, created_at FROM courses WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $course_id);
mysqli_stmt_execute($stmt);
$course = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$course) {
    header('Location: courses.php');
    exit;
}

$materials_stmt = mysqli_prepare($conn, "SELECT id, material_title, material_type, description, resource_link, created_at FROM course_materials WHERE course_id=? ORDER BY created_at DESC");
mysqli_stmt_bind_param($materials_stmt, 'i', $course_id);
mysqli_stmt_execute($materials_stmt);
$materials = mysqli_stmt_get_result($materials_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo e($course['course_name']); ?> | Forces Academy LMS</title>
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
  <div class="mb-3"><a class="text-decoration-none" href="courses.php"><i class="bi bi-arrow-left"></i> Back to courses</a></div>

  <div class="card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
      <div>
        <h2 class="mb-2"><?php echo e($course['course_name']); ?></h2>
        <p class="text-muted mb-3"><?php echo e($course['description']); ?></p>
        <div class="detail-meta-wrap">
          <span class="detail-meta-pill"><i class="bi bi-person-workspace"></i> <?php echo e($course['teacher_name']); ?></span>
          <span class="detail-meta-pill"><i class="bi bi-calendar-event"></i> <?php echo date('d M Y', strtotime($course['created_at'])); ?></span>
        </div>
      </div>
      <div class="hero-badge-box">
        <div class="hero-badge-icon"><i class="bi bi-journal-richtext"></i></div>
        <strong>Course Materials</strong>
      </div>
    </div>
  </div>

  <div class="card p-4">
    <div class="section-header mb-3">
      <h5 class="section-title mb-0"><i class="bi bi-folder2-open"></i> Materials</h5>
      <span class="text-muted small">Download links and references added by admin</span>
    </div>

    <?php if (mysqli_num_rows($materials) > 0): ?>
      <div class="row g-3">
        <?php while ($material = mysqli_fetch_assoc($materials)): ?>
          <div class="col-md-6">
            <div class="material-card h-100">
              <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <h6 class="mb-0"><?php echo e($material['material_title']); ?></h6>
                <span class="badge subtle-badge"><?php echo e($material['material_type']); ?></span>
              </div>
              <p class="text-muted small mb-3"><?php echo e($material['description']); ?></p>
              <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <span class="small text-muted"><i class="bi bi-clock-history"></i> <?php echo date('d M Y', strtotime($material['created_at'])); ?></span>
                <?php if (!empty($material['resource_link'])): ?>
                  <a class="btn btn-sm btn-outline-primary" href="<?php echo e($material['resource_link']); ?>" target="_blank">Open Resource</a>
                <?php else: ?>
                  <span class="small text-muted">Link not added</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-folder-x"></i>
        <h5>No materials yet</h5>
        <p class="text-muted mb-0">Admin has not uploaded or linked any material for this course.</p>
      </div>
    <?php endif; ?>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
