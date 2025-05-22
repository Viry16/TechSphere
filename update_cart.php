<?php
// Start session
session_start();

// Include cart functions
require_once 'includes/cart_functions.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to access the cart'
    ]);
    exit;
}

// Check if request is POST and has required data
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // If quantity is 0, remove product from cart
    if($quantity <= 0) {
        $success = removeFromCart($user_id, $product_id);
        $action = "delete";
    } else {
        // Check product stock
        $sql = "SELECT product_stock_quantity, product_price FROM product WHERE product_id = ?";
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
            
            // Update cart quantity
            $success = updateCartQuantity($user_id, $product_id, $quantity);
            $action = "update";
            
            // Calculate item subtotal
            $subtotal = $product['product_price'] * $quantity;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Product not found'
            ]);
            exit;
        }
    }
    
    if($success) {
        // Get updated cart count and total
        $cart_count_sql = "SELECT SUM(cart_product_quantity) as cart_count FROM cart WHERE user_id = ?";
        $cart_count_stmt = mysqli_prepare($conn, $cart_count_sql);
        mysqli_stmt_bind_param($cart_count_stmt, "i", $user_id);
        mysqli_stmt_execute($cart_count_stmt);
        $cart_count_result = mysqli_stmt_get_result($cart_count_stmt);
        $cart_count_data = mysqli_fetch_assoc($cart_count_result);
        $cart_count = $cart_count_data['cart_count'] ? $cart_count_data['cart_count'] : 0;
        
        $cart_total_sql = "SELECT SUM(p.product_price * c.cart_product_quantity) as cart_total
                           FROM cart c
                           JOIN product p ON c.product_id = p.product_id
                           WHERE c.user_id = ?";
        $cart_total_stmt = mysqli_prepare($conn, $cart_total_sql);
        mysqli_stmt_bind_param($cart_total_stmt, "i", $user_id);
        mysqli_stmt_execute($cart_total_stmt);
        $cart_total_result = mysqli_stmt_get_result($cart_total_stmt);
        $cart_total_data = mysqli_fetch_assoc($cart_total_result);
        $cart_total = $cart_total_data['cart_total'] ? $cart_total_data['cart_total'] : 0;
        
        $response = [
            'success' => true,
            'action' => $action,
            'message' => $action === 'delete' ? 'Product removed from cart' : 'Cart successfully updated',
            'cart_count' => $cart_count,
            'cart_total' => $cart_total,
            'cart_total_formatted' => 'Rp. ' . number_format($cart_total, 0, ',', '.')
        ];
        
        if($action === 'update') {
            $response['subtotal'] = $subtotal;
            $response['subtotal_formatted'] = 'Rp. ' . number_format($subtotal, 0, ',', '.');
        }
        
        echo json_encode($response);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update cart'
        ]);
    }
    
    mysqli_close($conn);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Incomplete data'
    ]);
} 