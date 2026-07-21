<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$error = '';

/* ---- Delete a result ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM results WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Result deleted successfully.');
    header('Location: admin_results.php');
    exit;
}

/* ---- Insert a new result ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_result') {
    $student_id  = (int) ($_POST['student_id'] ?? 0);
    $course_id   = (int) ($_POST['course_id'] ?? 0);
    $subject     = trim($_POST['subject'] ?? '');
    $marks       = (int) ($_POST['marks'] ?? 0);
    $total_marks = (int) ($_POST['total_marks'] ?? 0);
    $grade       = trim($_POST['grade'] ?? '');
    $exam_type   = trim($_POST['exam_type'] ?? '');

    if ($student_id <= 0 || $subject === '' || $total_marks <= 0) {
        $error = 'Please select a student and fill in subject and total marks.';
    } elseif ($marks > $total_marks) {
        $error = 'Obtained marks cannot be greater than total marks.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type)
             VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iisiiss', $student_id, $course_id, $subject, $marks, $total_marks, $grade, $exam_type);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Result uploaded successfully.');
            header('Location: admin_results.php');
            exit;
        }
        $error = 'Unable to upload the result. Please try again.';
    }
}

/* ---- Dropdown data ---- */
$students = mysqli_query($conn, "SELECT id, full_name, roll_number FROM students ORDER BY full_name ASC");
$courses  = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");

/* ---- Recently uploaded results (with student + course name) ---- */
$results = mysqli_query($conn, "
    SELECT r.id, r.subject, r.marks, r.total_marks, r.grade, r.exam_type,
           s.full_name, c.course_name
    FROM results r
    LEFT JOIN students s ON s.id = r.student_id
    LEFT JOIN courses  c ON c.id = r.course_id
    ORDER BY r.id DESC");

$page_title    = 'Upload Results';
$page_subtitle = 'Add exam results for students and review recently uploaded ones.';
$active        = 'results';
require 'admin_partials/header.php';
?>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card p-4">
      <h5 class="form-card-title"><i class="bi bi-bar-chart-line"></i> Add Result</h5>

      <form method="POST">
        <input type="hidden" name="action" value="save_result">

        <div class="mb-3">
          <label class="form-label">Student</label>
          <select class="form-select form-control-solid" name="student_id" required>
            <option value="">-- Select student --</option>
            <?php while ($s = mysqli_fetch_assoc($students)): ?>
              <option value="<?php echo (int) $s['id']; ?>">
                <?php echo e($s['full_name']); ?> (<?php echo e($s['roll_number']); ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Course</label>
          <select class="form-select form-control-solid" name="course_id">
            <option value="">-- Select course --</option>
            <?php while ($c = mysqli_fetch_assoc($courses)): ?>
              <option value="<?php echo (int) $c['id']; ?>"><?php echo e($c['course_name']); ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Subject</label>
          <input class="form-control form-control-solid" name="subject" placeholder="e.g. Data Structures" required>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-3">
            <label class="form-label">Marks</label>
            <input type="number" class="form-control form-control-solid" name="marks" min="0" value="0" required>
          </div>
          <div class="col-6 mb-3">
            <label class="form-label">Total</label>
            <input type="number" class="form-control form-control-solid" name="total_marks" min="1" value="100" required>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-3">
            <label class="form-label">Grade</label>
            <input class="form-control form-control-solid" name="grade" placeholder="e.g. A+">
          </div>
          <div class="col-6 mb-3">
            <label class="form-label">Exam Type</label>
            <input class="form-control form-control-solid" name="exam_type" placeholder="e.g. Midterm">
          </div>
        </div>

        <button class="btn btn-auth-primary w-100" type="submit">
          <i class="bi bi-upload"></i> Upload Result
        </button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-card-list"></i> Recently Uploaded Results</h5>
        <span class="text-muted small"><?php echo (int) mysqli_num_rows($results); ?> total</span>
      </div>

      <?php if (mysqli_num_rows($results) > 0): ?>
        <div class="table-responsive">
          <table class="table align-middle table-modern mb-0">
            <thead>
              <tr>
                <th>Student</th><th>Subject</th><th>Marks</th><th>Grade</th><th>Exam</th><th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($r = mysqli_fetch_assoc($results)): ?>
                <tr>
                  <td><?php echo e($r['full_name'] ?? 'Unknown'); ?></td>
                  <td><?php echo e($r['subject']); ?></td>
                  <td class="record"><?php echo (int) $r['marks']; ?>/<?php echo (int) $r['total_marks']; ?></td>
                  <td><span class="badge bg-primary"><?php echo e($r['grade']); ?></span></td>
                  <td><?php echo e($r['exam_type']); ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-danger btn-icon-danger"
                       href="admin_results.php?delete=<?php echo (int) $r['id']; ?>"
                       onclick="return confirm('Delete this result?');"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-bar-chart-line"></i>
          <h5>No results uploaded yet</h5>
          <p class="text-muted mb-0">Use the form to add the first result.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
