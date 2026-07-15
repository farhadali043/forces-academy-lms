<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_student();

$student_id = (int) $_SESSION['student_id'];
$flash = '';        // message text
$flashType = '';    // 'success' | 'danger'

// ---------- Handle a submission upload ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment_id'])) {
    $assignment_id = (int) $_POST['assignment_id'];

    // Already submitted?
    $chk = mysqli_prepare($conn, "SELECT id FROM submissions WHERE assignment_id=? AND student_id=?");
    mysqli_stmt_bind_param($chk, 'ii', $assignment_id, $student_id);
    mysqli_stmt_execute($chk);
    mysqli_stmt_store_result($chk);
    $already = mysqli_stmt_num_rows($chk) > 0;
    mysqli_stmt_close($chk);

    if ($already) {
        $flash = 'You have already submitted this assignment.';
        $flashType = 'danger';
    } elseif (!isset($_FILES['submission']) || $_FILES['submission']['error'] !== UPLOAD_ERR_OK) {
        $flash = 'Please choose a file before submitting.';
        $flashType = 'danger';
    } else {
        $file    = $_FILES['submission'];
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime    = mime_content_type($file['tmp_name']);
        $okMime  = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($ext, $allowed) || !in_array($mime, $okMime)) {
            $flash = 'Only PDF and image files (jpg, png, gif, webp) are allowed.';
            $flashType = 'danger';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $flash = 'That file is larger than 5 MB. Please upload a smaller file.';
            $flashType = 'danger';
        } else {
            $newName = 'sub_' . $student_id . '_' . $assignment_id . '_' . uniqid() . '.' . $ext;
            $target  = 'uploads/' . $newName;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $ins = mysqli_prepare($conn, "INSERT INTO submissions (assignment_id, student_id, file_path, status) VALUES (?, ?, ?, 'submitted')");
                mysqli_stmt_bind_param($ins, 'iis', $assignment_id, $student_id, $target);
                if (mysqli_stmt_execute($ins)) {
                    $flash = 'Your assignment was submitted.';
                    $flashType = 'success';
                } else {
                    $flash = 'Could not save your submission. Please try again.';
                    $flashType = 'danger';
                }
                mysqli_stmt_close($ins);
            } else {
                $flash = 'Upload failed. Make sure the uploads folder exists and is writable.';
                $flashType = 'danger';
            }
        }
    }
}

// ---------- Which assignments has this student already submitted? ----------
$submitted = [];
$sres = mysqli_query($conn, "SELECT assignment_id FROM submissions WHERE student_id = " . $student_id);
while ($row = mysqli_fetch_assoc($sres)) {
    $submitted[(int) $row['assignment_id']] = true;
}

// ---------- All assignments with their course name ----------
$assignments = mysqli_query($conn, "
    SELECT a.id, a.title, a.description, a.due_date, c.course_name
    FROM assignments a
    LEFT JOIN courses c ON a.course_id = c.id
    ORDER BY a.due_date ASC
");

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assignments | Forces Academy LMS</title>
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
      <a class="nav-link text-white active" href="assignments.php">Assignments</a>
      <a class="nav-link text-white" href="results.php">Results</a>
      <a class="nav-link text-white" href="notices.php">Notices</a>
      <a class="nav-link text-white" href="profile.php">Profile</a>
      <a class="btn btn-logout" href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="page-hero mb-4">
    <h2 class="mb-1">Assignments</h2>
    <p class="text-muted mb-0">Submit your work as a PDF or image before the due date.</p>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?php echo $flashType === 'success' ? 'success' : 'danger'; ?>-custom">
      <i class="bi bi-<?php echo $flashType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
      <?php echo e($flash); ?>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <?php if (mysqli_num_rows($assignments) > 0): ?>
      <?php while ($a = mysqli_fetch_assoc($assignments)): ?>
        <?php
          $isSubmitted = isset($submitted[(int) $a['id']]);
          $isOverdue   = $a['due_date'] && $a['due_date'] < $today && !$isSubmitted;
        ?>
        <div class="col-md-6">
          <div class="card course-tile p-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
              <h5 class="mb-0"><?php echo e($a['title']); ?></h5>
              <?php if ($isSubmitted): ?>
                <span class="status-pill status-submitted"><i class="bi bi-check-circle-fill"></i> Submitted</span>
              <?php else: ?>
                <span class="status-pill status-pending"><i class="bi bi-hourglass-split"></i> Pending</span>
              <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
              <span class="badge teacher-badge"><i class="bi bi-journal-bookmark"></i> <?php echo e($a['course_name'] ?? 'General'); ?></span>
              <span class="due-pill <?php echo $isOverdue ? 'overdue' : ''; ?>">
                <i class="bi bi-calendar-event"></i>
                Due <?php echo $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : 'N/A'; ?>
              </span>
            </div>

            <p class="text-muted small mb-3"><?php echo e($a['description']); ?></p>

            <div class="mt-auto">
              <?php if ($isSubmitted): ?>
                <button class="btn btn-auth-primary w-100" disabled><i class="bi bi-check2-all"></i> Already submitted</button>
              <?php else: ?>
                <form method="POST" enctype="multipart/form-data" class="upload-inline">
                  <input type="hidden" name="assignment_id" value="<?php echo (int) $a['id']; ?>">
                  <input type="file" name="submission" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" required>
                  <button type="submit" class="btn btn-auth-primary text-nowrap"><i class="bi bi-upload"></i> Submit</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="card p-4">
          <div class="empty-state">
            <i class="bi bi-clipboard-check"></i>
            <h5>No assignments yet</h5>
            <p class="text-muted mb-0">Assignments will appear here once your teachers add them.</p>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
