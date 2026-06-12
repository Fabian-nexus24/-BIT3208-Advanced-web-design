<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

require_farmer();
deny_cross_role_access();

$userId = current_user_id();
$farmer = fetch_farmer_by_id($userId);
if ($farmer === null) {
    flash_set('danger', 'Account not found.');
    redirect(LOGIN_PATH);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'profile') {
        $fullname = trim((string) ($_POST['fullname'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $location = trim((string) ($_POST['farming_location'] ?? ''));

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
        if ($location === '') {
            $errors[] = 'Farming location is required.';
        }
        if ($errors === [] && email_exists_for_role($email, ROLE_FARMER, $userId)) {
            $errors[] = 'This email is already in use.';
        }

        $profileImage = $farmer['profile_image'] ?? null;
        if ($errors === [] && isset($_FILES['profile_image']) && ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = upload_profile_image($_FILES['profile_image'], 'farmer_' . $userId);
            if (!$upload['ok']) {
                $errors[] = $upload['error'] ?? 'Image upload failed.';
            } else {
                if (!empty($profileImage)) {
                    delete_profile_image($profileImage);
                }
                $profileImage = $upload['path'] ?? $profileImage;
            }
        }

        if ($errors === []) {
            try {
                $stmt = $pdo->prepare(
                    'UPDATE farmers SET full_name = ?, email = ?, phone = ?, farming_location = ?, county = ?, profile_image = ? WHERE id = ?'
                );
                $stmt->execute([
                    $fullname,
                    $email,
                    $phone,
                    $location,
                    $location,
                    $profileImage,
                    $userId,
                ]);
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'Unknown column')) {
                    $stmt = $pdo->prepare(
                        'UPDATE farmers SET full_name = ?, email = ?, phone = ?, county = ? WHERE id = ?'
                    );
                    $stmt->execute([$fullname, $email, $phone, $location, $userId]);
                } else {
                    $errors[] = 'Could not update profile.';
                }
            }

            if ($errors === []) {
                update_session_name($fullname);
                $_SESSION['auth']['email'] = $email;
                $farmer = fetch_farmer_by_id($userId);
                flash_set('success', 'Profile updated successfully.');
            }
        }
    }

    if ($action === 'password' && $errors === []) {
        $result = change_user_password(
            ROLE_FARMER,
            $userId,
            (string) ($_POST['current_password'] ?? ''),
            (string) ($_POST['new_password'] ?? ''),
            (string) ($_POST['new_password_confirm'] ?? '')
        );
        if ($result['ok']) {
            flash_set('success', 'Password changed successfully.');
        } else {
            $errors[] = $result['error'] ?? 'Could not change password.';
        }
    }
}

$pageTitle = 'My Profile';
$avatarUrl = !empty($farmer['profile_image']) ? url($farmer['profile_image']) : url('assets/img/placeholder.svg');

dashboard_shell_start(ROLE_FARMER, 'profile', $pageTitle);
?>
<p class="text-muted mb-4">Update your seller profile, farming location, and profile photo shown to customers.</p>
<?php

if ($errors !== []): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Profile information</h2></div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="<?= e($avatarUrl) ?>" alt="Profile" class="profile-avatar">
                </div>
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="profile">
                    <div class="mb-3">
                        <label class="form-label">Full name</label>
                        <input type="text" name="fullname" class="form-control" required value="<?= e($farmer['full_name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($farmer['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required value="<?= e($farmer['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Farming location</label>
                        <input type="text" name="farming_location" class="form-control" required
                               value="<?= e($farmer['farming_location'] ?? $farmer['county'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile image</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/jpeg,image/png,image/webp">
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

<?php dashboard_shell_end(); ?>
