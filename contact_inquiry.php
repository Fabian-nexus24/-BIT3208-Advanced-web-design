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
    $dest = 'contact_inquiry.php?product_id=' . $productId;
    flash_set('info', 'Please log in as a customer to contact this seller.');
    redirect(LOGIN_PATH . '?redirect=' . rawurlencode($dest));
}

if (current_role() !== ROLE_CUSTOMER) {
    flash_set('warning', 'Only customer accounts can send product inquiries.');
    redirect_to_dashboard();
}

$customerId = current_user_id();
$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $message = trim((string) ($_POST['message'] ?? ''));
        $result = create_inquiry($customerId, $productId, $message);

        if ($result['ok']) {
            flash_set('success', 'Your inquiry was sent to the farmer. They will see it in their dashboard.');
            redirect('customer/inquiries.php');
        }
        $errors[] = $result['error'] ?? 'Could not send inquiry.';
    }
}

$pageTitle = 'Contact Seller';
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
            <li class="breadcrumb-item active">Contact seller</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm inquiry-product-summary">
                <img src="<?= e($imgUrl) ?>" class="card-img-top" alt="" style="height:200px;object-fit:cover;"
                     onerror="this.src='<?= e(url(PRODUCT_FALLBACK_IMAGE)) ?>'">
                <div class="card-body">
                    <h1 class="h5 fw-bold"><?= e($product['name']) ?></h1>
                    <p class="text-success fw-bold mb-1">KES <?= e(number_format((float) $product['price'], 2)) ?> / kg</p>
                    <p class="small text-muted mb-1"><i class="bi bi-person"></i> <?= e($product['farmer_name']) ?></p>
                    <p class="small text-muted mb-0"><i class="bi bi-geo-alt"></i> <?= e($product['display_location'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h2 class="h5 mb-0"><i class="bi bi-chat-left-text"></i> Send inquiry to seller</h2>
                </div>
                <div class="card-body p-4">
                    <?= flash_render() ?>

                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted">Ask about availability, bulk orders, or delivery. Example: <em>Hello, I need 100kg of tomatoes. Are they available?</em></p>

                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $productId ?>">
                        <div class="mb-3">
                            <label for="message" class="form-label fw-semibold">Your message</label>
                            <textarea class="form-control inquiry-message-input" id="message" name="message" rows="6" required
                                      minlength="<?= INQUIRY_MIN_LENGTH ?>" maxlength="<?= INQUIRY_MAX_LENGTH ?>"
                                      placeholder="Write your message to the farmer..."><?= e($message) ?></textarea>
                            <div class="form-text">Minimum <?= INQUIRY_MIN_LENGTH ?> characters.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-marketplace px-4">Send inquiry</button>
                            <a href="<?= e(url('product_details.php?id=' . $productId)) ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
