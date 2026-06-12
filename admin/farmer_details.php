<?php
require_once __DIR__ . '/_bootstrap.php';

$farmer_id = $_GET['id'] ?? null;

if (!$farmer_id) {
    header('Location: farmers.php');
    exit();
}

// Get farmer info
$stmt = $pdo->prepare("SELECT * FROM farmers WHERE id = ?");
$stmt->execute([$farmer_id]);
$farmer = $stmt->fetch();

if (!$farmer) {
    header('Location: farmers.php');
    exit();
}

// Get farmer's products
$stmt = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->execute([$farmer_id]);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Details - FarmConnect Kenya</title>
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
            <div class="col-md-8">
                <h1><?php echo htmlspecialchars($farmer['fullname']); ?></h1>
                <p class="text-muted">Farmer ID: <?php echo $farmer['id']; ?></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="farmers.php" class="btn btn-secondary">← Back to Farmers</a>
            </div>
        </div>

        <!-- Farmer Information Card -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Farmer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($farmer['fullname']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($farmer['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($farmer['phone']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($farmer['location']); ?></p>
                        <p><strong>Registered:</strong> <?php echo date('M d, Y H:i', strtotime($farmer['created_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5>Statistics</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE farmer_id = ?");
                        $stmt->execute([$farmer_id]);
                        $total_products = $stmt->fetch()['total'];

                        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM products WHERE farmer_id = ?");
                        $stmt->execute([$farmer_id]);
                        $total_stock = $stmt->fetch()['total'] ?? 0;
                        ?>
                        <p><strong>Total Products:</strong> <?php echo $total_products; ?></p>
                        <p><strong>Total Stock (kg):</strong> <?php echo number_format($total_stock, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Farmer's Products -->
        <div class="row">
            <div class="col-md-12">
                <h3>Products Listed</h3>
                <?php if (count($products) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-success">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price (KES/kg)</th>
                                    <th>Quantity (kg)</th>
                                    <th>Location</th>
                                    <th>Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo number_format($product['price_per_kg'], 2); ?></td>
                                        <td><?php echo number_format($product['quantity'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($product['location']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">🗑️ Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        This farmer has not listed any products yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
