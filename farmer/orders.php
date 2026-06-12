<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$farmerId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    $result = farmer_update_order_status($orderId, $farmerId, $action);

    if ($result['ok']) {
        $messages = [
            'accept'  => 'Order accepted. Stock has been updated.',
            'reject'  => 'Order rejected.',
            'deliver' => 'Order marked as delivered.',
        ];
        flash_set('success', $messages[$action] ?? 'Order updated.');
    } else {
        flash_set('danger', $result['error'] ?? 'Could not update order.');
    }

    redirect('farmer/orders.php?page=' . pagination_page_from_request());
}

$page = pagination_page_from_request();
$total = farmer_orders_count($farmerId);
$meta = pagination_meta($total, $page, PER_PAGE_ORDERS);
$orders = farmer_orders_list($farmerId, $meta['per_page'], $meta['offset']);
$orderStats = farmer_orders_by_status($farmerId);
$pageTitle = 'Customer Orders';

dashboard_shell_start(ROLE_FARMER, 'orders', $pageTitle);
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

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0">
        <?= count($orders) ?> on this page · <?= e((string) $total) ?> total · <?= farmer_pending_order_count($farmerId) ?> awaiting action
    </p>
</div>

<?php if ($orders === []): ?>
    <div class="card border-0 shadow-sm">
        <?php render_empty_state(
            'bi-cart-check',
            'No orders yet',
            'When customers place orders on your products, they will appear here.',
            'My products',
            'farmer/manage_products.php'
        ); ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($orders as $order): ?>
            <?php
            $status = (string) $order['status'];
            $orderId = (int) $order['id'];
            ?>
            <div class="col-12">
                <article class="card inquiry-card border-0 shadow-sm <?= $status === ORDER_STATUS_PENDING ? 'inquiry-card-new' : '' ?>">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <span class="badge <?= e(order_status_badge($status)) ?>">
                                    <?= e(order_status_label($status)) ?>
                                </span>
                                <h3 class="h6 fw-bold mt-2 mb-1">
                                    <a href="<?= e(url('product_details.php?id=' . (int) $order['product_id'])) ?>" class="text-decoration-none">
                                        <?= e($order['product_name']) ?>
                                    </a>
                                </h3>
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-person"></i> <?= e($order['customer_name']) ?>
                                    · <?= e(date('d M Y, H:i', strtotime((string) $order['created_at']))) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <p class="h5 text-success mb-0">KES <?= e(number_format((float) $order['total_price'], 2)) ?></p>
                                <p class="small text-muted mb-0"><?= e((string) $order['quantity']) ?> kg</p>
                            </div>
                        </div>

                        <div class="row g-2 small mb-3">
                            <div class="col-md-6">
                                <strong>Payment:</strong> <?= e(order_payment_label((string) $order['payment_method'])) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong> <?= e($order['customer_email'] ?? '') ?>
                            </div>
                            <?php if (!empty($order['customer_phone'])): ?>
                                <div class="col-md-6">
                                    <strong>Phone:</strong> <?= e($order['customer_phone']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($order['delivery_notes'])): ?>
                                <div class="col-12">
                                    <strong>Delivery notes:</strong>
                                    <p class="mb-0 text-muted"><?= nl2br(e($order['delivery_notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($status === ORDER_STATUS_PENDING): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-check-lg"></i> Accept Order
                                    </button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Reject this order?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-x-lg"></i> Reject Order
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($status === ORDER_STATUS_ACCEPTED): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <input type="hidden" name="action" value="deliver">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-truck"></i> Mark Delivered
                                    </button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Reject and restore stock for this order?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-x-lg"></i> Reject Order
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
    <?php render_pagination($total, $meta['page'], $meta['per_page'], 'farmer/orders.php'); ?>
<?php endif; ?>

<?php dashboard_shell_end(); ?>
