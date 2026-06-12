<?php
declare(strict_types=1);

/**
 * Database-driven in-app notifications (no real-time sockets).
 */

function notifications_table_exists(): bool
{
    global $pdo;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
        return $stmt !== false && $stmt->rowCount() > 0;
    } catch (PDOException) {
        return false;
    }
}

function create_notification(
    string $role,
    int $userId,
    string $type,
    string $title,
    string $message,
    ?string $linkUrl = null
): void {
    global $pdo;

    if (!notifications_table_exists()) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_role, user_id, type, title, message, link_url, is_read)
         VALUES (?, ?, ?, ?, ?, ?, 0)'
    );
    $stmt->execute([$role, $userId, $type, $title, $message, $linkUrl]);
}

function notification_unread_count(string $role, int $userId): int
{
    global $pdo;

    if (!notifications_table_exists()) {
        return 0;
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM notifications WHERE user_role = ? AND user_id = ? AND is_read = 0'
    );
    $stmt->execute([$role, $userId]);
    return (int) $stmt->fetchColumn();
}

/**
 * @return list<array<string, mixed>>
 */
function notifications_list(string $role, int $userId, int $limit = 20, int $offset = 0): array
{
    global $pdo;

    if (!notifications_table_exists()) {
        return [];
    }

    $stmt = $pdo->prepare(
        'SELECT id, type, title, message, link_url, is_read, created_at
         FROM notifications
         WHERE user_role = ? AND user_id = ?
         ORDER BY created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->bindValue(1, $role);
    $stmt->bindValue(2, $userId, PDO::PARAM_INT);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function notifications_count(string $role, int $userId): int
{
    global $pdo;

    if (!notifications_table_exists()) {
        return 0;
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM notifications WHERE user_role = ? AND user_id = ?'
    );
    $stmt->execute([$role, $userId]);
    return (int) $stmt->fetchColumn();
}

function mark_notification_read(int $id, string $role, int $userId): void
{
    global $pdo;

    if (!notifications_table_exists()) {
        return;
    }

    $stmt = $pdo->prepare(
        'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_role = ? AND user_id = ?'
    );
    $stmt->execute([$id, $role, $userId]);
}

function mark_all_notifications_read(string $role, int $userId): void
{
    global $pdo;

    if (!notifications_table_exists()) {
        return;
    }

    $stmt = $pdo->prepare(
        'UPDATE notifications SET is_read = 1 WHERE user_role = ? AND user_id = ? AND is_read = 0'
    );
    $stmt->execute([$role, $userId]);
}

function notify_farmer_new_order(int $farmerId, string $productName, int $orderId): void
{
    create_notification(
        ROLE_FARMER,
        $farmerId,
        'order_new',
        'New order received',
        'A customer ordered ' . $productName . '. Review and accept or reject.',
        'farmer/orders.php'
    );
}

function notify_customer_order_status(int $customerId, string $productName, string $statusLabel): void
{
    create_notification(
        ROLE_CUSTOMER,
        $customerId,
        'order_status',
        'Order ' . $statusLabel,
        'Your order for ' . $productName . ' is now ' . strtolower($statusLabel) . '.',
        'customer/orders.php'
    );
}

function notify_farmer_new_inquiry(int $farmerId, string $productName): void
{
    create_notification(
        ROLE_FARMER,
        $farmerId,
        'inquiry_new',
        'New inquiry received',
        'A customer sent an inquiry about ' . $productName . '.',
        'farmer/inquiries.php'
    );
}

function render_notification_cards(array $items): void
{
    if ($items === []) {
        render_empty_state(
            'bi-bell',
            'No notifications',
            'Activity about orders and inquiries will appear here.',
            null,
            null
        );
        return;
    }

    foreach ($items as $n) {
        $isUnread = !(bool) $n['is_read'];
        $link = !empty($n['link_url']) ? url((string) $n['link_url']) : '#';
        ?>
        <article class="card border-0 shadow-sm mb-3 <?= $isUnread ? 'border-start border-4 border-warning' : '' ?>">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <?php if ($isUnread): ?>
                            <span class="badge bg-warning text-dark mb-1">New</span>
                        <?php endif; ?>
                        <h3 class="h6 mb-1 fw-semibold"><?= e($n['title']) ?></h3>
                        <p class="small text-muted mb-1"><?= e($n['message']) ?></p>
                        <p class="small text-muted mb-0"><?= e(date('d M Y, H:i', strtotime((string) $n['created_at']))) ?></p>
                    </div>
                    <?php if (!empty($n['link_url'])): ?>
                        <a href="<?= e($link) ?>" class="btn btn-sm btn-outline-success flex-shrink-0">View</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php
    }
}
