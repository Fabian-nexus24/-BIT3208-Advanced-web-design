<?php
declare(strict_types=1);

/**
 * Product data access and marketplace queries.
 */

function product_image_url(?string $imagePath): string
{
    if ($imagePath !== null && $imagePath !== '') {
        $full = dirname(__DIR__) . '/' . ltrim($imagePath, '/');
        if (is_file($full)) {
            return url($imagePath);
        }
    }
    return url(PRODUCT_FALLBACK_IMAGE);
}

/**
 * @param array{search?: string, category?: string, location?: string, price_min?: float|null, price_max?: float|null, farmer_id?: int} $filters
 * @return array{sql: string, params: list<mixed>}
 */
function marketplace_products_where(array $filters): array
{
    $sql = " FROM products p
            INNER JOIN farmers f ON p.farmer_id = f.id
            WHERE p.status = 'active' AND f.status = 'active'";
    $params = [];

    if (!empty($filters['farmer_id'])) {
        $sql .= ' AND p.farmer_id = ?';
        $params[] = (int) $filters['farmer_id'];
    }

    if (!empty($filters['search'])) {
        $sql .= ' AND (p.name LIKE ? OR p.description LIKE ? OR p.category LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    if (!empty($filters['category'])) {
        $sql .= ' AND p.category = ?';
        $params[] = $filters['category'];
    }

    if (!empty($filters['location'])) {
        $sql .= ' AND (p.location = ? OR f.farming_location = ? OR f.county = ?)';
        $params[] = $filters['location'];
        $params[] = $filters['location'];
        $params[] = $filters['location'];
    }

    if (isset($filters['price_min']) && $filters['price_min'] !== null) {
        $sql .= ' AND p.price >= ?';
        $params[] = $filters['price_min'];
    }

    if (isset($filters['price_max']) && $filters['price_max'] !== null) {
        $sql .= ' AND p.price <= ?';
        $params[] = $filters['price_max'];
    }

    return ['sql' => $sql, 'params' => $params];
}

function marketplace_count_products(array $filters = []): int
{
    global $pdo;
    $where = marketplace_products_where($filters);
    $stmt = $pdo->prepare('SELECT COUNT(*)' . $where['sql']);
    $stmt->execute($where['params']);
    return (int) $stmt->fetchColumn();
}

/**
 * @param array{search?: string, category?: string, location?: string, price_min?: float|null, price_max?: float|null, farmer_id?: int, limit?: int, offset?: int} $filters
 * @return list<array<string, mixed>>
 */
function marketplace_search_products(array $filters = []): array
{
    global $pdo;

    $where = marketplace_products_where($filters);

    $sql = "SELECT p.id, p.farmer_id, p.name, p.category, p.description, p.price, p.unit,
                   p.stock_qty, p.location, p.image_path, p.status, p.created_at,
                   f.full_name AS farmer_name,
                   COALESCE(p.location, f.farming_location, f.county, 'Kenya') AS display_location"
        . $where['sql']
        . ' ORDER BY p.created_at DESC';

    $params = $where['params'];

    if (isset($filters['limit'])) {
        $sql .= ' LIMIT ' . (int) $filters['limit'];
        if (isset($filters['offset'])) {
            $sql .= ' OFFSET ' . (int) $filters['offset'];
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function marketplace_product_by_id(int $id): ?array
{
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT p.*, f.full_name AS farmer_name, f.phone AS farmer_phone,
                COALESCE(p.location, f.farming_location, f.county, 'Kenya') AS display_location
         FROM products p
         INNER JOIN farmers f ON p.farmer_id = f.id
         WHERE p.id = ? AND p.status = 'active' AND f.status = 'active'
         LIMIT 1"
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

function marketplace_filter_categories(): array
{
    global $pdo;

    try {
        $stmt = $pdo->query(
            "SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category"
        );
        $list = array_column($stmt->fetchAll(), 'category');
        return $list !== [] ? $list : PRODUCT_CATEGORIES;
    } catch (PDOException $e) {
        return PRODUCT_CATEGORIES;
    }
}

function marketplace_location_sql(string $productAlias = 'p', string $farmerAlias = 'f'): string
{
    return "COALESCE(NULLIF(TRIM({$productAlias}.location), ''), NULLIF(TRIM({$farmerAlias}.farming_location), ''), NULLIF(TRIM({$farmerAlias}.county), ''))";
}

function marketplace_filter_locations(): array
{
    global $pdo;

    $locationExpr = marketplace_location_sql('p', 'f');
    $stmt = $pdo->query(
        "SELECT DISTINCT {$locationExpr} AS loc
         FROM products p
         INNER JOIN farmers f ON p.farmer_id = f.id
         WHERE p.status = 'active'
         HAVING loc IS NOT NULL AND loc != ''
         ORDER BY loc"
    );
    return array_column($stmt->fetchAll(), 'loc');
}

function farmer_product_by_id(int $productId, int $farmerId): ?array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND farmer_id = ? LIMIT 1');
    $stmt->execute([$productId, $farmerId]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

function farmer_product_count(int $farmerId): int
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE farmer_id = ?');
    $stmt->execute([$farmerId]);
    return (int) $stmt->fetchColumn();
}

/**
 * All products for a farmer (manage screen).
 * @return list<array<string, mixed>>
 */
function farmer_products_list(int $farmerId): array
{
    global $pdo;
    $stmt = $pdo->prepare(
        "SELECT p.*, COALESCE(p.location, f.farming_location, f.county, 'Kenya') AS display_location
         FROM products p
         INNER JOIN farmers f ON p.farmer_id = f.id
         WHERE p.farmer_id = ?
         ORDER BY p.created_at DESC"
    );
    $stmt->execute([$farmerId]);
    return $stmt->fetchAll();
}

/**
 * @return array{ok: bool, id?: int, error?: string}
 */
function create_product(int $farmerId, array $data, ?array $imageFile = null): array
{
    global $pdo;

    $imagePath = null;
    if ($imageFile !== null && ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $upload = upload_product_image($imageFile, 'product_' . $farmerId);
        if (!$upload['ok']) {
            return ['ok' => false, 'error' => $upload['error'] ?? 'Image upload failed.'];
        }
        $imagePath = $upload['path'] ?? null;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO products (farmer_id, name, category, description, price, unit, stock_qty, location, image_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $farmerId,
        $data['name'],
        $data['category'],
        $data['description'],
        $data['price'],
        'kg',
        $data['stock_qty'],
        $data['location'],
        $imagePath,
        'active',
    ]);

    return ['ok' => true, 'id' => (int) $pdo->lastInsertId()];
}

/**
 * @return array{ok: bool, error?: string}
 */
function update_product(int $productId, int $farmerId, array $data, ?array $imageFile = null): array
{
    global $pdo;

    $existing = farmer_product_by_id($productId, $farmerId);
    if ($existing === null) {
        return ['ok' => false, 'error' => 'Product not found.'];
    }

    $imagePath = $existing['image_path'] ?? null;
    if ($imageFile !== null && ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $upload = upload_product_image($imageFile, 'product_' . $farmerId);
        if (!$upload['ok']) {
            return ['ok' => false, 'error' => $upload['error'] ?? 'Image upload failed.'];
        }
        if (!empty($imagePath)) {
            delete_upload_file($imagePath);
        }
        $imagePath = $upload['path'] ?? $imagePath;
    }

    $stmt = $pdo->prepare(
        'UPDATE products SET name = ?, category = ?, description = ?, price = ?, stock_qty = ?, location = ?, image_path = ?
         WHERE id = ? AND farmer_id = ?'
    );
    $stmt->execute([
        $data['name'],
        $data['category'],
        $data['description'],
        $data['price'],
        $data['stock_qty'],
        $data['location'],
        $imagePath,
        $productId,
        $farmerId,
    ]);

    return ['ok' => true];
}

/**
 * @return array{ok: bool, error?: string}
 */
function delete_product(int $productId, int $farmerId): array
{
    global $pdo;

    $existing = farmer_product_by_id($productId, $farmerId);
    if ($existing === null) {
        return ['ok' => false, 'error' => 'Product not found.'];
    }

    if (!empty($existing['image_path'])) {
        delete_upload_file($existing['image_path']);
    }

    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ? AND farmer_id = ?');
    $stmt->execute([$productId, $farmerId]);

    return ['ok' => true];
}

/**
 * @param array<string, mixed> $product
 */
function validate_product_input(array $input): array
{
    $errors = [];
    $name = trim((string) ($input['name'] ?? ''));
    $category = trim((string) ($input['category'] ?? ''));
    $description = trim((string) ($input['description'] ?? ''));
    $location = trim((string) ($input['location'] ?? ''));
    $price = (string) ($input['price'] ?? '');
    $stock = (string) ($input['stock_qty'] ?? '');

    if ($name === '') {
        $errors[] = 'Product name is required.';
    }
    if ($category === '' || !in_array($category, PRODUCT_CATEGORIES, true)) {
        $errors[] = 'Please select a valid category.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }
    if ($location === '') {
        $errors[] = 'Farming location is required.';
    }
    if (!is_numeric($price) || (float) $price <= 0) {
        $errors[] = 'Price per kg must be a positive number.';
    }
    if (!ctype_digit($stock) || (int) $stock <= 0) {
        $errors[] = 'Quantity available must be a positive whole number.';
    }

    return [
        'errors' => $errors,
        'data'   => [
            'name'        => $name,
            'category'    => $category,
            'description' => $description,
            'location'    => $location,
            'price'       => round((float) $price, 2),
            'stock_qty'   => (int) $stock,
        ],
    ];
}
