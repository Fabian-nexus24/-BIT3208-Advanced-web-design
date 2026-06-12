<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_guest();

$pageTitle = 'Get Started';
$currentPage = 'register';

require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="text-center mb-5">
                <span class="badge bg-success-subtle text-success px-3 py-2 fw-semibold mb-3">Join FarmConnect Kenya</span>
                <h1 class="h2 fw-bold mb-2">How would you like to join?</h1>
                <p class="text-muted">Choose your account type to get started on Kenya's farm-to-table marketplace.</p>
            </div>

            <div class="row g-4">

                <!-- Farmer Card -->
                <div class="col-md-6">
                    <a href="<?= e(url(REGISTER_FARMER_PATH)) ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 register-choice-card" style="border-radius: var(--fc-radius-lg); transition: var(--fc-transition);">
                            <div class="card-body p-4 p-md-5 text-center d-flex flex-column align-items-center">
                                <div class="register-choice-icon mb-4"
                                     style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--fc-green-dark),var(--fc-green));display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-tree-fill text-white fs-2"></i>
                                </div>
                                <h2 class="h5 fw-bold mb-2" style="color:var(--fc-green-dark);">I'm a Farmer</h2>
                                <p class="text-muted small mb-4">List your fresh produce, manage orders, and connect directly with buyers across Kenya.</p>
                                <ul class="list-unstyled text-start small text-muted mb-4 w-100">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>List unlimited products</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Receive orders & inquiries</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Manage your farm profile</li>
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Track earnings & deliveries</li>
                                </ul>
                                <span class="btn btn-fc-green w-100 mt-auto">
                                    <i class="bi bi-tree me-2"></i>Register as Farmer
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Customer Card -->
                <div class="col-md-6">
                    <a href="<?= e(url(REGISTER_CUSTOMER_PATH)) ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 register-choice-card" style="border-radius: var(--fc-radius-lg); transition: var(--fc-transition);">
                            <div class="card-body p-4 p-md-5 text-center d-flex flex-column align-items-center">
                                <div class="register-choice-icon mb-4"
                                     style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--fc-orange),var(--fc-orange-light));display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-basket2-fill text-white fs-2"></i>
                                </div>
                                <h2 class="h5 fw-bold mb-2" style="color:var(--fc-orange);">I'm a Customer</h2>
                                <p class="text-muted small mb-4">Browse fresh produce, place orders, and buy directly from farmers near you.</p>
                                <ul class="list-unstyled text-start small text-muted mb-4 w-100">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Browse the marketplace</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Place & track orders</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Contact farmers directly</li>
                                    <li><i class="bi bi-check-circle-fill text-warning me-2"></i>Get farm-fresh produce</li>
                                </ul>
                                <span class="btn btn-marketplace w-100 mt-auto">
                                    <i class="bi bi-basket2 me-2"></i>Register as Customer
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

            </div>

            <p class="text-center text-muted small mt-4 mb-0">
                Already have an account? <a href="<?= e(url(LOGIN_PATH)) ?>" class="fw-semibold text-success">Login here</a>
            </p>

        </div>
    </div>
</div>

<style>
.register-choice-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--fc-shadow-hover) !important;
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
