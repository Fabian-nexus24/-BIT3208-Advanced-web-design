<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_guest();

$pageTitle = 'Customer Registration';
$currentPage = 'register-customer';
$errors = [];
$old = [
    'fullname' => '',
    'email'    => '',
    'phone'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $old = [
            'fullname' => trim((string) ($_POST['fullname'] ?? '')),
            'email'    => trim((string) ($_POST['email'] ?? '')),
            'phone'    => trim((string) ($_POST['phone'] ?? '')),
        ];
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        if ($old['fullname'] === '') {
            $errors[] = 'Full name is required.';
        }
        if (!is_valid_email($old['email'])) {
            $errors[] = 'A valid email address is required.';
        }
        if ($old['phone'] === '') {
            $errors[] = 'Phone number is required.';
        } elseif (!is_valid_phone($old['phone'])) {
            $errors[] = 'Phone number format is invalid.';
        }
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if ($errors === [] && email_exists_for_role($old['email'], ROLE_CUSTOMER)) {
            $errors[] = 'An account with this email already exists.';
        }

        if ($errors === []) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare(
                'INSERT INTO customers (full_name, email, password_hash, phone, status)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $insert->execute([
                $old['fullname'],
                $old['email'],
                $hash,
                $old['phone'],
                'active',
            ]);

            flash_set('success', 'Customer account created. Please log in.');
            redirect(LOGIN_PATH);
        }
    }
}

require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h4 fw-bold mb-4">Register as Customer</h2>
                    <?= flash_render() ?>
                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required value="<?= e($old['fullname']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?= e($old['email']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" required value="<?= e($old['phone']) ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="<?= MIN_PASSWORD_LENGTH ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirm password</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Create account</button>
                    </form>
                    <p class="text-center small mt-3 mb-0">Already registered? <a href="<?= e(url(LOGIN_PATH)) ?>">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
