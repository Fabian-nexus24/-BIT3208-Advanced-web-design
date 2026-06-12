<?php
declare(strict_types=1);

/**
 * Dashboard analytics (Phase 7).
 */

function farmer_dashboard_analytics(int $farmerId): array
{
    global $pdo;

    $products = farmer_product_count($farmerId);
    $pending = farmer_pending_order_count($farmerId);

    $defaults = [
        'total_products'   => $products,
        'total_orders'     => 0,
        'pending_orders'   => $pending,
        'completed_orders' => 0,
        'total_sales'      => 0.0,
        'new_inquiries'    => farmer_new_inquiry_count($farmerId),
    ];

    if (!orders_table_exists()) {
        return $defaults;
    }

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM orders WHERE farmer_id = ?'
    );
    $stmt->execute([$farmerId]);
    $defaults['total_orders'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM orders WHERE farmer_id = ? AND status = ?'
    );
    $stmt->execute([$farmerId, ORDER_STATUS_DELIVERED]);
    $defaults['completed_orders'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(total_price), 0) FROM orders
         WHERE farmer_id = ? AND status IN (?, ?)"
    );
    $stmt->execute([$farmerId, ORDER_STATUS_ACCEPTED, ORDER_STATUS_DELIVERED]);
    $defaults['total_sales'] = (float) $stmt->fetchColumn();

    return $defaults;
}

function customer_dashboard_analytics(int $customerId): array
{
    global $pdo;

    $defaults = [
        'total_orders'     => 0,
        'pending_orders'   => 0,
        'recent_purchases' => [],
    ];

    if (!orders_table_exists()) {
        return $defaults;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE customer_id = ?');
    $stmt->execute([$customerId]);
    $defaults['total_orders'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM orders WHERE customer_id = ? AND status = ?'
    );
    $stmt->execute([$customerId, ORDER_STATUS_PENDING]);
    $defaults['pending_orders'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT o.id, o.total_price, o.status, o.created_at, p.name AS product_name
         FROM orders o
         INNER JOIN products p ON o.product_id = p.id
         WHERE o.customer_id = ?
         ORDER BY o.created_at DESC
         LIMIT 5"
    );
    $stmt->execute([$customerId]);
    $defaults['recent_purchases'] = $stmt->fetchAll();

    return $defaults;
}

/**
 * Combined recent marketplace activity for admin.
 *
 * @return list<array<string, mixed>>
 */
function admin_marketplace_activity(int $limit = 12): array
{
    global $pdo;

    $activity = [];

    if (orders_table_exists()) {
        $orders = $pdo->query(
            "SELECT 'order' AS kind, o.id, o.total_price, o.status, o.created_at,
                    c.full_name AS actor, p.name AS subject
             FROM orders o
             INNER JOIN customers c ON o.customer_id = c.id
             INNER JOIN products p ON o.product_id = p.id
             ORDER BY o.created_at DESC
             LIMIT " . (int) $limit
        )->fetchAll();
        foreach ($orders as $row) {
            $activity[] = $row;
        }
    }

    $users = $pdo->query(
        "SELECT 'registration' AS kind, full_name AS actor, email AS subject, created_at, 'farmer' AS user_type
         FROM farmers
         UNION ALL
         SELECT 'registration', full_name, email, created_at, 'customer'
         FROM customers
         ORDER BY created_at DESC
         LIMIT " . (int) $limit
    )->fetchAll();
    foreach ($users as $row) {
        $activity[] = $row;
    }

    usort($activity, static function (array $a, array $b): int {
        return strtotime((string) $b['created_at']) <=> strtotime((string) $a['created_at']);
    });

    return array_slice($activity, 0, $limit);
}
