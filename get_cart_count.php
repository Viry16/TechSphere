<?php
// Start session
session_start();

// Default response
$response = array(
    'success' => false,
    'count' => 0
);

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Connect to database
    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
    
    // Check connection
    if($conn) {
        $user_id = $_SESSION['user_id'];
        
        // Get cart count
        $sql = "SELECT SUM(cart_product_quantity) as total FROM cart WHERE user_id = '$user_id'";
        $result = mysqli_query($conn, $sql);
        
        if($result) {
            $row = mysqli_fetch_assoc($result);
            $count = $row['total'] ? $row['total'] : 0;
            
            $response['success'] = true;
            $response['count'] = $count;
        }
        
        // Close connection
        mysqli_close($conn);
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 