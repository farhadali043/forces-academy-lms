-- ============================================================
--  WEEK 7 ADD-ON — Fees table
-- ============================================================
--  Run this on your EXISTING database (local AND live hosting).
--  It only ADDS a new table — it will NOT touch or delete any
--  of your existing students, courses, notices, results, etc.
--
--  HOW TO RUN:
--    1. Open phpMyAdmin -> select your database (forces_academy_lms
--       locally, or if0_xxxxxxx_farhad on InfinityFree)
--    2. Click the "SQL" tab
--    3. Paste this whole file in and click "Go"
-- ============================================================

CREATE TABLE IF NOT EXISTS fees (
    id          INT(11) PRIMARY KEY AUTO_INCREMENT,
    student_id  INT(11) NOT NULL,
    amount      DECIMAL(10,2) NOT NULL,
    due_date    DATE NOT NULL,
    paid_date   DATE DEFAULT NULL,
    status      ENUM('paid','pending','overdue') NOT NULL DEFAULT 'pending',
    description VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fees_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Sample fee records for the demo student (student_id = 1) so the
-- page has something to show immediately.
INSERT INTO fees (student_id, amount, due_date, paid_date, status, description) VALUES
(1, 45000.00, '2026-07-10', '2026-07-08', 'paid',    'Semester 4 tuition fee'),
(1, 3500.00,  '2026-07-15', NULL,         'overdue', 'Library & lab charges'),
(1, 12000.00, '2026-09-01', NULL,         'pending', 'Examination fee - Fall 2026');
