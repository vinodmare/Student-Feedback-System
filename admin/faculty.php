<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    $id = int_post('id');
    $name = post('name');
    $email = strtolower(post('email'));
    try {
        if ($action === 'delete' && $id) {
            $stmt = $pdo->prepare('SELECT user_id FROM faculty WHERE id = ?');
            $stmt->execute([$id]);
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([(int) $stmt->fetchColumn()]);
            flash('success', 'Faculty deleted.');
        } elseif ($name && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($action === 'create') {
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'faculty', 'active')")
                    ->execute([$name, $email, password_hash(post('password') ?: 'password', PASSWORD_DEFAULT)]);
                $uid = (int) $pdo->lastInsertId();
                $pdo->prepare('INSERT INTO faculty (user_id, employee_no, full_name, department, phone) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$uid, post('employee_no'), $name, post('department'), post('phone')]);
                $pdo->commit();
                flash('success', 'Faculty added.');
            } elseif ($action === 'update' && $id) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT user_id FROM faculty WHERE id = ?');
                $stmt->execute([$id]);
                $uid = (int) $stmt->fetchColumn();
                $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $uid]);
                if (post('password') !== '') {
                    $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash(post('password'), PASSWORD_DEFAULT), $uid]);
                }
                $pdo->prepare('UPDATE faculty SET employee_no = ?, full_name = ?, department = ?, phone = ? WHERE id = ?')
                    ->execute([post('employee_no'), $name, post('department'), post('phone'), $id]);
                $pdo->commit();
                flash('success', 'Faculty updated.');
            }
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('danger', 'Operation failed. Email or employee number may already exist.');
    }
    redirect('/admin/faculty.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT f.*, u.email FROM faculty f JOIN users u ON u.id = f.user_id WHERE f.id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$faculty = $pdo->query('SELECT f.*, u.email FROM faculty f JOIN users u ON u.id = f.user_id ORDER BY f.full_name')->fetchAll();
$title = 'Manage Faculty';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Faculty</h1>
<div class="row g-4">
    <div class="col-lg-4"><div class="card"><div class="card-body">
        <h2 class="h5"><?= $edit ? 'Edit Faculty' : 'Add Faculty' ?></h2>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>"><input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
            <div class="mb-2"><label class="form-label">Full Name</label><input class="form-control" name="name" required value="<?= e($edit['full_name'] ?? '') ?>"></div>
            <div class="mb-2"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>"></div>
            <div class="mb-2"><label class="form-label">Employee No</label><input class="form-control" name="employee_no" required value="<?= e($edit['employee_no'] ?? '') ?>"></div>
            <div class="mb-2"><label class="form-label">Department</label><input class="form-control" name="department" value="<?= e($edit['department'] ?? '') ?>"></div>
            <div class="mb-2"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($edit['phone'] ?? '') ?>"></div>
            <div class="mb-3"><label class="form-label">Password <?= $edit ? '(blank to keep)' : '' ?></label><input class="form-control" type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
            <button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Add' ?> Faculty</button>
        </form>
    </div></div></div>
    <div class="col-lg-8"><div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Name</th><th>Email</th><th>Employee No</th><th>Department</th><th></th></tr></thead><tbody>
        <?php foreach ($faculty as $f): ?><tr><td><?= e($f['full_name']) ?></td><td><?= e($f['email']) ?></td><td><?= e($f['employee_no']) ?></td><td><?= e($f['department']) ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= $f['id'] ?>">Edit</a><form class="d-inline" method="post" data-confirm="Delete this faculty?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $f['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

