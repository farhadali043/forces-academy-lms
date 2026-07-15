<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$error = '';
$success = '';

/* ---- Delete a course (materials cascade via FK) ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Course deleted along with its materials.');
    header('Location: admin_courses.php');
    exit;
}

/* ---- Create or update a course ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_course') {
    $course_id    = (int) ($_POST['course_id'] ?? 0);
    $course_name  = trim($_POST['course_name'] ?? '');
    $teacher_name = trim($_POST['teacher_name'] ?? '');
    $description  = trim($_POST['description'] ?? '');

    if ($course_name === '' || $teacher_name === '') {
        $error = 'Course name and teacher name are required.';
    } elseif ($course_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE courses SET course_name=?, description=?, teacher_name=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssi', $course_name, $description, $teacher_name, $course_id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Course updated successfully.');
            header('Location: admin_courses.php');
            exit;
        }
        $error = 'Unable to update the course.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO courses (course_name, description, teacher_name) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $course_name, $description, $teacher_name);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Course added successfully.');
            header('Location: admin_courses.php');
            exit;
        }
        $error = 'Unable to add the course.';
    }
}

/* ---- Load a course into the form when editing ---- */
$edit = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT id, course_name, description, teacher_name FROM courses WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$courses = mysqli_query($conn, "
    SELECT c.id, c.course_name, c.teacher_name, c.created_at, COUNT(cm.id) AS material_count
    FROM courses c
    LEFT JOIN course_materials cm ON cm.course_id = c.id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");

$page_title    = 'Courses';
$page_subtitle = 'Add, edit, and remove courses. Manage each course\'s materials from here too.';
$active        = 'courses';
require 'admin_partials/header.php';
?>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card p-4">
      <h5 class="form-card-title">
        <i class="bi <?php echo $edit ? 'bi-pencil-square' : 'bi-plus-circle'; ?>"></i>
        <?php echo $edit ? 'Edit Course' : 'Add Course'; ?>
      </h5>

      <?php if ($edit): ?>
        <div class="editing-banner">
          <span>Editing "<?php echo e($edit['course_name']); ?>"</span>
          <a href="admin_courses.php">Cancel</a>
        </div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="action" value="save_course">
        <input type="hidden" name="course_id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
        <div class="mb-3">
          <label class="form-label">Course Name</label>
          <input class="form-control form-control-solid" name="course_name" value="<?php echo $edit ? e($edit['course_name']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Teacher Name</label>
          <input class="form-control form-control-solid" name="teacher_name" value="<?php echo $edit ? e($edit['teacher_name']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control form-control-solid" name="description" rows="4"><?php echo $edit ? e($edit['description']) : ''; ?></textarea>
        </div>
        <button class="btn btn-auth-primary w-100" type="submit">
          <i class="bi bi-save"></i> <?php echo $edit ? 'Update Course' : 'Save Course'; ?>
        </button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-book"></i> Course List</h5>
        <span class="text-muted small">Deleting a course also removes its materials</span>
      </div>
      <div class="table-responsive">
        <table class="table align-middle table-modern mb-0">
          <thead>
            <tr><th>Course</th><th>Teacher</th><th>Materials</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
          <?php if (mysqli_num_rows($courses) > 0): ?>
            <?php while ($course = mysqli_fetch_assoc($courses)): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?php echo e($course['course_name']); ?></div>
                  <div class="small text-muted"><?php echo date('d M Y', strtotime($course['created_at'])); ?></div>
                </td>
                <td><?php echo e($course['teacher_name']); ?></td>
                <td><span class="badge-count"><?php echo (int) $course['material_count']; ?></span></td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                    <a class="btn btn-sm btn-outline-secondary btn-icon-view" href="admin_course_materials.php?course_id=<?php echo (int) $course['id']; ?>"><i class="bi bi-folder2-open"></i> Materials</a>
                    <a class="btn btn-sm btn-outline-primary btn-icon-edit" href="admin_courses.php?edit=<?php echo (int) $course['id']; ?>"><i class="bi bi-pencil"></i> Edit</a>
                    <a class="btn btn-sm btn-outline-danger btn-icon-danger" href="admin_courses.php?delete=<?php echo (int) $course['id']; ?>" onclick="return confirm('Delete this course and all its materials?');"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No courses found. Add your first course on the left.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
