<?php
declare(strict_types=1);

require_once __DIR__ . '/head.php';
require_once __DIR__ . '/brand.php';

$currentPage = $currentPage ?? '';
$auth = auth_user();

$navLinkClass = static function (string $page) use ($currentPage): string {
    return $page === $currentPage ? 'nav-link active' : 'nav-link';
};
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-market shadow-sm sticky-top">
    <div class="container">
        <?php render_brand(); ?>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <?php if ($auth === null): ?>
                    <li class="nav-item">
                        <a class="<?= e($navLinkClass('home')) ?>" href="<?= e(url(INDEX_PATH)) ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?= e($navLinkClass('marketplace')) ?>" href="<?= e(url(PRODUCTS_PATH)) ?>">Marketplace</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?= e($navLinkClass('login')) ?>" href="<?= e(url(LOGIN_PATH)) ?>">Login</a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="<?= e($navLinkClass('register-farmer')) ?>" href="<?= e(url(REGISTER_FARMER_PATH)) ?>">Register Farmer</a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="<?= e($navLinkClass('register-customer')) ?>" href="<?= e(url(REGISTER_CUSTOMER_PATH)) ?>">Register Customer</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-marketplace btn-sm px-3" href="<?= e(url('register_choice.php')) ?>">Get Started</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item d-none d-lg-block">
                        <span class="nav-link text-white-50 small">Hi, <?= e($auth['name']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="<?= e($navLinkClass('marketplace')) ?>" href="<?= e(url(PRODUCTS_PATH)) ?>">Marketplace</a>
                    </li>
                    <li class="nav-item">
                        <a class="<?= e($navLinkClass('dashboard')) ?>" href="<?= e(url(dashboard_path_for_role($auth['role']))) ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= e(url(LOGOUT_PATH)) ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="flex-grow-1">
