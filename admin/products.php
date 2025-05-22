<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$result = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($result);

// Delete product if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Check if product exists
    $check_sql = "SELECT * FROM product WHERE product_id = $product_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Get product type to determine which specialized table to delete from
            $product = mysqli_fetch_assoc($check_result);
            $product_type = $product['product_type'];
            
            // First, check if the product is referenced in order_product
            $check_orders_sql = "SELECT COUNT(*) as order_count FROM order_product WHERE product_id = $product_id";
            $check_orders_result = mysqli_query($conn, $check_orders_sql);
            $order_count = mysqli_fetch_assoc($check_orders_result)['order_count'];
            
            if ($order_count > 0) {
                throw new Exception("Cannot delete product because it is referenced in $order_count orders. Please archive the product instead of deleting it.");
            }
            
            // Delete from cart if there are any references
            $delete_cart_sql = "DELETE FROM cart WHERE product_id = $product_id";
            if (!mysqli_query($conn, $delete_cart_sql)) {
                throw new Exception("Error deleting product from cart: " . mysqli_error($conn));
            }
            
            // Delete from specific product type table based on product_type if applicable
            $type_tables = [
                'CPU' => 'product_cpu',
                'GPU' => 'product_gpu',
                'RAM' => 'product_ram',
                'Motherboard' => 'product_motherboard',
                'Storage' => 'product_storage',
                'PSU' => 'product_psu',
                'Case' => 'product_case',
                'Cooling' => 'product_cooling',
                'Monitor' => 'product_monitor',
                'Accessories' => 'product_accessories'
            ];
            
            if (isset($type_tables[$product_type])) {
                $type_table = $type_tables[$product_type];
                
                // Check if record exists in the type table
                $check_type_sql = "SELECT COUNT(*) as type_count FROM $type_table WHERE product_id = $product_id";
                $check_type_result = mysqli_query($conn, $check_type_sql);
                
                if ($check_type_result) {
                    $type_count = mysqli_fetch_assoc($check_type_result)['type_count'];
                    
                    if ($type_count > 0) {
                        $delete_type_sql = "DELETE FROM $type_table WHERE product_id = $product_id";
                        if (!mysqli_query($conn, $delete_type_sql)) {
                            throw new Exception("Error deleting from $type_table: " . mysqli_error($conn));
                        }
                    }
                }
            }
            
            // Finally, delete from main product table
            $delete_sql = "DELETE FROM product WHERE product_id = $product_id";
            if (!mysqli_query($conn, $delete_sql)) {
                throw new Exception("Error deleting product: " . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            $success_message = "Product deleted successfully!";
            
        } catch (Exception $e) {
            // Rollback in case of error
            mysqli_rollback($conn);
            $error_message = $e->getMessage();
        }
    } else {
        $error_message = "Product not found";
    }
}

// Get products with categories
$products_sql = "SELECT p.* 
               FROM product p
               ORDER BY p.product_id DESC";
$products_result = mysqli_query($conn, $products_sql);
if (!$products_result) {
    $error_message = "Query error: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Tech Sphere Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            background-color: #343a40;
        }
        .sidebar-link {
            color: #ffffff;
            padding: 15px;
            display: block;
            text-decoration: none;
            border-bottom: 1px solid #444;
        }
        .sidebar-link:hover {
            background-color: #495057;
            color: #ffffff;
        }
        .sidebar-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .sidebar-link i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
        }
        .admin-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        .product-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3 text-center text-white">
                    <h4>TECH SPHERE</h4>
                    <p class="mb-0">Admin Panel</p>
                </div>
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="products.php" class="sidebar-link active">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php" class="sidebar-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php" class="sidebar-link">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="profile.php" class="sidebar-link">
                    <i class="fas fa-user-cog"></i> Admin Profile
                </a>
                <a href="../logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ml-auto">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Product Management</h4>
                    <div class="admin-info">
                        <span class="me-2">Welcome, <?php echo $admin['admin_name']; ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['admin_name']); ?>&size=32&background=random" class="rounded-circle" alt="Admin">
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center bg-white">
                            <h5 class="mb-0">Product List</h5>
                            <a href="add_product.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Product
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                                            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                                <tr>
                                                    <td>#<?php echo $product['product_id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($product['product_image_url'])): ?>
                                                            <img src="<?php echo $product['product_image_url']; ?>" class="product-thumbnail" alt="<?php echo $product['product_name']; ?>">
                                                        <?php else: ?>
                                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($product['product_name']); ?>&size=50&background=random" class="product-thumbnail" alt="<?php echo $product['product_name']; ?>">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $product['product_name']; ?>
                                                        <br>
                                                        <small class="text-muted">SKU: <?php echo $product['model_number'] ?? 'N/A'; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo $product['product_type']; ?>
                                                    </td>
                                                    <td>Rp <?php echo number_format($product['product_price'], 0, ',', '.'); ?></td>
                                                    <td>
                                                        <?php if ($product['product_stock_quantity'] > 0): ?>
                                                            <span class="badge bg-success"><?php echo $product['product_stock_quantity']; ?> In Stock</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Out of Stock</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-info">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product['product_id']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <!-- Delete Modal -->
                                                        <div class="modal fade" id="deleteModal<?php echo $product['product_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="deleteModalLabel">Delete Product</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Are you sure you want to delete <strong><?php echo $product['product_name']; ?></strong>?
                                                                        <p class="text-danger mb-0 mt-2">This action cannot be undone!</p>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <a href="products.php?delete=<?php echo $product['product_id']; ?>" class="btn btn-danger">Delete</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                                    <h5>No products found</h5>
                                                    <p class="text-muted mb-0">Start by adding your first product.</p>
                                                    <a href="add_product.php" class="btn btn-primary mt-3">Add Product</a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 