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

// Handle order deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $order_id = $_GET['delete'];
    
    // Check if order exists
    $check_sql = "SELECT order_id FROM orders WHERE order_id = $order_id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // First delete from order_product
            $delete_items_sql = "DELETE FROM order_product WHERE order_id = $order_id";
            if (!mysqli_query($conn, $delete_items_sql)) {
                throw new Exception("Error deleting order items: " . mysqli_error($conn));
            }
            
            // Delete the order itself
            $delete_order_sql = "DELETE FROM orders WHERE order_id = $order_id";
            if (!mysqli_query($conn, $delete_order_sql)) {
                throw new Exception("Error deleting order: " . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            $success_message = "Order #$order_id has been deleted successfully!";
        } catch (Exception $e) {
            // Rollback in case of error
            mysqli_rollback($conn);
            $error_message = $e->getMessage();
        }
    } else {
        $error_message = "Order not found";
    }
}

// Execute the SQL to modify order_status enum to include 'Processing'
$alter_orders_sql = "ALTER TABLE orders CHANGE order_status order_status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Pending'";
if (mysqli_query($conn, $alter_orders_sql)) {
    $sql_success = "Order status options updated successfully";
} else {
    $sql_error = "Error updating order status options: " . mysqli_error($conn);
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$result = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($result);

// Handle order status update
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE orders SET order_status = '$new_status' WHERE order_id = '$order_id'";
    if (mysqli_query($conn, $update_sql)) {
        $success_message = "Order status updated successfully!";
    } else {
        $error_message = "Error updating order status: " . mysqli_error($conn);
    }
}

// Get order details if order_id is set
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    
    // Get order information
    $order_sql = "SELECT o.*, u.user_name, u.user_email, u.user_phone_number FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  WHERE o.order_id = '$order_id'";
    $order_result = mysqli_query($conn, $order_sql);

    if ($order_result === false) {
        $error_message = "Error fetching order: " . mysqli_error($conn);
    } else {
        $order = mysqli_fetch_assoc($order_result);
        
        if (!$order) {
            $error_message = "Order not found";
        } else {
            // Get order items
            $items_sql = "SELECT op.*, p.product_name, p.product_image_url, p.product_price FROM order_product op 
                          JOIN product p ON op.product_id = p.product_id 
                          WHERE op.order_id = '$order_id'";
            $items_result = mysqli_query($conn, $items_sql);
            
            if ($items_result === false) {
                $items_error = "Error fetching order items: " . mysqli_error($conn);
            }
            
            // Get shipping address
            $address_sql = "SELECT * FROM address WHERE user_id = '{$order['user_id']}' AND user_address_name = '{$order['address_id']}'";
            $address_result = mysqli_query($conn, $address_sql);

            // Check if query failed and handle error
            if ($address_result === false) {
                $address_error = "Error fetching address: " . mysqli_error($conn);
                $address = null;
            } else {
                $address = mysqli_fetch_assoc($address_result);
            }
        }
    }
} else {
    // Get all orders with pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Search functionality
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $search_condition = '';
    if (!empty($search)) {
        $search_condition = " AND (o.order_id LIKE '%$search%' OR u.user_name LIKE '%$search%' OR u.user_email LIKE '%$search%')";
    }
    
    // Filter by status
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $status_condition = '';
    if (!empty($status_filter)) {
        $status_condition = " AND o.order_status = '$status_filter'";
    }
    
    // Count total orders
    $count_sql = "SELECT COUNT(*) as total FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  WHERE 1=1 $search_condition $status_condition";
    $count_result = mysqli_query($conn, $count_sql);
    $total_orders = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_orders / $limit);
    
    // Get orders
    $orders_sql = "SELECT o.*, u.user_name, COUNT(op.order_item_id) as total_items 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.user_id 
                  LEFT JOIN order_product op ON o.order_id = op.order_id 
                  WHERE 1=1 $search_condition $status_condition
                  GROUP BY o.order_id
                  ORDER BY o.order_date DESC 
                  LIMIT $offset, $limit";
    $orders_result = mysqli_query($conn, $orders_sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Tech Sphere Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0167F3;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #e9ecef;
            --shadow-sm: 0 .125rem .25rem rgba(0,0,0,.075);
            --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
            --border-radius: 0.375rem;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f7fa;
        }
        
        .sidebar {
            min-height: 100vh;
            box-shadow: var(--shadow);
            background-color: var(--dark-color);
            position: sticky;
            top: 0;
        }
        
        .sidebar-link {
            color: #ffffff;
            padding: 15px 20px;
            display: block;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
        }
        
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding-left: 25px;
        }
        
        .sidebar-link.active {
            background-color: var(--primary-color);
            color: white;
            border-left: 4px solid #fff;
        }
        
        .sidebar-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            padding: 25px;
        }
        
        .admin-header {
            background-color: var(--light-color);
            padding: 15px 25px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 25px;
            box-shadow: var(--shadow-sm);
        }
        
        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .product-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            padding: 3px;
            background-color: white;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .order-detail-card {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .order-detail-card:hover {
            box-shadow: var(--shadow);
        }
        
        .order-detail-card .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }
        
        .order-detail-card .card-body {
            padding: 20px;
        }
        
        .order-summary-value {
            font-weight: 600;
            float: right;
            color: var(--primary-color);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            background-color: var(--light-color);
            font-weight: 600;
            color: var(--dark-color);
            border-bottom-width: 1px;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .btn {
            font-weight: 500;
            padding: 0.375rem 1rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(1, 103, 243, 0.25);
        }
        
        .alert {
            border-radius: var(--border-radius);
            border: none;
            padding: 15px 20px;
        }
        
        .search-form .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .search-form .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        .status-filter-btn {
            margin-right: 8px;
            margin-bottom: 8px;
            border-radius: 20px;
            font-size: 0.85rem;
            padding: 0.3rem 0.8rem;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: static;
                min-height: auto;
            }
            .content {
                padding: 15px;
            }
            .order-detail-card {
                margin-bottom: 15px;
            }
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
                <a href="orders.php" class="sidebar-link active">
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
                    <h4 class="mb-0">Order Management</h4>
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
                    
                    <?php if (isset($items_error)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $items_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($address_error)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $address_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['id']) && isset($order)): ?>
                        <!-- Order Detail View -->
                        <div class="mb-4">
                            <a href="orders.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to All Orders
                            </a>
                        </div>
                        
                        <div class="row">
                            <!-- Order Summary -->
                            <div class="col-md-4 mb-4">
                                <div class="order-detail-card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Order Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Order #<?php echo $order['order_id']; ?></p>
                                        <p>Date: <?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></p>
                                        <p>
                                            Status: 
                                            <span class="badge bg-<?php 
                                                echo $order['order_status'] == 'Delivered' ? 'success' : 
                                                    ($order['order_status'] == 'Pending' ? 'warning' : 
                                                        ($order['order_status'] == 'Shipped' ? 'primary' : 'danger')); 
                                            ?>"><?php echo $order['order_status']; ?></span>
                                        </p>
                                        <p>Payment Method: <?php echo $order['order_payment_method']; ?></p>
                                        
                                        <hr>
                                        
                                        <p>Items Total <span class="order-summary-value">Rp <?php echo number_format($order['order_subtotal'], 0, ',', '.'); ?></span></p>
                                        <p>Shipping <span class="order-summary-value">Rp <?php echo number_format($order['order_shipping'], 0, ',', '.'); ?></span></p>
                                        <p>Tax <span class="order-summary-value">Rp <?php echo number_format($order['order_tax'], 0, ',', '.'); ?></span></p>
                                        <p class="fw-bold">Total Amount <span class="order-summary-value">Rp <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></span></p>
                                        
                                        <form method="post" class="mt-4">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <div class="mb-3">
                                                <label for="new_status" class="form-label">Update Order Status</label>
                                                <select class="form-select" id="new_status" name="new_status" required>
                                                    <option value="">Select Status</option>
                                                    <option value="Pending" <?php echo $order['order_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Processing" <?php echo $order['order_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                    <option value="Shipped" <?php echo $order['order_status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                    <option value="Delivered" <?php echo $order['order_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="Cancelled" <?php echo $order['order_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </div>
                                            <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Info -->
                            <div class="col-md-4 mb-4">
                                <div class="order-detail-card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Customer Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <h6>Customer Details</h6>
                                        <p><i class="fas fa-user me-2"></i> <?php echo $order['user_name']; ?></p>
                                        <p><i class="fas fa-envelope me-2"></i> <?php echo $order['user_email']; ?></p>
                                        <p><i class="fas fa-phone me-2"></i> <?php echo $order['user_phone_number']; ?></p>
                                        
                                        <!-- Shipping Address -->
                                        <div class="order-detail-card mb-4">
                                            <div class="card-header bg-white">
                                                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Shipping Address</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (isset($address) && $address): ?>
                                                    <p>
                                                        <strong><?php echo htmlspecialchars($order['user_name']); ?></strong><br>
                                                        <?php echo htmlspecialchars($address['address_location']); ?>, 
                                                        No. <?php echo htmlspecialchars($address['address_number']); ?><br>
                                                        <?php echo htmlspecialchars($address['address_city']); ?>, 
                                                        <?php echo htmlspecialchars($address['address_country']); ?>, 
                                                        <?php echo htmlspecialchars($address['address_postal_code']); ?>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="text-muted">
                                                        <i class="fas fa-info-circle me-2"></i> Address information not available or has been removed.
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Notes -->
                            <div class="col-md-4 mb-4">
                                <div class="order-detail-card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="mb-0">Order Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($order['order_notes'])): ?>
                                            <p><?php echo nl2br($order['order_notes']); ?></p>
                                        <?php else: ?>
                                            <p class="text-muted">No notes for this order</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mb-4 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOrderModal">
                                <i class="fas fa-trash me-2"></i> Delete Order
                            </button>
                            
                            <!-- Delete Order Modal -->
                            <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteOrderModalLabel">Delete Order</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete Order #<?php echo $order['order_id']; ?>?</p>
                                            <p class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i> This action cannot be undone! All order data will be permanently deleted.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="orders.php?delete=<?php echo $order['order_id']; ?>" class="btn btn-danger">Delete Order</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Order Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (isset($items_result) && mysqli_num_rows($items_result) > 0): ?>
                                                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                                    <tr>
                                                        <td class="d-flex align-items-center">
                                                            <img src="<?php echo !empty($item['product_image_url']) ? $item['product_image_url'] : 'https://placehold.co/50x50'; ?>" class="product-thumbnail me-3" alt="Product Image">
                                                            <div>
                                                                <h6 class="mb-0"><?php echo $item['product_name']; ?></h6>
                                                                <small class="text-muted">SKU: <?php echo $item['product_id']; ?></small>
                                                            </div>
                                                        </td>
                                                        <td>Rp <?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?></td>
                                                        <td><?php echo $item['order_quantity']; ?></td>
                                                        <td>Rp <?php echo number_format($item['price_at_purchase'] * $item['order_quantity'], 0, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No items found for this order</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Orders List View -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">All Orders</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <form method="get" class="d-flex">
                                            <input type="text" class="form-control me-2" placeholder="Search orders..." name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                            <button type="submit" class="btn btn-primary">Search</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <!-- Status filter buttons -->
                                <div class="mb-3">
                                    <a href="orders.php" class="btn btn-sm <?php echo !isset($_GET['status']) || $_GET['status'] === '' ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">All</a>
                                    <a href="orders.php?status=Pending<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="btn btn-sm <?php echo isset($_GET['status']) && $_GET['status'] === 'Pending' ? 'btn-warning' : 'btn-outline-warning'; ?> me-2">Pending</a>
                                    <a href="orders.php?status=Processing<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="btn btn-sm <?php echo isset($_GET['status']) && $_GET['status'] === 'Processing' ? 'btn-info' : 'btn-outline-info'; ?> me-2">Processing</a>
                                    <a href="orders.php?status=Shipped<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="btn btn-sm <?php echo isset($_GET['status']) && $_GET['status'] === 'Shipped' ? 'btn-primary' : 'btn-outline-primary'; ?> me-2">Shipped</a>
                                    <a href="orders.php?status=Delivered<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="btn btn-sm <?php echo isset($_GET['status']) && $_GET['status'] === 'Delivered' ? 'btn-success' : 'btn-outline-success'; ?> me-2">Delivered</a>
                                    <a href="orders.php?status=Cancelled<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="btn btn-sm <?php echo isset($_GET['status']) && $_GET['status'] === 'Cancelled' ? 'btn-danger' : 'btn-outline-danger'; ?>">Cancelled</a>
                                </div>
                                
                                <?php if (mysqli_num_rows($orders_result) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer</th>
                                                    <th>Date</th>
                                                    <th>Total</th>
                                                    <th>Items</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                                    <tr>
                                                        <td>#<?php echo $order['order_id']; ?></td>
                                                        <td><?php echo $order['user_name']; ?></td>
                                                        <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                                        <td>Rp. <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></td>
                                                        <td><?php echo $order['total_items']; ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $order['order_status'] == 'Delivered' ? 'success' : 
                                                                    ($order['order_status'] == 'Pending' ? 'warning' : 
                                                                        ($order['order_status'] == 'Shipped' ? 'primary' : 
                                                                            ($order['order_status'] == 'Processing' ? 'info' : 'danger'))); 
                                                            ?>"><?php echo $order['order_status']; ?></span>
                                                        </td>
                                                        <td>
                                                            <a href="orders.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $order['order_id']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                            
                                                            <!-- Delete Modal -->
                                                            <div class="modal fade" id="deleteModal<?php echo $order['order_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $order['order_id']; ?>" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="deleteModalLabel<?php echo $order['order_id']; ?>">Delete Order</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <p>Are you sure you want to delete Order #<?php echo $order['order_id']; ?>?</p>
                                                                            <p><strong>Customer:</strong> <?php echo $order['user_name']; ?></p>
                                                                            <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($order['order_date'])); ?></p>
                                                                            <p><strong>Total:</strong> Rp. <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></p>
                                                                            <p class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i> This action cannot be undone! All order data will be permanently deleted.</p>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            <a href="orders.php?delete=<?php echo $order['order_id']; ?>" class="btn btn-danger">Delete Order</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Pagination -->
                                    <?php if ($total_pages > 1): ?>
                                        <nav aria-label="Orders pagination" class="mt-4">
                                            <ul class="pagination justify-content-center">
                                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>">Previous</a>
                                                </li>
                                                
                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>"><?php echo $i; ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <h5>No orders found</h5>
                                        <p class="text-muted">
                                            <?php if (!empty($search)): ?>
                                                No orders match your search criteria. Try different keywords.
                                            <?php elseif (!empty($status_filter)): ?>
                                                No orders with status: <?php echo $status_filter; ?>
                                            <?php else: ?>
                                                No orders have been placed yet.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 