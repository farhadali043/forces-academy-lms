-- ============================================================
--  FORCES ACADEMY LMS  —  Full Database (Week 1 + 2 + 3)
-- ============================================================
--  HOW TO IMPORT:
--    1. Open phpMyAdmin  ->  http://localhost/phpmyadmin
--    2. Click the "Import" tab at the top
--    3. Choose this file (forces_academy_lms_database.sql) and press "Go"
--       -- OR -- paste the whole file into the "SQL" tab and run it.
-- ============================================================

CREATE DATABASE IF NOT EXISTS forces_academy_lms;
USE forces_academy_lms;

-- ------------------------------------------------------------
--  WEEK 1  —  core tables
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS students (
    id           INT(11) PRIMARY KEY AUTO_INCREMENT,
    full_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(100) UNIQUE NOT NULL,
    password     VARCHAR(255) NOT NULL,
    roll_number  VARCHAR(20)  UNIQUE NOT NULL,
    class        VARCHAR(50)  NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id        INT(11) PRIMARY KEY AUTO_INCREMENT,
    username  VARCHAR(50) UNIQUE NOT NULL,
    password  VARCHAR(255) NOT NULL,
    email     VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS courses (
    id            INT(11) PRIMARY KEY AUTO_INCREMENT,
    course_name   VARCHAR(100) NOT NULL,
    description   TEXT,
    teacher_name  VARCHAR(100),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notices (
    id          INT(11) PRIMARY KEY AUTO_INCREMENT,
    title       VARCHAR(200) NOT NULL,
    content     TEXT,
    posted_by   VARCHAR(100),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS course_materials (
    id             INT(11) PRIMARY KEY AUTO_INCREMENT,
    course_id      INT(11) NOT NULL,
    material_title VARCHAR(150) NOT NULL,
    material_type  VARCHAR(50) NOT NULL DEFAULT 'Notes',
    description    TEXT,
    resource_link  VARCHAR(255),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_materials_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
--  WEEK 3  —  assignments, submissions, results
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS assignments (
    id          INT(11) PRIMARY KEY AUTO_INCREMENT,
    title       VARCHAR(200) NOT NULL,
    description TEXT,
    course_id   INT(11),
    due_date    DATE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_assignments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS submissions (
    id            INT(11) PRIMARY KEY AUTO_INCREMENT,
    assignment_id INT(11),
    student_id    INT(11),
    file_path     VARCHAR(255),
    submitted_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status        ENUM('submitted','graded') DEFAULT 'submitted',
    CONSTRAINT fk_sub_assignment FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    CONSTRAINT fk_sub_student    FOREIGN KEY (student_id)    REFERENCES students(id)    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS results (
    id           INT(11) PRIMARY KEY AUTO_INCREMENT,
    student_id   INT(11),
    course_id    INT(11),
    subject      VARCHAR(100),
    marks        INT(11),
    total_marks  INT(11),
    grade        VARCHAR(10),
    exam_type    VARCHAR(50),
    CONSTRAINT fk_results_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- ============================================================
--  SAMPLE DATA
-- ============================================================

-- Admin account
--   Username: admin
--   Password: admin123
INSERT INTO admins (username, password, email) VALUES
('admin', '$2b$10$8xsbLjy2iS95OiYe6H8AiePtweyngUpApfO16XNYwGYqv97KGWT22', 'admin@forcesacademy.edu.pk')
ON DUPLICATE KEY UPDATE password = VALUES(password), email = VALUES(email);

-- Demo student
--   Email:    farhad.ali@student.forces.edu.pk
--   Password: 12345
INSERT INTO students (full_name, email, password, roll_number, class) VALUES
('Farhad Ali', 'farhad.ali@student.forces.edu.pk', '$2b$10$DmFnDfmNkjlX8r.Cb7ZLm.2Oh3c9HDgfuDdL0J1ddm8Lznf9u8aoi', '2024-BS-SE-023', 'BS Computer Science — 4th');

-- Courses (fresh set with distinct instructors)
INSERT INTO courses (course_name, description, teacher_name) VALUES
('Calculus & Analytical Geometry', 'Limits, differentiation, integration and their applications in engineering problems.', 'Dr. Kamran Sethi'),
('Digital Logic Design', 'Boolean algebra, combinational and sequential circuits, and register design.', 'Engr. Rabia Tanveer'),
('Data Structures & Algorithms', 'Arrays, linked lists, trees, graphs, sorting and algorithmic complexity.', 'Dr. Imran Qureshi'),
('Software Engineering', 'Requirements, design patterns, agile workflows and the software lifecycle.', 'Ms. Nida Farooq'),
('Artificial Intelligence', 'Search, knowledge representation, and an introduction to machine learning.', 'Dr. Waleed Siddiqui'),
('Cybersecurity Fundamentals', 'Threat models, cryptography basics, network defense and secure coding.', 'Engr. Hassan Javed');

-- Notices
INSERT INTO notices (title, content, posted_by) VALUES
('Midterm Examination Schedule Released', 'The midterm timetable for all Computer Science courses is now available on the notice board. Exams begin Monday, 27 July.', 'Examinations Office'),
('Assignment Submission Portal Now Live', 'You can now upload your assignments directly from the Assignments page. Only PDF and image files are accepted.', 'Academic Office'),
('Semester Project Guidelines Updated', 'Revised guidelines for the final semester project have been published. Please review the marking rubric before you begin.', 'Project Committee'),
('Career Fair 2026 — Registration Open', 'Meet recruiters from leading tech companies on 5 August in the Main Auditorium. Register early as seats are limited.', 'Placement Cell');

-- Course materials
INSERT INTO course_materials (course_id, material_title, material_type, description, resource_link) VALUES
(1, 'Integration Techniques — Lecture Notes', 'PDF', 'Worked examples on integration by parts and substitution.', '#'),
(2, 'K-Map Simplification Slides', 'PPT', 'Simplifying Boolean expressions using Karnaugh maps.', '#'),
(3, 'Binary Trees & Traversals', 'PDF', 'Pre-order, in-order and post-order traversal with code.', '#'),
(3, 'Sorting Algorithms Cheat Sheet', 'Notes', 'Time and space complexity comparison table.', '#'),
(5, 'Intro to Search Algorithms', 'PDF', 'BFS, DFS and A* search with diagrams.', '#');

-- Assignments (Week 3) — tied to courses above
INSERT INTO assignments (title, description, course_id, due_date) VALUES
('Definite Integrals Problem Set', 'Solve the ten problems on definite integrals and upload your handwritten solutions as a single PDF.', 1, '2026-07-24'),
('4-bit Adder Circuit', 'Design a 4-bit ripple-carry adder, draw the schematic, and submit an image of your circuit.', 2, '2026-07-26'),
('Implement a Binary Search Tree', 'Write a report explaining your BST insert and delete operations. Submit as PDF.', 3, '2026-07-29'),
('Requirements Specification Draft', 'Prepare a one-page SRS for the sample e-library system and upload it as a PDF.', 4, '2026-08-01');

-- Results (Week 3) — for the demo student (student_id = 1)
INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type) VALUES
(1, 1, 'Calculus & Analytical Geometry', 43, 50, 'A',  'Midterm'),
(1, 2, 'Digital Logic Design',          38, 50, 'B+', 'Midterm'),
(1, 3, 'Data Structures & Algorithms',  47, 50, 'A+', 'Midterm'),
(1, 4, 'Software Engineering',          40, 50, 'A',  'Quiz'),
(1, 5, 'Artificial Intelligence',       33, 50, 'B',  'Quiz');
