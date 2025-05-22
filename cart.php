<?php
// Start session
session_start();

// Include cart functions
require_once 'includes/cart_functions.php';

// Function to get valid image URL
if (!function_exists('getValidImageUrl')) {
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
}

// Get cart contents
$cart = [];
$cart_total = 0;
$cart_count = 0;

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_data = getCartContents($user_id);
    $cart = $cart_data['items'];
    $cart_total = $cart_data['total'];
    $cart_count = $cart_data['count'];
}
?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Shopping Cart - TechSphere</title>
    <meta name="description" content="TechSphere Shopping Cart" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        .cart-single-list {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            position: relative;
            margin-bottom: 15px;
        }
        
        .cart-single-list:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .product-thumb {
            width: 80px;
            margin-right: 15px;
        }
        
        .product-thumb img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-title a {
            color: #333;
            font-weight: 600;
            text-decoration: none;
        }
        
        .product-title a:hover {
            color: #0167F3;
        }
        
        .price {
            color: #0167F3;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .product-quantity {
            margin: 0 20px;
        }
        
        .input-group {
            display: flex;
            align-items: center;
            max-width: 150px;
        }
        
        .button {
            background: #f9f9f9;
            border: 1px solid #eee;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .button:hover {
            background: #0167F3;
            color: white;
            border-color: #0167F3;
        }
        
        .quantity-input {
            width: 50px;
            height: 30px;
            text-align: center;
            border: 1px solid #eee;
            margin: 0 5px;
            border-radius: 3px;
        }
        
        .total-price {
            font-weight: 600;
            color: #0167F3;
            margin: 0 20px;
            min-width: 80px;
            text-align: right;
        }
        
        .remove-item {
            background: none;
            border: none;
            color: #ff5a5a;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }
        
        .remove-item:hover {
            color: #ff0000;
        }
        
        .cart-list-head {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: #f6f7fb;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .cart-list-title {
            flex: 1;
        }
        
        .cart-list-title.quantity {
            flex: 0 0 150px;
            text-align: center;
        }
        
        .cart-list-title.price {
            flex: 0 0 100px;
            text-align: right;
        }
        
        .empty-cart-message {
            padding: 30px;
            text-align: center;
            font-size: 18px;
            color: #777;
        }
        
        .cart-summary {
            background: #f6f7fb;
            padding: 30px;
            border-radius: 5px;
        }
        
        .cart-summary .summary-title {
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .cart-summary .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .cart-summary .total {
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e1e1e1;
        }
        
        .cart-button {
            margin-top: 20px;
        }
        
        /* Button styles */
        .btn-primary {
            background: #0167F3;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #0051C8;
        }
        
        .btn-outline {
            background: white;
            color: #0167F3;
            border: 1px solid #0167F3;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background: #f6f7fb;
        }
        
        @media (max-width: 767px) {
            .cart-list-head {
                display: none;
            }
            
            .cart-single-list {
                flex-wrap: wrap;
            }
            
            .product-thumb {
                width: 60px;
            }
            
            .product-info {
                flex: 1;
            }
            
            .product-quantity {
                width: 100%;
                margin: 10px 0;
                display: flex;
                align-items: center;
            }
            
            .product-quantity::before {
                content: "Quantity: ";
                margin-right: 10px;
            }
            
            .total-price {
                flex: 1;
                text-align: right;
                margin: 0;
            }
            
            .remove-item {
                position: absolute;
                top: 15px;
                right: 15px;
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
                        <h1 class="page-title">Shopping Cart</h1>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                        <li><a href="product-grids.php">Shop</a></li>
                        <li>Cart</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Shopping Cart Section -->
    <section class="shopping-cart section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Shopping Cart -->
                    <div class="cart-list-wrapper">
                        <!-- Cart List Head -->
                    <div class="cart-list-head">
                            <div class="cart-list-title" style="flex: 0 0 100px;"></div>
                            <div class="cart-list-title">Product</div>
                            <div class="cart-list-title quantity">Quantity</div>
                            <div class="cart-list-title price">Total</div>
                            <div class="cart-list-title" style="flex: 0 0 20px;"></div>
                        </div>
                        <!-- Cart List Body -->
                    <div class="cart-list-body">
                            <?php if(count($cart) > 0): ?>
                                <?php foreach($cart as $item): ?>
                                    <div class="cart-single-list" data-product-id="<?php echo $item['product_id']; ?>">
                            <div class="product-thumb">
                                <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                                                <img src="<?php echo getValidImageUrl($item['product_image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                </a>
                            </div>
                            <div class="product-info">
                                <h6 class="product-title">
                                                <a href="product-details.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                </h6>
                                            <div class="price">Rp. <?php echo number_format($item['product_price'], 0, ',', '.'); ?></div>
                            </div>
                            <div class="product-quantity">
                                    <div class="input-group">
                                        <button type="button" class="button minus" onclick="decrementQuantity(this)">-</button>
                                                <input type="number" class="quantity-input" data-auto-update="true" value="<?php echo $item['cart_product_quantity']; ?>" min="1" max="<?php echo $item['product_stock_quantity']; ?>" 
                                                       onchange="updateCartQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                                <button type="button" class="button plus" onclick="incrementQuantity(this, <?php echo $item['product_stock_quantity']; ?>)">+</button>
                                            </div>
                                        </div>
                                        <div class="total-price">
                                            Rp. <?php echo number_format($item['product_price'] * $item['cart_product_quantity'], 0, ',', '.'); ?>
                                        </div>
                                        <button type="button" class="remove-item" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                            <i class="lni lni-close"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-cart-message">
                                    Your shopping cart is empty. <a href="product-grids.php">Shop now!</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(count($cart) > 0): ?>
                <div class="row mt-5">
                <div class="col-lg-8 col-md-6 col-12">
                        <div class="cart-button">
                            <a href="product-grids.php" class="btn btn-outline"><i class="lni lni-arrow-left"></i> Continue Shopping</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                        <div class="cart-summary">
                            <div class="summary-title">Order Summary</div>
                            <div class="summary-item">
                            <span>Subtotal</span>
                                <span>Rp. <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                        </div>
                            <div class="summary-item">
                                <span>Shipping</span>
                                <span>Free</span>
                        </div>
                            <div class="summary-item total">
                            <span>Total</span>
                                <span class="total-amount">Rp. <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                        </div>
                            <div class="cart-button mt-3">
                                <a href="checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- End Shopping Cart Section -->

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
    <script src="assets/js/cart.js"></script>
</body>

</html>