# Week 7 — Fee Management + Search + Print

All changes follow your existing style (procedural mysqli, `config/db.php`,
`config/auth.php`, the `e()` helper, and the admin header/footer partials).

## 1. Run the SQL first
Import **`week7_fees.sql`** in phpMyAdmin (local **and** on InfinityFree).
It only ADDS a `fees` table plus 3 sample rows for the demo student — it does
not touch your existing data.

## Step 1 — Fee Management
- **`admin_fees.php`** (new) — admin adds fee records (select student, amount,
  due date, description), lists all fees, can *Mark paid* or *Delete*.
  A "Fees" link was added to the admin sidebar (`admin_partials/header.php`).
- **`fees.php`** (new) — student view. Shows **Total Pending Amount prominently
  at the top**, plus Total Paid / record count, then a table of all their fees
  with status badges (paid / pending / overdue). Overdue is auto-detected when
  an unpaid fee's due date has passed.
- A "Fees" link was added to the student navbar on every page.

## Step 2 — Search
- **`notices.php`** — added search by **title** (PHP `$_GET` + `LIKE`).
- **`admin_students.php`** — already searched by name / email / roll number
  (built in Week 6), so nothing was needed there. It uses the same GET+LIKE pattern.

## Step 3 — Print Results
- **`results.php`** — added a **Print Results** button that calls `window.print()`,
  and a `@media print` block that hides the navbar, buttons and form so only the
  results table prints.

## Screenshots to submit (Friday Aug 14)
1. `admin_fees.php` — the fee management page (form + records list).
2. `fees.php` — student view with the total pending amount at top.
3. `notices.php?q=exam` — search working (title search).
4. `admin_students.php?q=farhad` — search working (name/email/roll).
5. `results.php` — the Print Results button (and the print preview if you like).

## Quick local test
```
Admin  -> admin_login.php  (admin / admin123)  -> Fees in sidebar
Student-> login.php (farhad.ali@student.forces.edu.pk / 12345) -> Fees + Results (Print)
```
