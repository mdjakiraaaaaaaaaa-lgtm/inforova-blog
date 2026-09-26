<?php
declare(strict_types=1);

function loadLocalEnv(string $file): void
{
    if (!is_file($file) || !is_readable($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        if ($value !== '' && (($value[0] ?? '') === '"' || ($value[0] ?? '') === "'")) {
            $quote = $value[0];
            if (substr($value, -1) === $quote) {
                $value = substr($value, 1, -1);
            }
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

loadLocalEnv(__DIR__ . '/../.env');

const APP_NAME = 'INFOROVA';
const APP_TZ = 'Asia/Dhaka';

date_default_timezone_set(APP_TZ);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0750, true);
}
ini_set('error_log', $logDir . '/php-error.log');
error_reporting(E_ALL);

function e(?string $v): string
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function app_url(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if ($host !== '' && preg_match('/^[a-z0-9.\-]+(?::[0-9]{1,5})?$/i', $host)) {
        $forwardedProto = strtolower(trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
        $scheme = $forwardedProto === 'https'
            ? 'https'
            : ((($_SERVER['HTTPS'] ?? '') === 'on') ? 'https' : 'http');

        return $scheme . '://' . $host;
    }

    $env = getenv('APP_URL');
    return $env ? rtrim($env, '/') : 'http://localhost';
}

function base_url(string $path = ''): string
{
    return app_url() . '/' . ltrim($path, '/');
}

function redirect_to(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

/*
 * Never expose PHP exception details to visitors. API endpoints receive JSON;
 * normal pages receive a generic error page. Full details go to the server log.
 */
set_exception_handler(function (Throwable $e): void {
    error_log(sprintf(
        'Unhandled %s: %s in %s:%d',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $isApi = str_contains($script, '/api/');

    http_response_code(500);

    if ($isApi) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
        }
        echo json_encode([
            'ok' => false,
            'message' => 'A temporary server error occurred. Please try again.'
        ], JSON_UNESCAPED_SLASHES);
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;max-width:640px;margin:60px auto;padding:0 20px;color:#222;line-height:1.6">'
        . '<h2 style="color:#c00">Something went wrong</h2>'
        . '<p>Please try again shortly.</p>'
        . '</body>';
});
