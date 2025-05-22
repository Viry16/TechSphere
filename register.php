<?php
include 'utils.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const passwordInput = document.getElementById("password");
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
    </script>';
}

insertHeader("Register - TechSphere", "insertCSSLinks");
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
                                    <h2>Create New Account</h2>
                                    <p>Join us and start shopping with more convenience and benefits.</p>
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
                            <div class="register-form">
                                <div class="title">
                                    <h3>Create New Account</h3>
                                    <p>Please fill in your information correctly</p>
                                </div>
                                
                                <?php if (isset($_SESSION['error'])) { ?>
                                    <div class="alert alert-danger animate__animated animate__shakeX">
                                        <i class="lni lni-warning me-2"></i>
                                        <?php echo $_SESSION['error']; ?>
                                        <?php unset($_SESSION['error']); ?>
                                    </div>
                                <?php } ?>
                                
                                <form action="register_process.php" method="post" class="mt-4">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="fullname">Full Name</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-user"></i>
                                                    <input type="text" class="form-control" name="user_name" id="fullname" placeholder="Full Name" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-envelope"></i>
                                                    <input type="email" class="form-control" name="user_email" id="email" placeholder="Email Address" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="password">Password</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-lock-alt"></i>
                                                    <input type="password" class="form-control" name="user_password" id="password" placeholder="Password" required>
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
                                                <label for="confirm_password">Confirm Password</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-lock-alt"></i>
                                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <label for="phone">Phone Number</label>
                                                <div class="input-icon">
                                                    <i class="lni lni-phone"></i>
                                                    <input type="tel" class="form-control" name="user_phone_number" id="phone" placeholder="Phone Number" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                                    <label class="form-check-label" for="terms">I agree to the Terms and Conditions and Privacy Policy</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="button">
                                                <button type="submit" class="btn">
                                                    <i class="lni lni-user me-2"></i>Register Now
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="outer-link text-center">
                                        Already have an account? <a href="login.php">Sign in here</a>
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