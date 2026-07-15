<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$stats = [
    'students'  => (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students"))['total'],
    'courses'   => (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM courses"))['total'],
    'materials' => (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM course_materials"))['total'],
    'notices'   => (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM notices"))['total'],
];

$recent_courses = mysqli_query($conn, "
    SELECT c.id, c.course_name, c.teacher_name, COUNT(cm.id) AS material_count
    FROM courses c
    LEFT JOIN course_materials cm ON cm.course_id = c.id
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 5
");

$recent_notices = mysqli_query($conn, "SELECT id, title, posted_by, created_at FROM notices ORDER BY created_at DESC LIMIT 5");
$recent_students = mysqli_query($conn, "SELECT id, full_name, roll_number, class, created_at FROM students ORDER BY created_at DESC LIMIT 5");

$page_title    = 'Dashboard';
$page_subtitle = 'Overview of everything happening across Forces Academy LMS.';
$active        = 'dashboard';
require 'admin_partials/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="mini-stat-card"><span class="mini-stat-icon"><i class="bi bi-people"></i></span><div><h3><?php echo $stats['students']; ?></h3><p>Students</p></div></div></div>
  <div class="col-6 col-md-3"><div class="mini-stat-card"><span class="mini-stat-icon"><i class="bi bi-book"></i></span><div><h3><?php echo $stats['courses']; ?></h3><p>Courses</p></div></div></div>
  <div class="col-6 col-md-3"><div class="mini-stat-card"><span class="mini-stat-icon"><i class="bi bi-folder2-open"></i></span><div><h3><?php echo $stats['materials']; ?></h3><p>Materials</p></div></div></div>
  <div class="col-6 col-md-3"><div class="mini-stat-card"><span class="mini-stat-icon"><i class="bi bi-megaphone"></i></span><div><h3><?php echo $stats['notices']; ?></h3><p>Notices</p></div></div></div>
</div>

<div class="row g-4">
  <div class="col-xl-7">
    <div class="card p-4 mb-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-book"></i> Recent Courses</h5>
        <a class="section-link" href="admin_courses.php">Manage courses</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle table-modern mb-0">
          <thead><tr><th>Course</th><th>Teacher</th><th>Materials</th><th class="text-end">Action</th></tr></thead>
          <tbody>
          <?php if (mysqli_num_rows($recent_courses) > 0): ?>
            <?php while ($c = mysqli_fetch_assoc($recent_courses)): ?>
              <tr>
                <td><?php echo e($c['course_name']); ?></td>
                <td><?php echo e($c['teacher_name']); ?></td>
                <td><span class="badge-count"><?php echo (int) $c['material_count']; ?></span></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary btn-icon-view" href="admin_course_materials.php?course_id=<?php echo (int) $c['id']; ?>">Materials</a></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No courses yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-people"></i> New Students</h5>
        <a class="section-link" href="admin_students.php">View all</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle table-modern mb-0">
          <thead><tr><th>Name</th><th>Roll No</th><th>Class</th><th class="text-end">Joined</th></tr></thead>
          <tbody>
          <?php if (mysqli_num_rows($recent_students) > 0): ?>
            <?php while ($s = mysqli_fetch_assoc($recent_students)): ?>
              <tr>
                <td><?php echo e($s['full_name']); ?></td>
                <td><?php echo e($s['roll_number']); ?></td>
                <td><?php echo e($s['class']); ?></td>
                <td class="text-end text-muted small"><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No students registered yet.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card p-4 mb-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-megaphone"></i> Latest Notices</h5>
        <a class="section-link" href="admin_notices.php">Manage notices</a>
      </div>
      <?php if (mysqli_num_rows($recent_notices) > 0): ?>
        <?php while ($n = mysqli_fetch_assoc($recent_notices)): ?>
          <div class="notice-item">
            <div class="d-flex justify-content-between align-items-start">
              <h6 class="mb-1"><?php echo e($n['title']); ?></h6>
              <span class="small text-muted"><?php echo date('d M', strtotime($n['created_at'])); ?></span>
            </div>
            <span class="small posted-by">Posted by <?php echo e($n['posted_by']); ?></span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted mb-0">No notices published yet.</p>
      <?php endif; ?>
    </div>

    <div class="card p-4">
      <h5 class="form-card-title"><i class="bi bi-lightning-charge"></i> Quick Actions</h5>
      <div class="d-grid gap-2">
        <a class="btn btn-auth-primary" href="admin_courses.php"><i class="bi bi-plus-circle"></i> Add a Course</a>
        <a class="btn btn-auth-primary" href="admin_notices.php"><i class="bi bi-megaphone"></i> Publish a Notice</a>
        <a class="btn btn-outline-primary" href="admin_students.php"><i class="bi bi-people"></i> Review Students</a>
      </div>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
