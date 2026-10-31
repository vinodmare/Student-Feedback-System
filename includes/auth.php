<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (isset($_SESSION['last_activity']) && time() - (int) $_SESSION['last_activity'] > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    session_start();
    flash('warning', 'Your session expired. Please login again.');
    redirect('/login.php');
}

$_SESSION['last_activity'] = time();

function require_login(?string $role = null): void
{
    if (empty($_SESSION['user'])) {
        redirect('/login.php');
    }

    if ($role !== null && ($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];
    $_SESSION['last_activity'] = time();
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
    }
    session_destroy();
}

