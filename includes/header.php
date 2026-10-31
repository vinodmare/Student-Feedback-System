<?php $user = current_user(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php if ($user): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/index.php">SFS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link <?= active_class('/admin/dashboard') ?>" href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('/admin/students') ?>" href="<?= BASE_URL ?>/admin/students.php">Students</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('/admin/faculty') ?>" href="<?= BASE_URL ?>/admin/faculty.php">Faculty</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('/admin/classes') ?>" href="<?= BASE_URL ?>/admin/classes.php">Classes</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('/admin/subjects') ?>" href="<?= BASE_URL ?>/admin/subjects.php">Subjects</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('/admin/assignments') ?>" href="<?= BASE_URL ?>/admin/assignments.php">Assignments</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('/admin/reports') ?>" href="<?= BASE_URL ?>/admin/reports.php">Reports</a></li>
                <?php elseif ($user['role'] === 'student'): ?>
                    <li class="nav-item"><a class="nav-link <?= active_class('/student/dashboard') ?>" href="<?= BASE_URL ?>/student/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('/student/feedback') ?>" href="<?= BASE_URL ?>/student/feedback.php">Feedback</a></li>
                <?php elseif ($user['role'] === 'faculty'): ?>
                    <li class="nav-item"><a class="nav-link <?= active_class('/faculty/dashboard') ?>" href="<?= BASE_URL ?>/faculty/dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text me-3"><?= e($user['name']) ?></span>
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container py-4">
<?php foreach (flashes() as $message): ?>
    <div class="alert alert-<?= e($message['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($message['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endforeach; ?>

