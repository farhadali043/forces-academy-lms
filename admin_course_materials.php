<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$error = '';
$success = '';

/* ---- Resolve which course we are managing ---- */
$course_id = (int) ($_GET['course_id'] ?? ($_POST['course_id'] ?? 0));
if ($course_id <= 0) {
    header('Location: admin_courses.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, course_name, teacher_name FROM courses WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $course_id);
mysqli_stmt_execute($stmt);
$course = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$course) {
    set_flash('error', 'That course no longer exists.');
    header('Location: admin_courses.php');
    exit;
}

/* ---- Delete a material (must belong to this course) ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM course_materials WHERE id=? AND course_id=?");
    mysqli_stmt_bind_param($stmt, 'ii', $delete_id, $course_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Material deleted successfully.');
    header('Location: admin_course_materials.php?course_id=' . $course_id);
    exit;
}

/* ---- Create or update a material ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_material') {
    $material_id    = (int) ($_POST['material_id'] ?? 0);
    $material_title = trim($_POST['material_title'] ?? '');
    $material_type  = trim($_POST['material_type'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $resource_link  = trim($_POST['resource_link'] ?? '');

    if ($material_title === '' || $material_type === '') {
        $error = 'Material title and type are required.';
    } elseif ($material_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE course_materials SET material_title=?, material_type=?, description=?, resource_link=? WHERE id=? AND course_id=?");
        mysqli_stmt_bind_param($stmt, 'ssssii', $material_title, $material_type, $description, $resource_link, $material_id, $course_id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Material updated successfully.');
            header('Location: admin_course_materials.php?course_id=' . $course_id);
            exit;
        }
        $error = 'Unable to update the material.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO course_materials (course_id, material_title, material_type, description, resource_link) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'issss', $course_id, $material_title, $material_type, $description, $resource_link);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Material added successfully.');
            header('Location: admin_course_materials.php?course_id=' . $course_id);
            exit;
        }
        $error = 'Unable to add the material.';
    }
}

/* ---- Load a material into the form when editing ---- */
$edit = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT id, material_title, material_type, description, resource_link FROM course_materials WHERE id=? AND course_id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $edit_id, $course_id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$materials = mysqli_query($conn, "SELECT id, material_title, material_type, description, resource_link, created_at FROM course_materials WHERE course_id=$course_id ORDER BY created_at DESC");

$page_title    = 'Materials · ' . $course['course_name'];
$page_subtitle = 'Teacher: ' . $course['teacher_name'];
$active        = 'courses';
require 'admin_partials/header.php';
?>

<div class="mb-3"><a class="section-link" href="admin_courses.php"><i class="bi bi-arrow-left"></i> Back to courses</a></div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card p-4">
      <h5 class="form-card-title">
        <i class="bi <?php echo $edit ? 'bi-pencil-square' : 'bi-folder-plus'; ?>"></i>
        <?php echo $edit ? 'Edit Material' : 'Add Material'; ?>
      </h5>

      <?php if ($edit): ?>
        <div class="editing-banner">
          <span>Editing "<?php echo e($edit['material_title']); ?>"</span>
          <a href="admin_course_materials.php?course_id=<?php echo $course_id; ?>">Cancel</a>
        </div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="action" value="save_material">
        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
        <input type="hidden" name="material_id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
        <div class="mb-3">
          <label class="form-label">Material Title</label>
          <input class="form-control form-control-solid" name="material_title" value="<?php echo $edit ? e($edit['material_title']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Material Type</label>
          <input class="form-control form-control-solid" name="material_type" list="typeOptions" placeholder="PDF, Video, Notes, Link" value="<?php echo $edit ? e($edit['material_type']) : ''; ?>" required>
          <datalist id="typeOptions">
            <option value="PDF"><option value="Video"><option value="Notes"><option value="PPT"><option value="Link">
          </datalist>
        </div>
        <div class="mb-3">
          <label class="form-label">Resource Link</label>
          <input class="form-control form-control-solid" name="resource_link" placeholder="https://example.com/material.pdf" value="<?php echo $edit ? e($edit['resource_link']) : ''; ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control form-control-solid" name="description" rows="3"><?php echo $edit ? e($edit['description']) : ''; ?></textarea>
        </div>
        <button class="btn btn-auth-primary w-100" type="submit">
          <i class="bi bi-save"></i> <?php echo $edit ? 'Update Material' : 'Save Material'; ?>
        </button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-folder2-open"></i> Materials in this course</h5>
        <span class="text-muted small"><?php echo (int) mysqli_num_rows($materials); ?> total</span>
      </div>
      <div class="table-responsive">
        <table class="table align-middle table-modern mb-0">
          <thead>
            <tr><th>Title</th><th>Type</th><th>Link</th><th class="text-end">Actions</th></tr>
          </thead>
          <tbody>
          <?php if (mysqli_num_rows($materials) > 0): ?>
            <?php while ($m = mysqli_fetch_assoc($materials)): ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?php echo e($m['material_title']); ?></div>
                  <?php if ($m['description'] !== ''): ?><div class="small text-muted"><?php echo e($m['description']); ?></div><?php endif; ?>
                </td>
                <td><span class="badge-soft"><?php echo e($m['material_type']); ?></span></td>
                <td>
                  <?php if (!empty($m['resource_link'])): ?>
                    <a href="<?php echo e($m['resource_link']); ?>" target="_blank" rel="noopener">Open</a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2 justify-content-end">
                    <a class="btn btn-sm btn-outline-primary btn-icon-edit" href="admin_course_materials.php?course_id=<?php echo $course_id; ?>&edit=<?php echo (int) $m['id']; ?>"><i class="bi bi-pencil"></i> Edit</a>
                    <a class="btn btn-sm btn-outline-danger btn-icon-danger" href="admin_course_materials.php?course_id=<?php echo $course_id; ?>&delete=<?php echo (int) $m['id']; ?>" onclick="return confirm('Delete this material?');"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted py-4">No materials yet for this course.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
