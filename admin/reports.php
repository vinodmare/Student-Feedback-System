<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$fromDate = trim((string)($_GET['from_date'] ?? ''));
$toDate = trim((string)($_GET['to_date'] ?? ''));
$dateFilter = '';
$params = [];

if ($fromDate !== '') {
    $dateFilter .= ' AND f.created_at >= ?';
    $params[] = $fromDate . ' 00:00:00';
}

if ($toDate !== '') {
    $dateFilter .= ' AND f.created_at <= ?';
    $params[] = $toDate . ' 23:59:59';
}

$generatedAt = date('d M Y, h:i A');
if ($fromDate && $toDate) {
    $periodLabel = format_datetime($fromDate, 'd M Y') . ' to ' . format_datetime($toDate, 'd M Y');
} elseif ($fromDate) {
    $periodLabel = 'From ' . format_datetime($fromDate, 'd M Y');
} elseif ($toDate) {
    $periodLabel = 'Until ' . format_datetime($toDate, 'd M Y');
} else {
    $periodLabel = 'All dates';
}

$facultySql =
    'SELECT fac.full_name, ROUND(AVG(f.rating), 2) AS avg_rating, COUNT(f.id) AS total
     FROM faculty fac
     LEFT JOIN faculty_subjects fs ON fs.faculty_id = fac.id
     LEFT JOIN feedback f ON f.faculty_subject_id = fs.id' . $dateFilter . '
     GROUP BY fac.id, fac.full_name ORDER BY avg_rating DESC';
$stmt = $pdo->prepare($facultySql);
$stmt->execute($params);
$facultyReport = $stmt->fetchAll();

$subjectSql =
    'SELECT s.subject_name, ROUND(AVG(f.rating), 2) AS avg_rating, COUNT(f.id) AS total
     FROM subjects s
     LEFT JOIN faculty_subjects fs ON fs.subject_id = s.id
     LEFT JOIN feedback f ON f.faculty_subject_id = fs.id' . $dateFilter . '
     GROUP BY s.id, s.subject_name ORDER BY s.subject_name';
$stmt = $pdo->prepare($subjectSql);
$stmt->execute($params);
$subjectReport = $stmt->fetchAll();

$semesterSql =
    'SELECT c.semester, ROUND(AVG(f.rating), 2) AS avg_rating, COUNT(f.id) AS total
     FROM classes c
     LEFT JOIN subjects s ON s.class_id = c.id
     LEFT JOIN faculty_subjects fs ON fs.subject_id = s.id
     LEFT JOIN feedback f ON f.faculty_subject_id = fs.id' . $dateFilter . '
     GROUP BY c.semester ORDER BY c.semester';
$stmt = $pdo->prepare($semesterSql);
$stmt->execute($params);
$semesterReport = $stmt->fetchAll();

$overallSql = 'SELECT ROUND(AVG(rating), 2) AS avg_rating, COUNT(*) AS total FROM feedback WHERE 1=1';
$overallParams = [];
if ($fromDate !== '') {
    $overallSql .= ' AND created_at >= ?';
    $overallParams[] = $fromDate . ' 00:00:00';
}
if ($toDate !== '') {
    $overallSql .= ' AND created_at <= ?';
    $overallParams[] = $toDate . ' 23:59:59';
}
$stmt = $pdo->prepare($overallSql);
$stmt->execute($overallParams);
$overall = $stmt->fetch();

$criteriaSql =
    'SELECT fc.criteria_name, ROUND(AVG(fs.score), 2) AS avg_score, COUNT(fs.id) AS total
     FROM feedback_criteria fc
     JOIN feedback_scores fs ON fs.criteria_id = fc.id
     JOIN feedback f ON f.id = fs.feedback_id
     WHERE 1=1';
$criteriaParams = [];
if ($fromDate !== '') {
    $criteriaSql .= ' AND f.created_at >= ?';
    $criteriaParams[] = $fromDate . ' 00:00:00';
}
if ($toDate !== '') {
    $criteriaSql .= ' AND f.created_at <= ?';
    $criteriaParams[] = $toDate . ' 23:59:59';
}
$criteriaSql .= ' GROUP BY fc.id, fc.criteria_name, fc.display_order ORDER BY fc.display_order';
$stmt = $pdo->prepare($criteriaSql);
$stmt->execute($criteriaParams);
$criteriaReport = $stmt->fetchAll();

$exportUrl = BASE_URL . '/admin/export_report.php?' . http_build_query([
    'from_date' => $fromDate,
    'to_date' => $toDate,
]);

$title = 'Reports';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="report-header mb-4">
    <div>
        <span class="admin-kicker">Analytics</span>
        <h1>Feedback Reports</h1>
        <p>Generated on <?= e($generatedAt) ?> | Period: <?= e($periodLabel) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-light" href="<?= e($exportUrl) ?>"><i class="bi bi-filetype-csv"></i> Export CSV</a>
        <button class="btn btn-outline-light" onclick="window.print()"><i class="bi bi-printer"></i> Print Report</button>
    </div>
</div>
<div class="card mb-4 report-filter">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="get">
            <div class="col-md-4">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="from_date" value="<?= e($fromDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="to_date" value="<?= e($toDate) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-funnel"></i> Apply Filter</button>
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/admin/reports.php">Reset</a>
            </div>
        </form>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-icon"><i class="bi bi-star-fill"></i></div><div class="text-muted small text-uppercase">Overall Rating</div><div class="display-6 fw-bold"><?= e((string)($overall['avg_rating'] ?? '0')) ?>/5</div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-icon"><i class="bi bi-chat-square-text"></i></div><div class="text-muted small text-uppercase">Feedback Entries</div><div class="display-6 fw-bold"><?= (int)($overall['total'] ?? 0) ?></div></div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="card-body"><div class="stat-icon"><i class="bi bi-calendar-check"></i></div><div class="text-muted small text-uppercase">Generated</div><div class="h4 fw-bold mb-0"><?= e(date('d M Y')) ?></div><div class="text-muted small"><?= e(date('h:i A')) ?></div></div></div></div>
</div>
<div class="row g-4">
    <div class="col-lg-6"><div class="card report-card"><div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge"></i> Faculty Performance</div><div class="card-body"><canvas id="facultyChart"></canvas></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Faculty</th><th>Average</th><th>Responses</th></tr></thead><tbody><?php foreach ($facultyReport as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><span class="rating-pill"><?= e((string)($r['avg_rating'] ?? '0')) ?></span></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
    <div class="col-lg-6"><div class="card report-card"><div class="card-header bg-white fw-semibold"><i class="bi bi-journal-bookmark"></i> Subject-wise Report</div><div class="card-body"><canvas id="subjectChart"></canvas></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Subject</th><th>Average</th><th>Responses</th></tr></thead><tbody><?php foreach ($subjectReport as $r): ?><tr><td><?= e($r['subject_name']) ?></td><td><span class="rating-pill"><?= e((string)($r['avg_rating'] ?? '0')) ?></span></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
    <div class="col-lg-12"><div class="card report-card"><div class="card-header bg-white fw-semibold"><i class="bi bi-clipboard-data"></i> Criteria-wise Analysis</div><div class="card-body"><canvas id="criteriaChart"></canvas></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Evaluation Criteria</th><th>Average Score</th><th>Responses</th></tr></thead><tbody><?php foreach ($criteriaReport as $r): ?><tr><td><?= e($r['criteria_name']) ?></td><td><span class="rating-pill"><?= e((string)($r['avg_score'] ?? '0')) ?></span></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
    <div class="col-lg-12"><div class="card report-card"><div class="card-header bg-white fw-semibold"><i class="bi bi-calendar3"></i> Semester-wise Report</div><div class="card-body"><canvas id="semesterChart"></canvas></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Semester</th><th>Average</th><th>Responses</th></tr></thead><tbody><?php foreach ($semesterReport as $r): ?><tr><td>Semester <?= e((string)$r['semester']) ?></td><td><span class="rating-pill"><?= e((string)($r['avg_rating'] ?? '0')) ?></span></td><td><?= (int)$r['total'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
</div>
<script>
window.sfsReports = {
    faculty: <?= json_encode($facultyReport) ?>,
    subjects: <?= json_encode($subjectReport) ?>,
    semesters: <?= json_encode($semesterReport) ?>,
    criteria: <?= json_encode($criteriaReport) ?>
};
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
