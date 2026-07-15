<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$name = $_SESSION['student_name'];
$student_id = (int) $_SESSION['student_id'];

$stmt = mysqli_prepare($conn, "SELECT full_name, email, roll_number, class, created_at FROM students WHERE id=?");
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Session points to a student that no longer exists (e.g. DB re-imported).
if (!$student) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$courses = mysqli_query($conn, "
    SELECT c.id, c.course_name, c.description, c.teacher_name, COUNT(cm.id) AS material_count
    FROM courses c
    LEFT JOIN course_materials cm ON cm.course_id = c.id
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 4
");

$notices = mysqli_query($conn, "SELECT id, title, content, posted_by, created_at FROM notices ORDER BY created_at DESC LIMIT 4");

$courseCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM courses"));
$noticeCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM notices"));
$materialCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM course_materials"));
$pendingAssignments = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM assignments
    WHERE id NOT IN (SELECT assignment_id FROM submissions WHERE student_id = $student_id)"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Forces Academy LMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-body">

<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container-fluid px-4">
    <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
      <i class="bi bi-shield-shaded"></i> Forces Academy LMS
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 mt-3 mt-lg-0">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
        <li class="nav-item"><a class="nav-link" href="assignments.php">Assignments</a></li>
        <li class="nav-item"><a class="nav-link" href="results.php">Results</a></li>
        <li class="nav-item"><a class="nav-link" href="notices.php">Notices</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_login.php">Admin Panel</a></li>
        <li class="nav-item"><a class="btn btn-logout ms-lg-2" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4 dashboard">
  <div class="welcome-banner mb-4">
    <h2 class="mb-1">Welcome back, <?php echo e($name); ?> 👋</h2>
    <p class="mb-3 text-muted">Your courses, assignments, results and notices — all in one place.</p>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-auth-primary" href="assignments.php"><i class="bi bi-clipboard-check"></i> Submit an assignment</a>
      <a class="btn btn-logout" style="border-color:var(--line);color:var(--heading);" href="results.php"><i class="bi bi-graph-up"></i> View my results</a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="mini-stat-card">
        <span class="mini-stat-icon"><i class="bi bi-book"></i></span>
        <div>
          <h3><?php echo (int) $courseCount['total']; ?></h3>
          <p>Available Courses</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="mini-stat-card">
        <span class="mini-stat-icon"><i class="bi bi-clipboard-check"></i></span>
        <div>
          <h3><?php echo (int) $pendingAssignments['total']; ?></h3>
          <p>Pending Assignments</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="mini-stat-card">
        <span class="mini-stat-icon"><i class="bi bi-folder2-open"></i></span>
        <div>
          <h3><?php echo (int) $materialCount['total']; ?></h3>
          <p>Course Materials</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="mini-stat-card">
        <span class="mini-stat-icon"><i class="bi bi-megaphone"></i></span>
        <div>
          <h3><?php echo (int) $noticeCount['total']; ?></h3>
          <p>Active Notices</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card profile-card p-4 h-100">
        <div class="profile-avatar mb-3"><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></div>
        <h5 class="mb-1"><?php echo e($student['full_name']); ?></h5>
        <p class="text-muted mb-3">Class <?php echo e($student['class']); ?></p>
        <ul class="list-unstyled profile-meta">
          <li><i class="bi bi-envelope"></i> <?php echo e($student['email']); ?></li>
          <li><i class="bi bi-person-vcard"></i> Roll No: <?php echo e($student['roll_number']); ?></li>
          <li><i class="bi bi-calendar3"></i> Joined: <?php echo date('d M Y', strtotime($student['created_at'])); ?></li>
        </ul>
        <a class="btn btn-auth-primary w-100 mt-3" href="profile.php"><i class="bi bi-pencil-square"></i> Edit Profile</a>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card p-4 mb-4">
        <div class="section-header mb-3">
          <h5 class="section-title mb-0"><i class="bi bi-book"></i> Your Courses</h5>
          <a class="section-link" href="courses.php">View all</a>
        </div>
        <?php if (mysqli_num_rows($courses) > 0): ?>
          <div class="row g-3">
            <?php while ($c = mysqli_fetch_assoc($courses)): ?>
              <div class="col-md-6">
                <a class="course-link-wrap" href="course_detail.php?id=<?php echo (int) $c['id']; ?>">
                  <div class="course-tile p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                      <h6 class="mb-1"><?php echo e($c['course_name']); ?></h6>
                      <span class="badge subtle-badge"><?php echo (int) $c['material_count']; ?> items</span>
                    </div>
                    <p class="small text-muted mb-2"><?php echo e($c['description']); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="badge teacher-badge"><i class="bi bi-person-workspace"></i> <?php echo e($c['teacher_name']); ?></span>
                      <span class="course-open-text">Open <i class="bi bi-arrow-right"></i></span>
                    </div>
                  </div>
                </a>
              </div>
            <?php endwhile; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">No courses have been added yet.</p>
        <?php endif; ?>
      </div>

      <div class="card p-4">
        <div class="section-header mb-3">
          <h5 class="section-title mb-0"><i class="bi bi-megaphone"></i> Latest Notices</h5>
          <a class="section-link" href="notices.php">View full page</a>
        </div>
        <?php if (mysqli_num_rows($notices) > 0): ?>
          <?php while ($n = mysqli_fetch_assoc($notices)): ?>
            <a class="notice-link-wrap" href="notices.php#notice-<?php echo (int) $n['id']; ?>">
              <div class="notice-item">
                <div class="d-flex justify-content-between align-items-start">
                  <h6 class="mb-1"><?php echo e($n['title']); ?></h6>
                  <span class="small text-muted"><?php echo date('d M', strtotime($n['created_at'])); ?></span>
                </div>
                <p class="small text-muted mb-1"><?php echo e(strlen($n['content']) > 120 ? substr($n['content'], 0, 120) . '...' : $n['content']); ?></p>
                <span class="small posted-by">Posted by <?php echo e($n['posted_by']); ?></span>
              </div>
            </a>
          <?php endwhile; ?>
        <?php else: ?>
          <p class="text-muted mb-0">No notices at the moment.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
