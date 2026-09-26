<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

try {
    $pdo->query('SELECT 1');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'ok', 'site' => 'inforova']);
} catch (Throwable $e) {
    error_log('INFOROVA health check failed: ' . $e->getMessage());
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error']);
}
