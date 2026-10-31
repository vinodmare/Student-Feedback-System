<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $posted = $_POST['csrf_token'] ?? '';
    if (!$posted || !hash_equals($_SESSION['csrf_token'] ?? '', $posted)) {
        http_response_code(419);
        exit('Invalid security token. Please go back and try again.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function int_post(string $key): int
{
    return max(0, (int) ($_POST[$key] ?? 0));
}

function format_datetime(?string $value, string $format = 'd M Y, h:i A'): string
{
    if (!$value) {
        return '-';
    }

    try {
        return (new DateTime($value))->format($format);
    } catch (Throwable $e) {
        return $value;
    }
}

function active_class(string $needle): string
{
    return strpos($_SERVER['SCRIPT_NAME'], $needle) !== false ? 'active' : '';
}

function ensure_demo_admin(PDO $pdo): void
{
    $exists = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ((int) $exists === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(['System Admin', 'admin@sfs.test', $hash, 'admin', 'active']);
        $userId = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO admin (user_id, full_name, phone) VALUES (?, ?, ?)')
            ->execute([$userId, 'System Admin', '9999999999']);
    }
}
