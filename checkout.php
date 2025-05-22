<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: login.php?redirect=checkout.php");
    exit();
}

// Include cart functions
require_once 'includes/cart_functions.php';

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

// Get cart contents
$cart_data = getCartContents($user_id);
$cart = $cart_data['items'];
$cart_total = $cart_data['total'];
$cart_count = $cart_data['count'];

// Redirect if cart is empty
if ($cart_count == 0) {
    header("Location: cart.php");
    exit();
}

// Get user addresses
$address_sql = "SELECT user_id, user_address_name, address_location, address_number, address_postal_code, address_city, address_country 
                FROM address 
                WHERE user_id = ?";
$address_stmt = mysqli_prepare($conn, $address_sql);

if ($address_stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($address_stmt, "i", $user_id);
mysqli_stmt_execute($address_stmt);
$address_result = mysqli_stmt_get_result($address_stmt);
$addresses = [];
while ($address = mysqli_fetch_assoc($address_result)) {
    $addresses[] = $address;
}
mysqli_stmt_close($address_stmt);

// Process checkout
$order_message = '';
$order_success = false;

if (isset($_POST['place_order'])) {
    $selected_address_name = isset($_POST['address_name']) ? mysqli_real_escape_string($conn, $_POST['address_name']) : '';
    $payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : '';
    $order_notes = isset($_POST['order_notes']) ? mysqli_real_escape_string($conn, $_POST['order_notes']) : '';
    
    // Validate inputs
    if (!empty($selected_address_name) && !empty($payment_method)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Calculate totals
            $subtotal = $cart_total;
            $shipping = 0; // Free shipping
            $tax = round($subtotal * 0.1, 2); // 10% tax
            $total = $subtotal + $shipping + $tax;
            
            // Insert order
            $order_sql = "INSERT INTO orders (user_id, order_date, order_subtotal, order_shipping, order_tax, order_total_purchase, order_payment_method, order_notes, address_id, order_status) 
                         VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            $order_stmt = mysqli_prepare($conn, $order_sql);

            if ($order_stmt === false) {
                throw new Exception("Error preparing order statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($order_stmt, "iddddsss", $user_id, $subtotal, $shipping, $tax, $total, $payment_method, $order_notes, $selected_address_name);

            if (!mysqli_stmt_execute($order_stmt)) {
                throw new Exception("Error executing order statement: " . mysqli_stmt_error($order_stmt));
            }
            
            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($order_stmt);
            
            // Insert order items
            foreach ($cart as $item) {
                $item_sql = "INSERT INTO order_product (order_id, product_id, order_quantity, price_at_purchase) 
                            VALUES (?, ?, ?, ?)";
                $item_stmt = mysqli_prepare($conn, $item_sql);
                mysqli_stmt_bind_param($item_stmt, "iiid", $order_id, $item['product_id'], $item['cart_product_quantity'], $item['product_price']);
                mysqli_stmt_execute($item_stmt);
                mysqli_stmt_close($item_stmt);
                
                // Update product stock
                $update_stock_sql = "UPDATE product SET product_stock_quantity = product_stock_quantity - ? WHERE product_id = ?";
                $update_stock_stmt = mysqli_prepare($conn, $update_stock_sql);
                mysqli_stmt_bind_param($update_stock_stmt, "ii", $item['cart_product_quantity'], $item['product_id']);
                mysqli_stmt_execute($update_stock_stmt);
                mysqli_stmt_close($update_stock_stmt);
            }
            
            // Clear cart
            clearCart($user_id);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $order_success = true;
            $order_message = "Order placed successfully! Your order number is #" . $order_id;
            
            // If payment is via Bank Transfer
            if ($payment_method == 'Bank Transfer') {
                // Insert payment entry
                $payment_sql = "INSERT INTO payment (order_id, payment_method, payment_amount, payment_status, payment_date) 
                               VALUES (?, ?, ?, 'Pending', NOW())";
                $payment_stmt = mysqli_prepare($conn, $payment_sql);
                mysqli_stmt_bind_param($payment_stmt, "isd", $order_id, $payment_method, $total);
                mysqli_stmt_execute($payment_stmt);
                mysqli_stmt_close($payment_stmt);
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $order_message = "Error processing order: " . $e->getMessage();
        }
    } else {
        $order_message = "Please select a shipping address and payment method.";
    }
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Checkout - TechSphere</title>
    <meta name="description" content="TechSphere Checkout" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <style>
        .checkout-section {
            padding: 60px 0;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .checkout-step {
            text-align: center;
            position: relative;
            flex: 1;
        }
        
        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background-color: #f0f0f0;
            border-radius: 50%;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .checkout-step.active .step-number {
            background-color: #0167F3;
            color: white;
        }
        
        .checkout-content {
            margin-bottom: 40px;
        }
        
        .checkout-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .checkout-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .checkout-card h4 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .address-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .address-card:hover {
            border-color: #0167F3;
        }
        
        .address-card.selected {
            border-color: #0167F3;
            background-color: rgba(1, 103, 243, 0.05);
        }
        
        .address-card .name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .new-address-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 120px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            color: #777;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .new-address-btn:hover {
            border-color: #0167F3;
            color: #0167F3;
        }
        
        .payment-method {
            margin-bottom: 15px;
        }
        
        .payment-label {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-label:hover {
            border-color: #0167F3;
        }
        
        .payment-label.selected {
            border-color: #0167F3;
            background-color: rgba(1, 103, 243, 0.05);
        }
        
        .payment-radio {
            margin-right: 10px;
        }
        
        .payment-details {
            padding: 15px;
            margin-top: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            display: none;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
        }
        
        .order-summary h4 {
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .order-product {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-product:last-child {
            border-bottom: none;
        }
        
        .order-product-image {
            width: 60px;
            margin-right: 15px;
        }
        
        .order-product-image img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        
        .order-product-info {
            flex: 1;
        }
        
        .order-product-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .order-product-price {
            color: #0167F3;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .order-product-quantity {
            color: #777;
            font-size: 14px;
        }
        
        .order-totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .order-total-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .order-total-item.final {
            font-size: 18px;
            font-weight: 600;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        .place-order-btn {
            margin-top: 20px;
            width: 100%;
            padding: 15px;
        }
        
        .order-success {
            text-align: center;
            padding: 50px 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        
        .order-success i {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 25px;
            display: block;
            animation: checkmark 0.8s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        
        @keyframes checkmark {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .order-success h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .order-success p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .order-actions {
            margin-top: 30px;
        }
        
        .order-actions .btn {
            padding: 12px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .order-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Responsive */
        @media (max-width: 767px) {
            .checkout-steps {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .checkout-step {
                width: 100%;
            margin-bottom: 15px;
                text-align: left;
                display: flex;
                align-items: center;
        }
        
            .step-number {
                margin-bottom: 0;
                margin-right: 10px;
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
                        <h1 class="page-title">Checkout</h1>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <li>Checkout</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Checkout Section -->
    <section class="checkout-section section">
        <div class="container">
            <?php if ($order_success): ?>
                <!-- Order Success Message -->
                <div class="order-success">
                    <i class="lni lni-checkmark-circle"></i>
                    <h2>Thank You for Your Order!</h2>
                    <p><?php echo $order_message; ?></p>
                    <?php if (isset($user['user_email']) && !empty($user['user_email'])): ?>
                    <p>We've sent an order confirmation email to <strong><?php echo $user['user_email']; ?></strong>.</p>
                    <?php else: ?>
                    <p>We've sent an order confirmation email to your registered email address.</p>
                    <?php endif; ?>
                    <div class="order-actions">
                        <a href="index.php" class="btn btn-outline-primary me-3"><i class="lni lni-home me-2"></i>Back to Home</a>
                        <a href="orders.php" class="btn btn-primary"><i class="lni lni-clipboard me-2"></i>View Orders</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Checkout Steps -->
                <div class="checkout-steps">
                    <div class="checkout-step active">
                        <div class="step-number">1</div>
                        <div class="step-name">Shipping</div>
                    </div>
                    <div class="checkout-step active">
                        <div class="step-number">2</div>
                        <div class="step-name">Payment</div>
                    </div>
                    <div class="checkout-step">
                        <div class="step-number">3</div>
                        <div class="step-name">Confirmation</div>
                </div>
            </div>
                
                <?php if (!empty($order_message)): ?>
                    <div class="alert alert-danger mb-4"><?php echo $order_message; ?></div>
            <?php endif; ?>
            
            <div class="row">
                    <!-- Checkout Form -->
                    <div class="col-lg-8 col-md-6 col-12">
                        <form method="post" action="checkout.php">
                            <div class="checkout-content">
                                <!-- Shipping Address Section -->
                                <div class="checkout-card">
                                    <h4><i class="lni lni-map-marker me-2"></i>Shipping Address</h4>
                                    
                        <div class="row">
                                        <?php if (count($addresses) > 0): ?>
                                            <?php foreach ($addresses as $address): ?>
                                                <div class="col-md-6 col-12 mb-3">
                                                    <div class="address-card <?php echo isset($_POST['address_name']) && $_POST['address_name'] == $address['user_address_name'] ? 'selected' : ''; ?>">
                                                        <input type="radio" 
                                                               name="address_name" 
                                                               id="address_<?php echo $address['user_id'] . '_' . htmlspecialchars($address['user_address_name']); ?>" 
                                                               value="<?php echo htmlspecialchars($address['user_address_name']); ?>" 
                                                               class="d-none" 
                                                               <?php echo isset($_POST['address_name']) && $_POST['address_name'] == $address['user_address_name'] ? 'checked' : ''; ?>>
                                                        <label for="address_<?php echo $address['user_id'] . '_' . htmlspecialchars($address['user_address_name']); ?>" class="d-block mb-0" style="cursor: pointer;">
                                                            <div class="name"><?php echo htmlspecialchars($address['user_address_name']); ?></div>
                                                            <div class="address">
                                                                <?php echo htmlspecialchars($address['address_location']); ?>, 
                                                                No. <?php echo htmlspecialchars($address['address_number']); ?>, 
                                                                <?php echo htmlspecialchars($address['address_city']); ?>, 
                                                                <?php echo htmlspecialchars($address['address_country']); ?>, 
                                                                <?php echo htmlspecialchars($address['address_postal_code']); ?>
                            </div>
                                                        </label>
                                </div>
                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="col-12">
                                                <div class="alert alert-warning">
                                                    You haven't added any addresses yet. Please add a shipping address to continue.
                                </div>
                            </div>
                                        <?php endif; ?>
                            
                            <div class="col-md-6 col-12">
                                            <a href="add_address.php?redirect=checkout.php" class="new-address-btn">
                                                <i class="lni lni-plus me-2"></i> Add New Address
                                            </a>
                                </div>
                                </div>
                            </div>
                            
                                <!-- Payment Method Section -->
                                <div class="checkout-card">
                                    <h4><i class="lni lni-credit-cards me-2"></i>Payment Method</h4>
                                    
                                    <div class="payment-methods">
                                        <div class="payment-method">
                                            <div class="payment-label <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == 'Bank Transfer' ? 'selected' : ''; ?>">
                                                <input type="radio" name="payment_method" id="payment_bank" value="Bank Transfer" class="payment-radio" <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] == 'Bank Transfer') ? 'checked' : ''; ?>>
                                                <label for="payment_bank" class="mb-0">Bank Transfer</label>
                                </div>
                                            <div class="payment-details" id="bank_details" style="display: <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] == 'Bank Transfer') ? 'block' : 'none'; ?>;">
                                                <p>Please transfer the exact amount to the following bank account:</p>
                                                <p><strong>Bank:</strong> Bank Central Asia (BCA)</p>
                                                <p><strong>Account Number:</strong> 123-456-7890</p>
                                                <p><strong>Account Name:</strong> TechSphere</p>
                                                <p class="mb-0 text-muted">* We will verify your payment and process your order once the transfer is confirmed.</p>
                                </div>
                            </div>
                            
                                        <div class="payment-method">
                                            <div class="payment-label <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] == 'Cash on Delivery' ? 'selected' : ''; ?>">
                                                <input type="radio" name="payment_method" id="payment_cod" value="Cash on Delivery" class="payment-radio" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Cash on Delivery') ? 'checked' : ''; ?>>
                                                <label for="payment_cod" class="mb-0">Cash on Delivery</label>
                                </div>
                                            <div class="payment-details" id="cod_details" style="display: <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Cash on Delivery') ? 'block' : 'none'; ?>;">
                                                <p>You can pay in cash when our delivery courier arrives at your delivery address.</p>
                                                <p class="mb-0 text-muted">* Please prepare the exact amount to help our delivery process.</p>
                                    </div>
                                        </div>
                                    </div>
                        </div>
                        
                                <!-- Order Notes Section -->
                                <div class="checkout-card">
                                    <h4><i class="lni lni-notepad me-2"></i>Order Notes</h4>
                            
                                    <div class="form-group">
                                        <textarea name="order_notes" class="form-control" rows="4" placeholder="Special instructions for delivery or product preparation..."><?php echo isset($_POST['order_notes']) ? htmlspecialchars($_POST['order_notes']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Place Order Button (Mobile View) -->
                            <div class="d-block d-md-none">
                                <button type="submit" name="place_order" class="btn btn-primary place-order-btn">Place Order</button>
                            </div>
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="order-summary">
                                <h4><i class="lni lni-cart me-2"></i>Order Summary</h4>
                                
                                <div class="order-products">
                                    <?php foreach ($cart as $item): ?>
                                        <div class="order-product">
                                            <div class="order-product-image">
                                                <img src="<?php echo getValidImageUrl($item['product_image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                            </div>
                                            <div class="order-product-info">
                                                <div class="order-product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                <div class="order-product-quantity">Qty: <?php echo $item['cart_product_quantity']; ?></div>
                                                <div class="order-product-price">Rp. <?php echo number_format($item['product_price'] * $item['cart_product_quantity'], 0, ',', '.'); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                        </div>
                    
                                <div class="order-totals">
                                    <div class="order-total-item">
                                        <span>Subtotal</span>
                                        <span>Rp. <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="order-total-item">
                                        <span>Shipping</span>
                                        <span>Free</span>
                </div>
                                    <div class="order-total-item">
                                        <span>Tax (10%)</span>
                                        <span>Rp. <?php echo number_format($cart_total * 0.1, 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="order-total-item final">
                                        <span>Total</span>
                                        <span>Rp. <?php echo number_format($cart_total + ($cart_total * 0.1), 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                                
                                <!-- Place Order Button (Desktop View) -->
                                <div class="d-none d-md-block">
                                    <button type="submit" name="place_order" class="btn btn-primary place-order-btn">Place Order</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- End Checkout Section -->

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
    <script>
        // Address selection
        document.querySelectorAll('.address-card').forEach(function(card) {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.address-card').forEach(function(c) {
                    c.classList.remove('selected');
                });
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });
            
            // Payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                // Hide all payment details
                document.querySelectorAll('.payment-details').forEach(function(details) {
                    details.style.display = 'none';
                });
                
                // Remove selected class from all payment labels
                document.querySelectorAll('.payment-label').forEach(function(label) {
                    label.classList.remove('selected');
                });
                
                // Show selected payment details
                const paymentId = this.id;
                const detailsId = paymentId.replace('payment_', '') + '_details';
                document.getElementById(detailsId).style.display = 'block';
                
                // Add selected class to payment label
                this.closest('.payment-label').classList.add('selected');
            });
        });
    </script>
</body>

</html>