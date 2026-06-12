<?php
declare(strict_types=1);

require_once __DIR__ . '/brand.php';
?>
</main>
<footer class="site-footer py-5 mt-auto">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <?php render_brand('d-inline-flex align-items-center gap-2 text-white text-decoration-none mb-3'); ?>
                <p class="small mb-0 opacity-75">Kenya's vibrant farm-to-table marketplace. Fresh produce, fair prices, direct from the shamba.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-bold mb-3">Explore</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= e(url(INDEX_PATH)) ?>">Home</a></li>
                    <li class="mb-2"><a href="<?= e(url(PRODUCTS_PATH)) ?>">Marketplace</a></li>
                    <li class="mb-2"><a href="<?= e(url(LOGIN_PATH)) ?>">Login</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white fw-bold mb-3">Join</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= e(url(REGISTER_FARMER_PATH)) ?>">Sell as Farmer</a></li>
                    <li class="mb-2"><a href="<?= e(url(REGISTER_CUSTOMER_PATH)) ?>">Buy as Customer</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white fw-bold mb-3">Why FarmConnect?</h6>
                <p class="small mb-0 opacity-75">Mobile-friendly, secure accounts, and direct farmer contact — built for Kenya.</p>
            </div>
        </div>
        <hr class="border-secondary my-4 opacity-25">
        <p class="small text-center mb-0 opacity-50">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
