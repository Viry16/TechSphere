<?php
session_start();
require_once 'database.php';
require_once 'utils.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirmation password do not match!";
    } else {
        // Get current password from database
        $sql = "SELECT user_password FROM users WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Verify current password
        if (password_verify($current_password, $user['user_password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_sql = "UPDATE users SET user_password = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error updating password: " . mysqli_error($conn);
            }
            mysqli_stmt_close($update_stmt);
        } else {
            $error = "Current password is incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Change Password - TechSphere</title>
    <meta name="description" content="Change your account password" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="assets/css/auth-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- /End Preloader -->

    <!-- Start Header Area -->
    <?php include 'includes/header.php'; ?>
    <!-- End Header Area -->

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="breadcrumbs-content">
                        <h1 class="page-title">Change Password</h1>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li>Change Password</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <section class="auth-section section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="auth-wrapper auth-animation animate__animated animate__fadeIn">
                        <div class="register-form">
                            <div class="title">
                                <h3>Change Password</h3>
                                <p>Please enter your current and new password</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger animate__animated animate__shakeX">
                                    <i class="lni lni-warning me-2"></i>
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success animate__animated animate__fadeIn">
                                    <i class="lni lni-checkmark-circle me-2"></i>
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form action="change_password.php" method="post" class="mt-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label for="current_password">Current Password</label>
                                            <div class="input-icon">
                                                <i class="lni lni-lock-alt"></i>
                                                <input type="password" class="form-control" name="current_password" id="current_password" placeholder="Current Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label for="new_password">New Password</label>
                                            <div class="input-icon">
                                                <i class="lni lni-lock-alt"></i>
                                                <input type="password" class="form-control" name="new_password" id="new_password" placeholder="New Password" required>
                                            </div>
                                            <div class="password-strength-meter mt-2">
                                                <div class="progress" style="height: 5px;">
                                                    <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <small class="d-flex justify-content-between mt-1">
                                                    <span>Password Strength</span>
                                                    <span id="strength-text"></span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-3">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <div class="input-icon">
                                                <i class="lni lni-lock-alt"></i>
                                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="button">
                                            <button type="submit" class="btn">
                                                <i class="lni lni-lock me-2"></i>Change Password
                                            </button>
                                        </div>
                                        <div class="outer-link text-center mt-3">
                                            <a href="profile.php" class="btn-link"><i class="lni lni-arrow-left me-2"></i>Back to Profile</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Start Footer Area -->
    <?php include 'includes/footer.php'; ?>
    <!-- End Footer Area -->

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const passwordInput = document.getElementById("new_password");
        const progressBar = document.getElementById("password-strength");
        const strengthText = document.getElementById("strength-text");
        
        passwordInput.addEventListener("input", function() {
            const password = passwordInput.value;
            if(password.length === 0) {
                progressBar.style.width = "0%";
                progressBar.className = "progress-bar";
                strengthText.textContent = "";
                return;
            }
            
            const result = zxcvbn(password);
            let percent = (result.score * 25);
            progressBar.style.width = percent + "%";
            
            // Update color and text based on strength
            let strengthClass = "bg-danger";
            let message = "Very weak";
            
            if (result.score === 1) {
                strengthClass = "bg-danger";
                message = "Weak";
            } else if (result.score === 2) {
                strengthClass = "bg-warning";
                message = "Medium";
            } else if (result.score === 3) {
                strengthClass = "bg-info";
                message = "Strong";
            } else if (result.score === 4) {
                strengthClass = "bg-success";
                message = "Very strong";
            }
            
            progressBar.className = "progress-bar " + strengthClass;
            strengthText.textContent = message;
        });
    });
    </script>
</body>

</html> 