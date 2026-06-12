<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Marketplace';
$currentPage = 'marketplace';
$useMarketplaceCss = true;

$search = trim((string) ($_GET['search'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$location = trim((string) ($_GET['location'] ?? ''));
$priceMin = positive_decimal($_GET['price_min'] ?? null, 0.0);
$priceMax = positive_decimal($_GET['price_max'] ?? null, 0.0);
$page = pagination_page_from_request();

if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
    [$priceMin, $priceMax] = [$priceMax, $priceMin];
}

$filters = array_filter([
    'search'    => $search,
    'category'  => $category,
    'location'  => $location,
    'price_min' => $priceMin,
    'price_max' => $priceMax,
], static fn ($v) => $v !== null && $v !== '');

$total = marketplace_count_products($filters);
$meta = pagination_meta($total, $page, PER_PAGE_PRODUCTS);

$products = marketplace_search_products(array_merge($filters, [
    'limit'  => $meta['per_page'],
    'offset' => $meta['offset'],
]));

$categories = marketplace_filter_categories();
$locations = marketplace_filter_locations();
if ($categories === []) {
    $categories = PRODUCT_CATEGORIES;
}

$queryParams = [
    'search'     => $search,
    'category'   => $category,
    'location'   => $location,
    'price_min'  => $priceMin !== null ? (string) $priceMin : '',
    'price_max'  => $priceMax !== null ? (string) $priceMax : '',
];

require_once __DIR__ . '/includes/navbar.php';
?>

<section class="products-page-header py-4 py-lg-5">
    <div class="container animate-fade-in">
        <h1 class="section-title-market display-6 fw-bold mb-2">Fresh Produce Marketplace</h1>
        <p class="section-subtitle mb-0">Discover farm-fresh goods from Kenyan farmers — filter by category, location, and price.</p>
    </div>
</section>

<div class="container py-4">
    <div class="marketplace-filters p-3 p-md-4 mb-4">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search produce</label>
                <input type="search" name="search" class="form-control" placeholder="e.g. tomatoes"
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Category</label>
                <select name="category" class="form-select">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Location</label>
                <select name="location" class="form-select">
                    <option value="">All locations</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= e($loc) ?>" <?= $location === $loc ? 'selected' : '' ?>><?= e($loc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Min price (KES/kg)</label>
                <input type="number" name="price_min" class="form-control" min="0" step="0.01"
                       value="<?= $priceMin !== null ? e((string) $priceMin) : '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Max price (KES/kg)</label>
                <input type="number" name="price_max" class="form-control" min="0" step="0.01"
                       value="<?= $priceMax !== null ? e((string) $priceMax) : '' ?>">
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-marketplace w-100">Filter</button>
            </div>
        </form>
        <?php if ($search !== '' || $category !== '' || $location !== '' || $priceMin !== null || $priceMax !== null): ?>
            <p class="small mb-0 mt-2">
                <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="text-decoration-none">Clear all filters</a>
            </p>
        <?php endif; ?>
    </div>

    <p class="text-muted mb-3"><?= e((string) $total) ?> product(s) found</p>

    <?php if ($products === []): ?>
        <?php render_empty_state(
            'bi-search',
            'No products found',
            'Try different keywords, categories, locations, or price range.',
            'View all products',
            PRODUCTS_PATH
        ); ?>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <?php render_product_card($product); ?>
            <?php endforeach; ?>
        </div>
        <?php render_pagination($total, $meta['page'], $meta['per_page'], PRODUCTS_PATH, $queryParams); ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
