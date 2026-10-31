# Student Feedback System - Core PHP & MySQL

### Take a look at this Project
Repository: https://github.com/vinodmare/Student-Feedback-System

### It is a Core PHP and MySQL Web Application. Built using PDO, Bootstrap 5 and Chart.js.
## It is an Online Student Feedback Collection and Analytics System.

### Application Features:
- It shows the working example of how a college feedback system works, where students give feedback about faculty and subjects.
- Where a Student can login, view assigned subjects, submit criteria-based feedback and update it later.
- Where Faculty can login, view average rating, response count, criteria-wise analysis and student comments.
- Where Admin can manage students, faculty, classes, subjects, faculty-subject assignments and view reports.

### Main Features:
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

### Technology used in this Project:
- i) PHP 8.x with PDO: all backend logic has been written in Core PHP.
- ii) HTML5, CSS3 and Bootstrap 5: designing page layout and responsive UI.
- iii) JavaScript and Chart.js: front end logic and visual analytics charts.
- iv) MySQL / MariaDB: used as the database.
- v) Apache: project runs over Apache server (XAMPP / WAMP / LAMP).
- vi) Bootstrap Icons: icons used across the interface.

### Software And Tools Required:
- PHP 8.x
- XAMPP / WAMP / LAMP (Apache + MySQL)
- phpMyAdmin (comes with XAMPP / WAMP)
- Git
- VS Code (or any code editor)

### Folder Structure

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

### Steps To Clone And Run The Project

- Open Command Prompt / Terminal
- Go to your web server root folder:
  - XAMPP: `htdocs`
  - WAMP: `www`
  - LAMP: `/var/www/html`
- Clone the repository:

```bat
git clone https://github.com/vinodmare/Student-Feedback-System.git student_feedback_system
```

- Start **Apache** and **MySQL** from XAMPP / WAMP / LAMP control panel.
- Open phpMyAdmin: `http://localhost/phpmyadmin`
- Click on **Import** and select this file:

```text
database/feedback.sql
```

- Click **Go**. The script creates the `student_feedback_system` database, all tables, constraints, indexes, and sample data.
- Now open the project folder > open `config` folder > open `database.php` file.
  Now make some changes in this file if needed,
  change the database host, database name, username and password,
  like if it is:

```php
$host = 'localhost';
$db   = 'student_feedback_system';
$user = 'root';
$pass = '';
```

  change the values according to your MySQL setup.

- After doing changes save the `database.php` file.
- Now open the Website in Browser, Type Url:

```text
http://localhost/student_feedback_system
```

- Note: If your Apache runs on a different port, put the Port in the Url, e.g. `http://localhost:8080/student_feedback_system`.

### Demo Login Credentials

All seeded users use the password `password`.

| Role    | Email              | Password   |
|---------|--------------------|------------|
| Admin   | admin@sfs.test     | password   |
| Student | student@sfs.test   | password   |
| Faculty | faculty@sfs.test   | password   |

When new users are created through the admin screens, passwords are hashed using PHP `password_hash()`.

### Some Screenshots of this Project:
(Add your screenshots inside a `docs/screenshots` folder and update the file names below.)

![Login Page](docs/screenshots/login.png)
==================================================================================================================================================================
![Admin Dashboard](docs/screenshots/admin-dashboard.png)
==================================================================================================================================================================
![Manage Students / Faculty](docs/screenshots/manage-users.png)
==================================================================================================================================================================
![Assignments Page](docs/screenshots/assignments.png)
==================================================================================================================================================================
![Student Dashboard](docs/screenshots/student-dashboard.png)
==================================================================================================================================================================
![Feedback Form](docs/screenshots/feedback-form.png)
==================================================================================================================================================================
![Faculty Dashboard](docs/screenshots/faculty-dashboard.png)
==================================================================================================================================================================
![Reports and Charts](docs/screenshots/reports.png)
==================================================================================================================================================================

### Database Schema Summary

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

### MCA-Level Enhancements

- Multi-criteria feedback model instead of a single-rating-only design.
- Overall rating is automatically calculated from detailed criterion scores.
- Criteria include teaching clarity, subject knowledge, punctuality, student interaction, and assessment fairness.
- Admin reports include faculty, subject, semester, overall, and criteria-wise analytics.
- Reports support date-range filtering, print view, and CSV export.
- Faculty dashboard includes criteria-wise performance insights and student comments.
- Database design is normalized using separate `feedback_criteria` and `feedback_scores` tables.

### User Manual

#### Admin

- Login with the admin credentials.
- Add classes first, then subjects.
- Add faculty and students.
- Assign faculty to subjects from the Assignments page.
- Open Reports to view performance summaries and charts.

#### Student

- Login with a student account.
- Review assigned subjects on the dashboard.
- Open Feedback, score each academic criterion from 1 to 5, add comments, and submit.
- A student can update previously submitted feedback for the same subject-faculty assignment.

#### Faculty

- Login with a faculty account.
- View average rating, response count, subject-wise results, and student comments.

### Security Notes

- Keep `display_errors` disabled in production.
- Change demo passwords immediately after deployment.
- Use HTTPS in production so session cookies and login data are encrypted in transit.
- Use a dedicated MySQL user instead of `root` on production servers.
- Restrict public access to database backup files.

### Troubleshooting

- **Blank page or error:** make sure Apache and MySQL are both running, and the folder name is exactly `student_feedback_system`.
- **Database connection failed:** check host, username, password and database name in `config/database.php`.
- **Import error in phpMyAdmin:** make sure you are using MySQL 8+ / MariaDB 10.5+ (required for the rating check constraint) and the file size limit is not exceeded.
- **Login not working:** re-import `database/feedback.sql` to restore the demo users, then use the password `password`.

### Academic Documentation

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

### Project Creator: Vinod Mare

- GitHub: [vinodmare](https://github.com/vinodmare)
- Email: vinodmare05@gmail.com
- Repository: https://github.com/vinodmare/Student-Feedback-System
