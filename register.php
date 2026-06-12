<?php
require_once __DIR__ . '/config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $location = trim($_POST['location']);

    if (empty($fullname) || empty($phone) || empty($email) || empty($password) || empty($location)) {
        $message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM farmers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already registered.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO farmers (fullname, phone, email, password, location) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$fullname, $phone, $email, $hashed_password, $location])) {
                $message = 'Registration successful! You can now log in.';
                // Optionally redirect to login page
                header('Location: login.php');
                exit();
            } else {
                $message = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Registration - FarmConnect Kenya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Custom CSS -->
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Farmer Registration</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Farming Location</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Register</button>
                        </form>
                        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
