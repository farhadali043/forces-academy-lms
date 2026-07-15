<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$error = '';
$success = '';

/* ---- Delete a notice ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM notices WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Notice deleted successfully.');
    header('Location: admin_notices.php');
    exit;
}

/* ---- Create or update a notice ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_notice') {
    $notice_id = (int) ($_POST['notice_id'] ?? 0);
    $title     = trim($_POST['title'] ?? '');
    $content   = trim($_POST['content'] ?? '');
    $posted_by = trim($_POST['posted_by'] ?? '');
    $posted_by = $posted_by !== '' ? $posted_by : $admin_username;

    if ($title === '' || $content === '') {
        $error = 'Notice title and content are required.';
    } elseif ($notice_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE notices SET title=?, content=?, posted_by=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssi', $title, $content, $posted_by, $notice_id);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Notice updated successfully.');
            header('Location: admin_notices.php');
            exit;
        }
        $error = 'Unable to update the notice.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, content, posted_by) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $title, $content, $posted_by);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Notice published successfully.');
            header('Location: admin_notices.php');
            exit;
        }
        $error = 'Unable to publish the notice.';
    }
}

/* ---- Load a notice into the form when editing ---- */
$edit = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $stmt = mysqli_prepare($conn, "SELECT id, title, content, posted_by FROM notices WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$notices = mysqli_query($conn, "SELECT id, title, content, posted_by, created_at FROM notices ORDER BY created_at DESC");

$page_title    = 'Notices';
$page_subtitle = 'Publish announcements and edit or remove existing ones.';
$active        = 'notices';
require 'admin_partials/header.php';
?>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card p-4">
      <h5 class="form-card-title">
        <i class="bi <?php echo $edit ? 'bi-pencil-square' : 'bi-megaphone'; ?>"></i>
        <?php echo $edit ? 'Edit Notice' : 'Publish Notice'; ?>
      </h5>

      <?php if ($edit): ?>
        <div class="editing-banner">
          <span>Editing "<?php echo e($edit['title']); ?>"</span>
          <a href="admin_notices.php">Cancel</a>
        </div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="action" value="save_notice">
        <input type="hidden" name="notice_id" value="<?php echo $edit ? (int) $edit['id'] : 0; ?>">
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input class="form-control form-control-solid" name="title" value="<?php echo $edit ? e($edit['title']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Posted By</label>
          <input class="form-control form-control-solid" name="posted_by" value="<?php echo $edit ? e($edit['posted_by']) : e($admin_username); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Content</label>
          <textarea class="form-control form-control-solid" name="content" rows="6" required><?php echo $edit ? e($edit['content']) : ''; ?></textarea>
        </div>
        <button class="btn btn-auth-primary w-100" type="submit">
          <i class="bi bi-save"></i> <?php echo $edit ? 'Update Notice' : 'Publish Notice'; ?>
        </button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-card-list"></i> Published Notices</h5>
        <span class="text-muted small"><?php echo (int) mysqli_num_rows($notices); ?> total</span>
      </div>

      <?php if (mysqli_num_rows($notices) > 0): ?>
        <?php while ($n = mysqli_fetch_assoc($notices)): ?>
          <div class="notice-item notice-item-large">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-2">
              <div>
                <h6 class="mb-1"><?php echo e($n['title']); ?></h6>
                <div class="small text-muted">Posted by <?php echo e($n['posted_by']); ?> · <?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></div>
              </div>
              <div class="d-inline-flex gap-2 align-items-start">
                <a class="btn btn-sm btn-outline-primary btn-icon-edit" href="admin_notices.php?edit=<?php echo (int) $n['id']; ?>"><i class="bi bi-pencil"></i> Edit</a>
                <a class="btn btn-sm btn-outline-danger btn-icon-danger" href="admin_notices.php?delete=<?php echo (int) $n['id']; ?>" onclick="return confirm('Delete this notice?');"><i class="bi bi-trash"></i></a>
              </div>
            </div>
            <p class="mb-0 notice-content"><?php echo nl2br(e(strlen($n['content']) > 200 ? substr($n['content'], 0, 200) . '…' : $n['content'])); ?></p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-megaphone"></i>
          <h5>No notices yet</h5>
          <p class="text-muted mb-0">Publish your first announcement using the form.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
