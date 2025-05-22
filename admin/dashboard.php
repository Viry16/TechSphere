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

// Get total products
$products_sql = "SELECT COUNT(*) as total FROM product";
$products_result = mysqli_query($conn, $products_sql);
$products_count = mysqli_fetch_assoc($products_result)['total'];

// Get total users
$users_sql = "SELECT COUNT(*) as total FROM users";
$users_result = mysqli_query($conn, $users_sql);
$users_count = mysqli_fetch_assoc($users_result)['total'];

// Get total orders
$orders_sql = "SELECT COUNT(*) as total FROM orders";
$orders_result = mysqli_query($conn, $orders_sql);
$orders_count = mysqli_fetch_assoc($orders_result)['total'];

// Get total revenue
$revenue_sql = "SELECT SUM(order_total_purchase) as total FROM orders";
$revenue_result = mysqli_query($conn, $revenue_sql);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;

// Get recent orders
$recent_orders_sql = "SELECT o.*, u.user_name, COUNT(op.order_item_id) as item_count 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.user_id 
                    LEFT JOIN order_product op ON o.order_id = op.order_id
                    GROUP BY o.order_id
                    ORDER BY o.order_date DESC 
                    LIMIT 6";
$recent_orders_result = mysqli_query($conn, $recent_orders_sql);
if (!$recent_orders_result) {
    // Log error for debugging
    error_log("Error fetching recent orders: " . mysqli_error($conn));
}

// Top selling products
$top_products_query = "
    SELECT p.product_id, p.product_name, p.product_price, p.product_type, 
           COALESCE(SUM(op.order_quantity), p.product_sold_quantity, 0) as total_sold
    FROM product p
    LEFT JOIN order_product op ON p.product_id = op.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
";
$top_products_result = mysqli_query($conn, $top_products_query);
if (!$top_products_result) {
    $error_message = "Error fetching top products: " . mysqli_error($conn);
    // Log error message for debugging
    error_log($error_message);
    
    // Fallback query using only product table if join query fails
    $fallback_query = "
        SELECT product_id, product_name, product_price, product_type, 
               COALESCE(product_sold_quantity, 0) as total_sold
        FROM product
        ORDER BY product_sold_quantity DESC
        LIMIT 5
    ";
    $top_products_result = mysqli_query($conn, $fallback_query);
    if (!$top_products_result) {
        error_log("Fallback query also failed: " . mysqli_error($conn));
    }
}

// Get order status counts for pie chart
$status_sql = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
$status_result = mysqli_query($conn, $status_sql);
$status_data = [];
if ($status_result) {
    while ($row = mysqli_fetch_assoc($status_result)) {
        $status_data[] = $row;
    }
} else {
    // Log error for debugging
    error_log("Error fetching order status: " . mysqli_error($conn));
}
$status_json = json_encode($status_data);

// Get monthly revenue for line chart (last 6 months)
$monthly_revenue_sql = "SELECT DATE_FORMAT(order_date, '%b %Y') as month, 
                      SUM(order_total_purchase) as revenue 
                      FROM orders 
                      WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                      GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
                      ORDER BY order_date";
$monthly_revenue_result = mysqli_query($conn, $monthly_revenue_sql);
$revenue_data = [];
if ($monthly_revenue_result) {
    while ($row = mysqli_fetch_assoc($monthly_revenue_result)) {
        $revenue_data[] = $row;
    }
} else {
    // Log error for debugging
    error_log("Error fetching monthly revenue: " . mysqli_error($conn));
}
$revenue_json = json_encode($revenue_data);

// Product types for distribution chart
$product_types_sql = "SELECT product_type, COUNT(*) as count 
                FROM product 
                GROUP BY product_type 
                ORDER BY count DESC 
                LIMIT 5";
$product_types_result = mysqli_query($conn, $product_types_sql);
$product_types_data = [];
if ($product_types_result) {
    while ($row = mysqli_fetch_assoc($product_types_result)) {
        $product_types_data[] = $row;
    }
} else {
    // Log error for debugging
    error_log("Error fetching product types: " . mysqli_error($conn));
}
$product_types_json = json_encode($product_types_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tech Sphere Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .table-responsive {
            overflow-x: auto;
        }
        .order-status {
            font-weight: bold;
        }
        .order-status.pending {
            color: #ffc107;
        }
        .order-status.shipped {
            color: #0d6efd;
        }
        .order-status.delivered {
            color: #198754;
        }
        .order-status.cancelled {
            color: #dc3545;
        }
        .product-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .recent-order-card {
            transition: transform 0.2s;
        }
        .recent-order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,.1);
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
                <a href="dashboard.php" class="sidebar-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="products.php" class="sidebar-link">
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
                    <h4 class="mb-0">Dashboard</h4>
                    <div class="admin-info">
                        <span class="me-2">Welcome, <?php echo $admin['admin_name']; ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['admin_name']); ?>&size=32&background=random" class="rounded-circle" alt="Admin">
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <!-- Stats Row -->
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Total Products</h5>
                                            <h2 class="mb-0"><?php echo $products_count; ?></h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-box fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2 mb-0">
                                        <a href="products.php" class="text-white">View all products <i class="fas fa-arrow-right"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Total Revenue</h5>
                                            <h2 class="mb-0">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2 mb-0">
                                        <a href="orders.php" class="text-white">View all orders <i class="fas fa-arrow-right"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Total Orders</h5>
                                            <h2 class="mb-0"><?php echo $orders_count; ?></h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2 mb-0">
                                        <a href="orders.php" class="text-white">Manage orders <i class="fas fa-arrow-right"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h5 class="card-title">Total Customers</h5>
                                            <h2 class="mb-0"><?php echo $users_count; ?></h2>
                                        </div>
                                        <div>
                                            <i class="fas fa-users fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2 mb-0">
                                        <a href="users.php" class="text-white">View all users <i class="fas fa-arrow-right"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Revenue Over Time</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="revenueChart" width="100%" height="50"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Status</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="orderStatusChart" width="100%" height="100%"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category Chart and Top Products -->
                    <div class="row mb-4">
                        <div class="col-md-5 mb-3">
                            <div class="card shadow-sm h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Product Type Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="productTypeChart" width="100%" height="100%"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-7">
                            <div class="card shadow-sm h-100">
                                <div class="card-header">
                                    <h5 class="card-title">Top Selling Products</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive mb-4">
                                        <?php if ($top_products_result && mysqli_num_rows($top_products_result) > 0): ?>
                                            <?php 
                                            // Check if any products have been sold
                                            $has_sales = false;
                                            $sold_products = [];
                                            
                                            // Collect only products with sales
                                            while ($row = mysqli_fetch_assoc($top_products_result)) {
                                                if ($row['total_sold'] > 0) {
                                                    $has_sales = true;
                                                    $sold_products[] = $row;
                                                }
                                            }
                                            
                                            if ($has_sales): 
                                            ?>
                                            <table class="table table-striped table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th scope="col">Product ID</th>
                                                        <th scope="col">Product Name</th>
                                                        <th scope="col">Product Type</th>
                                                        <th scope="col">Price</th>
                                                        <th scope="col">Units Sold</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($sold_products as $product): ?>
                                                        <tr>
                                                            <td><?php echo $product['product_id']; ?></td>
                                                            <td><?php echo $product['product_name']; ?></td>
                                                            <td><?php echo $product['product_type'] ?: 'Unknown'; ?></td>
                                                            <td>Rp <?php echo number_format($product['product_price'], 0, ',', '.'); ?></td>
                                                            <td>
                                                                <?php echo $product['total_sold']; ?>
                                                                <div class="progress mt-1" style="height: 4px;">
                                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min(100, $product['total_sold'] * 10); ?>%" aria-valuenow="<?php echo $product['total_sold']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <?php else: ?>
                                                <div class="alert alert-warning mt-2">
                                                    <i class="fas fa-info-circle me-2"></i>Products are listed but no sales have been recorded yet.
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="alert alert-info text-center">
                                                <i class="fas fa-info-circle me-2"></i>No product sales data available at this time.
                                            </div>
                                            <p class="text-center">
                                                <a href="products.php" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-box me-1"></i>Manage Products
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if ($recent_orders_result && mysqli_num_rows($recent_orders_result) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($recent_orders_result)): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card recent-order-card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0">Order #<?php echo $order['order_id']; ?></h6>
                                                        <span class="badge bg-<?php 
                                                            echo $order['order_status'] == 'Delivered' ? 'success' : 
                                                                ($order['order_status'] == 'Pending' ? 'warning' : 
                                                                    ($order['order_status'] == 'Shipped' ? 'primary' : 'danger')); 
                                                        ?>"><?php echo $order['order_status']; ?></span>
                                                    </div>
                                                    <p class="mb-1"><i class="fas fa-user me-1"></i> <?php echo $order['user_name']; ?></p>
                                                    <p class="mb-1"><i class="fas fa-calendar me-1"></i> <?php echo date('d M Y', strtotime($order['order_date'])); ?></p>
                                                    <p class="mb-1"><i class="fas fa-shopping-basket me-1"></i> <?php echo $order['item_count']; ?> items</p>
                                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                                        <h5 class="mb-0">Rp <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></h5>
                                                        <a href="orders.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-eye"></i> Details
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php elseif (!$recent_orders_result): ?>
                                    <div class="col-12">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-circle me-2"></i>Failed to retrieve recent order data.
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle me-2"></i>No recent orders available
                                        </div>
                                        <p class="text-center">
                                            <a href="orders.php" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-shopping-cart me-1"></i>View Orders
                                            </a>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Charts JS -->
    <script>
        // Revenue Chart
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        var revenueData = <?php echo $revenue_json; ?> || [];
        
        var revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.month || ''),
                datasets: [{
                    label: 'Monthly Revenue',
                    data: revenueData.map(item => item.revenue || 0),
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw || 0);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });
        
        // Order Status Chart
        var statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        var statusData = <?php echo $status_json; ?> || [];
        
        var statusColors = {
            'Pending': '#ffc107',
            'Processing': '#6f42c1',
            'Shipped': '#0d6efd',
            'Delivered': '#198754',
            'Cancelled': '#dc3545'
        };
        
        var orderStatusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.order_status || ''),
                datasets: [{
                    data: statusData.map(item => item.count || 0),
                    backgroundColor: statusData.map(item => statusColors[item.order_status] || '#6c757d'),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Product Type Chart
        var typeCtx = document.getElementById('productTypeChart').getContext('2d');
        var typeData = <?php echo $product_types_json; ?> || [];
        
        var productTypeChart = new Chart(typeCtx, {
            type: 'bar',
            data: {
                labels: typeData.map(item => item.product_type || ''),
                datasets: [{
                    label: 'Products',
                    data: typeData.map(item => item.count || 0),
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 