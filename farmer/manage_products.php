<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$products = farmer_products_list(current_user_id());
$pageTitle = 'My Products';

dashboard_shell_start(ROLE_FARMER, 'products', $pageTitle);
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0"><?= count($products) ?> product(s) listed</p>
    <a href="<?= e(url('farmer/add_product.php')) ?>" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add product</a>
</div>

<?php if ($products === []): ?>
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <p class="text-muted mb-3">You have not listed any produce yet.</p>
            <a href="<?= e(url('farmer/add_product.php')) ?>" class="btn btn-marketplace">Add your first product</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <?php
            $imgUrl = product_image_url($product['image_path'] ?? null);
            $editUrl = url('farmer/edit_product.php?id=' . (int) $product['id']);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="<?= e($imgUrl) ?>" class="card-img-top" style="height:160px;object-fit:cover;" alt=""
                         onerror="this.src='<?= e(url(PRODUCT_FALLBACK_IMAGE)) ?>'">
                    <div class="card-body">
                        <span class="badge bg-warning text-dark"><?= e($product['category']) ?></span>
                        <h3 class="h6 mt-2"><?= e($product['name']) ?></h3>
                        <p class="small text-success fw-bold mb-1">KES <?= e(number_format((float) $product['price'], 2)) ?>/kg</p>
                        <p class="small text-muted mb-2"><?= e((string) $product['stock_qty']) ?> kg · <?= e($product['display_location'] ?? '') ?></p>
                        <div class="d-flex gap-2">
                            <a href="<?= e($editUrl) ?>" class="btn btn-sm btn-outline-success">Edit</a>
                            <form method="post" action="<?= e(url('farmer/delete_product.php')) ?>" class="d-inline"
                                  onsubmit="return confirm('Delete this product?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                            <a href="<?= e(url('product_details.php?id=' . (int) $product['id'])) ?>" class="btn btn-sm btn-outline-secondary ms-auto" target="_blank">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php dashboard_shell_end(); ?>
