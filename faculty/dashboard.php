<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('faculty');

$stmt = $pdo->prepare('SELECT id, full_name FROM faculty WHERE user_id = ?');
$stmt->execute([current_user()['id']]);
$faculty = $stmt->fetch();

if (!$faculty) {
    http_response_code(403);
    exit('Faculty profile not found.');
}

$summary = $pdo->prepare(
    'SELECT ROUND(AVG(f.rating), 2) AS avg_rating, COUNT(f.id) AS total
     FROM faculty_subjects fs LEFT JOIN feedback f ON f.faculty_subject_id = fs.id
     WHERE fs.faculty_id = ?'
);
$summary->execute([$faculty['id']]);
$summary = $summary->fetch();

$subjectRows = $pdo->prepare(
    'SELECT s.subject_name, s.subject_code, c.class_name, c.semester, ROUND(AVG(f.rating), 2) AS avg_rating, COUNT(f.id) AS total
     FROM faculty_subjects fs
     JOIN subjects s ON s.id = fs.subject_id
     JOIN classes c ON c.id = s.class_id
     LEFT JOIN feedback f ON f.faculty_subject_id = fs.id
     WHERE fs.faculty_id = ?
     GROUP BY fs.id, s.subject_name, s.subject_code, c.class_name, c.semester
     ORDER BY s.subject_name'
);
$subjectRows->execute([$faculty['id']]);
$subjects = $subjectRows->fetchAll();

$commentsStmt = $pdo->prepare(
    'SELECT f.rating, f.comments, f.created_at, s.subject_name
     FROM feedback f
     JOIN faculty_subjects fs ON fs.id = f.faculty_subject_id
     JOIN subjects s ON s.id = fs.subject_id
     WHERE fs.faculty_id = ? AND f.comments <> ""
     ORDER BY f.created_at DESC'
);
$commentsStmt->execute([$faculty['id']]);
$comments = $commentsStmt->fetchAll();

$criteriaStmt = $pdo->prepare(
    'SELECT fc.criteria_name, ROUND(AVG(fs.score), 2) AS avg_score
     FROM feedback_scores fs
     JOIN feedback f ON f.id = fs.feedback_id
     JOIN feedback_criteria fc ON fc.id = fs.criteria_id
     JOIN faculty_subjects fsub ON fsub.id = f.faculty_subject_id
     WHERE fsub.faculty_id = ?
     GROUP BY fc.id, fc.criteria_name, fc.display_order
     ORDER BY fc.display_order'
);
$criteriaStmt->execute([$faculty['id']]);
$criteriaBreakdown = $criteriaStmt->fetchAll();

$title = 'Faculty Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Faculty Dashboard</h1>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="text-muted small text-uppercase">Average Rating</div><div class="display-6 fw-bold"><?= e((string)($summary['avg_rating'] ?? '0')) ?>/5</div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="text-muted small text-uppercase">Responses</div><div class="display-6 fw-bold"><?= (int)($summary['total'] ?? 0) ?></div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="text-muted small text-uppercase">Assigned Subjects</div><div class="display-6 fw-bold"><?= count($subjects) ?></div></div></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Subject Results</div>
            <div class="card-body"><canvas id="facultySelfChart"></canvas></div>
            <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Subject</th><th>Class</th><th>Average</th><th>Responses</th></tr></thead><tbody>
                <?php foreach ($subjects as $s): ?><tr><td><?= e($s['subject_code'] . ' - ' . $s['subject_name']) ?></td><td><?= e($s['class_name'] . ' Sem ' . $s['semester']) ?></td><td><?= e((string)($s['avg_rating'] ?? '0')) ?></td><td><?= (int)$s['total'] ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-white fw-semibold">Criteria-wise Performance</div>
            <div class="card-body"><canvas id="facultyCriteriaChart"></canvas></div>
            <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Criteria</th><th>Average Score</th></tr></thead><tbody>
                <?php foreach ($criteriaBreakdown as $row): ?><tr><td><?= e($row['criteria_name']) ?></td><td><span class="rating-pill"><?= e((string)($row['avg_score'] ?? '0')) ?></span></td></tr><?php endforeach; ?>
                <?php if (!$criteriaBreakdown): ?><tr><td colspan="2" class="text-center text-muted py-4">No criteria scores yet.</td></tr><?php endif; ?>
            </tbody></table></div>
        </div>
        <div class="card">
            <div class="card-header bg-white fw-semibold">Student Comments</div>
            <div class="list-group list-group-flush">
                <?php foreach ($comments as $c): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between"><strong><?= e($c['subject_name']) ?></strong><span class="badge text-bg-primary"><?= (int)$c['rating'] ?>/5</span></div>
                        <p class="mb-1 mt-2"><?= e($c['comments']) ?></p>
                        <small class="text-muted"><?= e(format_datetime($c['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
                <?php if (!$comments): ?><div class="list-group-item text-muted">No comments yet.</div><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
window.sfsFacultySubjects = <?= json_encode($subjects) ?>;
window.sfsFacultyCriteria = <?= json_encode($criteriaBreakdown) ?>;
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
