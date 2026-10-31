<?php
require_once __DIR__ . '/../includes/auth.php';
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action');
    $id = int_post('id');
    $name = post('name');
    $email = strtolower(post('email'));
    $roll = post('roll_no');
    $classId = int_post('class_id');
    $password = post('password');

    try {
        if ($action === 'delete' && $id) {
            $userId = $pdo->prepare('SELECT user_id FROM students WHERE id = ?');
            $userId->execute([$id]);
            $uid = $userId->fetchColumn();
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
            flash('success', 'Student deleted.');
        } elseif ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $roll && $classId) {
            if ($action === 'create') {
                $hash = password_hash($password ?: 'password', PASSWORD_DEFAULT);
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')")
                    ->execute([$name, $email, $hash]);
                $uid = (int) $pdo->lastInsertId();
                $pdo->prepare('INSERT INTO students (user_id, class_id, roll_no, full_name, phone) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$uid, $classId, $roll, $name, post('phone')]);
                $pdo->commit();
                flash('success', 'Student added.');
            } elseif ($action === 'update' && $id) {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT user_id FROM students WHERE id = ?');
                $stmt->execute([$id]);
                $uid = (int) $stmt->fetchColumn();
                $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $uid]);
                if ($password !== '') {
                    $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $uid]);
                }
                $pdo->prepare('UPDATE students SET class_id = ?, roll_no = ?, full_name = ?, phone = ? WHERE id = ?')
                    ->execute([$classId, $roll, $name, post('phone'), $id]);
                $pdo->commit();
                flash('success', 'Student updated.');
            }
        } else {
            flash('danger', 'Please fill all required fields correctly.');
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash('danger', 'Operation failed. Email or roll number may already exist.');
    }
    redirect('/admin/students.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT st.*, u.email FROM students st JOIN users u ON u.id = st.user_id WHERE st.id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$classes = $pdo->query('SELECT * FROM classes ORDER BY class_name, semester')->fetchAll();
$students = $pdo->query('SELECT st.*, u.email, c.class_name, c.semester FROM students st JOIN users u ON u.id = st.user_id JOIN classes c ON c.id = st.class_id ORDER BY st.full_name')->fetchAll();
$title = 'Manage Students';
require_once __DIR__ . '/../includes/header.php';
?>
<h1 class="h3 mb-4">Students</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h2 class="h5"><?= $edit ? 'Edit Student' : 'Add Student' ?></h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= e((string) ($edit['id'] ?? '')) ?>">
                <div class="mb-2"><label class="form-label">Full Name</label><input class="form-control" name="name" required value="<?= e($edit['full_name'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label">Roll No</label><input class="form-control" name="roll_no" required value="<?= e($edit['roll_no'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label">Class</label><select class="form-select" name="class_id" required><option value="">Select</option><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= (int)($edit['class_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['class_name'] . ' - Sem ' . $c['semester']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-2"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($edit['phone'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Password <?= $edit ? '(blank to keep)' : '' ?></label><input class="form-control" type="password" name="password" <?= $edit ? '' : 'required' ?>></div>
                <button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Add' ?> Student</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Roll</th><th>Class</th><th></th></tr></thead>
            <tbody><?php foreach ($students as $s): ?><tr><td><?= e($s['full_name']) ?></td><td><?= e($s['email']) ?></td><td><?= e($s['roll_no']) ?></td><td><?= e($s['class_name'] . ' Sem ' . $s['semester']) ?></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= $s['id'] ?>">Edit</a><form class="d-inline" method="post" data-confirm="Delete this student?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr><?php endforeach; ?></tbody>
        </table></div></div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

