<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    $id = int_post('id');
    try {
        if ($action === 'delete' && $id) {
            $pdo->prepare('DELETE FROM subjects WHERE id = ?')->execute([$id]);
            flash('success', 'Subject deleted.');
        } elseif (post('subject_code') && post('subject_name') && int_post('class_id')) {
            if ($action === 'update' && $id) {
                $pdo->prepare('UPDATE subjects SET class_id = ?, subject_code = ?, subject_name = ? WHERE id = ?')
                    ->execute([int_post('class_id'), post('subject_code'), post('subject_name'), $id]);
                flash('success', 'Subject updated.');
            } else {
                $pdo->prepare('INSERT INTO subjects (class_id, subject_code, subject_name) VALUES (?, ?, ?)')
                    ->execute([int_post('class_id'), post('subject_code'), post('subject_name')]);
                flash('success', 'Subject added.');
            }
        }
    } catch (Throwable $e) {
        flash('danger', 'Operation failed. Subject code may already exist.');
    }
    redirect('/admin/subjects.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM subjects WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$classes = $pdo->query('SELECT * FROM classes ORDER BY class_name, semester')->fetchAll();
$subjects = $pdo->query('SELECT s.*, c.class_name, c.semester FROM subjects s JOIN classes c ON c.id = s.class_id ORDER BY c.class_name, s.subject_name')->fetchAll();
$title = 'Manage Subjects';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Subjects</h1>
<div class="row g-4">
    <div class="col-lg-4"><div class="card"><div class="card-body">
        <h2 class="h5"><?= $edit ? 'Edit Subject' : 'Add Subject' ?></h2>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>">
            <div class="mb-2"><label class="form-label">Class</label><select class="form-select" name="class_id" required><option value="">Select</option><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= (int)($edit['class_id'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= e($c['class_name'] . ' - Sem ' . $c['semester']) ?></option><?php endforeach; ?></select></div>
            <div class="mb-2"><label class="form-label">Subject Code</label><input class="form-control" name="subject_code" required value="<?= e($edit['subject_code'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Subject Name</label><input class="form-control" name="subject_name" required value="<?= e($edit['subject_name'] ?? '') ?>"></div>
            <button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Add' ?> Subject</button>
        </form>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Code</th><th>Subject</th><th>Class</th><th></th></tr></thead><tbody>
        <?php foreach ($subjects as $s): ?><tr><td><?= e($s['subject_code']) ?></td><td><?= e($s['subject_name']) ?></td><td><?= e($s['class_name'] . ' Sem ' . $s['semester']) ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= $s['id'] ?>">Edit</a><form class="d-inline" method="post" data-confirm="Delete this subject?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

