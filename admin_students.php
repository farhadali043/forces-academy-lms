<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$error = '';
$success = '';

/* ---- Delete a student ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Student removed successfully.');
    header('Location: admin_students.php');
    exit;
}

/* ---- Optional search ---- */
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, roll_number, class, created_at FROM students WHERE full_name LIKE ? OR email LIKE ? OR roll_number LIKE ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $students = mysqli_stmt_get_result($stmt);
} else {
    $students = mysqli_query($conn, "SELECT id, full_name, email, roll_number, class, created_at FROM students ORDER BY created_at DESC");
}

$page_title    = 'Students';
$page_subtitle = 'Registered students across the academy.';
$active        = 'students';
require 'admin_partials/header.php';
?>

<div class="card p-4">
  <div class="section-header mb-3">
    <h5 class="section-title mb-0"><i class="bi bi-people"></i> Student Directory</h5>
    <form method="GET" class="d-flex gap-2" style="max-width:340px;width:100%;">
      <input class="form-control form-control-solid" type="text" name="q" value="<?php echo e($q); ?>" placeholder="Search name, email, roll no">
      <button class="btn btn-auth-primary" type="submit"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <?php if ($q !== ''): ?>
    <p class="small text-muted">Showing results for "<?php echo e($q); ?>" — <a href="admin_students.php">clear</a></p>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table align-middle table-modern mb-0">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Roll No</th><th>Class</th><th>Joined</th><th class="text-end">Action</th></tr>
      </thead>
      <tbody>
      <?php if (mysqli_num_rows($students) > 0): ?>
        <?php while ($s = mysqli_fetch_assoc($students)): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span class="profile-avatar" style="width:38px;height:38px;font-size:1rem;"><?php echo strtoupper(substr($s['full_name'], 0, 1)); ?></span>
                <span class="fw-semibold"><?php echo e($s['full_name']); ?></span>
              </div>
            </td>
            <td class="text-muted"><?php echo e($s['email']); ?></td>
            <td><span class="badge-soft"><?php echo e($s['roll_number']); ?></span></td>
            <td><?php echo e($s['class']); ?></td>
            <td class="text-muted small"><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-danger btn-icon-danger" href="admin_students.php?delete=<?php echo (int) $s['id']; ?>" onclick="return confirm('Remove this student account?');"><i class="bi bi-trash"></i> Remove</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center text-muted py-4"><?php echo $q !== '' ? 'No students match your search.' : 'No students have registered yet.'; ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
