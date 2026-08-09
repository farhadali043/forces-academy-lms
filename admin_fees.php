<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_admin();

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$error = '';

/* ---- Delete a fee record ---- */
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM fees WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Fee record deleted successfully.');
    header('Location: admin_fees.php');
    exit;
}

/* ---- Mark a fee as paid ---- */
if (isset($_GET['pay'])) {
    $pay_id = (int) $_GET['pay'];
    $stmt = mysqli_prepare($conn, "UPDATE fees SET status='paid', paid_date=CURDATE() WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $pay_id);
    mysqli_stmt_execute($stmt);
    set_flash('success', 'Fee marked as paid.');
    header('Location: admin_fees.php');
    exit;
}

/* ---- Insert a new fee record ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_fee') {
    $student_id  = (int) ($_POST['student_id'] ?? 0);
    $amount      = (float) ($_POST['amount'] ?? 0);
    $due_date    = trim($_POST['due_date'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($student_id <= 0 || $amount <= 0 || $due_date === '') {
        $error = 'Please select a student and fill in amount and due date.';
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO fees (student_id, amount, due_date, description, status)
             VALUES (?, ?, ?, ?, 'pending')");
        mysqli_stmt_bind_param($stmt, 'idss', $student_id, $amount, $due_date, $description);
        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Fee record added successfully.');
            header('Location: admin_fees.php');
            exit;
        }
        $error = 'Unable to add the fee record. Please try again.';
    }
}

/* ---- Keep overdue status current (unpaid & past due date) ---- */
mysqli_query($conn, "UPDATE fees SET status='overdue' WHERE status='pending' AND due_date < CURDATE()");

/* ---- Dropdown data ---- */
$students = mysqli_query($conn, "SELECT id, full_name, roll_number FROM students ORDER BY full_name ASC");

/* ---- All fee records (with student name) ---- */
$fees = mysqli_query($conn, "
    SELECT f.id, f.amount, f.due_date, f.paid_date, f.status, f.description,
           s.full_name, s.roll_number
    FROM fees f
    LEFT JOIN students s ON s.id = f.student_id
    ORDER BY f.due_date DESC");

$page_title    = 'Fee Management';
$page_subtitle = 'Add fee records for students and track paid, pending and overdue fees.';
$active        = 'fees';
require 'admin_partials/header.php';
?>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card p-4">
      <h5 class="form-card-title"><i class="bi bi-cash-coin"></i> Add Fee Record</h5>

      <form method="POST">
        <input type="hidden" name="action" value="save_fee">

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
          <label class="form-label">Amount (PKR)</label>
          <input type="number" step="0.01" min="1" class="form-control form-control-solid" name="amount" placeholder="e.g. 12000" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Due Date</label>
          <input type="date" class="form-control form-control-solid" name="due_date" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Description</label>
          <input class="form-control form-control-solid" name="description" placeholder="e.g. Semester 4 tuition fee">
        </div>

        <button class="btn btn-auth-primary w-100" type="submit">
          <i class="bi bi-plus-circle"></i> Add Fee
        </button>
      </form>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card p-4">
      <div class="section-header mb-3">
        <h5 class="section-title mb-0"><i class="bi bi-card-list"></i> All Fee Records</h5>
        <span class="text-muted small"><?php echo (int) mysqli_num_rows($fees); ?> total</span>
      </div>

      <?php if (mysqli_num_rows($fees) > 0): ?>
        <div class="table-responsive">
          <table class="table align-middle table-modern mb-0">
            <thead>
              <tr>
                <th>Student</th><th>Amount</th><th>Due</th><th>Paid</th>
                <th>Status</th><th>Description</th><th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($f = mysqli_fetch_assoc($fees)): ?>
                <?php
                  $badge = $f['status'] === 'paid' ? 'bg-success'
                         : ($f['status'] === 'overdue' ? 'bg-danger' : 'bg-warning text-dark');
                ?>
                <tr>
                  <td class="fw-semibold"><?php echo e($f['full_name'] ?? 'Unknown'); ?></td>
                  <td class="record"><?php echo number_format((float) $f['amount'], 2); ?></td>
                  <td class="text-muted small"><?php echo date('d M Y', strtotime($f['due_date'])); ?></td>
                  <td class="text-muted small"><?php echo $f['paid_date'] ? date('d M Y', strtotime($f['paid_date'])) : '—'; ?></td>
                  <td><span class="badge <?php echo $badge; ?>"><?php echo e(ucfirst($f['status'])); ?></span></td>
                  <td class="text-muted small"><?php echo e($f['description']); ?></td>
                  <td class="text-end text-nowrap">
                    <?php if ($f['status'] !== 'paid'): ?>
                      <a class="btn btn-sm btn-outline-success" href="admin_fees.php?pay=<?php echo (int) $f['id']; ?>"
                         onclick="return confirm('Mark this fee as paid?');"><i class="bi bi-check2"></i></a>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-outline-danger btn-icon-danger" href="admin_fees.php?delete=<?php echo (int) $f['id']; ?>"
                       onclick="return confirm('Delete this fee record?');"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="bi bi-cash-coin"></i>
          <h5>No fee records yet</h5>
          <p class="text-muted mb-0">Use the form to add the first fee record.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require 'admin_partials/footer.php'; ?>
