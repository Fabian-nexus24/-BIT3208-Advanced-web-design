<?php
require_once __DIR__ . '/_bootstrap.php';

$farmer_id = $_GET['id'] ?? null;

if (!$farmer_id) {
    header('Location: farmers.php');
    exit();
}

// Delete farmer (cascade delete will remove products due to foreign key)
$stmt = $pdo->prepare("DELETE FROM farmers WHERE id = ?");
if ($stmt->execute([$farmer_id])) {
    header('Location: farmers.php?message=Farmer deleted successfully');
} else {
    header('Location: farmers.php?message=Failed to delete farmer');
}
exit();
?>
