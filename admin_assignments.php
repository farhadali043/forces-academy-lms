<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$error = '';

/* ---- Delete an assignment ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM assignments WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Assignment deleted successfully.');
    header('Location: admin_assignments.php');
    exit;
}

/* ---- Create or update an assignment ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_assignment') {
    $assignment_id = (int) ($_POST['assignment_id'] ?? 0);
    $title         = trim($_POST['title'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $course_id     = (int) ($_POST['course_id'] ?? 0);
    $due_date      = trim($_POST['due_date'] ?? '');

    if ($title === '' || $course_id <= 0 || $due_date === '') {
        $error = 'Title, course and due date are required.';
    } elseif ($assignment_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE assignments SET title=?, description=?, course_id=?, due_date=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssisi', $title, $description, $course_id, $due_date, $assignment_id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Assignment updated successfully.');
            header('Location: admin_assignments.php');
            exit;
        }
        $error = 'Unable to update the assignment.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO assignments (title, description, course_id, due_date) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssis', $title, $description, $course_id, $due_date);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Assignment added successfully.');
            header('Location: admin_assignments.php');
            exit;
        }
        $error = 'Unable to add the assignment.';
    }
}

/* ---- Load an assignment into the form when editing ---- */
$edit = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT id, title, description, course_id, due_date FROM assignments WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$courses = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");

$assignments = mysqli_query($conn, "
    SELECT a.id, a.title, a.due_date, c.course_name
    FROM assignments a
    LEFT JOIN courses c ON c.id = a.course_id
    ORDER BY a.due_date ASC");

$page_title    = 'Assignments';
$page_subtitle = 'Create assignments for courses and manage existing ones.';
$active        = 'assignments';
require 'admin_partials/header.php';
?>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card p-4">
      <h5 class="form-card-title">
        <i class="bi <?php echo $edit ? 'bi-pencil-square' : 'bi-clipboard-plus'; ?>"></i>
        <?php echo $edit ? 'Edit Assignment' : 'Add Assignment'; ?>
      </h5>

      <?php if ($edit): ?>
        <div class="editing-banner">
          <span>Editing "<?php echo e($edit['title']); ?>"</span>
          <a href="admin_assignments.php">Cancel</a>
        </div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="action" value="save_assignment">
        <input type="hidden" name="assignment_id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">

        <div class="mb-3">
          <label class="form-label">Title</label>
          <input class="form-control form-control-solid" name="title" value="<?php echo $edit ? e($edit['title']) : ''; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Course</label>
          <select class="form-select form-control-solid" name="course_id" required>
            <option value="">-- Select course --</option>
            <?php while ($c = mysqli_fetch_assoc($courses)): ?>
              <option value="<?php echo (int) $c['id']; ?>" <?php echo ($edit && (int) $edit['course_id'] === (int) $c['id']) ? 'selected' : ''; ?>>
                <?php echo e($c['course_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Due Date</label>
          <input type="date" class="form-control form-control-solid" name="due_date" value="<?php echo $edit ? e($edit['due_date']) : ''; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control form-control-solid" name="description" rows="4"><?php echo $edit ? e($edit['description']) : ''; ?></textarea>
        </div>

        <button class="btn btn-auth-primary w-100" type="submit">
          <i class="bi bi-save"></i> <?php echo $edit ? 'Update Assignment' : 'Add Assignment'; ?>
        </button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-card-list"></i> All Assignments</h5>
        <span class="text-muted small"><?php echo (int) mysqli_num_rows($assignments); ?> total</span>
      </div>

      <?php if (mysqli_num_rows($assignments) > 0): ?>
        <div class="table-responsive">
          <table class="table align-middle table-modern mb-0">
            <thead>
              <tr><th>Title</th><th>Course</th><th>Due Date</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
              <?php while ($a = mysqli_fetch_assoc($assignments)): ?>
                <tr>
                  <td><?php echo e($a['title']); ?></td>
                  <td><?php echo e($a['course_name'] ?? '—'); ?></td>
                  <td class="record"><?php echo $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : '—'; ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary btn-icon-edit" href="admin_assignments.php?edit=<?php echo (int) $a['id']; ?>"><i class="bi bi-pencil"></i></a>
                    <a class="btn btn-sm btn-outline-danger btn-icon-danger" href="admin_assignments.php?delete=<?php echo (int) $a['id']; ?>" onclick="return confirm('Delete this assignment?');"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-clipboard-check"></i>
          <h5>No assignments yet</h5>
          <p class="text-muted mb-0">Add the first assignment using the form.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
