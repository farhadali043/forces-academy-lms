# Forces Academy LMS

A Learning Management System built with **PHP + MySQL + Bootstrap 5**, covering
**Week 1, Week 2, and Week 3** of the Full Stack track in one project.

Students can register, log in, browse courses and materials, read notices,
**submit assignments**, and **view their results**. Admins get a full dashboard
to manage courses, materials, notices, and students.

## Setup (XAMPP / WAMP)

1. Copy the `forces-academy-lms` folder into your web root (e.g. `htdocs/`).
2. Start **Apache** and **MySQL**.
3. Import the database: open phpMyAdmin → Import → choose
   `forces_academy_lms_database.sql` (it creates the DB, all tables, and seed data).
4. Make sure the `uploads/` folder exists and is writable — submitted assignment
   files are saved there.
5. Check `config/db.php` — defaults are host `localhost`, user `root`,
   empty password, database `forces_academy_lms`. Adjust if your setup differs.
6. Visit `http://localhost/forces-academy-lms/`.

## Logins

- **Student:** email `farhad.ali@student.forces.edu.pk` · password `12345`
  (or register a brand-new account from the login page)
- **Admin:** username `admin` · password `admin123`
  (open from the login page → "Open admin panel", or go to `admin_login.php`)

## What's included

**Week 1 — accounts**
- `login.php`, `register.php`, `logout.php`, `index.php`
- Sessions + `password_hash()` / `password_verify()`

**Week 2 — core pages**
- `dashboard.php` — stat cards, courses, latest notices, quick links
- `courses.php` / `course_detail.php` — browse courses and open materials
- `notices.php` — all announcements
- `profile.php` — edit profile

**Week 3 — assignments & results**
- `assignments.php` — list assignments, upload a PDF/image submission,
  shows a **Submitted** badge once you've turned it in
- `results.php` — your marks, percentage and grade per subject
- Tables: `assignments`, `submissions`, `results`

**Admin side** (`admin_partials/` shared layout)
- `admin_dashboard.php`, `admin_courses.php`, `admin_course_materials.php`,
  `admin_notices.php`, `admin_students.php`, `admin_login.php`, `admin_logout.php`

**Shared**
- `config/db.php` (MySQL connection) · `config/auth.php` (session helpers,
  `require_student()`, `require_admin()`, `e()`)
- `css/style.css`, `js/main.js`, `uploads/` (submitted files)

## Notes

- All database access uses **prepared statements**; passwords are hashed;
  output is escaped with the `e()` helper.
- Assignment uploads accept **PDF and image files only** (validated by extension
  and real MIME type), max 5 MB, saved with a unique filename.
- Deleting a course/student cascades to related rows via foreign keys.
