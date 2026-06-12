<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
$product = marketplace_product_by_id($id);

if ($product === null) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    $currentPage = 'marketplace';
    $useMarketplaceCss = true;
    require_once __DIR__ . '/includes/navbar.php';
    echo '<div class="container py-5 text-center"><h1>Product not found</h1><a href="' . e(url(PRODUCTS_PATH)) . '" class="btn btn-marketplace">Back to marketplace</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $product['name'];
$currentPage = 'marketplace';
$useMarketplaceCss = true;
$imgUrl = product_image_url($product['image_path'] ?? null);

require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= e(url(INDEX_PATH)) ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= e(url(PRODUCTS_PATH)) ?>">Marketplace</a></li>
            <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-6">
            <img src="<?= e($imgUrl) ?>" alt="<?= e($product['name']) ?>" class="product-detail-img shadow-sm"
                 onerror="this.src='<?= e(url(PRODUCT_FALLBACK_IMAGE)) ?>'">
        </div>
        <div class="col-lg-6">
            <span class="badge bg-warning text-dark mb-2"><?= e($product['category'] ?? 'Other') ?></span>
            <h1 class="h2 fw-bold text-success"><?= e($product['name']) ?></h1>
            <p class="display-6 text-success fw-bold mb-3">KES <?= e(number_format((float) $product['price'], 2)) ?> <span class="fs-6 text-muted fw-normal">per kg</span></p>

            <ul class="list-unstyled mb-4">
                <li class="mb-2"><i class="bi bi-box-seam text-success"></i> <strong>Available:</strong> <?= e((string) $product['stock_qty']) ?> kg</li>
                <li class="mb-2"><i class="bi bi-geo-alt text-success"></i> <strong>Location:</strong> <?= e($product['display_location'] ?? '') ?></li>
                <li class="mb-2"><i class="bi bi-person text-success"></i> <strong>Seller:</strong> <?= e($product['farmer_name']) ?></li>
                <li class="mb-2"><i class="bi bi-calendar3 text-success"></i> <strong>Listed:</strong> <?= e(date('d M Y', strtotime((string) $product['created_at']))) ?></li>
            </ul>

            <div class="card border-0 bg-light mb-4">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Description</h2>
                    <p class="mb-0 text-muted"><?= nl2br(e($product['description'] ?? 'No description provided.')) ?></p>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php render_order_now_button((int) $product['id'], $product, 'btn-lg px-4'); ?>
                <?php render_contact_seller_button((int) $product['id'], 'btn-lg px-4'); ?>
                <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="btn btn-outline-success">← Back to marketplace</a>
            </div>
            <?php if (!is_logged_in()): ?>
                <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle"></i> <a href="<?= e(order_place_url((int) $product['id'])) ?>">Log in as a customer</a> to place an order or contact this seller.</p>
            <?php elseif (current_role() !== ROLE_CUSTOMER): ?>
                <p class="small text-muted mt-3 mb-0">Use a customer account to place orders or send inquiries.</p>
            <?php else: ?>
                <p class="small text-muted mt-3 mb-0">Orders start as <strong>Pending</strong> until the farmer accepts. Track them under <strong>My Orders</strong>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
