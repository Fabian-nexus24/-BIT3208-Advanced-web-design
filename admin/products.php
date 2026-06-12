<?php
require_once __DIR__ . '/_bootstrap.php';

// Get all products
$stmt = $pdo->query("SELECT p.*, f.fullname FROM products p JOIN farmers f ON p.farmer_id = f.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - FarmConnect Kenya</title>
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
                    <li class="nav-item"><a class="nav-link" href="farmers.php">Farmers</a></li>
                    <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1>Products Management</h1>
                <p class="text-muted">Monitor all products listed on the platform</p>
            </div>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Farmer</th>
                            <th>Price (KES/kg)</th>
                            <th>Quantity (kg)</th>
                            <th>Location</th>
                            <th>Listed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td>
                                    <a href="farmer_details.php?id=<?php echo $product['farmer_id']; ?>">
                                        <?php echo htmlspecialchars($product['fullname']); ?>
                                    </a>
                                </td>
                                <td><?php echo number_format($product['price_per_kg'], 2); ?></td>
                                <td><?php echo number_format($product['quantity'], 2); ?></td>
                                <td><?php echo htmlspecialchars($product['location']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">🗑️ Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h5>No products listed yet</h5>
                <p>Products will appear here once farmers start listing them.</p>
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
