<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$farmerId = current_user_id();
$page = pagination_page_from_request();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'mark_all_read') {
        mark_all_notifications_read(ROLE_FARMER, $farmerId);
        flash_set('success', 'All notifications marked as read.');
    } elseif ($action === 'mark_read') {
        mark_notification_read((int) ($_POST['notification_id'] ?? 0), ROLE_FARMER, $farmerId);
        flash_set('success', 'Notification marked as read.');
    }
    redirect('farmer/notifications.php?page=' . $page);
}

$total = notifications_count(ROLE_FARMER, $farmerId);
$meta = pagination_meta($total, $page, PER_PAGE_NOTIFICATIONS);
$items = notifications_list(ROLE_FARMER, $farmerId, $meta['per_page'], $meta['offset']);

$pageTitle = 'Notifications';
dashboard_shell_start(ROLE_FARMER, 'notifications', $pageTitle);
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0">
        <?= notification_unread_count(ROLE_FARMER, $farmerId) ?> unread · <?= $total ?> total
    </p>
    <?php if ($total > 0): ?>
        <form method="post" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-sm btn-outline-light border-secondary text-dark">Mark all read</button>
        </form>
    <?php endif; ?>
</div>

<?php render_notification_cards($items); ?>
<?php render_pagination($total, $meta['page'], $meta['per_page'], 'farmer/notifications.php'); ?>

<?php dashboard_shell_end(); ?>
