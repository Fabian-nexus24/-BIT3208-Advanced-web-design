<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$farmerId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $inquiryId = (int) ($_POST['inquiry_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'read') {
        mark_inquiry_read($inquiryId, $farmerId);
        flash_set('success', 'Inquiry marked as read.');
    } elseif ($action === 'close') {
        mark_inquiry_closed($inquiryId, $farmerId);
        flash_set('success', 'Inquiry closed.');
    }
    redirect('farmer/inquiries.php?page=' . pagination_page_from_request());
}

$page = pagination_page_from_request();
$total = farmer_inquiries_count($farmerId);
$meta = pagination_meta($total, $page, PER_PAGE_INQUIRIES);
$inquiries = farmer_inquiries_list($farmerId, $meta['per_page'], $meta['offset']);
$pageTitle = 'Customer Inquiries';

dashboard_shell_start(ROLE_FARMER, 'inquiries', $pageTitle);
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0"><?= e((string) $total) ?> inquiry(ies) · <?= farmer_new_inquiry_count($farmerId) ?> new</p>
</div>

<?php if ($inquiries === []): ?>
    <div class="card border-0 shadow-sm">
        <?php render_empty_state(
            'bi-inbox',
            'No inquiries yet',
            'List products on the marketplace so customers can contact you.',
            'My products',
            'farmer/manage_products.php'
        ); ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($inquiries as $inq): ?>
            <div class="col-12">
                <article class="card inquiry-card border-0 shadow-sm <?= $inq['status'] === 'new' ? 'inquiry-card-new' : '' ?>">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <span class="badge <?= e(inquiry_status_badge((string) $inq['status'])) ?>"><?= e(ucfirst((string) $inq['status'])) ?></span>
                                <?php if (!empty($inq['product_name'])): ?>
                                    <span class="badge bg-light text-dark border ms-1"><?= e($inq['product_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted"><i class="bi bi-clock"></i> <?= e(date('d M Y, H:i', strtotime((string) $inq['created_at']))) ?></small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <h3 class="h6 text-success mb-2">Customer</h3>
                                <p class="mb-1 fw-semibold"><?= e($inq['customer_name']) ?></p>
                                <p class="small mb-1"><i class="bi bi-envelope"></i> <a href="mailto:<?= e($inq['customer_email']) ?>"><?= e($inq['customer_email']) ?></a></p>
                                <?php if (!empty($inq['customer_phone'])): ?>
                                    <p class="small mb-0"><i class="bi bi-telephone"></i> <?= e($inq['customer_phone']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h3 class="h6 text-success mb-2">Message</h3>
                                <p class="mb-0 inquiry-message-body"><?= nl2br(e($inq['message'])) ?></p>
                            </div>
                        </div>

                        <?php if ($inq['status'] !== 'closed'): ?>
                            <div class="mt-3 pt-3 border-top d-flex flex-wrap gap-2">
                                <?php if ($inq['status'] === 'new'): ?>
                                    <form method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="inquiry_id" value="<?= (int) $inq['id'] ?>">
                                        <input type="hidden" name="action" value="read">
                                        <button type="submit" class="btn btn-sm btn-outline-info">Mark as read</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="inquiry_id" value="<?= (int) $inq['id'] ?>">
                                    <input type="hidden" name="action" value="close">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Close inquiry</button>
                                </form>
                                <?php if (!empty($inq['product_id'])): ?>
                                    <a href="<?= e(url('product_details.php?id=' . (int) $inq['product_id'])) ?>" class="btn btn-sm btn-outline-success ms-auto" target="_blank">View product</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
    <?php render_pagination($total, $meta['page'], $meta['per_page'], 'farmer/inquiries.php'); ?>
<?php endif; ?>

<?php dashboard_shell_end(); ?>
