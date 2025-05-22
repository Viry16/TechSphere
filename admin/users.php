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

// Execute the SQL to add time_created column if it doesn't exist
$check_column_sql = "SHOW COLUMNS FROM users LIKE 'time_created'";
$check_column_result = mysqli_query($conn, $check_column_sql);
if (mysqli_num_rows($check_column_result) == 0) {
    $alter_sql = "ALTER TABLE users ADD COLUMN time_created TIME NOT NULL DEFAULT CURRENT_TIME";
    if (mysqli_query($conn, $alter_sql)) {
        $sql_success = "Column time_created added to users table successfully";
    } else {
        $sql_error = "Error adding column: " . mysqli_error($conn);
    }
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$result = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($result);

// Get user detail if requested
$user_detail = null;
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $detail_sql = "SELECT u.*, COUNT(o.order_id) as total_orders, SUM(o.order_total_purchase) as total_spent 
                  FROM users u 
                  LEFT JOIN orders o ON u.user_id = o.user_id 
                  WHERE u.user_id = $user_id 
                  GROUP BY u.user_id";
    $detail_result = mysqli_query($conn, $detail_sql);
    $user_detail = mysqli_fetch_assoc($detail_result);
    
    // Get user addresses
    $address_sql = "SELECT * FROM address WHERE user_id = $user_id";
    $address_result = mysqli_query($conn, $address_sql);
    
    // Get user orders
    $orders_sql = "SELECT o.*, COUNT(op.order_item_id) as total_items 
                  FROM orders o 
                  LEFT JOIN order_product op ON o.order_id = op.order_id 
                  WHERE o.user_id = $user_id 
                  GROUP BY o.order_id 
                  ORDER BY o.order_date DESC 
                  LIMIT 5";
    $orders_result = mysqli_query($conn, $orders_sql);
}

// Get all users with order count and total spent
$users_sql = "SELECT u.*, COUNT(o.order_id) as order_count, SUM(o.order_total_purchase) as total_spent 
             FROM users u 
             LEFT JOIN orders o ON u.user_id = o.user_id 
             GROUP BY u.user_id 
             ORDER BY u.user_id DESC";
$users_result = mysqli_query($conn, $users_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Tech Sphere Admin</title>
    
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user-detail-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
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
                <a href="products.php" class="sidebar-link">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php" class="sidebar-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php" class="sidebar-link active">
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
            <div class="col-md-9 col-lg-10 ml-auto content-container">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">User Management</h4>
                    <div class="admin-info">
                        <span class="me-2">Welcome, <?php echo $admin['admin_name']; ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['admin_name']); ?>&size=32&background=random" class="rounded-circle" alt="Admin">
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <?php if ($user_detail): ?>
                        <!-- User Detail View -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <a href="users.php" class="btn btn-sm btn-outline-secondary mb-3">
                                    <i class="fas fa-arrow-left"></i> Back to User List
                                </a>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body text-center">
                                        <img src="<?php echo !empty($user_detail['user_imgprofile']) ? $user_detail['user_imgprofile'] : 'https://ui-avatars.com/api/?name=' . urlencode($user_detail['user_name']) . '&size=120&background=random'; ?>" alt="User Avatar" class="user-detail-avatar">
                                        <h5><?php echo $user_detail['user_name']; ?></h5>
                                        <p class="text-muted"><?php echo $user_detail['user_email']; ?></p>
                                        <?php if ($user_detail['user_phone_number']): ?>
                                            <p><i class="fas fa-phone me-2"></i><?php echo $user_detail['user_phone_number']; ?></p>
                                        <?php endif; ?>
                                        
                                        <hr>
                                        
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <h4><?php echo $user_detail['total_orders'] ?? 0; ?></h4>
                                                <p class="text-muted">Orders</p>
                                            </div>
                                            <div class="col-6">
                                                <h4>$ <?php echo number_format($user_detail['total_spent'] ?? 0, 0, ',', '.'); ?></h4>
                                                <p class="text-muted">Total Spent</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($user_detail['user_bio'] || $user_detail['user_birthdate'] || $user_detail['user_gender']): ?>
                                    <div class="card shadow-sm mt-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">Other Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($user_detail['user_gender']): ?>
                                                <p><strong>Gender:</strong> <?php echo $user_detail['user_gender'] == 'M' ? 'Male' : 'Female'; ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if ($user_detail['user_birthdate']): ?>
                                                <p><strong>Birth Date:</strong> <?php echo date('d F Y', strtotime($user_detail['user_birthdate'])); ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if ($user_detail['time_created']): ?>
                                                <p><strong>Registration Time:</strong> <?php echo $user_detail['time_created']; ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if ($user_detail['user_bio']): ?>
                                                <p><strong>Bio:</strong> <?php echo $user_detail['user_bio']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-8">
                                <!-- User Orders -->
                                <div class="card shadow-sm mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Recent Orders</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (mysqli_num_rows($orders_result) > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Date</th>
                                                            <th>Items</th>
                                                            <th>Total</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                                            <tr>
                                                                <td>#<?php echo $order['order_id']; ?></td>
                                                                <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                                                <td><?php echo $order['total_items']; ?> items</td>
                                                                <td>$ <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></td>
                                                                <td>
                                                                    <span class="badge bg-<?php 
                                                                        echo $order['order_status'] == 'Delivered' ? 'success' : 
                                                                            ($order['order_status'] == 'Pending' ? 'warning' : 
                                                                                ($order['order_status'] == 'Shipped' ? 'primary' : 'danger')); 
                                                                    ?>">
                                                                        <?php echo $order['order_status']; ?>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-center my-3">No orders yet</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- User Addresses -->
                                <div class="card shadow-sm">
                                    <div class="card-header">
                                        <h5 class="mb-0">Addresses</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (mysqli_num_rows($address_result) > 0): ?>
                                            <?php while ($address = mysqli_fetch_assoc($address_result)): ?>
                                                <div class="mb-3 border-bottom pb-3">
                                                    <h6><?php echo $address['user_address_name']; ?></h6>
                                                    <p class="mb-1">
                                                        <?php echo $address['address_location']; ?><br>
                                                        Number: <?php echo $address['address_number']; ?><br>
                                                        <?php echo $address['address_city']; ?>, <?php echo $address['address_postal_code']; ?><br>
                                                        <?php echo $address['address_country']; ?>
                                                    </p>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <p class="text-center my-3">No saved addresses</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- User List View -->
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">User List</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Contact</th>
                                                <th>Orders</th>
                                                <th>Spent</th>
                                                <th>Time Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($users_result) > 0): ?>
                                                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                                    <tr>
                                                        <td>#<?php echo $user['user_id']; ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="<?php echo !empty($user['user_imgprofile']) ? $user['user_imgprofile'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['user_name']) . '&size=40&background=random'; ?>" alt="<?php echo $user['user_name']; ?>" class="user-avatar me-2">
                                                                <div>
                                                                    <h6 class="mb-0"><?php echo $user['user_name']; ?></h6>
                                                                    <small class="text-muted"><?php echo $user['user_email']; ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if ($user['user_phone_number']): ?>
                                                                <?php echo $user['user_phone_number']; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not provided</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $user['order_count'] ?? 0; ?></td>
                                                        <td>$ <?php echo number_format($user['total_spent'] ?? 0, 0, ',', '.'); ?></td>
                                                        <td><?php echo $user['time_created'] ?? 'N/A'; ?></td>
                                                        <td>
                                                            <a href="users.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-3">No users found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 