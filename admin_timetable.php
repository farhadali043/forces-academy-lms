<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$error = '';

// Fixed day and time-slot lists so the weekly grid always lines up correctly.
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$time_slots = [
    '08:00 - 09:00', '09:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00',
    '12:00 - 01:00', '01:00 - 02:00', '02:00 - 03:00', '03:00 - 04:00',
];

/* ---- Delete a timetable entry ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM timetable WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Timetable entry deleted successfully.');
    header('Location: admin_timetable.php');
    exit;
}

/* ---- Add a new timetable entry ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_entry') {
    $class     = trim($_POST['class'] ?? '');
    $day       = trim($_POST['day'] ?? '');
    $time_slot = trim($_POST['time_slot'] ?? '');
    $subject   = trim($_POST['subject'] ?? '');
    $teacher   = trim($_POST['teacher'] ?? '');

    if ($class === '' || $day === '' || $time_slot === '' || $subject === '' || $teacher === '') {
        $error = 'Please fill in every field before saving.';
    } elseif (!in_array($day, $days, true)) {
        $error = 'Please choose a valid day.';
    } else {
        // Prevent double-booking the same class/day/time slot.
        $check = mysqli_prepare($conn, "SELECT id FROM timetable WHERE class=? AND day=? AND time_slot=?");
        mysqli_stmt_bind_param($check, 'sss', $class, $day, $time_slot);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = 'This class already has a subject scheduled for that day and time slot.';
        } else {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO timetable (class, day, time_slot, subject, teacher) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssss', $class, $day, $time_slot, $subject, $teacher);
            if (mysqli_stmt_execute($stmt)) {
                set_flash('success', 'Timetable entry added successfully.');
                header('Location: admin_timetable.php');
                exit;
            }
            $error = 'Unable to add the timetable entry. Please try again.';
        }
    }
}

/* ---- Classes to choose from (pulled from real students, so it always matches) ---- */
$classes = [];
$res = mysqli_query($conn, "SELECT DISTINCT class FROM students WHERE class IS NOT NULL AND class <> '' ORDER BY class ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $classes[] = $row['class'];
}

/* ---- All timetable entries, ordered by class / weekday / time ---- */
$dayOrderCase = "CASE day "
    . "WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 "
    . "WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 ELSE 7 END";
$entries = mysqli_query($conn, "SELECT * FROM timetable ORDER BY class ASC, $dayOrderCase ASC, time_slot ASC");

$page_title    = 'Timetable';
$page_subtitle = 'Add weekly class schedule entries. Students see these on their own Timetable page.';
$active        = 'timetable';
require 'admin_partials/header.php';
?>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card p-4">
      <h5 class="form-card-title"><i class="bi bi-calendar-plus"></i> Add Timetable Entry</h5>

      <form method="POST">
        <input type="hidden" name="action" value="save_entry">

        <div class="mb-3">
          <label class="form-label">Class</label>
          <?php if (count($classes) > 0): ?>
            <select class="form-select form-control-solid" name="class" required>
              <option value="">-- Select class --</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input class="form-control form-control-solid" name="class" placeholder="e.g. BS Computer Science - 4th" required>
            <div class="form-text">No students registered yet — type the class name manually.</div>
          <?php endif; ?>
        </div>

        <div class="mb-3">
          <label class="form-label">Day</label>
          <select class="form-select form-control-solid" name="day" required>
            <option value="">-- Select day --</option>
            <?php foreach ($days as $d): ?>
              <option value="<?php echo e($d); ?>"><?php echo e($d); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Time Slot</label>
          <select class="form-select form-control-solid" name="time_slot" required>
            <option value="">-- Select time slot --</option>
            <?php foreach ($time_slots as $t): ?>
              <option value="<?php echo e($t); ?>"><?php echo e($t); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Subject</label>
          <input class="form-control form-control-solid" name="subject" placeholder="e.g. Data Structures" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Teacher</label>
          <input class="form-control form-control-solid" name="teacher" placeholder="e.g. Dr. Imran Qureshi" required>
        </div>

        <button class="btn btn-auth-primary w-100" type="submit">
          <i class="bi bi-save"></i> Add to Timetable
        </button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-card-list"></i> All Timetable Entries</h5>
        <span class="text-muted small"><?php echo (int) mysqli_num_rows($entries); ?> total</span>
      </div>

      <?php if (mysqli_num_rows($entries) > 0): ?>
        <div class="table-responsive">
          <table class="table align-middle table-modern mb-0">
            <thead>
              <tr>
                <th>Class</th><th>Day</th><th>Time Slot</th><th>Subject</th><th>Teacher</th><th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($t = mysqli_fetch_assoc($entries)): ?>
                <tr>
                  <td><span class="badge subtle-badge"><?php echo e($t['class']); ?></span></td>
                  <td><?php echo e($t['day']); ?></td>
                  <td class="record"><?php echo e($t['time_slot']); ?></td>
                  <td><?php echo e($t['subject']); ?></td>
                  <td><?php echo e($t['teacher']); ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-danger btn-icon-danger"
                       href="admin_timetable.php?delete=<?php echo (int) $t['id']; ?>"
                       onclick="return confirm('Delete this timetable entry?');"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-calendar3"></i>
          <h5>No timetable entries yet</h5>
          <p class="text-muted mb-0">Use the form to add the first class schedule entry.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
