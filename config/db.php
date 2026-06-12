<?php
declare(strict_types=1);

/**
 * Database connection (PDO) and session bootstrap.
 */

require_once __DIR__ . '/../includes/env.php';

$env = app_env();

define('DB_HOST', env_string('DB_HOST', (string) ($env['DB_HOST'] ?? 'localhost')));
define('DB_NAME', env_string('DB_NAME', (string) ($env['DB_NAME'] ?? 'farmconnect_kenya')));
define('DB_USER', env_string('DB_USER', (string) ($env['DB_USER'] ?? 'root')));
define('DB_PASS', env_string('DB_PASS', (string) ($env['DB_PASS'] ?? '')));
define('DB_CHARSET', 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * @return PDO
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

$pdo = db();
