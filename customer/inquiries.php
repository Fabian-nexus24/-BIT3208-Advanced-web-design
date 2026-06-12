<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_customer();
deny_cross_role_access();

$page = pagination_page_from_request();
$total = customer_inquiries_count(current_user_id());
$meta = pagination_meta($total, $page, PER_PAGE_INQUIRIES);
$inquiries = customer_inquiries_list(current_user_id(), $meta['per_page'], $meta['offset']);
$pageTitle = 'My Inquiries';

dashboard_shell_start(ROLE_CUSTOMER, 'inquiries', $pageTitle);
?>

<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <p class="text-muted mb-0"><?= e((string) $total) ?> inquiry(ies) sent</p>
    <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="btn btn-marketplace btn-sm">Browse marketplace</a>
</div>

<?php if ($inquiries === []): ?>
    <div class="card border-0 shadow-sm">
        <?php render_empty_state(
            'bi-chat-dots',
            'No inquiries sent',
            'Browse the marketplace and use Contact Seller on products you like.',
            'Browse marketplace',
            PRODUCTS_PATH
        ); ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($inquiries as $inq): ?>
            <div class="col-12">
                <article class="card inquiry-card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <?php if (!empty($inq['product_name'])): ?>
                                    <h3 class="h6 mb-1">
                                        <a href="<?= e(url('product_details.php?id=' . (int) $inq['product_id'])) ?>" class="text-decoration-none">
                                            <?= e($inq['product_name']) ?>
                                        </a>
                                    </h3>
                                <?php endif; ?>
                                <p class="small text-muted mb-0">To: <strong><?= e($inq['farmer_name']) ?></strong></p>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= e(inquiry_status_badge((string) $inq['status'])) ?>"><?= e(ucfirst((string) $inq['status'])) ?></span>
                                <p class="small text-muted mb-0 mt-1"><?= e(date('d M Y, H:i', strtotime((string) $inq['created_at']))) ?></p>
                            </div>
                        </div>
                        <p class="inquiry-message-body mb-0"><?= nl2br(e($inq['message'])) ?></p>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
    <?php render_pagination($total, $meta['page'], $meta['per_page'], 'customer/inquiries.php'); ?>
<?php endif; ?>

<?php dashboard_shell_end(); ?>
