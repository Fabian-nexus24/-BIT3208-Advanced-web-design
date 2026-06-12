<?php
declare(strict_types=1);

/**
 * Customer–farmer inquiry data access.
 */

define('INQUIRY_MIN_LENGTH', 10);
define('INQUIRY_MAX_LENGTH', 2000);

function safe_redirect_path(string $path): ?string
{
    $path = ltrim(trim($path), '/');
    if ($path === '' || str_contains($path, '..') || str_contains($path, '://')) {
        return null;
    }
    return $path;
}

function inquiry_contact_url(int $productId): string
{
    $dest = 'contact_inquiry.php?product_id=' . $productId;

    if (is_logged_in() && current_role() === ROLE_CUSTOMER) {
        return url($dest);
    }

    return url(LOGIN_PATH . '?redirect=' . rawurlencode($dest));
}

/**
 * @return array{ok: bool, error?: string}
 */
function validate_inquiry_message(string $message): array
{
    $message = trim($message);
    $len = mb_strlen($message);

    if ($len < INQUIRY_MIN_LENGTH) {
        return ['ok' => false, 'error' => 'Message must be at least ' . INQUIRY_MIN_LENGTH . ' characters.'];
    }
    if ($len > INQUIRY_MAX_LENGTH) {
        return ['ok' => false, 'error' => 'Message must not exceed ' . INQUIRY_MAX_LENGTH . ' characters.'];
    }

    return ['ok' => true];
}

/**
 * @return array{ok: bool, id?: int, error?: string}
 */
function create_inquiry(int $customerId, int $productId, string $message): array
{
    global $pdo;

    $product = marketplace_product_by_id($productId);
    if ($product === null) {
        return ['ok' => false, 'error' => 'Product not found or no longer available.'];
    }

    $validation = validate_inquiry_message($message);
    if (!$validation['ok']) {
        return ['ok' => false, 'error' => $validation['error']];
    }

    $farmerId = (int) $product['farmer_id'];
    $stmt = $pdo->prepare(
        'INSERT INTO inquiries (customer_id, farmer_id, product_id, message, status)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$customerId, $farmerId, $productId, trim($message), 'new']);

    notify_farmer_new_inquiry($farmerId, (string) $product['name']);

    return ['ok' => true, 'id' => (int) $pdo->lastInsertId()];
}

function farmer_inquiries_count(int $farmerId): int
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM inquiries WHERE farmer_id = ?');
    $stmt->execute([$farmerId]);
    return (int) $stmt->fetchColumn();
}

function customer_inquiries_count(int $customerId): int
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM inquiries WHERE customer_id = ?');
    $stmt->execute([$customerId]);
    return (int) $stmt->fetchColumn();
}

/**
 * @return list<array<string, mixed>>
 */
function farmer_inquiries_list(int $farmerId, ?int $limit = null, ?int $offset = null): array
{
    global $pdo;

    $sql = "SELECT i.id, i.message, i.status, i.created_at,
                c.full_name AS customer_name, c.email AS customer_email, c.phone AS customer_phone,
                p.name AS product_name, p.id AS product_id
         FROM inquiries i
         INNER JOIN customers c ON i.customer_id = c.id
         LEFT JOIN products p ON i.product_id = p.id
         WHERE i.farmer_id = ?
         ORDER BY i.created_at DESC";

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

/**
 * @return list<array<string, mixed>>
 */
function customer_inquiries_list(int $customerId, ?int $limit = null, ?int $offset = null): array
{
    global $pdo;

    $sql = "SELECT i.id, i.message, i.status, i.created_at,
                f.full_name AS farmer_name, f.email AS farmer_email, f.phone AS farmer_phone,
                p.name AS product_name, p.id AS product_id
         FROM inquiries i
         INNER JOIN farmers f ON i.farmer_id = f.id
         LEFT JOIN products p ON i.product_id = p.id
         WHERE i.customer_id = ?
         ORDER BY i.created_at DESC";

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

function farmer_new_inquiry_count(int $farmerId): int
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inquiries WHERE farmer_id = ? AND status = 'new'");
    $stmt->execute([$farmerId]);
    return (int) $stmt->fetchColumn();
}

/**
 * @return array{ok: bool, error?: string}
 */
function mark_inquiry_read(int $inquiryId, int $farmerId): array
{
    global $pdo;
    $stmt = $pdo->prepare(
        "UPDATE inquiries SET status = 'read' WHERE id = ? AND farmer_id = ? AND status = 'new'"
    );
    $stmt->execute([$inquiryId, $farmerId]);
    return ['ok' => $stmt->rowCount() > 0];
}

/**
 * @return array{ok: bool, error?: string}
 */
function mark_inquiry_closed(int $inquiryId, int $farmerId): array
{
    global $pdo;
    $stmt = $pdo->prepare(
        "UPDATE inquiries SET status = 'closed' WHERE id = ? AND farmer_id = ?"
    );
    $stmt->execute([$inquiryId, $farmerId]);
    return ['ok' => $stmt->rowCount() > 0];
}

/**
 * Render Contact Seller button for marketplace UI.
 */
function render_contact_seller_button(int $productId, string $extraClass = ''): void
{
    $url = inquiry_contact_url($productId);
    $class = trim('btn btn-outline-success ' . $extraClass);
    $label = is_logged_in() && current_role() === ROLE_CUSTOMER
        ? 'Contact Seller'
        : 'Contact Seller';
    ?>
    <a href="<?= e($url) ?>" class="<?= e($class) ?>">
        <i class="bi bi-chat-dots"></i> <?= e($label) ?>
    </a>
    <?php
}

function inquiry_status_badge(string $status): string
{
    return match ($status) {
        'new'    => 'bg-warning text-dark',
        'read'   => 'bg-info text-dark',
        'closed' => 'bg-secondary',
        default  => 'bg-light text-dark',
    };
}
