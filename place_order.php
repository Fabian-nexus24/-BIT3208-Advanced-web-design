<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$productId = (int) ($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$product = marketplace_product_by_id($productId);

if ($product === null) {
    flash_set('danger', 'Product not found.');
    redirect(PRODUCTS_PATH);
}

if (!is_logged_in()) {
    $dest = PLACE_ORDER_PATH . '?product_id=' . $productId;
    flash_set('info', 'Please log in as a customer to place an order.');
    redirect(LOGIN_PATH . '?redirect=' . rawurlencode($dest));
}

if (current_role() !== ROLE_CUSTOMER) {
    flash_set('warning', 'Only customer accounts can place orders.');
    redirect_to_dashboard();
}

$customerId = current_user_id();
$errors = [];
$quantity = (string) ($_POST['quantity'] ?? '1');
$deliveryNotes = trim((string) ($_POST['delivery_notes'] ?? ''));
$paymentMethod = (string) ($_POST['payment_method'] ?? ORDER_PAYMENT_COD);
$unitPrice = (float) $product['price'];
$stockQty = (int) $product['stock_qty'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $result = create_order($customerId, $productId, [
            'quantity'        => (int) $quantity,
            'delivery_notes'  => $deliveryNotes,
            'payment_method'  => $paymentMethod,
        ]);

        if ($result['ok']) {
            $total = number_format((float) ($result['total_price'] ?? 0), 2);
            flash_set(
                'success',
                'Order placed successfully! Total: KES ' . $total . '. Status: Pending — waiting for farmer acceptance.'
            );
            redirect('customer/orders.php');
        }

        $errors[] = $result['error'] ?? 'Could not place order.';
    }
}

$pageTitle = 'Place Order';
$currentPage = 'marketplace';
$useMarketplaceCss = true;
$imgUrl = product_image_url($product['image_path'] ?? null);

require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= e(url(PRODUCTS_PATH)) ?>">Marketplace</a></li>
            <li class="breadcrumb-item"><a href="<?= e(url('product_details.php?id=' . $productId)) ?>"><?= e($product['name']) ?></a></li>
            <li class="breadcrumb-item active">Place order</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm inquiry-product-summary">
                <img src="<?= e($imgUrl) ?>" class="card-img-top" alt="" style="height:200px;object-fit:cover;"
                     onerror="this.src='<?= e(url(PRODUCT_FALLBACK_IMAGE)) ?>'">
                <div class="card-body">
                    <h1 class="h5 fw-bold"><?= e($product['name']) ?></h1>
                    <p class="text-success fw-bold mb-1">KES <?= e(number_format($unitPrice, 2)) ?> / kg</p>
                    <p class="small text-muted mb-1"><i class="bi bi-box-seam"></i> <?= e((string) $stockQty) ?> kg available</p>
                    <p class="small text-muted mb-1"><i class="bi bi-person"></i> <?= e($product['farmer_name']) ?></p>
                    <p class="small text-muted mb-0"><i class="bi bi-geo-alt"></i> <?= e($product['display_location'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h2 class="h5 mb-0"><i class="bi bi-cart-check"></i> Place your order</h2>
                </div>
                <div class="card-body p-4">
                    <?= flash_render() ?>

                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($stockQty <= 0): ?>
                        <div class="alert alert-warning mb-0">This product is currently out of stock.</div>
                    <?php else: ?>
                        <form method="post" novalidate id="orderForm">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $productId ?>">

                            <div class="mb-3">
                                <label for="quantity" class="form-label fw-semibold">Quantity (kg)</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required
                                       min="1" max="<?= e((string) $stockQty) ?>" step="1"
                                       value="<?= e($quantity) ?>">
                                <div class="form-text">Maximum available: <?= e((string) $stockQty) ?> kg</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estimated total</label>
                                <p class="h5 text-success mb-0" id="orderTotal">
                                    KES <?= e(number_format($unitPrice * max(1, (int) $quantity), 2)) ?>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label for="delivery_notes" class="form-label fw-semibold">Delivery notes <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="3"
                                          maxlength="<?= ORDER_NOTES_MAX_LENGTH ?>"
                                          placeholder="Delivery address, preferred time, or special instructions..."><?= e($deliveryNotes) ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="payment_method" class="form-label fw-semibold">Payment method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="<?= e(ORDER_PAYMENT_COD) ?>" <?= $paymentMethod === ORDER_PAYMENT_COD ? 'selected' : '' ?>>
                                        Cash on Delivery
                                    </option>
                                    <option value="<?= e(ORDER_PAYMENT_MPESA) ?>" disabled>M-Pesa (Coming Soon)</option>
                                </select>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-marketplace px-4">Confirm order</button>
                                <a href="<?= e(url('product_details.php?id=' . $productId)) ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($stockQty > 0): ?>
<script>
(function () {
    const qty = document.getElementById('quantity');
    const total = document.getElementById('orderTotal');
    const unitPrice = <?= json_encode($unitPrice, JSON_THROW_ON_ERROR) ?>;
    if (!qty || !total) return;
    const update = function () {
        const n = Math.max(1, parseInt(qty.value, 10) || 1);
        total.textContent = 'KES ' + (unitPrice * n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    qty.addEventListener('input', update);
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
