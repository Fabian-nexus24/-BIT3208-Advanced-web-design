<?php
require_once __DIR__ . '/_bootstrap.php';

// Get all farmers
$stmt = $pdo->query("SELECT * FROM farmers ORDER BY created_at DESC");
$farmers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers Management - FarmConnect Kenya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">🔐 FarmConnect Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="farmers.php">Farmers</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1>Farmers Management</h1>
                <p class="text-muted">View and manage all registered farmers</p>
            </div>
        </div>

        <?php if (count($farmers) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($farmers as $farmer): ?>
                            <tr>
                                <td><?php echo $farmer['id']; ?></td>
                                <td><?php echo htmlspecialchars($farmer['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($farmer['email']); ?></td>
                                <td><?php echo htmlspecialchars($farmer['phone']); ?></td>
                                <td><?php echo htmlspecialchars($farmer['location']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($farmer['created_at'])); ?></td>
                                <td>
                                    <a href="farmer_details.php?id=<?php echo $farmer['id']; ?>" class="btn btn-sm btn-info">👁️ View</a>
                                    <a href="delete_farmer.php?id=<?php echo $farmer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will delete all their products too.');">🗑️ Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h5>No farmers registered yet</h5>
                <p>Farmers will appear here once they register on the platform.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 FarmConnect Kenya. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
