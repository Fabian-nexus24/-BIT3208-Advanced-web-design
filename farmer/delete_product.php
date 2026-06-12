<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash_set('danger', 'Invalid delete request.');
    redirect('farmer/manage_products.php');
}

$productId = (int) ($_POST['id'] ?? 0);
$result = delete_product($productId, current_user_id());

if ($result['ok']) {
    flash_set('success', 'Product deleted.');
} else {
    flash_set('danger', $result['error'] ?? 'Could not delete product.');
}

redirect('farmer/manage_products.php');
