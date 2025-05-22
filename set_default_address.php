<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Check if is_default column exists, if not add it
$check_column_sql = "SHOW COLUMNS FROM `address` LIKE 'is_default'";
$check_column_result = mysqli_query($conn, $check_column_sql);

if (mysqli_num_rows($check_column_result) == 0) {
    // Column doesn't exist, add it
    $add_column_sql = "ALTER TABLE `address` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0";
    
    if (!mysqli_query($conn, $add_column_sql)) {
        // Failed to add column
        $_SESSION['message'] = "Failed to add is_default column: " . mysqli_error($conn);
        header("Location: profile.php");
        exit();
    }
    
    error_log("Added is_default column to address table");
}

// Check if address ID is provided
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $address_name = urldecode($_GET['id']);
    
    // Log the operation for debugging
    error_log("Setting default address: user_id=$user_id, address_name='$address_name'");
    
    // Verify that the address belongs to the currently logged in user
    $verify_sql = "SELECT * FROM address WHERE user_id = ? AND user_address_name = ?";
    $stmt = mysqli_prepare($conn, $verify_sql);
    
    if ($stmt === false) {
        $_SESSION['message'] = "Error in query preparation: " . mysqli_error($conn);
        header("Location: profile.php");
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, "is", $user_id, $address_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // First, reset all addresses as non-default
            $reset_sql = "UPDATE address SET is_default = 0 WHERE user_id = ?";
            $reset_stmt = mysqli_prepare($conn, $reset_sql);
            
            if ($reset_stmt === false) {
                throw new Exception("Error in reset query preparation: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($reset_stmt, "i", $user_id);
            mysqli_stmt_execute($reset_stmt);
            mysqli_stmt_close($reset_stmt);
            
            // Then, set the selected address as default
            $update_sql = "UPDATE address SET is_default = 1 WHERE user_id = ? AND user_address_name = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            
            if ($update_stmt === false) {
                throw new Exception("Error in update query preparation: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($update_stmt, "is", $user_id, $address_name);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $_SESSION['message'] = "Address has been set as default successfully.";
        } catch (Exception $e) {
            // Rollback if an error occurs
            mysqli_rollback($conn);
            $_SESSION['message'] = "Failed to set default address: " . $e->getMessage();
        }
    } else {
        $_SESSION['message'] = "Address not found.";
    }
    
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['message'] = "Invalid address ID. Please make sure the URL has a parameter '?id=address_name'.";
}

// Redirect back to profile page
header("Location: profile.php");
exit();
?> 