<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_customer();
deny_cross_role_access();

$customerId = current_user_id();
$page = pagination_page_from_request();
$total = customer_orders_count($customerId);
$meta = pagination_meta($total, $page, PER_PAGE_ORDERS);
$orders = customer_orders_list($customerId, $meta['per_page'], $meta['offset']);
$orderStats = customer_orders_by_status($customerId);
$pageTitle = 'My Orders';

dashboard_shell_start(ROLE_CUSTOMER, 'orders', $pageTitle);
?>

<?php if ($orders !== []): ?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge <?= e(order_status_badge(ORDER_STATUS_PENDING)) ?>">
        Pending <?= e((string) $orderStats[ORDER_STATUS_PENDING]) ?>
    </span>
    <span class="badge <?= e(order_status_badge(ORDER_STATUS_ACCEPTED)) ?>">
        Accepted <?= e((string) $orderStats[ORDER_STATUS_ACCEPTED]) ?>
    </span>
    <span class="badge <?= e(order_status_badge(ORDER_STATUS_REJECTED)) ?>">
        Rejected <?= e((string) $orderStats[ORDER_STATUS_REJECTED]) ?>
    </span>
    <span class="badge <?= e(order_status_badge(ORDER_STATUS_DELIVERED)) ?>">
        Delivered <?= e((string) $orderStats[ORDER_STATUS_DELIVERED]) ?>
    </span>
</div>
<?php endif; ?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <p class="text-muted mb-0"><?= e((string) $total) ?> order(s) placed</p>
    <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="btn btn-marketplace btn-sm">Browse marketplace</a>
</div>

<?php if ($orders === []): ?>
    <div class="card border-0 shadow-sm">
        <?php render_empty_state(
            'bi-cart',
            'No orders yet',
            'Browse the marketplace and use Order Now on products you want to buy.',
            'Browse marketplace',
            PRODUCTS_PATH
        ); ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($orders as $order): ?>
            <?php $status = (string) $order['status']; ?>
            <div class="col-12">
                <article class="card inquiry-card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <?php if (!empty($order['product_name'])): ?>
                                    <h3 class="h6 mb-1">
                                        <a href="<?= e(url('product_details.php?id=' . (int) $order['product_id'])) ?>" class="text-decoration-none fw-bold">
                                            <?= e($order['product_name']) ?>
                                        </a>
                                    </h3>
                                <?php endif; ?>
                                <p class="small text-muted mb-0">From: <strong><?= e($order['farmer_name']) ?></strong></p>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= e(order_status_badge($status)) ?>">
                                    <?= e(order_status_label($status)) ?>
                                </span>
                                <p class="small text-muted mb-0 mt-1">
                                    <?= e(date('d M Y, H:i', strtotime((string) $order['created_at']))) ?>
                                </p>
                            </div>
                        </div>

                        <div class="row g-2 small">
                            <div class="col-sm-4">
                                <strong>Quantity:</strong> <?= e((string) $order['quantity']) ?> kg
                            </div>
                            <div class="col-sm-4">
                                <strong>Total:</strong>
                                <span class="text-success fw-semibold">KES <?= e(number_format((float) $order['total_price'], 2)) ?></span>
                            </div>
                            <div class="col-sm-4">
                                <strong>Payment:</strong> <?= e(order_payment_label((string) $order['payment_method'])) ?>
                            </div>
                            <?php if (!empty($order['delivery_notes'])): ?>
                                <div class="col-12 mt-2">
                                    <strong>Your delivery notes:</strong>
                                    <p class="mb-0 text-muted"><?= nl2br(e($order['delivery_notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
    <?php render_pagination($total, $meta['page'], $meta['per_page'], 'customer/orders.php'); ?>
<?php endif; ?>

<?php dashboard_shell_end(); ?>
