<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$result = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($result);

// Process profile update
$message = '';
$messageType = '';

if (isset($_POST['update_profile'])) {
    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
    $admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
    
    // Check if email already exists for other admins
    $check_email_sql = "SELECT * FROM admin WHERE admin_email = '$admin_email' AND admin_id != $admin_id";
    $check_email_result = mysqli_query($conn, $check_email_sql);
    
    if (mysqli_num_rows($check_email_result) > 0) {
        $message = "Email is already used by another admin!";
        $messageType = "danger";
    } else {
        // Update admin info
        $update_sql = "UPDATE admin SET admin_name = '$admin_name', admin_email = '$admin_email' WHERE admin_id = $admin_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $message = "Profile updated successfully!";
            $messageType = "success";
            
            // Refresh admin data
            $result = mysqli_query($conn, $sql);
            $admin = mysqli_fetch_assoc($result);
        } else {
            $message = "Failed to update profile: " . mysqli_error($conn);
            $messageType = "danger";
        }
    }
}

// Process password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    // Note: This assumes password is not hashed in the database. If password is hashed, use password_verify()
    if ($current_password !== $admin['admin_password']) {
        $message = "Current password is invalid!";
        $messageType = "danger";
    } else if ($new_password !== $confirm_password) {
        $message = "New password and confirmation password do not match!";
        $messageType = "danger";
    } else {
        // Update password (save without hash)
        $update_pwd_sql = "UPDATE admin SET admin_password = '$new_password' WHERE admin_id = $admin_id";
        
        if (mysqli_query($conn, $update_pwd_sql)) {
            $message = "Password updated successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to update password: " . mysqli_error($conn);
            $messageType = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Tech Sphere</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            background-color: #343a40;
        }
        .sidebar-link {
            color: #ffffff;
            padding: 15px;
            display: block;
            text-decoration: none;
            border-bottom: 1px solid #444;
        }
        .sidebar-link:hover {
            background-color: #495057;
            color: #ffffff;
        }
        .sidebar-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .sidebar-link i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
        }
        .admin-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3 text-center text-white">
                    <h4>TECH SPHERE</h4>
                    <p class="mb-0">Admin Panel</p>
                </div>
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="products.php" class="sidebar-link">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php" class="sidebar-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php" class="sidebar-link">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="profile.php" class="sidebar-link active">
                    <i class="fas fa-user-cog"></i> Admin Profile
                </a>
                <a href="../logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ml-auto content-container">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Admin Profile</h4>
                    <div class="admin-info">
                        <span class="me-2">Welcome, <?php echo $admin['admin_name']; ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['admin_name']); ?>&size=32&background=random" class="rounded-circle" alt="Admin">
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['admin_name']); ?>&size=150&background=random" class="profile-img mb-3" alt="Admin Profile">
                                    <h4><?php echo $admin['admin_name']; ?></h4>
                                    <p class="text-muted"><?php echo $admin['admin_email']; ?></p>
                                    <p><span class="badge bg-primary">Administrator</span></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Edit Profile</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="admin_name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?php echo $admin['admin_name']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="admin_email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo $admin['admin_email']; ?>" required>
                                        </div>
                                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">Change Password</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 