<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: login.php?redirect=orders.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($user_stmt);

// Pagination
$limit = 10; // Records per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get total orders count for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $user_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);
mysqli_stmt_close($count_stmt);

// Helper function to get valid image URL
function getValidImageUrl($url) {
    if(empty($url)) {
        return "assets/images/products/default.jpg";
    }
    
    // Remove '../' from the beginning if it exists
    if(strpos($url, '../') === 0) {
        $url = substr($url, 3);
    }
    
    // Check if URL starts with http or https
    if(strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        return $url;
    }
    
    return $url;
}

// Get order details if order_id is set
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    
    // Verify the order belongs to the logged-in user
    $verify_sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
    $verify_stmt = mysqli_prepare($conn, $verify_sql);
    mysqli_stmt_bind_param($verify_stmt, "ii", $order_id, $user_id);
    mysqli_stmt_execute($verify_stmt);
    $verify_result = mysqli_stmt_get_result($verify_stmt);
    
    if (mysqli_num_rows($verify_result) == 0) {
        // Order not found or doesn't belong to this user
        header("Location: orders.php");
        exit();
    }
    
    // Get order information
    $order = mysqli_fetch_assoc($verify_result);
    mysqli_stmt_close($verify_stmt);
    
    // Get order items
    $items_sql = "SELECT op.*, p.product_name, p.product_image_url 
                 FROM order_product op 
                 JOIN product p ON op.product_id = p.product_id 
                 WHERE op.order_id = ?";
    $items_stmt = mysqli_prepare($conn, $items_sql);
    mysqli_stmt_bind_param($items_stmt, "i", $order_id);
    mysqli_stmt_execute($items_stmt);
    $items_result = mysqli_stmt_get_result($items_stmt);
    $order_items = [];
    while ($item = mysqli_fetch_assoc($items_result)) {
        $order_items[] = $item;
    }
    mysqli_stmt_close($items_stmt);
} else {
    // Get user orders with pagination
    $orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ?, ?";
    $orders_stmt = mysqli_prepare($conn, $orders_sql);
    mysqli_stmt_bind_param($orders_stmt, "iii", $user_id, $offset, $limit);
    mysqli_stmt_execute($orders_stmt);
    $orders_result = mysqli_stmt_get_result($orders_stmt);
    $orders = [];
    while ($order = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $order;
        
        // Get first product for each order (for display in the order list)
        $first_product_sql = "SELECT op.*, p.product_name, p.product_image_url 
                             FROM order_product op 
                             JOIN product p ON op.product_id = p.product_id 
                             WHERE op.order_id = ? 
                             LIMIT 1";
        $first_product_stmt = mysqli_prepare($conn, $first_product_sql);
        mysqli_stmt_bind_param($first_product_stmt, "i", $order['order_id']);
        mysqli_stmt_execute($first_product_stmt);
        $first_product_result = mysqli_stmt_get_result($first_product_stmt);
        $first_product = mysqli_fetch_assoc($first_product_result);
        
        // Count total items in the order
        $count_items_sql = "SELECT COUNT(*) as total_items FROM order_product WHERE order_id = ?";
        $count_items_stmt = mysqli_prepare($conn, $count_items_sql);
        mysqli_stmt_bind_param($count_items_stmt, "i", $order['order_id']);
        mysqli_stmt_execute($count_items_stmt);
        $count_items_result = mysqli_stmt_get_result($count_items_stmt);
        $total_items = mysqli_fetch_assoc($count_items_result)['total_items'];
        
        // Add to the order array
        $last_index = count($orders) - 1;
        $orders[$last_index]['first_product'] = $first_product;
        $orders[$last_index]['total_items'] = $total_items;
        
        mysqli_stmt_close($first_product_stmt);
        mysqli_stmt_close($count_items_stmt);
    }
    mysqli_stmt_close($orders_stmt);
}

// Close connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>My Orders - TechSphere</title>
    <meta name="description" content="TechSphere Order History" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <style>
        .orders-section {
            padding: 60px 0;
        }
        
        .order-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        
        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }
        
        .order-id {
            font-size: 18px;
            font-weight: 600;
            color: #0167F3;
        }
        
        .order-date {
            color: #777;
            font-size: 14px;
        }
        
        .order-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
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
        
        .order-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-product-preview {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .order-product-image {
            width: 70px;
            height: 70px;
            padding: 5px;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-right: 15px;
            background-color: #fff;
        }
        
        .order-product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .order-product-name {
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        
        .order-product-count {
            color: #777;
            font-size: 13px;
            font-style: italic;
        }
        
        .order-info {
            flex: 1;
            margin-top: 15px;
        }
        
        .order-info-item {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .order-info-item i {
            margin-right: 8px;
            color: #0167F3;
            width: 18px;
            text-align: center;
        }
        
        .order-info-item strong {
            display: inline-block;
            width: 120px;
            color: #666;
        }
        
        .order-total {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .order-actions {
            text-align: right;
        }
        
        .order-detail-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }
        
        .order-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 25px;
        }
        
        .order-detail-id {
            font-size: 22px;
            font-weight: 600;
            color: #0167F3;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-image {
            width: 80px;
            height: 80px;
            padding: 5px;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-right: 15px;
            background-color: #fff;
        }
        
        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .order-item-price {
            color: #0167F3;
            font-weight: 500;
        }
        
        .order-item-quantity {
            color: #777;
            font-size: 14px;
        }
        
        .order-summary {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .order-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-summary-item.total {
            font-size: 18px;
            font-weight: 600;
            padding-top: 10px;
            border-top: 1px solid #eee;
            margin-top: 10px;
        }
        
        .pagination-container {
            margin-top: 30px;
        }
        
        .back-to-orders {
            margin-bottom: 20px;
        }
        
        @media (max-width: 767px) {
            .order-header, .order-details {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-status {
                margin-top: 10px;
            }
            
            .order-actions {
                margin-top: 15px;
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <!--[if lte IE 9]>
      <p class="browserupgrade">
        You are using an <strong>outdated</strong> browser. Please
        <a href="https://browsehappy.com/">upgrade your browser</a> to improve
        your experience and security.
      </p>
    <![endif]-->

    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- /End Preloader -->

    <!-- Start Header Area -->
    <?php include 'includes/header.php'; ?>
    <!-- End Header Area -->

    <!-- Start Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="breadcrumbs-content">
                        <?php if (isset($_GET['id'])): ?>
                            <h1 class="page-title">Order Details #<?php echo $order_id; ?></h1>
                        <?php else: ?>
                            <h1 class="page-title">My Orders</h1>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                        <?php if (isset($_GET['id'])): ?>
                            <li><a href="orders.php">My Orders</a></li>
                            <li>Order Details</li>
                        <?php else: ?>
                            <li>My Orders</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Orders Section -->
    <section class="orders-section section">
        <div class="container">
            <?php if (isset($_GET['id'])): ?>
                <!-- Order Detail View -->
                <div class="row">
                    <div class="col-12">
                        <div class="back-to-orders">
                            <a href="orders.php" class="btn btn-outline-primary"><i class="lni lni-arrow-left me-2"></i>Back to Orders</a>
                        </div>
                        
                        <div class="order-detail-card">
                            <div class="order-detail-header">
                                <div>
                                    <div class="order-detail-id">Order #<?php echo $order['order_id']; ?></div>
                                    <div class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                                </div>
                                <div>
                                    <span class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                                        <?php echo $order['order_status']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <h4>Order Items</h4>
                            <div class="order-items">
                                <?php foreach ($order_items as $item): ?>
                                    <div class="order-item">
                                        <div class="order-item-image">
                                            <img src="<?php echo getValidImageUrl($item['product_image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        </div>
                                        <div class="order-item-info">
                                            <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                            <div class="order-item-quantity">Quantity: <?php echo $item['order_quantity']; ?></div>
                                            <div class="order-item-price">Rp <?php echo number_format($item['price_at_purchase'], 0, ',', '.'); ?></div>
                                        </div>
                                        <div class="order-item-total">
                                            Rp <?php echo number_format($item['price_at_purchase'] * $item['order_quantity'], 0, ',', '.'); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-summary">
                                <div class="order-summary-item">
                                    <span>Subtotal</span>
                                    <span>Rp <?php echo number_format($order['order_subtotal'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="order-summary-item">
                                    <span>Shipping</span>
                                    <span>Rp <?php echo number_format($order['order_shipping'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="order-summary-item">
                                    <span>Tax</span>
                                    <span>Rp <?php echo number_format($order['order_tax'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="order-summary-item total">
                                    <span>Total</span>
                                    <span>Rp <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($order['order_notes'])): ?>
                                <div class="mt-4">
                                    <h5>Order Notes</h5>
                                    <p><?php echo htmlspecialchars($order['order_notes']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($order['order_status'] == 'Pending' || $order['order_status'] == 'Processing'): ?>
                                <div class="mt-4">
                                    <h5>Payment Information</h5>
                                    <?php if ($order['order_payment_method'] == 'Bank Transfer'): ?>
                                        <div class="alert alert-info">
                                            <h6>Bank Transfer Instructions</h6>
                                            <p>Please transfer the exact amount to the following bank account:</p>
                                            <ul>
                                                <li><strong>Bank:</strong> Bank Central Asia (BCA)</li>
                                                <li><strong>Account Number:</strong> 123-456-7890</li>
                                                <li><strong>Account Name:</strong> TechSphere</li>
                                                <li><strong>Amount:</strong> Rp <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></li>
                                            </ul>
                                            <p class="mb-0">Please include your Order ID (<?php echo $order['order_id']; ?>) in the transfer description.</p>
                                        </div>
                                    <?php elseif ($order['order_payment_method'] == 'Cash on Delivery'): ?>
                                        <div class="alert alert-info">
                                            <h6>Cash on Delivery</h6>
                                            <p>Please prepare the exact amount of Rp <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?> when our courier arrives.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Orders List View -->
                <div class="row">
                    <div class="col-12">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <h4>You haven't placed any orders yet</h4>
                                <p class="text-muted">Browse our products and place your first order!</p>
                                <a href="index.php" class="btn btn-primary mt-3">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                                        <div class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                                        <span class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                                            <?php echo $order['order_status']; ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Product preview section -->
                                    <?php if (isset($order['first_product']) && $order['first_product']): ?>
                                    <div class="order-product-preview">
                                        <div class="order-product-image">
                                            <img src="<?php echo getValidImageUrl($order['first_product']['product_image_url']); ?>" alt="<?php echo htmlspecialchars($order['first_product']['product_name']); ?>">
                                        </div>
                                        <div>
                                            <div class="order-product-name"><?php echo htmlspecialchars($order['first_product']['product_name']); ?></div>
                                            <div class="order-product-count">
                                                <?php if ($order['total_items'] > 1): ?>
                                                    +<?php echo $order['total_items'] - 1; ?> other items
                                                <?php else: ?>
                                                    1 item
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="order-details">
                                        <div class="order-info">
                                            <div class="order-info-item">
                                                <i class="lni lni-credit-cards"></i>
                                                <strong>Payment Method:</strong> <?php echo $order['order_payment_method']; ?>
                                            </div>
                                            <div class="order-info-item">
                                                <i class="lni lni-wallet"></i>
                                                <strong>Total:</strong> <span class="order-total">Rp <?php echo number_format($order['order_total_purchase'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                        <div class="order-actions">
                                            <a href="orders.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination-container">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo ($page <= 1) ? '#' : 'orders.php?page=' . ($page - 1); ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="orders.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="<?php echo ($page >= $total_pages) ? '#' : 'orders.php?page=' . ($page + 1); ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- End Orders Section -->

    <!-- Start Footer Area -->
    <?php include 'includes/footer.php'; ?>
    <!--/ End Footer Area -->

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html> 