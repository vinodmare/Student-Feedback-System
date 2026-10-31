CREATE DATABASE IF NOT EXISTS student_feedback_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_feedback_system;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS feedback_scores;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS feedback_criteria;
DROP TABLE IF EXISTS faculty_subjects;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS faculty;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student','faculty') NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE admin (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_class_sem_year (class_name, semester, academic_year),
    INDEX idx_classes_semester (semester),
    CHECK (semester BETWEEN 1 AND 10)
) ENGINE=InnoDB;

CREATE TABLE students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    class_id INT UNSIGNED NOT NULL,
    roll_no VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    INDEX idx_students_class (class_id)
) ENGINE=InnoDB;

CREATE TABLE faculty (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    employee_no VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    department VARCHAR(120),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_faculty_department (department)
) ENGINE=InnoDB;

CREATE TABLE subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id INT UNSIGNED NOT NULL,
    subject_code VARCHAR(40) NOT NULL UNIQUE,
    subject_name VARCHAR(140) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    INDEX idx_subjects_class (class_id)
) ENGINE=InnoDB;

CREATE TABLE faculty_subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE RESTRICT,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_faculty_subject (faculty_id, subject_id),
    INDEX idx_faculty_subjects_faculty (faculty_id),
    INDEX idx_faculty_subjects_subject (subject_id)
) ENGINE=InnoDB;

CREATE TABLE feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    faculty_subject_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_subject_id) REFERENCES faculty_subjects(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_student_feedback_once (student_id, faculty_subject_id),
    INDEX idx_feedback_rating (rating),
    INDEX idx_feedback_created (created_at),
    CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE feedback_criteria (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    criteria_name VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255),
    display_order TINYINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    INDEX idx_feedback_criteria_status (status),
    INDEX idx_feedback_criteria_order (display_order)
) ENGINE=InnoDB;

CREATE TABLE feedback_scores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feedback_id INT UNSIGNED NOT NULL,
    criteria_id INT UNSIGNED NOT NULL,
    score TINYINT UNSIGNED NOT NULL,
    FOREIGN KEY (feedback_id) REFERENCES feedback(id) ON DELETE CASCADE,
    FOREIGN KEY (criteria_id) REFERENCES feedback_criteria(id) ON DELETE RESTRICT,
    UNIQUE KEY uq_feedback_criteria_score (feedback_id, criteria_id),
    INDEX idx_feedback_scores_criteria (criteria_id),
    CHECK (score BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- Sample password for all demo users: password
INSERT INTO users (id, name, email, password, role, status) VALUES
(1, 'System Admin', 'admin@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
(2, 'Asha Kumar', 'student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(3, 'Rahul Sharma', 'faculty@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'active'),
(4, 'Priya Nair', 'priya.faculty@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'active'),
(5, 'Vikram Rao', 'vikram.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(6, 'Neha Patel', 'neha.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(7, 'Arjun Mehta', 'arjun.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(8, 'Sara Thomas', 'sara.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(9, 'Kiran Das', 'kiran.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(10, 'Meera Iyer', 'meera.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(11, 'Farhan Ali', 'farhan.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(12, 'Sneha Reddy', 'sneha.student@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(13, 'Anil Verma', 'anil.faculty@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'active'),
(14, 'Lakshmi Menon', 'lakshmi.faculty@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'active'),
(15, 'Suresh Babu', 'suresh.faculty@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'active'),
(16, 'Isha Kapoor', 'isha.faculty@sfs.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'active');

INSERT INTO admin (user_id, full_name, phone) VALUES
(1, 'System Admin', '9999999999');

INSERT INTO classes (id, class_name, semester, academic_year) VALUES
(1, 'MCA', 2, '2025-2026'),
(2, 'MCA', 4, '2025-2026'),
(3, 'MCA', 1, '2025-2026'),
(4, 'BCA', 3, '2025-2026'),
(5, 'BSc Computer Science', 5, '2025-2026');

INSERT INTO students (id, user_id, class_id, roll_no, full_name, phone) VALUES
(1, 2, 1, 'MCA2026001', 'Asha Kumar', '9000000001'),
(2, 5, 1, 'MCA2026002', 'Vikram Rao', '9000000002'),
(3, 6, 1, 'MCA2026003', 'Neha Patel', '9000000003'),
(4, 7, 2, 'MCA2024001', 'Arjun Mehta', '9000000004'),
(5, 8, 2, 'MCA2024002', 'Sara Thomas', '9000000005'),
(6, 9, 3, 'MCA2027001', 'Kiran Das', '9000000006'),
(7, 10, 4, 'BCA2025001', 'Meera Iyer', '9000000007'),
(8, 11, 4, 'BCA2025002', 'Farhan Ali', '9000000008'),
(9, 12, 5, 'BSC2023001', 'Sneha Reddy', '9000000009');

INSERT INTO faculty (id, user_id, employee_no, full_name, department, phone) VALUES
(1, 3, 'FAC001', 'Rahul Sharma', 'Computer Applications', '9000000101'),
(2, 4, 'FAC002', 'Priya Nair', 'Computer Applications', '9000000102'),
(3, 13, 'FAC003', 'Anil Verma', 'Computer Applications', '9000000103'),
(4, 14, 'FAC004', 'Lakshmi Menon', 'Information Technology', '9000000104'),
(5, 15, 'FAC005', 'Suresh Babu', 'Computer Science', '9000000105'),
(6, 16, 'FAC006', 'Isha Kapoor', 'Data Science', '9000000106');

INSERT INTO subjects (id, class_id, subject_code, subject_name) VALUES
(1, 1, 'MCA201', 'Database Management Systems'),
(2, 1, 'MCA202', 'Web Technologies'),
(3, 2, 'MCA401', 'Cloud Computing'),
(4, 2, 'MCA402', 'Machine Learning'),
(5, 2, 'MCA403', 'Software Project Management'),
(6, 3, 'MCA101', 'Programming in C'),
(7, 3, 'MCA102', 'Computer Organization'),
(8, 4, 'BCA301', 'Object Oriented Programming'),
(9, 4, 'BCA302', 'Operating Systems'),
(10, 5, 'BSC501', 'Data Analytics'),
(11, 5, 'BSC502', 'Cyber Security'),
(12, 1, 'MCA203', 'Data Structures');

INSERT INTO faculty_subjects (id, faculty_id, subject_id) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 1, 3),
(4, 6, 4),
(5, 3, 5),
(6, 5, 6),
(7, 1, 7),
(8, 2, 8),
(9, 4, 9),
(10, 6, 10),
(11, 3, 11),
(12, 5, 12);

INSERT INTO feedback_criteria (id, criteria_name, description, display_order, status) VALUES
(1, 'Teaching Clarity', 'Ability to explain concepts clearly with suitable examples.', 1, 'active'),
(2, 'Subject Knowledge', 'Depth of knowledge and confidence in the subject.', 2, 'active'),
(3, 'Punctuality', 'Regularity, time management, and syllabus coverage.', 3, 'active'),
(4, 'Student Interaction', 'Encourages questions, discussion, and participation.', 4, 'active'),
(5, 'Assessment Fairness', 'Assignments, tests, and evaluation are fair and useful.', 5, 'active');

INSERT INTO feedback (id, student_id, faculty_subject_id, rating, comments) VALUES
(1, 1, 1, 5, 'Clear teaching and useful examples.'),
(2, 1, 2, 4, 'Good sessions, more lab practice would help.'),
(3, 1, 12, 5, 'Data structure problems are explained step by step.'),
(4, 2, 1, 4, 'Concepts are explained well.'),
(5, 2, 2, 5, 'The web technology lab sessions are very practical.'),
(6, 2, 12, 4, 'Assignments are useful and improve coding confidence.'),
(7, 3, 1, 5, 'Database normalization was taught very clearly.'),
(8, 3, 2, 4, 'More examples on JavaScript would be helpful.'),
(9, 3, 12, 5, 'Excellent problem-solving approach in class.'),
(10, 4, 3, 4, 'Cloud deployment topics were easy to follow.'),
(11, 4, 4, 5, 'Machine learning examples were interesting and current.'),
(12, 4, 5, 3, 'Project planning content is useful but needs more case studies.'),
(13, 5, 3, 5, 'Great explanation of cloud architecture.'),
(14, 5, 4, 4, 'Algorithms are explained with good visual examples.'),
(15, 5, 5, 4, 'Helpful for final semester project preparation.'),
(16, 6, 6, 4, 'Programming basics are covered patiently.'),
(17, 6, 7, 3, 'Computer organization is good, but pace is a little fast.'),
(18, 7, 8, 5, 'OOP concepts are very clear with Java examples.'),
(19, 7, 9, 4, 'Operating system scheduling examples were helpful.'),
(20, 8, 8, 4, 'Good classroom interaction and practical explanations.'),
(21, 8, 9, 5, 'Excellent explanation of process management.'),
(22, 9, 10, 5, 'Data visualization sessions were excellent.'),
(23, 9, 11, 4, 'Cyber security topics are relevant and engaging.');

INSERT INTO feedback_scores (feedback_id, criteria_id, score)
SELECT
    f.id,
    c.id,
    CASE
        WHEN c.id = 1 THEN f.rating
        WHEN c.id = 2 THEN LEAST(5, f.rating + IF(MOD(f.id, 3) = 0, 1, 0))
        WHEN c.id = 3 THEN GREATEST(1, f.rating - IF(MOD(f.id, 4) = 0, 1, 0))
        WHEN c.id = 4 THEN LEAST(5, f.rating + IF(MOD(f.id, 2) = 0, 1, 0))
        ELSE GREATEST(1, f.rating - IF(MOD(f.id, 5) = 0, 1, 0))
    END
FROM feedback f
CROSS JOIN feedback_criteria c;
