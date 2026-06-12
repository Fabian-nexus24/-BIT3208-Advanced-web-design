<?php
declare(strict_types=1);

/**
 * Marketplace product card (Bootstrap).
 *
 * @param array<string, mixed> $product
 */
function render_product_card(array $product): void
{
    $detailUrl = url('product_details.php?id=' . (int) $product['id']);
    $imgUrl = product_image_url($product['image_path'] ?? null);
    $category = (string) ($product['category'] ?? 'Produce');
    ?>
    <div class="col-6 col-md-6 col-lg-4 col-xl-3">
        <article class="card product-card h-100 border-0 shadow-sm">
            <a href="<?= e($detailUrl) ?>" class="product-card-img-wrap">
                <span class="badge bg-warning text-dark product-card-badge-float shadow-sm"><?= e($category) ?></span>
                <img src="<?= e($imgUrl) ?>" class="product-card-img" alt="<?= e($product['name']) ?>"
                     loading="lazy" onerror="this.src='<?= e(url(PRODUCT_FALLBACK_IMAGE)) ?>'">
            </a>
            <div class="card-body d-flex flex-column p-3">
                <h3 class="h6 card-title mb-2 fw-bold">
                    <a href="<?= e($detailUrl) ?>" class="text-decoration-none text-dark"><?= e($product['name']) ?></a>
                </h3>
                <p class="product-price mb-2">KES <?= e(number_format((float) $product['price'], 2)) ?> <span class="text-muted fw-normal small">/ kg</span></p>
                <ul class="list-unstyled small text-muted mb-3 flex-grow-1">
                    <li class="mb-1"><i class="bi bi-box-seam text-success"></i> <?= e((string) $product['stock_qty']) ?> kg</li>
                    <li class="mb-1"><i class="bi bi-geo-alt text-success"></i> <?= e($product['display_location'] ?? $product['location'] ?? '') ?></li>
                    <li><i class="bi bi-person text-success"></i> <?= e($product['farmer_name'] ?? 'Farmer') ?></li>
                </ul>
                <div class="d-grid gap-2 position-relative" style="z-index: 2;">
                    <a href="<?= e($detailUrl) ?>" class="btn btn-marketplace btn-sm">View Details</a>
                    <?php render_contact_seller_button((int) $product['id'], 'btn-sm btn-outline-success w-100'); ?>
                </div>
            </div>
        </article>
    </div>
    <?php
}
