<?php
declare(strict_types=1);

/**
 * Load optional environment overrides from config/env.local.php
 *
 * @return array<string, mixed>
 */
function app_env(): array
{
    static $env = null;
    if ($env !== null) {
        return $env;
    }

    $path = dirname(__DIR__) . '/config/env.local.php';
    $env = is_file($path) ? (require $path) : [];
    if (!is_array($env)) {
        $env = [];
    }
    return $env;
}

function env_string(string $key, string $default = ''): string
{
    $val = app_env()[$key] ?? $default;
    return is_string($val) ? $val : $default;
}
