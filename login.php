<?php
require_once __DIR__ . '/includes/auth.php';
ensure_demo_admin($pdo);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(post('email'));
    $password = post('password');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            redirect('/index.php');
        }
        $error = 'Invalid email or password.';
    }
}

$title = 'Login - ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<section class="login-shell">
    <div class="login-hero">
        <div class="login-badge"><i class="bi bi-mortarboard-fill"></i> MCA Project</div>
        <h1>Student Feedback System</h1>
        <p>Collect faculty feedback, analyze performance, and manage academic quality through one secure portal.</p>
        <div class="login-metrics">
            <div><strong>3</strong><span>Role Portals</span></div>
            <div><strong>5</strong><span>Point Rating</span></div>
            <div><strong>24/7</strong><span>Access</span></div>
        </div>
    </div>
    <div class="login-panel">
        <?php if (current_user()): ?>
        <div class="card login-card shadow-sm">
            <div class="card-body p-4 text-center">
                <h2 class="h5">You are already logged in</h2>
                <p class="text-muted">
                    Current account: <?= e(current_user()['name']) ?> (<?= e(current_user()['role']) ?>)
                </p>
                <div class="d-grid gap-2">
                    <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php">Continue to Dashboard</a>
                    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/logout.php">Logout and Switch Account</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card login-card shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <p class="text-uppercase text-primary fw-semibold small mb-1">Secure Login</p>
                    <h2 class="h4 fw-bold mb-1">Choose your portal</h2>
                    <p class="text-muted mb-0">Select a role, then sign in with your account.</p>
                </div>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="role-picker mb-4" aria-label="Select login role">
                        <input type="radio" class="btn-check" name="login_role" id="roleStudent" value="student" data-email="student@sfs.test" checked>
                        <label class="role-option" for="roleStudent">
                            <i class="bi bi-person-workspace"></i>
                            <span>Student</span>
                        </label>

                        <input type="radio" class="btn-check" name="login_role" id="roleAdmin" value="admin" data-email="admin@sfs.test">
                        <label class="role-option" for="roleAdmin">
                            <i class="bi bi-person-gear"></i>
                            <span>Admin</span>
                        </label>

                        <input type="radio" class="btn-check" name="login_role" id="roleFaculty" value="faculty" data-email="faculty@sfs.test">
                        <label class="role-option" for="roleFaculty">
                            <i class="bi bi-easel2"></i>
                            <span>Faculty</span>
                        </label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="loginEmail" class="form-control form-control-lg" value="student@sfs.test" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" value="password" required>
                    </div>
                    <button class="btn btn-primary btn-lg w-100" type="submit"><i class="bi bi-shield-lock"></i> Login</button>
                </form>
                <div class="login-help mt-3">
                    Demo credentials are filled automatically when you select a role.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
