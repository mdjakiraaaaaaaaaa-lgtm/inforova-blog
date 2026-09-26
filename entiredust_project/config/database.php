<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'inforova';
$user = getenv('DB_USER') ?: 'inforova_user';
$pass = getenv('DB_PASS');
$port = (int) (getenv('DB_PORT') ?: 3306);

if ($pass === false || $pass === '') {
    error_log('INFOROVA database configuration error: DB_PASS is not configured.');
    http_response_code(500);
    exit('A server configuration error occurred. Database configuration is incomplete.');
}

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    error_log('INFOROVA database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('A server error occurred. Please check the INFOROVA database configuration.');
}

require_once __DIR__ . '/../includes/schema-upgrade.php';
ensureInforovaSchema($pdo);
