<?php
require_once __DIR__ . '/_bootstrap.php';

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: products.php');
    exit();
}

// Delete product
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
if ($stmt->execute([$product_id])) {
    header('Location: products.php?message=Product deleted successfully');
} else {
    header('Location: products.php?message=Failed to delete product');
}
exit();
?>
