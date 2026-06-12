<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$productId = (int) ($_GET['id'] ?? 0);
$farmerId = current_user_id();
$product = farmer_product_by_id($productId, $farmerId);

if ($product === null) {
    flash_set('danger', 'Product not found.');
    redirect('farmer/manage_products.php');
}

$errors = [];
$old = [
    'name'        => $product['name'],
    'category'    => $product['category'] ?? 'Other',
    'description' => $product['description'] ?? '',
    'location'    => $product['location'] ?? '',
    'price'       => (string) $product['price'],
    'stock_qty'   => (string) $product['stock_qty'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $old = [
            'name'        => trim((string) ($_POST['name'] ?? '')),
            'category'    => trim((string) ($_POST['category'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'location'    => trim((string) ($_POST['location'] ?? '')),
            'price'       => trim((string) ($_POST['price'] ?? '')),
            'stock_qty'   => trim((string) ($_POST['stock_qty'] ?? '')),
        ];
        $validated = validate_product_input($old);
        $errors = $validated['errors'];

        if ($errors === []) {
            $result = update_product($productId, $farmerId, $validated['data'], $_FILES['product_image'] ?? null);
            if ($result['ok']) {
                flash_set('success', 'Product updated successfully.');
                redirect('farmer/manage_products.php');
            }
            $errors[] = $result['error'] ?? 'Could not update product.';
        }
    }
}

$pageTitle = 'Edit Product';
dashboard_shell_start(ROLE_FARMER, 'products', $pageTitle);
$imgUrl = product_image_url($product['image_path'] ?? null);
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="mb-3 text-center">
                    <img src="<?= e($imgUrl) ?>" alt="" class="rounded" style="max-height:180px;object-fit:cover;" onerror="this.src='<?= e(url(PRODUCT_FALLBACK_IMAGE)) ?>'">
                </div>
                <?php if ($errors !== []): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Product name</label>
                        <input type="text" name="name" class="form-control" required value="<?= e($old['name']) ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <?php foreach (PRODUCT_CATEGORIES as $cat): ?>
                                    <option value="<?= e($cat) ?>" <?= $old['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Farming location</label>
                            <input type="text" name="location" class="form-control" required value="<?= e($old['location']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?= e($old['description']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price per kg (KES)</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0.01" required value="<?= e($old['price']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity (kg)</label>
                            <input type="number" name="stock_qty" class="form-control" min="1" required value="<?= e($old['stock_qty']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Replace image (optional)</label>
                        <input type="file" name="product_image" class="form-control" accept="image/jpeg,image/png,image/jpg">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Save changes</button>
                        <a href="<?= e(url('farmer/manage_products.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php dashboard_shell_end(); ?>
