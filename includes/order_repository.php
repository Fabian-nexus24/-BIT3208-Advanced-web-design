<?php
declare(strict_types=1);

/**
 * Customer order placement data access.
 */

function orders_table_exists(): bool
{
    global $pdo;

    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
        return $stmt !== false && $stmt->rowCount() > 0;
    } catch (PDOException) {
        return false;
    }
}

function order_place_url(int $productId): string
{
    $dest = PLACE_ORDER_PATH . '?product_id=' . $productId;

    if (is_logged_in() && current_role() === ROLE_CUSTOMER) {
        return url($dest);
    }

    return url(LOGIN_PATH . '?redirect=' . rawurlencode($dest));
}

/**
 * @param array<string, mixed> $product
 */
function render_order_now_button(int $productId, array $product, string $extraClass = ''): void
{
    $stockQty = (int) ($product['stock_qty'] ?? 0);
    $class = trim('btn btn-marketplace ' . $extraClass);

    if ($stockQty <= 0) {
        ?>
        <button type="button" class="<?= e($class) ?>" disabled>
            <i class="bi bi-cart-x"></i> Out of stock
        </button>
        <?php
        return;
    }

    $url = order_place_url($productId);
    ?>
    <a href="<?= e($url) ?>" class="<?= e($class) ?>">
        <i class="bi bi-cart-plus"></i> Order Now
    </a>
    <?php
}

/**
 * @param array<string, mixed> $input
 * @param array<string, mixed> $product
 * @return array{errors: list<string>, data: array{quantity: int, delivery_notes: ?string, payment_method: string}}
 */
function validate_order_input(array $input, array $product): array
{
    $errors = [];
    $quantityRaw = (string) ($input['quantity'] ?? '');
    $notes = trim((string) ($input['delivery_notes'] ?? ''));
    $paymentMethod = (string) ($input['payment_method'] ?? '');
    $stockQty = (int) ($product['stock_qty'] ?? 0);

    if (!ctype_digit($quantityRaw) || (int) $quantityRaw <= 0) {
        $errors[] = 'Quantity must be a positive whole number.';
    }

    $quantity = (int) $quantityRaw;

    $available = product_available_quantity($productId, $stockQty);
    if ($errors === [] && $quantity > $available) {
        $errors[] = 'Quantity cannot exceed available stock (' . $available . ' kg).';
    }

    if ($notes !== '' && mb_strlen($notes) > ORDER_NOTES_MAX_LENGTH) {
        $errors[] = 'Delivery notes must not exceed ' . ORDER_NOTES_MAX_LENGTH . ' characters.';
    }

    if ($paymentMethod !== ORDER_PAYMENT_COD) {
        if ($paymentMethod === ORDER_PAYMENT_MPESA) {
            $errors[] = 'M-Pesa is coming soon — please use Cash on Delivery.';
        } else {
            $errors[] = 'Please select a valid payment method.';
        }
    }

    return [
        'errors' => $errors,
        'data'   => [
            'quantity'        => $quantity,
            'delivery_notes'  => $notes !== '' ? $notes : null,
            'payment_method'  => ORDER_PAYMENT_COD,
        ],
    ];
}

/**
 * @param array{quantity: int, delivery_notes: ?string, payment_method: string} $data
 * @return array{ok: bool, id?: int, total_price?: float, error?: string}
 */
function create_order(int $customerId, int $productId, array $data): array
{
    global $pdo;

    if (!orders_table_exists()) {
        return [
            'ok'    => false,
            'error' => 'Orders table is missing. Run: php tools/migrate_phase6_orders.php (or import database/migrations/phase6_orders.sql in phpMyAdmin).',
        ];
    }

    $product = marketplace_product_by_id($productId);
    if ($product === null) {
        return ['ok' => false, 'error' => 'Product not found or no longer available.'];
    }

    $validated = validate_order_input([
        'quantity'        => (string) $data['quantity'],
        'delivery_notes'  => $data['delivery_notes'] ?? '',
        'payment_method'  => $data['payment_method'] ?? '',
    ], $product);

    if ($validated['errors'] !== []) {
        return ['ok' => false, 'error' => $validated['errors'][0]];
    }

    $orderData = $validated['data'];
    $quantity = $orderData['quantity'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "SELECT id, farmer_id, price, stock_qty
             FROM products
             WHERE id = ? AND status = 'active'
             FOR UPDATE"
        );
        $stmt->execute([$productId]);
        $row = $stmt->fetch();

        if ($row === false) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Product not found or no longer available.'];
        }

        $stockQty = (int) $row['stock_qty'];
        $available = product_available_quantity($productId, $stockQty);
        if ($quantity > $available) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Only ' . $available . ' kg available. Please reduce your quantity.'];
        }

        $unitPrice = (float) $row['price'];
        $totalPrice = round($unitPrice * $quantity, 2);
        $farmerId = (int) $row['farmer_id'];

        $insert = $pdo->prepare(
            'INSERT INTO orders (customer_id, farmer_id, product_id, quantity, total_price, delivery_notes, payment_method, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $customerId,
            $farmerId,
            $productId,
            $quantity,
            $totalPrice,
            $orderData['delivery_notes'],
            ORDER_PAYMENT_COD,
            ORDER_STATUS_PENDING,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $pdo->commit();

        notify_farmer_new_order($farmerId, (string) $product['name'], $orderId);

        return ['ok' => true, 'id' => $orderId, 'total_price' => $totalPrice];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'Unknown table')) {
            return ['ok' => false, 'error' => 'Orders are not set up yet. Please run the database migration.'];
        }

        throw $e;
    }
}

/**
 * Pending orders reserve stock until farmer accepts (stock reduced on accept only).
 */
function product_pending_order_quantity(int $productId, ?int $excludeOrderId = null): int
{
    global $pdo;

    $sql = "SELECT COALESCE(SUM(quantity), 0) FROM orders
            WHERE product_id = ? AND status = ?";
    $params = [$productId, ORDER_STATUS_PENDING];

    if ($excludeOrderId !== null) {
        $sql .= ' AND id != ?';
        $params[] = $excludeOrderId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function product_available_quantity(int $productId, ?int $physicalStock = null): int
{
    global $pdo;

    if ($physicalStock === null) {
        $stmt = $pdo->prepare('SELECT stock_qty FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$productId]);
        $row = $stmt->fetch();
        $physicalStock = $row !== false ? (int) $row['stock_qty'] : 0;
    }

    $reserved = product_pending_order_quantity($productId);
    return max(0, $physicalStock - $reserved);
}

function order_status_badge(string $status): string
{
    return match ($status) {
        ORDER_STATUS_PENDING  => 'bg-warning text-dark',
        ORDER_STATUS_ACCEPTED => 'bg-success',
        ORDER_STATUS_REJECTED => 'bg-danger',
        ORDER_STATUS_DELIVERED => 'bg-primary',
        default               => 'bg-secondary',
    };
}

function order_status_label(string $status): string
{
    return match ($status) {
        ORDER_STATUS_PENDING  => 'Pending',
        ORDER_STATUS_ACCEPTED => 'Accepted',
        ORDER_STATUS_REJECTED => 'Rejected',
        ORDER_STATUS_DELIVERED => 'Delivered',
        default               => ucfirst($status),
    };
}

function order_payment_label(string $method): string
{
    return match ($method) {
        ORDER_PAYMENT_COD => 'Cash on Delivery',
        ORDER_PAYMENT_MPESA => 'M-Pesa',
        default           => ucfirst(str_replace('_', ' ', $method)),
    };
}

/**
 * @return list<array<string, mixed>>
 */
function farmer_orders_list(int $farmerId, ?int $limit = null, ?int $offset = null): array
{
    global $pdo;

    $sql = "SELECT o.id, o.quantity, o.total_price, o.delivery_notes, o.payment_method,
                o.status, o.created_at,
                c.full_name AS customer_name, c.email AS customer_email, c.phone AS customer_phone,
                p.name AS product_name, p.id AS product_id
         FROM orders o
         INNER JOIN customers c ON o.customer_id = c.id
         INNER JOIN products p ON o.product_id = p.id
         WHERE o.farmer_id = ?
         ORDER BY o.created_at DESC";

    if ($limit !== null) {
        $sql .= ' LIMIT ' . (int) $limit;
        if ($offset !== null) {
            $sql .= ' OFFSET ' . (int) $offset;
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$farmerId]);
    return $stmt->fetchAll();
}

function farmer_orders_count(int $farmerId): int
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE farmer_id = ?');
    $stmt->execute([$farmerId]);
    return (int) $stmt->fetchColumn();
}

/**
 * @return list<array<string, mixed>>
 */
function customer_orders_list(int $customerId, ?int $limit = null, ?int $offset = null): array
{
    global $pdo;

    $sql = "SELECT o.id, o.quantity, o.total_price, o.delivery_notes, o.payment_method,
                o.status, o.created_at,
                p.name AS product_name, p.id AS product_id,
                f.full_name AS farmer_name
         FROM orders o
         INNER JOIN products p ON o.product_id = p.id
         INNER JOIN farmers f ON o.farmer_id = f.id
         WHERE o.customer_id = ?
         ORDER BY o.created_at DESC";

    if ($limit !== null) {
        $sql .= ' LIMIT ' . (int) $limit;
        if ($offset !== null) {
            $sql .= ' OFFSET ' . (int) $offset;
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId]);
    return $stmt->fetchAll();
}

function customer_orders_count(int $customerId): int
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE customer_id = ?');
    $stmt->execute([$customerId]);
    return (int) $stmt->fetchColumn();
}

function farmer_pending_order_count(int $farmerId): int
{
    global $pdo;
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM orders WHERE farmer_id = ? AND status = ?'
    );
    $stmt->execute([$farmerId, ORDER_STATUS_PENDING]);
    return (int) $stmt->fetchColumn();
}

function fetch_order_for_farmer(int $orderId, int $farmerId): ?array
{
    global $pdo;
    $stmt = $pdo->prepare(
        'SELECT o.*, p.stock_qty AS product_stock_qty
         FROM orders o
         INNER JOIN products p ON o.product_id = p.id
         WHERE o.id = ? AND o.farmer_id = ?
         LIMIT 1'
    );
    $stmt->execute([$orderId, $farmerId]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/**
 * @return array{ok: bool, error?: string}
 */
function farmer_update_order_status(int $orderId, int $farmerId, string $action): array
{
    global $pdo;

    if (!orders_table_exists()) {
        return ['ok' => false, 'error' => 'Orders are not set up yet.'];
    }

    $order = fetch_order_for_farmer($orderId, $farmerId);
    if ($order === null) {
        return ['ok' => false, 'error' => 'Order not found.'];
    }

    $current = (string) $order['status'];
    $productId = (int) $order['product_id'];
    $quantity = (int) $order['quantity'];

    try {
        $pdo->beginTransaction();

        $lock = $pdo->prepare(
            'SELECT o.id, o.status, o.product_id, o.quantity, o.customer_id,
                    p.stock_qty, p.name AS product_name
             FROM orders o
             INNER JOIN products p ON o.product_id = p.id
             WHERE o.id = ? AND o.farmer_id = ?
             FOR UPDATE'
        );
        $lock->execute([$orderId, $farmerId]);
        $row = $lock->fetch();
        if ($row === false) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Order not found.'];
        }

        $current = (string) $row['status'];
        $productId = (int) $row['product_id'];
        $quantity = (int) $row['quantity'];
        $stockQty = (int) $row['stock_qty'];
        $customerId = (int) $row['customer_id'];
        $productName = (string) $row['product_name'];

        if ($action === 'accept') {
            if ($current !== ORDER_STATUS_PENDING) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Only pending orders can be accepted.'];
            }

            if ($quantity > $stockQty) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Insufficient stock to accept this order (' . $stockQty . ' kg available).'];
            }

            $deduct = $pdo->prepare(
                'UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty >= ?'
            );
            $deduct->execute([$quantity, $productId, $quantity]);
            if ($deduct->rowCount() === 0) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Could not reduce stock. Order was not accepted.'];
            }

            $newStatus = ORDER_STATUS_ACCEPTED;
        } elseif ($action === 'reject') {
            if (!in_array($current, [ORDER_STATUS_PENDING, ORDER_STATUS_ACCEPTED], true)) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'This order cannot be rejected.'];
            }

            if ($current === ORDER_STATUS_ACCEPTED) {
                $restore = $pdo->prepare(
                    'UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?'
                );
                $restore->execute([$quantity, $productId]);
            }

            $newStatus = ORDER_STATUS_REJECTED;
        } elseif ($action === 'deliver') {
            if ($current !== ORDER_STATUS_ACCEPTED) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Only accepted orders can be marked delivered.'];
            }
            $newStatus = ORDER_STATUS_DELIVERED;
        } else {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Invalid action.'];
        }

        $update = $pdo->prepare(
            'UPDATE orders SET status = ? WHERE id = ? AND farmer_id = ?'
        );
        $update->execute([$newStatus, $orderId, $farmerId]);

        $pdo->commit();

        if ($action === 'accept') {
            notify_customer_order_status($customerId, $productName, 'Accepted');
        } elseif ($action === 'reject') {
            notify_customer_order_status($customerId, $productName, 'Rejected');
        } elseif ($action === 'deliver') {
            notify_customer_order_status($customerId, $productName, 'Delivered');
        }

        return ['ok' => true];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

/**
 * @return array{
 *   total: int,
 *   recent: list<array<string, mixed>>,
 *   by_status: array<string, int>,
 *   revenue_total: float,
 *   revenue_pending: float
 * }
 */
function admin_orders_overview(): array
{
    global $pdo;

    $empty = [
        'total'           => 0,
        'recent'          => [],
        'by_status'       => [
            ORDER_STATUS_PENDING  => 0,
            ORDER_STATUS_ACCEPTED => 0,
            ORDER_STATUS_REJECTED => 0,
            ORDER_STATUS_DELIVERED => 0,
        ],
        'revenue_total'   => 0.0,
        'revenue_pending' => 0.0,
    ];

    if (!orders_table_exists()) {
        return $empty;
    }

    $total = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();

    $byStatus = $empty['by_status'];
    $statusRows = $pdo->query(
        'SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status'
    )->fetchAll();
    foreach ($statusRows as $row) {
        $status = (string) $row['status'];
        if (array_key_exists($status, $byStatus)) {
            $byStatus[$status] = (int) $row['cnt'];
        }
    }

    $revenueStmt = $pdo->prepare(
        'SELECT COALESCE(SUM(total_price), 0) FROM orders
         WHERE status IN (?, ?)'
    );
    $revenueStmt->execute([ORDER_STATUS_ACCEPTED, ORDER_STATUS_DELIVERED]);
    $revenueTotal = (float) $revenueStmt->fetchColumn();

    $pendingStmt = $pdo->prepare(
        'SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status = ?'
    );
    $pendingStmt->execute([ORDER_STATUS_PENDING]);
    $revenuePending = (float) $pendingStmt->fetchColumn();

    $recent = $pdo->query(
        "SELECT o.id, o.quantity, o.total_price, o.status, o.payment_method, o.created_at,
                c.full_name AS customer_name,
                f.full_name AS farmer_name,
                p.name AS product_name
         FROM orders o
         INNER JOIN customers c ON o.customer_id = c.id
         INNER JOIN farmers f ON o.farmer_id = f.id
         INNER JOIN products p ON o.product_id = p.id
         ORDER BY o.created_at DESC
         LIMIT 10"
    )->fetchAll();

    return [
        'total'           => $total,
        'recent'          => $recent,
        'by_status'       => $byStatus,
        'revenue_total'   => $revenueTotal,
        'revenue_pending' => $revenuePending,
    ];
}

/**
 * @return array<string, int>
 */
function farmer_orders_by_status(int $farmerId): array
{
    global $pdo;

    $counts = [
        ORDER_STATUS_PENDING  => 0,
        ORDER_STATUS_ACCEPTED => 0,
        ORDER_STATUS_REJECTED => 0,
        ORDER_STATUS_DELIVERED => 0,
    ];

    if (!orders_table_exists()) {
        return $counts;
    }

    $stmt = $pdo->prepare(
        'SELECT status, COUNT(*) AS cnt FROM orders WHERE farmer_id = ? GROUP BY status'
    );
    $stmt->execute([$farmerId]);
    foreach ($stmt->fetchAll() as $row) {
        $status = (string) $row['status'];
        if (array_key_exists($status, $counts)) {
            $counts[$status] = (int) $row['cnt'];
        }
    }

    return $counts;
}

/**
 * @return array<string, int>
 */
function customer_orders_by_status(int $customerId): array
{
    global $pdo;

    $counts = [
        ORDER_STATUS_PENDING  => 0,
        ORDER_STATUS_ACCEPTED => 0,
        ORDER_STATUS_REJECTED => 0,
        ORDER_STATUS_DELIVERED => 0,
    ];

    if (!orders_table_exists()) {
        return $counts;
    }

    $stmt = $pdo->prepare(
        'SELECT status, COUNT(*) AS cnt FROM orders WHERE customer_id = ? GROUP BY status'
    );
    $stmt->execute([$customerId]);
    foreach ($stmt->fetchAll() as $row) {
        $status = (string) $row['status'];
        if (array_key_exists($status, $counts)) {
            $counts[$status] = (int) $row['cnt'];
        }
    }

    return $counts;
}
