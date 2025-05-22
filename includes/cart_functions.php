<?php
/**
 * Cart Functions Library
 * Functions for managing shopping cart operations
 */

// Function to add product to cart
function addToCart($user_id, $product_id, $quantity) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // Check if product already in cart
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        // Update quantity if product already in cart
        $row = mysqli_fetch_assoc($result);
        $new_quantity = $row['cart_product_quantity'] + $quantity;
        
        $update_sql = "UPDATE cart SET cart_product_quantity = ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "iii", $new_quantity, $user_id, $product_id);
        $success = mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    } else {
        // Insert new product to cart
        $insert_sql = "INSERT INTO cart (user_id, product_id, cart_product_quantity) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "iii", $user_id, $product_id, $quantity);
        $success = mysqli_stmt_execute($insert_stmt);
        mysqli_stmt_close($insert_stmt);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $success;
}

// Function to remove product from cart
function removeFromCart($user_id, $product_id) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // Delete product from cart
    $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    $success = mysqli_stmt_execute($stmt);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $success;
}

// Function to update product quantity in cart
function updateCartQuantity($user_id, $product_id, $quantity) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    if($quantity <= 0) {
        // Remove product if quantity is 0 or negative
        return removeFromCart($user_id, $product_id);
    } else {
        // Update quantity
        $sql = "UPDATE cart SET cart_product_quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $quantity, $user_id, $product_id);
        $success = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
        return $success;
    }
}

// Function to get cart contents
function getCartContents($user_id) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // Get cart items with product details
    $sql = "SELECT c.*, p.product_name, p.product_price, p.product_image_url, p.product_stock_quantity 
            FROM cart c
            JOIN product p ON c.product_id = p.product_id
            WHERE c.user_id = ?
            ORDER BY p.product_name";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $cart_items = [];
    $cart_total = 0;
    
    while($item = mysqli_fetch_assoc($result)) {
        $item['subtotal'] = $item['product_price'] * $item['cart_product_quantity'];
        $cart_total += $item['subtotal'];
        $cart_items[] = $item;
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return [
        'items' => $cart_items,
        'total' => $cart_total,
        'count' => count($cart_items)
    ];
}

// Function to clear cart
function clearCart($user_id) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // Delete all products from cart
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    $success = mysqli_stmt_execute($stmt);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $success;
}

// Function to check if a product is in cart
function isProductInCart($user_id, $product_id) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // Check if product in cart
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $in_cart = mysqli_num_rows($result) > 0;
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    return $in_cart;
}

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