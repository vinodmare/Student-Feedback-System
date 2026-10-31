<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

$fromDate = trim((string)($_GET['from_date'] ?? ''));
$toDate = trim((string)($_GET['to_date'] ?? ''));
$where = '';
$params = [];

if ($fromDate !== '') {
    $where .= ' AND f.created_at >= ?';
    $params[] = $fromDate . ' 00:00:00';
}

if ($toDate !== '') {
    $where .= ' AND f.created_at <= ?';
    $params[] = $toDate . ' 23:59:59';
}

$stmt = $pdo->prepare(
    'SELECT fac.full_name AS faculty, s.subject_code, s.subject_name, c.class_name, c.semester,
            ROUND(AVG(f.rating), 2) AS average_rating, COUNT(f.id) AS responses
     FROM feedback f
     JOIN faculty_subjects fs ON fs.id = f.faculty_subject_id
     JOIN faculty fac ON fac.id = fs.faculty_id
     JOIN subjects s ON s.id = fs.subject_id
     JOIN classes c ON c.id = s.class_id
     WHERE 1=1' . $where . '
     GROUP BY fac.id, s.id, c.id
     ORDER BY fac.full_name, s.subject_name'
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=student_feedback_report_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['Generated At', date('d M Y, h:i A')]);
fputcsv($out, ['From Date', $fromDate ?: 'All']);
fputcsv($out, ['To Date', $toDate ?: 'All']);
fputcsv($out, []);
fputcsv($out, ['Faculty', 'Subject Code', 'Subject Name', 'Class', 'Semester', 'Average Rating', 'Responses']);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['faculty'],
        $row['subject_code'],
        $row['subject_name'],
        $row['class_name'],
        $row['semester'],
        $row['average_rating'],
        $row['responses'],
    ]);
}

fclose($out);
exit;

