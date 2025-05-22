<?php
include 'utils.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

function insertCSSLinks()
{
    echo '<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="assets/css/auth-style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />';
}

function insertScripts()
{
    echo '<script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Toggle between user and admin login tabs
            const userTab = document.getElementById("user-tab");
            const adminTab = document.getElementById("admin-tab");
            const userForm = document.getElementById("user-form");
            const adminForm = document.getElementById("admin-form");
            
            userTab.addEventListener("click", function() {
                userTab.classList.add("active");
                adminTab.classList.remove("active");
                userForm.style.display = "block";
                adminForm.style.display = "none";
                document.getElementById("login-type").value = "user";
            });
            
            adminTab.addEventListener("click", function() {
                adminTab.classList.add("active");
                userTab.classList.remove("active");
                adminForm.style.display = "block";
                userForm.style.display = "none";
                document.getElementById("login-type").value = "admin";
            });
        });
    </script>';
}

insertHeader("Login - TechSphere", "insertCSSLinks");
?>

<section class="auth-section section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="auth-wrapper auth-animation animate__animated animate__fadeIn">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="auth-sidebar">
                                <div class="logo mb-4">
                                    <h2 class="mb-0">TechSphere</h2>
                                    <p class="mt-2">Find Your Dream Products</p>
                                </div>
                                <div class="welcome-text">
                                    <h2>Welcome Back</h2>
                                    <p>Log in to your account to continue shopping and check your order status.</p>
                                </div>
                                
                                <div class="features">
                                    <ul class="mb-0">
                                        <li><i class="lni lni-checkmark-circle"></i> Access to exclusive promotions</li>
                                        <li><i class="lni lni-checkmark-circle"></i> Track order status</li>
                                        <li><i class="lni lni-checkmark-circle"></i> Faster checkout</li>
                                        <li><i class="lni lni-checkmark-circle"></i> Complete shopping history</li>
                                    </ul>
                                </div>
                                
                                <div class="auth-footer mt-5">
                                    <p>© 2025 TechSphere. All Rights Reserved.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="login-form">
                                <div class="title">
                                    <h3>Log in to Your Account</h3>
                                    <p>Select account type and enter your credentials</p>
                                </div>
                                
                                <!-- Account Type Tabs -->
                                <ul class="nav nav-tabs mb-4" id="account-tabs">
                                    <li class="nav-item" style="width: 50%;">
                                        <a class="nav-link active text-center" id="user-tab" href="javascript:void(0)">User</a>
                                    </li>
                                    <li class="nav-item" style="width: 50%;">
                                        <a class="nav-link text-center" id="admin-tab" href="javascript:void(0)">Admin</a>
                                    </li>
                                </ul>
                                
                                <?php if (isset($_SESSION['error'])) { ?>
                                    <div class="alert alert-danger animate__animated animate__shakeX">
                                        <i class="lni lni-warning me-2"></i>
                                        <?php echo $_SESSION['error']; ?>
                                        <?php unset($_SESSION['error']); ?>
                                    </div>
                                <?php } ?>
                                
                                <!-- User Login Form -->
                                <form action="login_process.php" method="post" class="mt-4" id="user-form">
                                    <input type="hidden" name="login_type" id="login-type" value="user">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-envelope"></i>
                                                    <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="password">Password</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-lock-alt"></i>
                                                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="button">
                                                <button type="submit" class="btn">
                                                    <i class="lni lni-enter me-2"></i>Login Now
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="outer-link text-center mt-4">
                                        Don't have an account? <a href="register.php">Register here</a>
                                    </div>
                                </form>
                                
                                <!-- Admin Login Form -->
                                <form action="login_process.php" method="post" id="admin-form" style="display: none;" class="mt-4">
                                    <input type="hidden" name="login_type" value="admin">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="admin_email">Admin Email</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-envelope"></i>
                                                    <input type="email" class="form-control" name="admin_email" id="admin_email" placeholder="Admin Email" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="admin_password">Admin Password</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-lock-alt"></i>
                                                    <input type="password" class="form-control" name="admin_password" id="admin_password" placeholder="Admin Password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="button">
                                                <button type="submit" class="btn">
                                                    <i class="lni lni-enter me-2"></i>Login as Admin
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
insertFooter("insertScripts");
?> 