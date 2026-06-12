<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$sqlFile = dirname(__DIR__) . '/database/migrations/phase7_platform.sql';
if (!is_file($sqlFile)) {
    fwrite(STDERR, "Migration file not found.\n");
    exit(1);
}

$raw = file_get_contents($sqlFile);
$statements = array_filter(
    array_map('trim', preg_split('/;\s*\n/', (string) $raw)),
    static fn (string $s): bool => $s !== '' && !preg_match('/^USE\s+/i', $s)
);

try {
    foreach ($statements as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
    echo "Success: notifications table ready.\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Migration failed: ' . $e->getMessage() . "\n");
    exit(1);
}
