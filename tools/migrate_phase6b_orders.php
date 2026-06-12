<?php
/**
 * Phase 6B: update orders.status enum and workflow.
 * Run: php tools/migrate_phase6b_orders.php
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!orders_table_exists()) {
    fwrite(STDERR, "orders table not found. Run php tools/migrate_phase6_orders.php first.\n");
    exit(1);
}

$sqlFile = dirname(__DIR__) . '/database/migrations/phase6b_order_workflow.sql';
$raw = file_get_contents($sqlFile);
if ($raw === false) {
    fwrite(STDERR, "Could not read migration file.\n");
    exit(1);
}

$statements = array_filter(
    array_map('trim', preg_split('/;\s*\n/', $raw)),
    static fn (string $s): bool => $s !== '' && !preg_match('/^USE\s+/i', $s)
);

try {
    foreach ($statements as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
    echo "Success: order status workflow updated (pending, accepted, rejected, delivered).\n";
    echo "Stock is now reduced only when a farmer accepts an order.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}
