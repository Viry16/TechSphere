<?php
session_start();

// Include database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine login type (user or admin)
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'user';
    
    if ($login_type == 'user') {
        // User login process
        handleUserLogin($conn);
    } else {
        // Admin login process
        handleAdminLogin($conn);
    }
} else {
    // If not a POST request, redirect to login page
    header("Location: login.php");
    exit();
}

function handleUserLogin($conn) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required!";
        header("Location: login.php");
        exit();
    }
    
    // Get user data
    $sql = "SELECT * FROM users WHERE user_email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['user_password'])) {
            // Success login
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            
            // Check "remember me" option
            if (isset($_POST['remember'])) {
                // Set cookies for 30 days
                setcookie('user_email', $email, time() + (86400 * 30), "/");
                setcookie('user_password', $password, time() + (86400 * 30), "/");
            }
            
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect password!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Email not registered!";
        header("Location: login.php");
        exit();
    }
}

function handleAdminLogin($conn) {
    $email = mysqli_real_escape_string($conn, $_POST['admin_email']);
    $password = $_POST['admin_password'];
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Admin email and password are required!";
        header("Location: login.php");
        exit();
    }
    
    // Get admin data
    $sql = "SELECT * FROM admin WHERE admin_email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        
        // For admin, check direct password match (you might want to upgrade this to use hashing)
        if ($password === $admin['admin_password']) {
            // Success login
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['admin_name'];
            
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect admin password!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Admin email not registered!";
        header("Location: login.php");
        exit();
    }
}
?>