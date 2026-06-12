<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_farmer();
deny_cross_role_access();

$farmerId = current_user_id();
$farmer = fetch_farmer_by_id($farmerId);
$analytics = farmer_dashboard_analytics($farmerId);

$pageTitle = 'Farmer Dashboard';
dashboard_shell_start(ROLE_FARMER, 'dashboard', $pageTitle);

dashboard_welcome_banner(
    (string) ($farmer['full_name'] ?? 'Farmer'),
    'Manage listings, orders, and customer inquiries from one place.'
);
?>

<div class="row g-3 mb-4">
    <?= stat_card('Total sales', 'KES ' . number_format($analytics['total_sales'], 0), 'bi-currency-exchange', 'success') ?>
    <?= stat_card('Total orders', (string) $analytics['total_orders'], 'bi-cart-check', 'primary') ?>
    <?= stat_card('Pending orders', (string) $analytics['pending_orders'], 'bi-hourglass-split', 'warning') ?>
    <?= stat_card('Delivered', (string) $analytics['completed_orders'], 'bi-truck', 'info') ?>
    <?= stat_card('My products', (string) $analytics['total_products'], 'bi-basket', 'success') ?>
    <?= stat_card('New inquiries', (string) $analytics['new_inquiries'], 'bi-chat-dots', 'dark') ?>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card quick-action-card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 fw-semibold">Quick actions</div>
            <div class="card-body">
                <a href="<?= e(url('farmer/add_product.php')) ?>" class="btn btn-marketplace me-2 mb-2">Add product</a>
                <a href="<?= e(url('farmer/manage_products.php')) ?>" class="btn btn-outline-success me-2 mb-2">My products</a>
                <a href="<?= e(url('farmer/orders.php')) ?>" class="btn btn-outline-primary me-2 mb-2">Customer orders</a>
                <a href="<?= e(url('farmer/inquiries.php')) ?>" class="btn btn-outline-warning me-2 mb-2">Inquiries</a>
                <a href="<?= e(url('farmer/notifications.php')) ?>" class="btn btn-outline-secondary mb-2">Notifications</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">Marketplace tips</div>
            <div class="card-body">
                <p class="text-muted mb-0">Accept orders promptly to confirm sales. Stock is reduced when you accept an order. Customers are notified of status changes.</p>
            </div>
        </div>
    </div>
</div>

<?php dashboard_shell_end(); ?>
