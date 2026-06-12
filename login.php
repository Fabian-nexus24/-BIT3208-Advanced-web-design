<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_guest();

$pageTitle = 'Login';
$currentPage = 'login';
$useMarketplaceCss = true;
$error = '';
$redirectAfterLogin = safe_redirect_path((string) ($_GET['redirect'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!is_valid_email($email) || $password === '') {
            $error = 'Invalid email or password.';
        } else {
            // Optional Phase 2: rate limiting / login throttling
            $result = authenticate_user($email, $password);

            if ($result['ok']) {
                flash_set('success', 'Welcome back!');
                $redirect = safe_redirect_path((string) ($_POST['redirect'] ?? ''));
                if ($redirect !== null && $result['role'] === ROLE_CUSTOMER) {
                    redirect($redirect);
                }
                redirect_to_dashboard($result['role']);
            }

            $error = $result['error'] ?? 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 card-auth auth-card mx-auto">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h4 fw-bold text-center mb-4">Login</h2>
                    <p class="text-muted text-center small mb-4">One login for all roles — we detect your account automatically.</p>

                    <?= flash_render() ?>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <?php if ($redirectAfterLogin !== null): ?>
                        <div class="alert alert-info small">Please log in as a <strong>customer</strong> to contact sellers.</div>
                    <?php endif; ?>

                    <form method="post" action="<?= e(url(LOGIN_PATH)) ?>" novalidate>
                        <?= csrf_field() ?>
                        <?php if ($redirectAfterLogin !== null): ?>
                            <input type="hidden" name="redirect" value="<?= e($redirectAfterLogin) ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus
                                   value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-marketplace w-100 mb-3">Sign in</button>
                    </form>

                    <p class="text-center small mb-0">
                        New here?
                        <a href="<?= e(url(REGISTER_FARMER_PATH)) ?>">Register as Farmer</a>
                        or
                        <a href="<?= e(url(REGISTER_CUSTOMER_PATH)) ?>">Customer</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
