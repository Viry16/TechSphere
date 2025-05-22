<?php
session_start();

// Include database connection
require_once 'database.php';
require_once 'utils.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = sanitize_input($conn, $_POST['user_name']);
    $email = sanitize_input($conn, $_POST['user_email']);
    $phone = !empty($_POST['user_phone_number']) ? sanitize_input($conn, $_POST['user_phone_number']) : null;
    $address = !empty($_POST['user_address']) ? sanitize_input($conn, $_POST['user_address']) : null;
    $password = $_POST['user_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        redirectWithMessage("register.php", "All required fields must be filled!", "error");
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        redirectWithMessage("register.php", "Password and confirmation password do not match!", "error");
    }
    
    // Verify terms agreement is checked
    if (!isset($_POST['terms'])) {
        redirectWithMessage("register.php", "You must agree to the Terms and Conditions and Privacy Policy!", "error");
    }
    
    // Check if email already exists
    $check_query = "SELECT * FROM users WHERE user_email = '$email'";
    $user_exists = get_row($conn, $check_query);
    
    if ($user_exists) {
        redirectWithMessage("register.php", "Email is already registered, please use another email!", "error");
    }
    
    // Check if phone already exists (if provided)
    if ($phone) {
        $phone_query = "SELECT * FROM users WHERE user_phone_number = '$phone'";
        $phone_exists = get_row($conn, $phone_query);
        
        if ($phone_exists) {
            redirectWithMessage("register.php", "Phone number is already registered!", "error");
        }
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user data
    $insert_query = "INSERT INTO users (user_name, user_email, user_phone_number, user_address, user_password) 
                    VALUES ('$name', '$email', ";
    
    // Handle null values
    $insert_query .= $phone ? "'$phone', " : "NULL, ";
    $insert_query .= $address ? "'$address', " : "NULL, ";
    $insert_query .= "'$hashed_password')";
    
    $result = insert_data($conn, $insert_query);
    if ($result) {
        // Registration successful
        redirectWithMessage("login.php", "Registration successful! Please login.", "success");
    } else {
        // Registration failed
        redirectWithMessage("register.php", "An error occurred: " . mysqli_error($conn), "error");
    }
}

// If not a POST request, redirect to register page
header("Location: register.php");
exit();
?>