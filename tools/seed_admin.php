<?php
/**
 * One-time admin password seeder.
 * Run from CLI: php tools/seed_admin.php
 * Sets admin password to Admin@123 (use after import if login fails).
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/db.php';

$email = 'admin@farmconnect.co.ke';
$hash = password_hash('Admin@123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO admins (full_name, email, password_hash, status)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), status = ?'
);
$stmt->execute([
    'System Administrator',
    $email,
    $hash,
    'active',
    'active',
]);

echo "Admin seeded: {$email} / Admin@123\n";
