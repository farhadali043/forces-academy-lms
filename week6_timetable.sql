-- ============================================================
--  WEEK 6 ADD-ON — Timetable table
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

CREATE TABLE IF NOT EXISTS timetable (
    id          INT(11) PRIMARY KEY AUTO_INCREMENT,
    class       VARCHAR(50)  NOT NULL,
    day         VARCHAR(20)  NOT NULL,
    time_slot   VARCHAR(30)  NOT NULL,
    subject     VARCHAR(100) NOT NULL,
    teacher     VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample entries so the grid has something to show immediately.
-- These use the class already in your students table.
INSERT INTO timetable (class, day, time_slot, subject, teacher)
SELECT DISTINCT class, 'Monday',    '09:00 - 10:00', 'Programming Fundamentals', 'Sir Zohaib Nasir' FROM students
UNION ALL
SELECT DISTINCT class, 'Monday',    '10:00 - 11:00', 'Linear Algebra',           'Dr. Saima Riaz'   FROM students
UNION ALL
SELECT DISTINCT class, 'Wednesday', '09:00 - 10:00', 'Database Systems',         'Ms. Komal Shahzad' FROM students
UNION ALL
SELECT DISTINCT class, 'Wednesday', '11:00 - 12:00', 'Web Technologies',         'Ms. Sana Yousuf'  FROM students
UNION ALL
SELECT DISTINCT class, 'Friday',    '10:00 - 11:00', 'Computer Organization',    'Dr. Tariq Mehmood' FROM students;
