# Forces Academy LMS

A simple Learning Management System built with **PHP + MySQL + Bootstrap 5**.
Students can register, log in, browse courses and their materials, read notices,
and edit their own profile. Admins get a full dashboard to manage courses,
course materials, notices, and students.

## Setup (XAMPP / WAMP)

1. Copy the `forces-academy-lms` folder into your web root (e.g. `htdocs/`).
2. Start **Apache** and **MySQL**.
3. Import the database: open phpMyAdmin → Import → choose
   `forces_academy_lms_database.sql` (it creates the DB, tables, and seed data).
4. Check `config/db.php` — defaults are host `localhost`, user `root`,
   empty password, database `forces_academy_lms`. Adjust if your setup differs.
5. Visit `http://localhost/forces-academy-lms/`.

## Logins

- **Admin:** username `admin` · password `admin123`
  (open the panel from the login page → "Open admin panel", or go to `admin_login.php`)
- **Student:** register a new account from the login page.

> Change the admin password after first login isn't wired to a UI; to reset it,
> update the `admins` table with a new `password_hash()` value.

## What's included

**Student side**
- `login.php`, `register.php`, `logout.php`
- `dashboard.php` — stats, clickable courses, latest notices, profile card
- `courses.php` / `course_detail.php` — browse courses and open materials
- `notices.php` — all announcements
- `profile.php` — edit name, email, roll no, class, and password

**Admin side** (shared sidebar layout via `admin_partials/`)
- `admin_login.php`, `admin_logout.php`
- `admin_dashboard.php` — overview stats + recent activity + quick actions
- `admin_courses.php` — add / edit / delete courses
- `admin_course_materials.php` — add / edit / delete materials per course
- `admin_notices.php` — publish / edit / delete notices
- `admin_students.php` — search and remove students

**Shared**
- `config/db.php` — MySQL connection
- `config/auth.php` — session helpers, `require_student()`, `require_admin()`, `e()`
- `admin_partials/header.php` & `footer.php` — sidebar, topbar, flash messages
- `css/style.css`, `js/main.js`

## Notes

- All database access uses **prepared statements**; passwords are hashed with
  `password_hash()`; output is escaped with the `e()` helper.
- Deleting a course cascades to its materials via a foreign key.
