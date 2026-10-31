<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (post('action') === 'delete' && int_post('id')) {
            $pdo->prepare('DELETE FROM faculty_subjects WHERE id = ?')->execute([int_post('id')]);
            flash('success', 'Assignment deleted.');
        } elseif (int_post('faculty_id') && int_post('subject_id')) {
            $pdo->prepare('INSERT INTO faculty_subjects (faculty_id, subject_id) VALUES (?, ?)')
                ->execute([int_post('faculty_id'), int_post('subject_id')]);
            flash('success', 'Faculty assigned to subject.');
        }
    } catch (Throwable $e) {
        flash('danger', 'Operation failed. This assignment may already exist or have feedback.');
    }
    redirect('/admin/assignments.php');
}

$faculty = $pdo->query('SELECT * FROM faculty ORDER BY full_name')->fetchAll();
$subjects = $pdo->query('SELECT s.*, c.class_name, c.semester FROM subjects s JOIN classes c ON c.id = s.class_id ORDER BY c.class_name, s.subject_name')->fetchAll();
$assignments = $pdo->query('SELECT fs.id, f.full_name, s.subject_code, s.subject_name, c.class_name, c.semester FROM faculty_subjects fs JOIN faculty f ON f.id = fs.faculty_id JOIN subjects s ON s.id = fs.subject_id JOIN classes c ON c.id = s.class_id ORDER BY f.full_name')->fetchAll();
$title = 'Faculty Assignments';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Assign Faculty to Subjects</h1>
<div class="row g-4">
    <div class="col-lg-4"><div class="card"><div class="card-body">
        <form method="post"><?= csrf_field() ?>
            <div class="mb-3"><label class="form-label">Faculty</label><select class="form-select" name="faculty_id" required><option value="">Select</option><?php foreach ($faculty as $f): ?><option value="<?= $f['id'] ?>"><?= e($f['full_name']) ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label">Subject</label><select class="form-select" name="subject_id" required><option value="">Select</option><?php foreach ($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['subject_code'] . ' - ' . $s['subject_name'] . ' (' . $s['class_name'] . ' Sem ' . $s['semester'] . ')') ?></option><?php endforeach; ?></select></div>
            <button class="btn btn-primary w-100">Assign</button>
        </form>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Faculty</th><th>Subject</th><th>Class</th><th></th></tr></thead><tbody>
        <?php foreach ($assignments as $a): ?><tr><td><?= e($a['full_name']) ?></td><td><?= e($a['subject_code'] . ' - ' . $a['subject_name']) ?></td><td><?= e($a['class_name'] . ' Sem ' . $a['semester']) ?></td><td class="text-end"><form method="post" data-confirm="Delete this assignment?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

