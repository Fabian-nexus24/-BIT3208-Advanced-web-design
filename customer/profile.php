<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_customer();
deny_cross_role_access();

$userId = current_user_id();
$customer = fetch_customer_by_id($userId);
if ($customer === null) {
    flash_set('danger', 'Account not found.');
    redirect(LOGIN_PATH);
}

$errors = [];
$profileSuccess = false;
$passwordSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'profile') {
        $fullname = trim((string) ($_POST['fullname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['delivery_address'] ?? ''));

        if ($fullname === '') {
            $errors[] = 'Full name is required.';
        }
        if (!is_valid_email($email)) {
            $errors[] = 'A valid email is required.';
        }
        if ($phone === '') {
            $errors[] = 'Phone is required.';
        } elseif (!is_valid_phone($phone)) {
            $errors[] = 'Invalid phone format.';
        }
        if ($errors === [] && email_exists_for_role($email, ROLE_CUSTOMER, $userId)) {
            $errors[] = 'This email is already in use.';
        }

        if ($errors === []) {
            $stmt = $pdo->prepare(
                'UPDATE customers SET full_name = ?, email = ?, phone = ?, delivery_address = ? WHERE id = ?'
            );
            $stmt->execute([
                $fullname,
                $email,
                $phone,
                $address !== '' ? $address : null,
                $userId,
            ]);
            update_session_name($fullname);
            $_SESSION['auth']['email'] = $email;
            $customer = fetch_customer_by_id($userId);
            $profileSuccess = true;
            flash_set('success', 'Profile updated successfully.');
        }
    }

    if ($action === 'password') {
        $result = change_user_password(
            ROLE_CUSTOMER,
            $userId,
            (string) ($_POST['current_password'] ?? ''),
            (string) ($_POST['new_password'] ?? ''),
            (string) ($_POST['new_password_confirm'] ?? '')
        );
        if ($result['ok']) {
            $passwordSuccess = true;
            flash_set('success', 'Password changed successfully.');
        } else {
            $errors[] = $result['error'] ?? 'Could not change password.';
        }
    }
}

$pageTitle = 'My Profile';
dashboard_shell_start(ROLE_CUSTOMER, 'profile', $pageTitle);

if ($errors !== []): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Profile information</h2></div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="profile">
                    <div class="mb-3">
                        <label class="form-label">Full name</label>
                        <input type="text" name="fullname" class="form-control" required value="<?= e($customer['full_name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($customer['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required value="<?= e($customer['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delivery address (optional)</label>
                        <textarea name="delivery_address" class="form-control" rows="2"><?= e($customer['delivery_address'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save profile</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Change password</h2></div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="password">
                    <div class="mb-3">
                        <label class="form-label">Current password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="<?= MIN_PASSWORD_LENGTH ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm new password</label>
                        <input type="password" name="new_password_confirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-success">Update password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
dashboard_shell_end();
