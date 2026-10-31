<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    $id = int_post('id');
    try {
        if ($action === 'delete' && $id) {
            $pdo->prepare('DELETE FROM classes WHERE id = ?')->execute([$id]);
            flash('success', 'Class deleted.');
        } elseif (post('class_name') !== '' && int_post('semester') > 0) {
            if ($action === 'update' && $id) {
                $pdo->prepare('UPDATE classes SET class_name = ?, semester = ?, academic_year = ? WHERE id = ?')
                    ->execute([post('class_name'), int_post('semester'), post('academic_year'), $id]);
                flash('success', 'Class updated.');
            } else {
                $pdo->prepare('INSERT INTO classes (class_name, semester, academic_year) VALUES (?, ?, ?)')
                    ->execute([post('class_name'), int_post('semester'), post('academic_year')]);
                flash('success', 'Class added.');
            }
        }
    } catch (Throwable $e) {
        flash('danger', 'Operation failed. The class may be in use or already exists.');
    }
    redirect('/admin/classes.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM classes WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$classes = $pdo->query('SELECT * FROM classes ORDER BY class_name, semester')->fetchAll();
$title = 'Manage Classes';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Classes</h1>
<div class="row g-4">
    <div class="col-lg-4"><div class="card"><div class="card-body">
        <h2 class="h5"><?= $edit ? 'Edit Class' : 'Create Class' ?></h2>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= e((string)($edit['id'] ?? '')) ?>">
            <div class="mb-2"><label class="form-label">Class Name</label><input class="form-control" name="class_name" required value="<?= e($edit['class_name'] ?? '') ?>"></div>
            <div class="mb-2"><label class="form-label">Semester</label><input class="form-control" type="number" min="1" max="10" name="semester" required value="<?= e((string)($edit['semester'] ?? '')) ?>"></div>
            <div class="mb-3"><label class="form-label">Academic Year</label><input class="form-control" name="academic_year" placeholder="2025-2026" value="<?= e($edit['academic_year'] ?? '') ?>"></div>
            <button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Create' ?> Class</button>
        </form>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Class</th><th>Semester</th><th>Academic Year</th><th></th></tr></thead><tbody>
        <?php foreach ($classes as $c): ?><tr><td><?= e($c['class_name']) ?></td><td><?= e((string)$c['semester']) ?></td><td><?= e($c['academic_year']) ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= $c['id'] ?>">Edit</a><form class="d-inline" method="post" data-confirm="Delete this class?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

