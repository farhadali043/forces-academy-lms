# Forces Academy LMS

This is a Learning Management System I built for Forces Academy Faisalabad during my Full Stack internship at Code Saviours. Students can log in to see their courses, notices, timetable, results and fees, and submit their assignments online. There is also an admin side where the academy can manage everything, from students and courses to results and fees.

I made the backend in PHP and MySQL and used Bootstrap 5 for the frontend. The project is hosted live, so it can be opened and tested in the browser.

**Live site:** https://farhadlms.great-site.net/login.php

## Try it out

**Student**
- Email: farhad.ali@student.forces.edu.pk
- Password: 12345

You can also register a new account from the login page.

**Admin**
- Username: admin
- Password: admin123

## Screenshots

| Student Dashboard | Fee Management |
|---|---|
| ![Dashboard](Screenshots/dashboard.png) | ![Fees](Screenshots/fee.png) |

| Notices Search | Print Results |
|---|---|
| ![Search](Screenshots/search.png) | ![Print](Screenshots/print.png) |

| Profile Page |
|---|
| ![Profile](Screenshots/profile.png) |


## What it can do

**For students**
- Sign up, log in and log out (with sessions and hashed passwords)
- A dashboard that shows their courses, latest notices and quick links
- Browse courses and open the study materials
- Upload an assignment as a PDF or image file
- Check results with marks, percentage and grade, and print them
- See the weekly class timetable
- View fee records with the total pending amount shown at the top
- Search notices by title
- Edit the profile and change password

**For the admin**
- A separate admin login and dashboard
- Add and manage courses and course materials
- Post notices
- Manage students and search them by name, email or roll number
- Upload student results
- Set up the class timetable
- Add fees and track which ones are paid, pending or overdue

## Built with

- PHP for the backend
- MySQL for the database
- Bootstrap 5, HTML, CSS and JavaScript for the frontend
- Hosted on InfinityFree

For safety I used prepared statements for the database queries, stored the passwords in hashed form, and allowed only PDF and image files for uploads.

## Running it locally

1. Start Apache and MySQL in XAMPP.
2. Put the project folder inside `htdocs`.
3. In phpMyAdmin, create a database and import `forces_academy_lms_database.sql`, then also import `week6_timetable.sql` and `week7_fees.sql`.
4. Copy `config/db.example.php` to `config/db.php` and fill in your own database details (host, user, password, database name).
5. Open `http://localhost/forces-academy-lms/` in your browser.

The real `config/db.php` is not in the repo (it is in `.gitignore`) so the database password stays private. That is what `db.example.php` is for.

---

Built by Farhad Ali — Code Saviours SI-26, 2026
