<?php
declare(strict_types=1);

define('APP_NAME', 'Student Feedback System');
define('BASE_URL', '/student_feedback_system');
define('SESSION_TIMEOUT', 1800);

date_default_timezone_set('Asia/Kolkata');

$dbHost = 'localhost';
$dbName = 'student_feedback_system';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $pdo->exec("SET time_zone = '+05:30'");
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check config/database.php and import database/feedback.sql.');
}
