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

// Check if request has required data
if(isset($_POST['product_id']) || isset($_GET['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : intval($_GET['product_id']);
    
    // Remove from cart
    $success = removeFromCart($user_id, $product_id);
    
    if($success) {
        // Get updated cart count and total
        $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
        
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
        
        mysqli_close($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Product successfully removed from cart',
            'cart_count' => $cart_count,
            'cart_total' => $cart_total,
            'cart_total_formatted' => 'Rp. ' . number_format($cart_total, 0, ',', '.')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove product from cart'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Incomplete data'
    ]);
} 