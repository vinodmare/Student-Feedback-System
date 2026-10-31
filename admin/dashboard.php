<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$counts = [
    'students' => $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
    'faculty' => $pdo->query('SELECT COUNT(*) FROM faculty')->fetchColumn(),
    'subjects' => $pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn(),
    'feedback' => $pdo->query('SELECT COUNT(*) FROM feedback')->fetchColumn(),
];
$overall = $pdo->query('SELECT ROUND(AVG(rating), 2) AS avg_rating, COUNT(*) AS total FROM feedback')->fetch();
$recent = $pdo->query(
    'SELECT f.rating, f.comments, f.created_at, u.name AS student, fac.full_name AS faculty, s.subject_name
     FROM feedback f
     JOIN students st ON st.id = f.student_id
     JOIN users u ON u.id = st.user_id
     JOIN faculty_subjects fs ON fs.id = f.faculty_subject_id
     JOIN faculty fac ON fac.id = fs.faculty_id
     JOIN subjects s ON s.id = fs.subject_id
     ORDER BY f.created_at DESC LIMIT 5'
)->fetchAll();

$title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-hero mb-4">
    <div>
        <span class="admin-kicker">Administration</span>
        <h1>Academic Feedback Control Center</h1>
        <p>Manage users, classes, subjects, assignments, and feedback analytics from one place.</p>
    </div>
    <div class="admin-hero-panel">
        <div class="small text-uppercase">Overall Rating</div>
        <strong><?= e((string)($overall['avg_rating'] ?? '0')) ?>/5</strong>
        <span><?= (int)($overall['total'] ?? 0) ?> feedback responses</span>
    </div>
</div>
<div class="row g-3 mb-4">
    <?php foreach ($counts as $label => $count): ?>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-icon"><i class="bi bi-<?= $label === 'students' ? 'people' : ($label === 'faculty' ? 'person-badge' : ($label === 'subjects' ? 'journal-bookmark' : 'chat-square-text')) ?>"></i></div>
                    <div class="text-muted text-uppercase small"><?= e($label) ?></div>
                    <div class="display-6 fw-bold"><?= (int) $count ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="admin-actions mb-4">
    <a href="<?= BASE_URL ?>/admin/students.php"><i class="bi bi-person-plus"></i><span>Add Students</span></a>
    <a href="<?= BASE_URL ?>/admin/faculty.php"><i class="bi bi-person-workspace"></i><span>Manage Faculty</span></a>
    <a href="<?= BASE_URL ?>/admin/assignments.php"><i class="bi bi-diagram-3"></i><span>Assign Subjects</span></a>
    <a href="<?= BASE_URL ?>/admin/reports.php"><i class="bi bi-bar-chart-line"></i><span>View Reports</span></a>
</div>
<div class="card">
    <div class="card-header bg-white fw-semibold">Recent Feedback</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Student</th><th>Faculty</th><th>Subject</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $row): ?>
                <tr>
                    <td><?= e($row['student']) ?></td>
                    <td><?= e($row['faculty']) ?></td>
                    <td><?= e($row['subject_name']) ?></td>
                    <td><span class="badge text-bg-primary"><?= e((string) $row['rating']) ?>/5</span></td>
                    <td><?= e($row['comments']) ?></td>
                    <td><?= e(format_datetime($row['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="6" class="text-center text-muted py-4">No feedback submitted yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
