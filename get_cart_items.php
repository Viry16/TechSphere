<?php
// Start session
session_start();

// Default response
$response = array(
    'success' => false,
    'items' => []
);

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // Check connection
    if($conn) {
        $user_id = $_SESSION['user_id'];
        
        // Get cart items with product details
        $sql = "SELECT c.*, p.product_name, p.product_price, p.product_image_url
                FROM cart c
                JOIN product p ON c.product_id = p.product_id
                WHERE c.user_id = ?
                ORDER BY p.product_name
                LIMIT 2";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $items = [];
        while($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        
        $response['success'] = true;
        $response['items'] = $items;
        
        // Close connection
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 