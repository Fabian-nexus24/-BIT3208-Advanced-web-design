<?php
declare(strict_types=1);

/**
 * User data access (customers, farmers, admin stats).
 */

function current_user_id(): int
{
    $auth = auth_user();
    return (int) ($auth['id'] ?? 0);
}

function fetch_customer_by_id(int $id): ?array
{
    global $pdo;
    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, phone, delivery_address, status, created_at, updated_at
         FROM customers WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

function fetch_farmer_by_id(int $id): ?array
{
    global $pdo;

    try {
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, phone, farm_name, county, farming_location,
                    profile_image, status, created_at, updated_at
             FROM farmers WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    } catch (PDOException $e) {
        if (!str_contains($e->getMessage(), 'Unknown column')) {
            throw $e;
        }
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, phone, farm_name, county, status, created_at, updated_at
             FROM farmers WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }
}

function email_exists_for_role(string $email, string $role, ?int $excludeId = null): bool
{
    global $pdo;

    $table = match ($role) {
        ROLE_FARMER   => 'farmers',
        ROLE_CUSTOMER => 'customers',
        default       => null,
    };
    if ($table === null) {
        return false;
    }

    $sql = "SELECT id FROM {$table} WHERE email = ?";
    $params = [$email];
    if ($excludeId !== null) {
        $sql .= ' AND id != ?';
        $params[] = $excludeId;
    }
    $sql .= ' LIMIT 1';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

function update_session_name(string $name): void
{
    if (isset($_SESSION['auth'])) {
        $_SESSION['auth']['name'] = $name;
    }
}

function admin_dashboard_stats(): array
{
    global $pdo;

    $farmers = (int) $pdo->query('SELECT COUNT(*) FROM farmers')->fetchColumn();
    $customers = (int) $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    $products = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

    $recent = $pdo->query(
        "SELECT 'farmer' AS user_type, id, full_name AS name, email, created_at
         FROM farmers
         UNION ALL
         SELECT 'customer', id, full_name, email, created_at
         FROM customers
         ORDER BY created_at DESC
         LIMIT 8"
    )->fetchAll();

    return [
        'farmers'   => $farmers,
        'customers' => $customers,
        'products'  => $products,
        'recent'    => $recent,
    ];
}

/**
 * @return array{ok: bool, error?: string}
 */
function change_user_password(string $role, int $userId, string $current, string $new, string $confirm): array
{
    global $pdo;

    if (strlen($new) < MIN_PASSWORD_LENGTH) {
        return ['ok' => false, 'error' => 'New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.'];
    }
    if ($new !== $confirm) {
        return ['ok' => false, 'error' => 'New passwords do not match.'];
    }

    $table = match ($role) {
        ROLE_FARMER   => 'farmers',
        ROLE_CUSTOMER => 'customers',
        ROLE_ADMIN    => 'admins',
        default       => null,
    };
    if ($table === null) {
        return ['ok' => false, 'error' => 'Invalid role.'];
    }

    $stmt = $pdo->prepare("SELECT password_hash FROM {$table} WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if ($row === false || !password_verify($current, (string) $row['password_hash'])) {
        return ['ok' => false, 'error' => 'Current password is incorrect.'];
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE {$table} SET password_hash = ? WHERE id = ?");
    $update->execute([$hash, $userId]);

    return ['ok' => true];
}
