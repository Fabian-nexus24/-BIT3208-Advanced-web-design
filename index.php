<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Home';
$currentPage = 'home';
$useMarketplaceCss = true;

$featured = marketplace_search_products(['limit' => 8]);

global $pdo;
$statsProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$statsFarmers = (int) $pdo->query("SELECT COUNT(*) FROM farmers WHERE status = 'active'")->fetchColumn();

require_once __DIR__ . '/includes/navbar.php';
?>

<section class="hero-marketplace text-white py-5">
    <div class="container py-lg-5 animate-fade-in">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 fw-semibold">Kenya's farm-to-table marketplace</span>
                <h1 class="display-4 fw-bold mb-3 lh-sm">Fresh Produce.<br><span class="text-warning">Direct from the Farm.</span></h1>
                <p class="lead mb-4 opacity-90 pe-lg-4">
                    Discover tomatoes, onions, potatoes and more from trusted farmers.
                    Vibrant, local, and mobile-friendly — built for Kenya.
                </p>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="btn btn-marketplace btn-lg px-4">
                        <i class="bi bi-basket2 me-1"></i> Browse Marketplace
                    </a>
                    <a href="<?= e(url(REGISTER_FARMER_PATH)) ?>" class="btn btn-hero-outline btn-lg px-4">Sell Your Produce</a>
                </div>
                <div class="hero-stats-bar row g-0 text-center py-3 px-2">
                    <div class="col-4 border-end border-white border-opacity-25">
                        <div class="fw-bold fs-5"><?= e((string) $statsProducts) ?>+</div>
                        <div class="small opacity-75">Listings</div>
                    </div>
                    <div class="col-4 border-end border-white border-opacity-25">
                        <div class="fw-bold fs-5"><?= e((string) $statsFarmers) ?>+</div>
                        <div class="small opacity-75">Farmers</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5">24/7</div>
                        <div class="small opacity-75">Browse</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div class="position-relative d-inline-block w-100">
                    <img src="<?= e(url(PRODUCT_FALLBACK_IMAGE)) ?>" alt="Fresh Kenyan produce"
                         class="img-fluid rounded-4 shadow-lg w-100" style="max-height:340px;object-fit:cover;">
                    <span class="position-absolute bottom-0 start-50 translate-middle-x mb-3 badge bg-white text-success shadow px-3 py-2">
                        <i class="bi bi-patch-check-fill"></i> Farm-fresh quality
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-4 mb-lg-5">
            <h2 class="section-title-market h3">Shop by category</h2>
            <p class="section-subtitle mx-auto">Find what you need — from sukuma wiki to fresh fruits</p>
        </div>
        <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
            <?php foreach (array_slice(PRODUCT_CATEGORIES, 0, 5) as $cat): ?>
                <div class="col">
                    <a href="<?= e(url(PRODUCTS_PATH . '?category=' . rawurlencode($cat))) ?>" class="category-pill w-100">
                        <i class="bi <?= e(category_icon($cat)) ?>"></i>
                        <span class="small fw-semibold text-center"><?= e($cat) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="btn btn-outline-success">View all categories</a>
        </div>
    </div>
</section>

<section class="featured-strip py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
            <div>
                <h2 class="section-title-market h3 mb-1">Featured produce</h2>
                <p class="text-muted mb-0">Latest listings from farmers across Kenya</p>
            </div>
            <a href="<?= e(url(PRODUCTS_PATH)) ?>" class="btn btn-marketplace">View all products</a>
        </div>
        <?php if ($featured === []): ?>
            <?php render_empty_state(
                'bi-basket2',
                'No products yet',
                'Be the first to list fresh produce on FarmConnect Kenya.',
                'Register as Farmer',
                REGISTER_FARMER_PATH
            ); ?>
        <?php else: ?>
            <div class="row g-3 g-md-4">
                <?php foreach ($featured as $product): ?>
                    <?php render_product_card($product); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title-market h3">Why choose FarmConnect?</h2>
            <p class="section-subtitle mx-auto">A modern marketplace experience tailored for Kenyan agriculture</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm p-4 text-center">
                    <span class="feature-icon-wrap bg-success-subtle text-success mb-3"><i class="bi bi-phone"></i></span>
                    <h3 class="h6 fw-bold">Mobile first</h3>
                    <p class="small text-muted mb-0">Optimized for phones — browse and contact farmers on the go.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm p-4 text-center">
                    <span class="feature-icon-wrap bg-warning-subtle text-warning mb-3"><i class="bi bi-geo-alt"></i></span>
                    <h3 class="h6 fw-bold">Local produce</h3>
                    <p class="small text-muted mb-0">Filter by county and connect with farmers near you.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm p-4 text-center">
                    <span class="feature-icon-wrap bg-primary-subtle text-primary mb-3"><i class="bi bi-chat-dots"></i></span>
                    <h3 class="h6 fw-bold">Direct contact</h3>
                    <p class="small text-muted mb-0">Send inquiries to farmers — no middlemen on pricing talks.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card feature-card shadow-sm p-4 text-center">
                    <span class="feature-icon-wrap bg-danger-subtle text-danger mb-3"><i class="bi bi-shield-check"></i></span>
                    <h3 class="h6 fw-bold">Secure accounts</h3>
                    <p class="small text-muted mb-0">Verified roles, protected sessions, and safe file uploads.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8 text-center text-lg-start">
                <h2 class="section-title-market h4 mb-2">Ready to buy or sell?</h2>
                <p class="text-muted mb-0">Join thousands of Kenyans building a fairer food marketplace.</p>
            </div>
            <div class="col-lg-4 d-flex flex-wrap gap-2 justify-content-center justify-content-lg-end">
                <a href="<?= e(url(REGISTER_CUSTOMER_PATH)) ?>" class="btn btn-fc-green px-4">Join as Customer</a>
                <a href="<?= e(url(REGISTER_FARMER_PATH)) ?>" class="btn btn-marketplace px-4">Join as Farmer</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
