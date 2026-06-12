<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_customer();
deny_cross_role_access();

$auth = auth_user();
$customerId = current_user_id();
$customer = fetch_customer_by_id($customerId);
$analytics = customer_dashboard_analytics($customerId);

$pageTitle = 'Customer Dashboard';
dashboard_shell_start(ROLE_CUSTOMER, 'dashboard', $pageTitle);

dashboard_welcome_banner(
    (string) ($auth['name'] ?? 'Customer'),
    'Browse fresh produce, place orders, and track your purchases.'
);
?>

<div class="row g-3 mb-4">
    <?= stat_card('Total orders', (string) $analytics['total_orders'], 'bi-cart', 'primary') ?>
    <?= stat_card('Pending orders', (string) $analytics['pending_orders'], 'bi-hourglass-split', 'warning') ?>
    <?= stat_card('Account', ucfirst((string) ($customer['status'] ?? 'active')), 'bi-person-check', 'success') ?>
    <?= stat_card('Member since', date('M Y', strtotime((string) ($customer['created_at'] ?? 'now'))), 'bi-calendar3', 'info') ?>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card quick-action-card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 fw-semibold">Quick actions</div>
            <div class="card-body">
                <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="btn btn-marketplace me-2 mb-2">Browse marketplace</a>
                <a href="<?= e(url('customer/orders.php')) ?>" class="btn btn-outline-primary me-2 mb-2">My orders</a>
                <a href="<?= e(url('customer/inquiries.php')) ?>" class="btn btn-outline-success me-2 mb-2">My inquiries</a>
                <a href="<?= e(url('customer/notifications.php')) ?>" class="btn btn-outline-secondary mb-2">Notifications</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">Recent purchases</div>
            <div class="list-group list-group-flush">
                <?php if ($analytics['recent_purchases'] === []): ?>
                    <div class="list-group-item text-muted small">No orders yet. Start shopping on the marketplace.</div>
                <?php else: ?>
                    <?php foreach ($analytics['recent_purchases'] as $purchase): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="small fw-semibold"><?= e($purchase['product_name']) ?></span>
                                <span class="badge <?= e(order_status_badge((string) $purchase['status'])) ?>"><?= e(order_status_label((string) $purchase['status'])) ?></span>
                            </div>
                            <p class="small text-muted mb-0">
                                KES <?= e(number_format((float) $purchase['total_price'], 2)) ?>
                                · <?= e(date('d M Y', strtotime((string) $purchase['created_at']))) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php dashboard_shell_end(); ?>
