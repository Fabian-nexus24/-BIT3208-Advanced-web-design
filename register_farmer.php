<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_guest();

$pageTitle = 'Farmer Registration';
$currentPage = 'register-farmer';
$errors = [];
$old = [
    'fullname'          => '',
    'email'             => '',
    'phone'             => '',
    'farming_location'  => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $old = [
            'fullname'         => trim((string) ($_POST['fullname'] ?? '')),
            'email'            => trim((string) ($_POST['email'] ?? '')),
            'phone'            => trim((string) ($_POST['phone'] ?? '')),
            'farming_location' => trim((string) ($_POST['farming_location'] ?? '')),
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
        if ($old['farming_location'] === '') {
            $errors[] = 'Farming location is required.';
        }
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        $profileImage = null;
        if ($errors === [] && isset($_FILES['profile_image'])) {
            $upload = upload_profile_image($_FILES['profile_image'], 'farmer_new');
            if (!$upload['ok']) {
                $errors[] = $upload['error'] ?? 'Image upload failed.';
            } else {
                $profileImage = $upload['path'] ?? null;
            }
        }

        if ($errors === [] && email_exists_for_role($old['email'], ROLE_FARMER)) {
            if ($profileImage !== null) {
                delete_profile_image($profileImage);
            }
            $errors[] = 'An account with this email already exists.';
        }

        if ($errors === []) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare(
                'INSERT INTO farmers (full_name, email, password_hash, phone, farming_location, county, profile_image, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            try {
                $insert->execute([
                    $old['fullname'],
                    $old['email'],
                    $hash,
                    $old['phone'],
                    $old['farming_location'],
                    $old['farming_location'],
                    $profileImage,
                    'active',
                ]);
            } catch (PDOException $e) {
                if ($profileImage !== null) {
                    delete_profile_image($profileImage);
                }
                if (str_contains($e->getMessage(), 'Unknown column')) {
                    $errors[] = 'Database needs Phase 2 migration. Import database/migrations/phase2_user_systems.sql';
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }

            if ($errors === []) {
                flash_set('success', 'Farmer account created. Please log in.');
                redirect(LOGIN_PATH);
            }
        }
    }
}

require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h4 fw-bold mb-4">Register as Farmer</h2>
                    <?= flash_render() ?>
                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul></div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data" novalidate>
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fullname" class="form-label">Full name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required value="<?= e($old['fullname']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?= e($old['email']) ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" required value="<?= e($old['phone']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="farming_location" class="form-label">Farming location</label>
                                <input type="text" class="form-control" id="farming_location" name="farming_location" required
                                       placeholder="e.g. Kiambu, Gatundu" value="<?= e($old['farming_location']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile image (optional)</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG or WebP. Max 2 MB.</div>
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
