<?php
/**
 * Utility file to store common functions used throughout the application
 */

/**
 * Function to insert modern notification CSS
 */
function insertNotificationCSS() {
    echo '
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            transition: all 0.5s ease;
            opacity: 0;
            transform: translateY(-20px);
        }
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        .alert-success, .alert-danger {
            border-left: 5px solid;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .alert-success {
            border-left-color: #28a745;
        }
        .alert-danger {
            border-left-color: #dc3545;
        }
    </style>
    ';
}

/**
 * Function to insert notification container HTML
 */
function insertNotificationContainer() {
    echo '<div id="notification-container"></div>';
}

/**
 * Function to insert modern notification JavaScript
 */
function insertNotificationJS() {
    echo '
    <script>
        function showNotification(message, type) {
            const container = document.getElementById("notification-container");
            const notification = document.createElement("div");
            notification.className = `notification alert alert-${type} alert-dismissible fade show`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            container.appendChild(notification);
            
            // Show notification with animation
            setTimeout(() => {
                notification.classList.add("show");
            }, 100);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                notification.classList.remove("show");
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 5000);
        }
    ';
    
    // Display error message if any
    if (isset($_SESSION['error'])) {
        echo "showNotification('" . addslashes($_SESSION['error']) . "', 'danger');";
        unset($_SESSION['error']);
    }
    
    // Display success message if any
    if (isset($_SESSION['success'])) {
        echo "showNotification('" . addslashes($_SESSION['success']) . "', 'success');";
        unset($_SESSION['success']);
    }
    
    echo '</script>';
}

/**
 * Function to validate input
 */
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Function to check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Function to check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Function to redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    if ($type == 'error') {
        $_SESSION['error'] = $message;
    } else {
        $_SESSION[$type] = $message;
    }
    header("Location: $url");
    exit();
}

/**
 * Function to insert HTML header
 */
function insertHeader($title, $cssFunction = null) {
    echo '<!DOCTYPE html>
    <html class="no-js" lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <title>' . $title . '</title>
        <meta name="description" content="TechSphere - Trusted Online Store" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />';
        
    // If there's a CSS function to include
    if ($cssFunction && function_exists($cssFunction)) {
        call_user_func($cssFunction);
    }
    
    // Insert notification CSS
    insertNotificationCSS();
        
    echo '</head>
    <body>';
    
    // Insert notification container
    insertNotificationContainer();
    
    echo '<div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>';
}

/**
 * Function to insert HTML footer
 */
function insertFooter($scriptsFunction = null) {
    echo '<footer class="footer">
        <div class="footer-bottom">
            <div class="container">
                <div class="inner-content">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-12">
                            <div class="payment-gateway">
                                <span>We Accept:</span>
                                <img src="assets/images/footer/credit-cards-footer.png" alt="#">
                            </div>
                        </div>
                        <div class="col-lg-4 col-12">
                            <div class="copyright">
                                <p>Designed and Developed by <a href="index.html" rel="nofollow" target="_blank">TechSphere</a></p>
                            </div>
                        </div>
                        <div class="col-lg-4 col-12">
                            <ul class="socila">
                                <li><span>Follow Us:</span></li>
                                <li><a href="javascript:void(0)"><i class="lni lni-facebook-filled"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="lni lni-twitter-original"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="lni lni-instagram"></i></a></li>
                                <li><a href="javascript:void(0)"><i class="lni lni-google"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>';
    
    // If there's a JS function to include
    if ($scriptsFunction && function_exists($scriptsFunction)) {
        call_user_func($scriptsFunction);
    }
    
    // Insert notification JS
    insertNotificationJS();
    
    echo '</body></html>';
}

/**
 * Function to create database connection
 */
function createDatabaseConnection() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "tech_sphere";
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}
?> 