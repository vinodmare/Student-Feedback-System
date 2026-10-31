<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('student');

$stmt = $pdo->prepare('SELECT id, class_id, full_name FROM students WHERE user_id = ?');
$stmt->execute([current_user()['id']]);
$student = $stmt->fetch();

$subjects = [];
$submitted = 0;
if ($student) {
    $stmt = $pdo->prepare(
        'SELECT fs.id AS faculty_subject_id, s.subject_code, s.subject_name, fac.full_name AS faculty_name,
                f.id AS feedback_id, f.rating
         FROM faculty_subjects fs
         JOIN subjects s ON s.id = fs.subject_id
         JOIN faculty fac ON fac.id = fs.faculty_id
         LEFT JOIN feedback f ON f.faculty_subject_id = fs.id AND f.student_id = ?
         WHERE s.class_id = ?
         ORDER BY s.subject_name'
    );
    $stmt->execute([$student['id'], $student['class_id']]);
    $subjects = $stmt->fetchAll();
    $submitted = count(array_filter($subjects, fn($row) => !empty($row['feedback_id'])));
}

$title = 'Student Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Student Dashboard</h1>
        <p class="text-muted mb-0">Welcome, <?= e($student['full_name'] ?? current_user()['name']) ?>.</p>
    </div>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/student/feedback.php"><i class="bi bi-pencil-square"></i> Submit Feedback</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="text-muted small text-uppercase">Assigned Subjects</div><div class="display-6 fw-bold"><?= count($subjects) ?></div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="text-muted small text-uppercase">Submitted</div><div class="display-6 fw-bold"><?= $submitted ?></div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="text-muted small text-uppercase">Pending</div><div class="display-6 fw-bold"><?= max(0, count($subjects) - $submitted) ?></div></div></div></div>
</div>
<div class="card">
    <div class="card-header bg-white fw-semibold">Assigned Subjects</div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Code</th><th>Subject</th><th>Faculty</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($subjects as $s): ?><tr><td><?= e($s['subject_code']) ?></td><td><?= e($s['subject_name']) ?></td><td><?= e($s['faculty_name']) ?></td><td><?= $s['feedback_id'] ? '<span class="badge text-bg-success">Submitted</span>' : '<span class="badge text-bg-warning">Pending</span>' ?></td></tr><?php endforeach; ?>
        <?php if (!$subjects): ?><tr><td colspan="4" class="text-center text-muted py-4">No subjects assigned yet.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

