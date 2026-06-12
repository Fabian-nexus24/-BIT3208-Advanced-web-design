<?php
declare(strict_types=1);

/**
 * Authentication, session management, and access guards.
 */

function login_user(string $role, array $row): void
{
    session_regenerate_id(true);

    $_SESSION['auth'] = [
        'id'    => (int) $row['id'],
        'role'  => $role,
        'name'  => (string) $row['full_name'],
        'email' => (string) $row['email'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            (bool) $params['secure'],
            (bool) $params['httponly']
        );
    }

    session_destroy();
}

function auth_user(): ?array
{
    return $_SESSION['auth'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['auth']['role']);
}

function current_role(): ?string
{
    return $_SESSION['auth']['role'] ?? null;
}

function dashboard_path_for_role(string $role): string
{
    $paths = DASHBOARD_PATHS;
    if (!isset($paths[$role])) {
        return INDEX_PATH;
    }
    return $paths[$role];
}

function redirect_to_dashboard(?string $role = null): never
{
    $role = $role ?? current_role();
    if ($role === null) {
        redirect(LOGIN_PATH);
    }
    redirect(dashboard_path_for_role($role));
}

function require_guest(): void
{
    if (is_logged_in()) {
        redirect_to_dashboard();
    }
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash_set('warning', 'Please log in to continue.');
        redirect(LOGIN_PATH);
    }
}

function require_role(string ...$roles): void
{
    require_login();

    $role = current_role();
    if ($role === null || !in_array($role, $roles, true)) {
        flash_set('danger', 'You do not have permission to access that page.');
        redirect_to_dashboard($role);
    }
}

/**
 * Unified login: admin → farmer → customer (email lookup order).
 *
 * @return array{ok: bool, error?: string, role?: string}
 */
function authenticate_user(string $email, string $password): array
{
    global $pdo;

    $tables = [
        ROLE_ADMIN    => 'admins',
        ROLE_FARMER   => 'farmers',
        ROLE_CUSTOMER => 'customers',
    ];

    foreach ($tables as $role => $table) {
        $stmt = $pdo->prepare(
            "SELECT id, full_name, email, password_hash, status
             FROM {$table}
             WHERE email = ? AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row === false) {
            continue;
        }

        if (!password_verify($password, (string) $row['password_hash'])) {
            return ['ok' => false, 'error' => 'Invalid email or password.'];
        }

        login_user($role, $row);
        return ['ok' => true, 'role' => $role];
    }

    return ['ok' => false, 'error' => 'Invalid email or password.'];
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

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && $token !== '' && hash_equals(csrf_token(), $token);
}
