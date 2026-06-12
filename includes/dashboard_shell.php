<?php
declare(strict_types=1);

require_once __DIR__ . '/brand.php';

/**
 * Bootstrap 5 dashboard layout with sidebar + mobile offcanvas.
 */
function dashboard_shell_start(string $role, string $activeNav, string $pageTitle): void
{
    $auth = auth_user();
    $menu = match ($role) {
        ROLE_ADMIN => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'url' => 'admin/dashboard.php', 'icon' => 'bi-speedometer2'],
        ],
        ROLE_FARMER => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'url' => 'farmer/dashboard.php', 'icon' => 'bi-speedometer2'],
            ['id' => 'products', 'label' => 'My Products', 'url' => 'farmer/manage_products.php', 'icon' => 'bi-basket'],
            ['id' => 'add-product', 'label' => 'Add Product', 'url' => 'farmer/add_product.php', 'icon' => 'bi-plus-circle'],
            ['id' => 'inquiries', 'label' => 'Inquiries', 'url' => 'farmer/inquiries.php', 'icon' => 'bi-chat-dots'],
            ['id' => 'orders', 'label' => 'Orders', 'url' => 'farmer/orders.php', 'icon' => 'bi-cart-check'],
            ['id' => 'notifications', 'label' => 'Notifications', 'url' => 'farmer/notifications.php', 'icon' => 'bi-bell'],
            ['id' => 'profile', 'label' => 'Profile', 'url' => 'farmer/profile.php', 'icon' => 'bi-person'],
        ],
        ROLE_CUSTOMER => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'url' => 'customer/dashboard.php', 'icon' => 'bi-speedometer2'],
            ['id' => 'orders', 'label' => 'My Orders', 'url' => 'customer/orders.php', 'icon' => 'bi-cart'],
            ['id' => 'inquiries', 'label' => 'My Inquiries', 'url' => 'customer/inquiries.php', 'icon' => 'bi-chat-left-text'],
            ['id' => 'notifications', 'label' => 'Notifications', 'url' => 'customer/notifications.php', 'icon' => 'bi-bell'],
            ['id' => 'profile', 'label' => 'Profile', 'url' => 'customer/profile.php', 'icon' => 'bi-person'],
        ],
        default => [],
    };

    $roleBadge = match ($role) {
        ROLE_ADMIN    => 'Admin',
        ROLE_FARMER   => 'Farmer',
        ROLE_CUSTOMER => 'Customer',
        default       => 'User',
    };

    ob_start();
    ?>
    <ul class="nav nav-pills flex-column gap-1">
        <?php foreach ($menu as $item): ?>
            <li class="nav-item">
                <a class="nav-link text-white <?= $activeNav === $item['id'] ? 'active' : '' ?>"
                   href="<?= e(url($item['url'])) ?>">
                    <i class="bi <?= e($item['icon']) ?> me-2"></i><?= e($item['label']) ?>
                    <?php if ($item['id'] === 'notifications' && isset($auth['id'])): ?>
                        <?php $unread = notification_unread_count($role, (int) $auth['id']); ?>
                        <?php if ($unread > 0): ?>
                            <span class="badge bg-danger ms-1"><?= e((string) $unread) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li class="nav-item mt-3 pt-3 border-top border-secondary">
            <a class="nav-link text-white-50" href="<?= e(url(PRODUCTS_PATH)) ?>" target="_blank">
                <i class="bi bi-shop me-2"></i>View marketplace
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white-50" href="<?= e(url(LOGOUT_PATH)) ?>">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </li>
    </ul>
    <?php
    $sidebarNav = ob_get_clean();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/favicon.svg')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/theme.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/dashboard.css')) ?>">
</head>
<body class="dashboard-body">
<div class="dashboard-layout d-flex flex-column flex-lg-row">
    <!-- Mobile top bar -->
    <div class="dashboard-mobile-bar d-lg-none d-flex align-items-center justify-content-between px-3 py-2">
        <?php render_brand('text-white text-decoration-none', true); ?>
        <button class="btn btn-sm btn-light dashboard-offcanvas-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashSidebar" aria-label="Menu">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>

    <!-- Mobile offcanvas -->
    <div class="offcanvas offcanvas-start dashboard-sidebar text-white" tabindex="-1" id="dashSidebar">
        <div class="offcanvas-header border-secondary border-opacity-25">
            <?php render_brand('text-white text-decoration-none'); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body pt-0">
            <span class="badge bg-warning text-dark mb-2"><?= e($roleBadge) ?></span>
            <p class="small text-white-50 mb-3"><?= e($auth['name'] ?? '') ?></p>
            <?= $sidebarNav ?>
        </div>
    </div>

    <!-- Desktop sidebar -->
    <aside class="dashboard-sidebar dashboard-sidebar-desktop text-white p-3 d-none d-lg-flex flex-column">
        <?php render_brand('text-white text-decoration-none mb-4'); ?>
        <span class="badge bg-warning text-dark align-self-start mb-2"><?= e($roleBadge) ?></span>
        <p class="small text-white-50 mb-3"><?= e($auth['name'] ?? '') ?></p>
        <?= $sidebarNav ?>
    </aside>

    <div class="dashboard-main flex-grow-1 d-flex flex-column">
        <header class="dashboard-topbar px-3 px-md-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="h5 mb-0 fw-bold text-success"><?= e($pageTitle) ?></h1>
            <span class="text-muted small d-none d-md-inline"><i class="bi bi-envelope"></i> <?= e($auth['email'] ?? '') ?></span>
        </header>
        <div class="dashboard-content flex-grow-1">
            <?= flash_render() ?>
    <?php
}

function dashboard_shell_end(): void
{
    ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    <?php
}

function stat_card(string $title, string $value, string $icon, string $color = 'success'): string
{
    return sprintf(
        '<div class="col-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <p class="text-muted small mb-1 fw-medium">%s</p>
                            <h3 class="mb-0 fs-4">%s</h3>
                        </div>
                        <span class="stat-icon bg-%s-subtle text-%s"><i class="bi %s"></i></span>
                    </div>
                </div>
            </div>
        </div>',
        e($title),
        e($value),
        e($color),
        e($color),
        e($icon)
    );
}

function dashboard_welcome_banner(string $name, string $subtitle): void
{
    ?>
    <div class="dashboard-welcome p-4 mb-4 animate-fade-in">
        <div class="row align-items-center">
            <div class="col">
                <p class="text-muted small mb-1">Welcome back</p>
                <h2 class="h4 fw-bold mb-1 text-success"><?= e($name) ?></h2>
                <p class="text-muted mb-0 small"><?= e($subtitle) ?></p>
            </div>
            <div class="col-auto d-none d-sm-block">
                <i class="bi bi-sun display-4 text-warning opacity-75"></i>
            </div>
        </div>
    </div>
    <?php
}
