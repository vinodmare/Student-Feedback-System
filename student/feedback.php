<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('student');

$stmt = $pdo->prepare('SELECT id, class_id FROM students WHERE user_id = ?');
$stmt->execute([current_user()['id']]);
$student = $stmt->fetch();

if (!$student) {
    http_response_code(403);
    exit('Student profile not found.');
}

$criteria = $pdo->query("SELECT * FROM feedback_criteria WHERE status = 'active' ORDER BY display_order, id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $facultySubjectId = int_post('faculty_subject_id');
    $scores = $_POST['scores'] ?? [];
    $comments = post('comments');

    $allowed = $pdo->prepare(
        'SELECT COUNT(*) FROM faculty_subjects fs JOIN subjects s ON s.id = fs.subject_id
         WHERE fs.id = ? AND s.class_id = ?'
    );
    $allowed->execute([$facultySubjectId, $student['class_id']]);

    $cleanScores = [];
    foreach ($criteria as $criterion) {
        $score = (int)($scores[$criterion['id']] ?? 0);
        if ($score < 1 || $score > 5) {
            $cleanScores = [];
            break;
        }
        $cleanScores[(int)$criterion['id']] = $score;
    }

    if (!$facultySubjectId || !$cleanScores || (int) $allowed->fetchColumn() === 0) {
        flash('danger', 'Please choose valid scores for all feedback criteria.');
    } else {
        try {
            $rating = (int) round(array_sum($cleanScores) / count($cleanScores));
            $pdo->beginTransaction();
            $pdo->prepare(
                'INSERT INTO feedback (student_id, faculty_subject_id, rating, comments)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comments = VALUES(comments), updated_at = CURRENT_TIMESTAMP'
            )->execute([$student['id'], $facultySubjectId, $rating, $comments]);

            $feedbackStmt = $pdo->prepare('SELECT id FROM feedback WHERE student_id = ? AND faculty_subject_id = ?');
            $feedbackStmt->execute([$student['id'], $facultySubjectId]);
            $feedbackId = (int)$feedbackStmt->fetchColumn();

            $scoreStmt = $pdo->prepare(
                'INSERT INTO feedback_scores (feedback_id, criteria_id, score)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE score = VALUES(score)'
            );
            foreach ($cleanScores as $criteriaId => $score) {
                $scoreStmt->execute([$feedbackId, $criteriaId, $score]);
            }

            $pdo->commit();
            flash('success', 'Feedback submitted successfully.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('danger', 'Unable to submit feedback.');
        }
    }
    redirect('/student/feedback.php');
}

$stmt = $pdo->prepare(
    'SELECT fs.id AS faculty_subject_id, s.subject_code, s.subject_name, fac.full_name AS faculty_name,
            f.rating, f.comments
     FROM faculty_subjects fs
     JOIN subjects s ON s.id = fs.subject_id
     JOIN faculty fac ON fac.id = fs.faculty_id
     LEFT JOIN feedback f ON f.faculty_subject_id = fs.id AND f.student_id = ?
     WHERE s.class_id = ?
     ORDER BY s.subject_name'
);
$stmt->execute([$student['id'], $student['class_id']]);
$items = $stmt->fetchAll();

$existingScores = [];
if ($items) {
    $scoreStmt = $pdo->prepare(
        'SELECT fs.feedback_id, fs.criteria_id, fs.score
         FROM feedback_scores fs
         JOIN feedback f ON f.id = fs.feedback_id
         WHERE f.student_id = ?'
    );
    $scoreStmt->execute([$student['id']]);
    foreach ($scoreStmt->fetchAll() as $scoreRow) {
        $existingScores[(int)$scoreRow['feedback_id']][(int)$scoreRow['criteria_id']] = (int)$scoreRow['score'];
    }
}

$title = 'Submit Feedback';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Submit Feedback</h1>
<div class="row g-4">
    <?php foreach ($items as $item): ?>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <h2 class="h5 mb-1"><?= e($item['subject_name']) ?></h2>
                            <p class="text-muted mb-0"><?= e($item['subject_code']) ?> | <?= e($item['faculty_name']) ?></p>
                        </div>
                        <?= $item['rating'] ? '<span class="badge text-bg-success align-self-start">Saved</span>' : '<span class="badge text-bg-secondary align-self-start">New</span>' ?>
                    </div>
                    <form method="post" class="mt-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="faculty_subject_id" value="<?= (int) $item['faculty_subject_id'] ?>">
                        <?php foreach ($criteria as $criterion): ?>
                            <?php $savedScore = $existingScores[(int)($item['feedback_id'] ?? 0)][(int)$criterion['id']] ?? 0; ?>
                            <div class="criteria-rating mb-3">
                                <div>
                                    <label class="form-label mb-1"><?= e($criterion['criteria_name']) ?></label>
                                    <div class="small text-muted"><?= e($criterion['description']) ?></div>
                                </div>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input id="r<?= $item['faculty_subject_id'] ?>_<?= $criterion['id'] ?>_<?= $i ?>" type="radio" name="scores[<?= (int)$criterion['id'] ?>]" value="<?= $i ?>" <?= $savedScore === $i ? 'checked' : '' ?> required>
                                        <label for="r<?= $item['faculty_subject_id'] ?>_<?= $criterion['id'] ?>_<?= $i ?>"><?= $i ?></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea class="form-control" name="comments" rows="3" maxlength="1000"><?= e($item['comments'] ?? '') ?></textarea>
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-send"></i> Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (!$items): ?><div class="col-12"><div class="alert alert-info">No assigned subjects are available for feedback.</div></div><?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
