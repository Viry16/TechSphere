<?php
// Start session
session_start();

// Include cart functions
require_once 'includes/cart_functions.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to add products to cart',
        'redirect' => 'login.php'
    ]);
    exit;
}

// Check if request is POST and has required data
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Validate quantity
    if($quantity <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Product quantity must be greater than 0'
        ]);
        exit;
    }
    
    // Check product stock
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    $sql = "SELECT product_stock_quantity FROM product WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        
        // Check if requested quantity is available
        if($quantity > $product['product_stock_quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient stock. Available stock: ' . $product['product_stock_quantity']
            ]);
            exit;
        }
        
        // Check if product already in cart
        $cart_check_sql = "SELECT cart_product_quantity FROM cart WHERE user_id = ? AND product_id = ?";
        $cart_check_stmt = mysqli_prepare($conn, $cart_check_sql);
        mysqli_stmt_bind_param($cart_check_stmt, "ii", $user_id, $product_id);
        mysqli_stmt_execute($cart_check_stmt);
        $cart_check_result = mysqli_stmt_get_result($cart_check_stmt);
        
        if(mysqli_num_rows($cart_check_result) > 0) {
            $cart_item = mysqli_fetch_assoc($cart_check_result);
            $total_quantity = $cart_item['cart_product_quantity'] + $quantity;
            
            // Check if total quantity exceeds stock
            if($total_quantity > $product['product_stock_quantity']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Total quantity exceeds available stock. You already have ' . 
                                 $cart_item['cart_product_quantity'] . ' in your cart. Available stock: ' . 
                                 $product['product_stock_quantity']
                ]);
                exit;
            }
        }
        
        // Add to cart
        $success = addToCart($user_id, $product_id, $quantity);
        
        if($success) {
            // Get updated cart count
            $cart_count_sql = "SELECT SUM(cart_product_quantity) as cart_count FROM cart WHERE user_id = ?";
            $cart_count_stmt = mysqli_prepare($conn, $cart_count_sql);
            mysqli_stmt_bind_param($cart_count_stmt, "i", $user_id);
            mysqli_stmt_execute($cart_count_stmt);
            $cart_count_result = mysqli_stmt_get_result($cart_count_stmt);
            $cart_count_data = mysqli_fetch_assoc($cart_count_result);
            $cart_count = $cart_count_data['cart_count'] ? $cart_count_data['cart_count'] : 0;
            
            echo json_encode([
                'success' => true,
                'message' => 'Product successfully added to cart',
                'cart_count' => $cart_count
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add product to cart'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Incomplete data'
    ]);
} 