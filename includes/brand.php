<?php
declare(strict_types=1);

function brand_logo_url(): string
{
    return url('assets/img/logo.svg');
}

function render_brand(string $linkClass = 'navbar-brand d-flex align-items-center gap-2 text-white text-decoration-none', bool $showText = true): void
{
    ?>
    <a href="<?= e(url(INDEX_PATH)) ?>" class="<?= e($linkClass) ?>">
        <img src="<?= e(brand_logo_url()) ?>" alt="" width="40" height="40" class="brand-logo">
        <?php if ($showText): ?>
            <span class="brand-text fw-bold">FarmConnect<span class="brand-accent"> Kenya</span></span>
        <?php endif; ?>
    </a>
    <?php
}
