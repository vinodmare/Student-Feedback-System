# Student Feedback System

A production-ready Core PHP and MySQL web application for collecting student feedback about faculty and subjects. It includes secure authentication, role-based dashboards, CRUD screens, feedback submission, analytics reports, Chart.js charts, sample data, and academic documentation.

## Technology Stack

- PHP 8.x with PDO
- MySQL / MariaDB
- HTML5, CSS3, JavaScript
- Bootstrap 5, Bootstrap Icons, Chart.js
- XAMPP / WAMP / LAMP compatible

## Folder Structure

```text
student_feedback_system/
├── admin/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
├── database/
│   └── feedback.sql
├── docs/
│   └── diagrams.md
├── faculty/
├── includes/
├── student/
├── index.php
├── login.php
├── logout.php
└── README.md
```

## Installation Guide

1. Copy the `student_feedback_system` folder into your web server root:
   - XAMPP: `htdocs/student_feedback_system`
   - WAMP: `www/student_feedback_system`
   - LAMP: `/var/www/html/student_feedback_system`

2. Start Apache and MySQL from XAMPP/WAMP/LAMP.

3. Open phpMyAdmin and import:

   ```text
   database/feedback.sql
   ```

   The script creates the `student_feedback_system` database, all tables, constraints, indexes, and sample data.

4. Update database credentials if needed in:

   ```text
   config/database.php
   ```

5. Open the app:

   ```text
   http://localhost/student_feedback_system
   ```

## Demo Login Credentials

All seeded users use the password `password`.

| Role | Email |
| --- | --- |
| Admin | admin@sfs.test |
| Student | student@sfs.test |
| Faculty | faculty@sfs.test |

When new users are created through the admin screens, passwords are hashed using PHP `password_hash()`.

## Main Features

- Secure login with password hashing
- Session timeout and session regeneration on login
- Role-based access control for Admin, Student, and Faculty
- CSRF protection for form submissions
- SQL injection prevention using PDO prepared statements
- Admin CRUD for students, faculty, classes, subjects, and faculty-subject assignments
- Student subject list and criteria-based feedback submission
- Faculty dashboard with average ratings, criteria-wise analysis, and comments
- Admin reports for faculty, subject, semester, criteria, and overall performance
- CSV export and print-friendly report output
- Chart.js visual analytics
- Responsive Bootstrap 5 user interface

## Database Schema Summary

- `users`: shared login table with role and hashed password
- `admin`: admin profile linked to `users`
- `students`: student profile linked to `users` and `classes`
- `faculty`: faculty profile linked to `users`
- `classes`: class, semester, and academic year records
- `subjects`: subjects linked to classes
- `faculty_subjects`: faculty-to-subject assignment table
- `feedback`: student feedback, rating, and comments
- `feedback_criteria`: configurable academic evaluation parameters
- `feedback_scores`: criterion-wise scores linked to each feedback entry

Foreign keys protect relationships, unique constraints prevent duplicate users/roll numbers/assignments, and rating uses a `1` to `5` check constraint.

## MCA-Level Enhancements

- Multi-criteria feedback model instead of a single-rating-only design.
- Overall rating is automatically calculated from detailed criterion scores.
- Criteria include teaching clarity, subject knowledge, punctuality, student interaction, and assessment fairness.
- Admin reports include faculty, subject, semester, overall, and criteria-wise analytics.
- Reports support date-range filtering, print view, and CSV export.
- Faculty dashboard includes criteria-wise performance insights and student comments.
- Database design is normalized using separate `feedback_criteria` and `feedback_scores` tables.

## User Manual

### Admin

1. Login with the admin credentials.
2. Add classes first, then subjects.
3. Add faculty and students.
4. Assign faculty to subjects from the Assignments page.
5. Open Reports to view performance summaries and charts.

### Student

1. Login with a student account.
2. Review assigned subjects on the dashboard.
3. Open Feedback, score each academic criterion from 1 to 5, add comments, and submit.
4. A student can update previously submitted feedback for the same subject-faculty assignment.

### Faculty

1. Login with a faculty account.
2. View average rating, response count, subject-wise results, and student comments.

## Security Notes

- Keep `display_errors` disabled in production.
- Change demo passwords immediately after deployment.
- Use HTTPS in production so session cookies and login data are encrypted in transit.
- Use a dedicated MySQL user instead of `root` on production servers.
- Restrict public access to database backup files.

## Academic Documentation

The diagrams are available in:

```text
docs/diagrams.md
```

It includes:

- ER Diagram
- DFD Level 0
- DFD Level 1
- Use Case Diagram
- System Architecture Diagram
