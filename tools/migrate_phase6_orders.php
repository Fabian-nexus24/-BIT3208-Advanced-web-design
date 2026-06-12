<?php
/**
 * One-time migration: create orders table for Phase 6 order placement.
 *
 * Run from project root:
 *   php tools/migrate_phase6_orders.php
 *
 * Uses credentials from config/db.php (database: farmconnect_kenya).
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/db.php';

$sqlFile = dirname(__DIR__) . '/database/migrations/phase6_orders.sql';

if (!is_file($sqlFile)) {
    fwrite(STDERR, "Migration file not found: {$sqlFile}\n");
    exit(1);
}

$raw = file_get_contents($sqlFile);
if ($raw === false) {
    fwrite(STDERR, "Could not read migration file.\n");
    exit(1);
}

// Strip USE database; PDO is already connected to DB_NAME
$statements = array_filter(
    array_map('trim', preg_split('/;\s*\n/', $raw)),
    static fn (string $s): bool => $s !== '' && !preg_match('/^USE\s+/i', $s)
);

try {
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
    }

    $check = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($check === false || $check->rowCount() === 0) {
        fwrite(STDERR, "Migration ran but orders table was not found. Check MySQL errors.\n");
        exit(1);
    }

    echo "Success: orders table is ready in database '" . DB_NAME . "'.\n";
    echo "You can place orders from the marketplace (customer login required).\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Ensure farmconnect_kenya exists and schema.sql (Phases 1–5) was imported first.\n");
    exit(1);
}
