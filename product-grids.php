<?php
// Start session at the beginning of the file, before any output
session_start();
// Include image helper
include_once 'image_helper.php';
?>
<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Product Grids - TechSphere</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --text-color: #333;
            --text-light: #666;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        .product-image img {
            height: 250px;
            object-fit: cover;
            width: 100%;
            border-radius: 8px 8px 0 0;
        }

        .product-sidebar {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 20px;
        }

        .single-widget {
            margin-bottom: 35px;
        }

        .single-widget h3 {
            font-size: 20px;
            margin-bottom: 25px;
            color: var(--text-color);
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .single-widget h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px;
        }

        .list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .list li {
            margin-bottom: 12px;
        }

        .list li a {
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 8px;
            background: var(--bg-light);
        }

        .list li a:hover, 
        .list li a.active {
            color: var(--primary-color);
            background: rgba(37, 99, 235, 0.1);
            transform: translateX(5px);
        }

        .list li a i {
            margin-right: 10px;
            font-size: 18px;
        }

        .product-sorting {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
        }

        .product-sorting select {
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-light);
            color: var(--text-color);
            font-size: 14px;
            transition: var(--transition);
        }

        .product-sorting select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .single-product {
            transition: var(--transition);
            margin-bottom: 30px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .single-product:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .price span {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 20px;
        }

        .category {
            color: var(--text-light);
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title a {
            color: var(--text-color);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 600;
            font-size: 18px;
            line-height: 1.4;
        }

        .title a:hover {
            color: var(--primary-color);
        }

        .button .btn {
            background: var(--primary-color);
            color: #fff;
            padding: 12px 25px;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
            width: 100%;
            text-align: center;
        }

        .button .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .search form {
            display: flex;
            gap: 10px;
        }

        .search input {
            flex: 1;
            padding: 12px 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-light);
            transition: var(--transition);
        }

        .search input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search button {
            background: var(--primary-color);
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .search button:hover {
            background: var(--primary-hover);
        }

        .product-info {
            padding: 20px;
        }

        .section {
            padding: 80px 0;
            background: var(--bg-light);
        }

        .breadcrumbs {
            background: #fff;
            padding: 30px 0;
            margin-bottom: 0;
            box-shadow: var(--shadow-sm);
        }

        .breadcrumbs-content h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breadcrumb-nav li {
            color: var(--text-light);
        }

        .breadcrumb-nav li a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb-nav li a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!--[if lte IE 9]>
        <p class="browserupgrade">
            You are using an <strong>unsecured</strong> browser. Please
            <a href="https://browsehappy.com/">upgrade your browser</a> to improve
            your experience and security.
        </p>
    <![endif]-->

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

    <!-- Start Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="breadcrumbs-content">
                        <h1 class="page-title">Product Grids</h1>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php">Home</a></li>
                        <li>Product Grids</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Product Grids -->
    <section class="product-grids section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4 col-12">
                    <!-- Start Product Sidebar -->
                    <div class="product-sidebar">
                        <!-- Start Single Widget -->
                        <div class="single-widget">
                            <h3>All Categories</h3>
                            <ul class="list">
                                <li>
                                    <a href="product-grids.php" class="<?php echo !isset($_GET['category']) ? 'active' : ''; ?>">
                                        <i class="lni lni-grid-alt"></i>
                                        Show All
                                    </a>
                                </li>
                                <?php
                                // Connect to database
                                $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
                                
                                // Get product types (categories)
                                $sql = "SELECT DISTINCT product_type FROM product ORDER BY product_type";
                                $result = mysqli_query($conn, $sql);
                                
                                // Loop through categories
                                while($category = mysqli_fetch_assoc($result)) {
                                    $active = (isset($_GET['category']) && $_GET['category'] == $category['product_type']) ? 'active' : '';
                                    $icon = '';
                                    switch($category['product_type']) {
                                        case 'CPU':
                                            $icon = 'lni lni-cpu';
                                            break;
                                        case 'GPU':
                                            $icon = 'lni lni-display';
                                            break;
                                        case 'RAM':
                                            $icon = 'lni lni-memory';
                                            break;
                                        case 'Motherboard':
                                            $icon = 'lni lni-motherboard';
                                            break;
                                        case 'Storage':
                                            $icon = 'lni lni-harddrive';
                                            break;
                                        case 'PSU':
                                            $icon = 'lni lni-bolt';
                                            break;
                                        case 'Case':
                                            $icon = 'lni lni-cog';
                                            break;
                                        case 'Cooling':
                                            $icon = 'lni lni-fan';
                                            break;
                                        case 'Monitor':
                                            $icon = 'lni lni-display-alt';
                                            break;
                                        case 'Accessories':
                                            $icon = 'lni lni-keyboard';
                                            break;
                                        default:
                                            $icon = 'lni lni-grid-alt';
                                    }
                                    echo '<li><a href="product-grids.php?category=' . urlencode($category['product_type']) . '" class="' . $active . '"><i class="' . $icon . '"></i>' . $category['product_type'] . '</a></li>';
                                }
                                
                                // Close connection
                                mysqli_close($conn);
                                ?>
                            </ul>
                        </div>
                        <!-- End Single Widget -->
                    </div>
                    <!-- End Product Sidebar -->
                </div>
                <div class="col-lg-9 col-md-8 col-12">
                    <div class="product-grids-head">
                        <div class="product-grid-topbar">
                            <div class="row align-items-center">
                                <div class="col-lg-7 col-md-8 col-12">
                                    <div class="product-sorting">
                                        <label for="sorting">Sort by:</label>
                                        <select class="form-control" id="sorting" onchange="sortProducts(this.value)">
                                            <option value="newest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest</option>
                                            <option value="price_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                                            <option value="price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                                            <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                                            <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name: Z to A</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-grid" role="tabpanel" aria-labelledby="nav-grid-tab">
                                <div class="row">
                                    <?php
                                    // Connect to database
                                    $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
                                    
                                    // Build query based on filters
                                    $sql = "SELECT * FROM product WHERE 1=1";
                                    $params = array();
                                    
                                    // Category filter
                                    if(isset($_GET['category']) && !empty($_GET['category'])) {
                                        $category = mysqli_real_escape_string($conn, $_GET['category']);
                                        $sql .= " AND product_type = ?";
                                        $params[] = $category;
                                    }
                                    
                                    // Search filter
                                    if(isset($_GET['search']) && !empty($_GET['search'])) {
                                        $search = mysqli_real_escape_string($conn, $_GET['search']);
                                        $sql .= " AND (product_name LIKE ? OR product_description LIKE ? OR product_type LIKE ?)";
                                        $params[] = "%$search%";
                                        $params[] = "%$search%";
                                        $params[] = "%$search%";
                                    }
                                    
                                    // Sorting
                                    if(isset($_GET['sort'])) {
                                        switch($_GET['sort']) {
                                            case 'newest':
                                                $sql .= " ORDER BY product_id DESC";
                                                break;
                                            case 'price_low':
                                                $sql .= " ORDER BY product_price ASC";
                                                break;
                                            case 'price_high':
                                                $sql .= " ORDER BY product_price DESC";
                                                break;
                                            case 'name_asc':
                                                $sql .= " ORDER BY product_name ASC";
                                                break;
                                            case 'name_desc':
                                                $sql .= " ORDER BY product_name DESC";
                                                break;
                                            default:
                                                $sql .= " ORDER BY product_id DESC";
                                        }
                                    } else {
                                        $sql .= " ORDER BY product_id DESC";
                                    }
                                    
                                    // Prepare and execute query
                                    $stmt = mysqli_prepare($conn, $sql);
                                    if (!empty($params)) {
                                        $types = str_repeat('s', count($params));
                                        mysqli_stmt_bind_param($stmt, $types, ...$params);
                                    }
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    
                                    // Count total products
                                    $total_products = mysqli_num_rows($result);
                                    
                                    // Loop through products
                                    while($product = mysqli_fetch_assoc($result)) {
                                        $image_url = !empty($product['product_image_url']) ? 
                                            (strpos($product['product_image_url'], 'http') === 0 ? 
                                                $product['product_image_url'] : 
                                                'assets/images/products/' . basename($product['product_image_url'])) : 
                                            'assets/images/products/default.jpg';
                                    ?>
                                    <div class="col-lg-4 col-md-6 col-12">
                                        <!-- Start Single Product -->
                                        <div class="single-product">
                                            <div class="product-image">
                                                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                <div class="button">
                                                    <a href="product-details.php?id=<?php echo $product['product_id']; ?>" class="btn"><i></i>Detail Product</a>
                                                </div>
                                            </div>
                                            <div class="product-info">
                                                <span class="category"><?php echo htmlspecialchars($product['product_type']); ?></span>
                                                <h4 class="title">
                                                    <a href="product-details.php?id=<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['product_name']); ?></a>
                                                </h4>
                                                <div class="price">
                                                    <span>Rp <?php echo number_format($product['product_price'], 0, ',', '.'); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Single Product -->
                                    </div>
                                    <?php
                                    }
                                    
                                    // Close connection
                                    mysqli_close($conn);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Product Grids -->

    <!-- Start Footer Area -->
    <?php include 'includes/footer.php'; ?>
    <!-- End Footer Area -->

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function sortProducts(sortValue) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sortValue);
            window.location.href = url.toString();
        }
    </script>
</body>

</html> 