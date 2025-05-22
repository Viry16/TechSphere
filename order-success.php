<?php
// Start session
session_start();

// Check if order success message exists
if (!isset($_SESSION['order_success']) || !isset($_SESSION['order_id'])) {
    header("Location: index.php");
    exit();
}

// Get order ID
$order_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['order_id'];

// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch order details
$order_sql = "SELECT * FROM `order` WHERE order_id = $order_id";
$order_result = mysqli_query($conn, $order_sql);

if (mysqli_num_rows($order_result) == 0) {
    header("Location: index.php");
    exit();
}

$order = mysqli_fetch_assoc($order_result);

// Fetch order items
$items_sql = "SELECT oi.*, p.product_name, p.product_image_url 
              FROM order_item oi 
              JOIN product p ON oi.product_id = p.product_id 
              WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_sql);

// Payment instructions based on payment method
$payment_instructions = '';
if ($order['payment_method'] == 'bank_transfer') {
    $payment_instructions = '
        <div class="payment-info">
            <h4>Payment Instructions</h4>
            <p>Please transfer the total amount to the following bank account:</p>
            <ul>
                <li>Bank: Bank Mandiri</li>
                <li>Account Number: 123-456-7890</li>
                <li>Account Name: PT TechSphere Indonesia</li>
                <li>Amount: Rp ' . number_format($order['order_total'] * 15000, 0, ',', '.') . '</li>
            </ul>
            <p>After making the payment, please confirm by sending the transfer proof to email: payment@techsphere.id or WhatsApp: +62 812-3456-7890 with your order number.</p>
        </div>';
} else if ($order['payment_method'] == 'cash_on_delivery') {
    $payment_instructions = '
        <div class="payment-info">
            <h4>Payment Instructions</h4>
            <p>You have chosen Cash on Delivery (COD) as your payment method.</p>
            <p>Please prepare cash amount of Rp ' . number_format($order['order_total'] * 15000, 0, ',', '.') . ' when your order arrives at your delivery address.</p>
        </div>';
}

// Clear the session order success message and ID
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Order Success - TechSphere</title>
    <meta name="description" content="Order Success - TechSphere" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <style>
        .order-success {
            padding: 60px 0;
        }
        
        .success-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #4CAF50;
            color: #fff;
            font-size: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
        
        .success-heading {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .success-message {
            font-size: 16px;
            color: #666;
            max-width: 600px;
            margin: 0 auto 25px;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #0167F3;
        }
        
        .order-date {
            font-size: 14px;
            color: #777;
            margin-bottom: 25px;
        }
        
        .order-details {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .order-details .title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-items {
            margin-bottom: 30px;
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
            width: 60px;
            height: 60px;
            border-radius: 5px;
            overflow: hidden;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        
        .order-item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-size: 15px;
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }
        
        .order-item-info {
            font-size: 13px;
            color: #777;
        }
        
        .order-item-price {
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }
        
        .order-summary {
            margin-top: 30px;
        }
        
        .order-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
            color: #777;
        }
        
        .order-summary-row.final {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .order-summary-row.final .value {
            color: #0167F3;
        }
        
        .shipping-info {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .shipping-info .title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .shipping-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .shipping-info-item {
            margin-bottom: 15px;
        }
        
        .shipping-info-item strong {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .shipping-info-item span {
            font-size: 14px;
            color: #666;
        }
        
        .payment-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .payment-info h4 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .payment-info ul {
            padding-left: 20px;
            margin-bottom: 15px;
        }
        
        .payment-info li {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .action-buttons {
            margin-top: 30px;
        }
        
        .btn-continue-shopping, .btn-track-order {
            padding: 10px 20px;
            font-weight: 600;
            margin: 0 10px;
        }
        
        @media (max-width: 767px) {
            .success-card {
                padding: 25px 15px;
            }
            
            .order-details, .shipping-info {
                padding: 20px 15px;
            }
            
            .success-heading {
                font-size: 24px;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-continue-shopping, .btn-track-order {
                margin: 0;
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
        <?php include 'includes/footer.php'; ?>
    <!-- End Header Area -->

    <!-- Start Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="breadcrumbs-content">
                        <h1 class="page-title">Order Success</h1>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <li>Order Success</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Order Success Area -->
    <section class="order-success section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="success-card">
                        <div class="success-icon">
                            <i class="lni lni-checkmark"></i>
                        </div>
                        <h1 class="success-heading">Order Successfully Created!</h1>
                        <p class="success-message">Thank you for your order. Your order has been successfully created and is being processed. You will receive a confirmation email shortly.</p>
                        <div class="order-number">Order Number: #<?php echo $order_id; ?></div>
                        <div class="order-date">Order Date: <?php echo date('F d Y, H:i', strtotime($order['order_date'])); ?></div>
                        
                        <div class="action-buttons">
                            <a href="index.php" class="btn btn-outline-primary btn-continue-shopping">Continue Shopping</a>
                            <!-- <a href="order-tracking.php?id=<?php echo $order_id; ?>" class="btn btn-primary btn-track-order">Track Order</a> -->
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8 col-12">
                    <div class="order-details">
                        <div class="title">Order Details</div>
                        
                        <div class="order-items">
                            <?php 
                            $subtotal = 0;
                            while ($item = mysqli_fetch_assoc($items_result)): 
                                $subtotal += $item['order_item_subtotal'];
                            ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <img src="<?php echo !empty($item['product_image_url']) ? $item['product_image_url'] : 'assets/images/products/default.jpg'; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                </div>
                                <div class="order-item-details">
                                    <h4 class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <div class="order-item-info">Quantity: <?php echo $item['order_item_quantity']; ?></div>
                                </div>
                                <div class="order-item-price">
                                    $<?php echo number_format($item['order_item_subtotal'], 2, '.', ','); ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="order-summary">
                            <div class="order-summary-row">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($subtotal, 2, '.', ','); ?></span>
                            </div>
                            <?php 
                            $tax = $subtotal * 0.1;
                            $shipping = 0;
                            $total = $subtotal + $tax + $shipping;
                            ?>
                            <div class="order-summary-row">
                                <span>Shipping</span>
                                <span>$<?php echo number_format($shipping, 2, '.', ','); ?></span>
                            </div>
                            <div class="order-summary-row">
                                <span>Tax (10%)</span>
                                <span>$<?php echo number_format($tax, 2, '.', ','); ?></span>
                            </div>
                            <div class="order-summary-row final">
                                <span>Total</span>
                                <span class="value">$<?php echo number_format($total, 2, '.', ','); ?></span>
                            </div>
                        </div>
                        
                        <?php echo $payment_instructions; ?>
                    </div>
                </div>
                
                <div class="col-lg-4 col-12">
                    <div class="shipping-info">
                        <div class="title">Shipping Information</div>
                        
                        <ul class="shipping-info-list">
                            <li class="shipping-info-item">
                                <strong>Order Status</strong>
                                <span>
                                    <?php 
                                    $status = $order['order_status'];
                                    $status_label = '';
                                    
                                    switch ($status) {
                                        case 'pending':
                                            $status_label = 'Pending Payment';
                                            break;
                                        case 'processing':
                                            $status_label = 'Processing';
                                            break;
                                        case 'shipped':
                                            $status_label = 'Shipped';
                                            break;
                                        case 'delivered':
                                            $status_label = 'Delivered';
                                            break;
                                        case 'cancelled':
                                            $status_label = 'Cancelled';
                                            break;
                                        default:
                                            $status_label = 'Pending Payment';
                                    }
                                    
                                    echo $status_label;
                                    ?>
                                </span>
                            </li>
                            <li class="shipping-info-item">
                                <strong>Recipient Name</strong>
                                <span><?php echo htmlspecialchars($order['shipping_name']); ?></span>
                            </li>
                            <li class="shipping-info-item">
                                <strong>Shipping Address</strong>
                                <span><?php echo htmlspecialchars($order['shipping_address']); ?></span>
                            </li>
                            <li class="shipping-info-item">
                                <strong>Email</strong>
                                <span><?php echo htmlspecialchars($order['shipping_email']); ?></span>
                            </li>
                            <li class="shipping-info-item">
                                <strong>Phone</strong>
                                <span><?php echo htmlspecialchars($order['shipping_phone']); ?></span>
                            </li>
                            <li class="shipping-info-item">
                                <strong>Payment Method</strong>
                                <span>
                                    <?php 
                                    $payment_method = $order['payment_method'];
                                    $payment_label = '';
                                    
                                    switch ($payment_method) {
                                        case 'bank_transfer':
                                            $payment_label = 'Bank Transfer';
                                            break;
                                        case 'cash_on_delivery':
                                            $payment_label = 'Cash on Delivery (COD)';
                                            break;
                                        default:
                                            $payment_label = 'Bank Transfer';
                                    }
                                    
                                    echo $payment_label;
                                    ?>
                                </span>
                            </li>
                            <?php if (!empty($order['order_notes'])): ?>
                            <li class="shipping-info-item">
                                <strong>Order Notes</strong>
                                <span><?php echo htmlspecialchars($order['order_notes']); ?></span>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Order Success Area -->

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
<?php
// Close database connection
mysqli_close($conn);
?> 