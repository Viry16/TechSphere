    <!-- Start Header Area -->
    <header class="header navbar-area">
        <!-- Start Header Middle -->
        <div class="header-middle">
            <div class="container">
                <!-- Cart functions -->
                <?php
                // Include cart functions if not already included
                if (!function_exists('getValidImageUrl')) {
                    require_once 'includes/cart_functions.php';
                }
                ?>
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-3 col-7">
                        <!-- Start Header Logo -->
                        <a class="navbar-brand" href="index.php">
                            <img src="assets/images/logo/logo.png" alt="Logo">
                        </a>
                        <!-- End Header Logo -->
                    </div>
                    <div class="col-lg-5 col-md-7 d-xs-none">
                        <!-- Start Main Menu Search -->
                        <div class="main-menu-search">
                            <!-- navbar search start -->
                            <form action="product-grids.php" method="GET" class="navbar-search search-style-5">
                                <div class="search-select">
                                    <div class="select-position">
                                        <select id="select1" name="category">
                                            <option value="">All Categories</option>
                                            <?php
                                            // Connect to database
                                            $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
                                            
                                            // Get product types (categories)
                                            $sql = "SELECT DISTINCT product_type FROM product ORDER BY product_type";
                                            $result = mysqli_query($conn, $sql);
                                            
                                            // Loop through categories
                                            while($category = mysqli_fetch_assoc($result)) {
                                                $selected = (isset($_GET['category']) && $_GET['category'] == $category['product_type']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($category['product_type']) . '" ' . $selected . '>' . htmlspecialchars($category['product_type']) . '</option>';
                                            }
                                            
                                            // Close connection
                                            mysqli_close($conn);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="search-input">
                                    <input type="text" name="search" placeholder="Search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>
                                <div class="search-btn">
                                    <button type="submit"><i class="lni lni-search-alt"></i></button>
                                </div>
                            </form>
                            <!-- navbar search Ends -->
                        </div>
                        <!-- End Main Menu Search -->
                    </div>
                    <div class="col-lg-4 col-md-2 col-5">
                        <div class="middle-right-area">
                            <div class="navbar-cart">
                                <div class="cart-items">
                                    <a href="javascript:void(0)" class="main-btn">
                                        <i class="lni lni-cart"></i>
                                        <?php
                                        // Get cart count
                                        if(isset($_SESSION['user_id'])) {
                                            $user_id = $_SESSION['user_id'];
                                            $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
                                            
                                            $sql = "SELECT SUM(cart_product_quantity) as cart_count FROM cart WHERE user_id = '$user_id'";
                                            $result = mysqli_query($conn, $sql);
                                            $cart_data = mysqli_fetch_assoc($result);
                                            
                                            $cart_count = $cart_data['cart_count'] ? $cart_data['cart_count'] : 0;
                                        } else {
                                            $cart_count = 0;
                                        }
                                        ?>
                                        <span class="total-items"><?php echo $cart_count; ?></span>
                                    </a>
                                    <!-- Shopping Item -->
                                    <div class="shopping-item">
                                        <div class="dropdown-cart-header">
                                            <span><?php echo $cart_count; ?> Items</span>
                                            <a href="cart.php">View Cart</a>
                                        </div>
                                        <ul class="shopping-list">
                                            <?php
                                            // Get cart items
                                            if(isset($_SESSION['user_id']) && $cart_count > 0) {
                                                $sql = "SELECT c.*, p.product_name, p.product_price, p.product_image_url
                                                        FROM cart c
                                                        JOIN product p ON c.product_id = p.product_id
                                                        WHERE c.user_id = '$user_id'
                                                        LIMIT 2";
                                                $result = mysqli_query($conn, $sql);
                                                
                                                while($item = mysqli_fetch_assoc($result)) {
                                                    $total = $item['product_price'] * $item['cart_product_quantity'];
                                            ?>
                                            <li>
                                                <a href="javascript:void(0)" class="remove" title="Remove this item" 
                                                   onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                                   <i class="lni lni-close"></i>
                                                </a>
                                                <div class="cart-img-head">
                                                    <a class="cart-img" href="product-details.php?id=<?php echo $item['product_id']; ?>">
                                                        <img src="<?php echo getValidImageUrl($item['product_image_url']); ?>" alt="<?php echo $item['product_name']; ?>">
                                                    </a>
                                                </div>

                                                <div class="content">
                                                    <h4>
                                                        <a href="product-details.php?id=<?php echo $item['product_id']; ?>">
                                                            <?php echo $item['product_name']; ?>
                                                        </a>
                                                    </h4>
                                                    <p class="quantity"><?php echo $item['cart_product_quantity']; ?>x - 
                                                       <span class="amount">Rp. <?php echo number_format($item['product_price'], 0, ',', '.'); ?></span>
                                                    </p>
                                                </div>
                                            </li>
                                            <?php
                                                }
                                                
                                                // Get cart total
                                                $sql = "SELECT SUM(p.product_price * c.cart_product_quantity) as cart_total
                                                        FROM cart c
                                                        JOIN product p ON c.product_id = p.product_id
                                                        WHERE c.user_id = '$user_id'";
                                                $result = mysqli_query($conn, $sql);
                                                $total_data = mysqli_fetch_assoc($result);
                                                $cart_total = $total_data['cart_total'] ? $total_data['cart_total'] : 0;
                                                
                                                mysqli_close($conn);
                                            } else {
                                                echo '<li class="text-center py-3">Your cart is empty</li>';
                                                $cart_total = 0;
                                            }
                                            ?>
                                        </ul>
                                        <div class="bottom">
                                            <div class="total">
                                                <span>Total</span>
                                                <span class="total-amount">Rp. <?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="button">
                                                <a href="checkout.php" class="btn animate">Checkout</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--/ End Shopping Item -->
                                </div>
                            </div>
                            
                            <!-- User Login/Profile Section -->
                            <?php 
                            if(isset($_SESSION['user_id'])) {
                                // Connect to database
                                $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
                                
                                // Get user data
                                $user_id = $_SESSION['user_id'];
                                $sql = "SELECT user_name, user_imgprofile FROM users WHERE user_id = '$user_id'";
                                $result = mysqli_query($conn, $sql);
                                $user = mysqli_fetch_assoc($result);
                                
                                // Set default values if user data is not found
                                $user_name = $user ? $user['user_name'] : 'User';
                                $profile_img = ($user && !empty($user['user_imgprofile'])) ? 
                                    $user['user_imgprofile'] : 
                                    'https://ui-avatars.com/api/?name=' . urlencode($user_name) . '&size=50&background=random';
                            ?>
                                <div class="user-profile">
                                    <img src="<?php echo $profile_img; ?>" alt="Profile">
                                    <div class="user dropdown">
                                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="lni lni-user"></i>
                                            <?php echo $user_name; ?>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
                                            <li><a class="dropdown-item" href="edit_profile.php">Edit Profile</a></li>
                                            <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                        </ul>
                                    </div>
                                </div>
                            <?php
                            } else {
                            ?>
                                <div class="user">
                                    <i class="lni lni-user"></i>
                                    <a href="login.php">Login</a> / <a href="register.php">Register</a>
                                </div>
                            <?php
                            }
                            ?>
                            <!-- End User Login/Profile Section -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Header Middle -->
        <!-- Start Header Bottom -->
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-6 col-12">
                    <div class="nav-inner">
                        <!-- Start Mega Category Menu -->
                        <div class="mega-category-menu">
                            <span class="cat-button"><i class="lni lni-menu"></i>All Categories</span>
                            <ul class="sub-category">
                                <?php
                                // Connect to database
                                $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
                                
                                // Get product types (categories)
                                $sql = "SELECT DISTINCT product_type FROM product ORDER BY product_type";
                                $result = mysqli_query($conn, $sql);
                                
                                // Loop through categories
                                while($category = mysqli_fetch_assoc($result)) {
                                    echo '<li><a href="product-grids.php?category=' . urlencode($category['product_type']) . '">' . $category['product_type'] . '</a></li>';
                                }
                                
                                // Close connection
                                mysqli_close($conn);
                                ?>
                            </ul>
                        </div>
                        <!-- End Mega Category Menu -->
                        <!-- Start Navbar -->
                        <nav class="navbar navbar-expand-lg">
                            <button class="navbar-toggler mobile-menu-btn" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <ul id="nav" class="navbar-nav ms-auto">
                                    <?php
                                    // Function to get current page
                                    function getCurrentPage() {
                                        $current_page = basename($_SERVER['PHP_SELF']);
                                        return $current_page;
                                    }
                                    
                                    // Get current page
                                    $current_page = getCurrentPage();
                                    
                                    // Define navigation items
                                    $nav_items = [
                                        'index.php' => 'Home',
                                        'product-grids.php' => 'Shop',
                                        'orders.php' => 'Orders',
                                    ];
                                    
                                    // Output navigation items
                                    foreach ($nav_items as $page => $title) {
                                        $active = ($current_page == $page) ? 'active' : '';
                                        echo '<li class="nav-item">';
                                        echo '<a href="' . $page . '" class="' . $active . '" aria-label="Toggle navigation">' . $title . '</a>';
                                        echo '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </nav>
                        <!-- End Navbar -->
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-12">
                    <!-- Start Nav Social -->
                    <div class="nav-social">
                        <h5 class="title">Support Email:</h5>
                        <p><a href="mailto:support@techsphere.com">support@techsphere.com</a></p>
                    </div>
                    <!-- End Nav Social -->
                </div>
            </div>
        </div>
        <!-- End Header Bottom -->
    </header>
    <!-- End Header Area -->