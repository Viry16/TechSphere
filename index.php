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
    <title>TechSphere</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />

</head>

<body>
    <!--[if lte IE 9]>
      <p class="browserupgrade">
        You are using an <strong>outdated</strong> browser. Please
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

    <?php include 'includes/header.php'; ?>

    <!-- Start Hero Area -->
    <section class="hero-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-12 custom-padding-right">
                    <div class="slider-head">
                        <!-- Start Hero Slider -->
                        <div class="hero-slider">
                            <!-- Start Single Slider -->
                            <div class="single-slider"
                                style="background-image: url(assets/images/hero/slider-bg3.jpg);">
                                <div class="content">
                                    <h2><span>Rp. 3.000.000</span>
                                    MSI B650 GAMING PLUS
                                    </h2>
                                    <p>The MSI B650 GAMING PLUS WIFI is a powerful and feature-rich ATX motherboard built for AMD’s latest AM5 platform, delivering great performance and connectivity for gamers and PC enthusiasts.</p>
                                    <h3><span>Now Only</span> Rp. 2.831.426</h3>
                                    <div class="button">
                                        <a href="product-grids.php" class="btn">Shop Now</a>
                                    </div>
                                </div>
                            </div>
                            <!-- End Single Slider -->
                        </div>
                        <!-- End Hero Slider -->
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-6 col-12 md-custom-padding">
                            <!-- Start Small Banner -->
                            <div class="hero-small-banner"
                                style="background-image: url('assets/images/hero/slider-bnr1.jpg');">
                                <div class="content">
                                    <h2>
                                        <span>New line required</span>
                                        i9-13900K
                                    </h2>
                                    <h3>Rp. 9.640.000</h3>
                                </div>
                            </div>
                            <!-- End Small Banner -->
                        </div>
                        <div class="col-lg-12 col-md-6 col-12">
                            <!-- Start Small Banner -->
                            <div class="hero-small-banner style2">
                                <div class="content">
                                    <h2>Weekly Sale!</h2>
                                    <p>Saving up to 10% off all online store items this week.</p>
                                    <div class="button">
                                        <a class="btn" href="product-grids.php">Shop Now</a>
                                    </div>
                                </div>
                            </div>
                            <!-- Start Small Banner -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br><br
    </section>

    <!-- End Hero Area -->

    <!-- Start Trending Product Area -->
    <section class="trending-product section" style="margin-top: 12px;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h2>Trending Product</h2>
                        <p>There are many variations of passages of Lorem Ipsum available, but the majority have
                            suffered alteration in some form.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php
                // Connect to database
                $conn = mysqli_connect("localhost", "root", "", "tech_sphere");
                
                // Check connection
                if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                }
                
                // Get trending products (products with highest sold_quantity)
                $sql = "SELECT p.*, 
                        CASE 
                            WHEN p.product_type = 'CPU' THEN 'Processor'
                            WHEN p.product_type = 'GPU' THEN 'Graphics Card'
                            ELSE p.product_type 
                        END AS product_category
                        FROM product p 
                        WHERE p.product_id IS NOT NULL
                        ORDER BY p.product_sold_quantity DESC LIMIT 8";
                $result = mysqli_query($conn, $sql);
                
                // Loop through products
                while($product = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Product -->
                    <div class="single-product">
                        <div class="product-image">
                            <img src="<?php echo getValidImageUrl(isset($product['product_image_url']) ? $product['product_image_url'] : ''); ?>" alt="<?php echo isset($product['product_name']) ? $product['product_name'] : ''; ?>">
                            <?php if (isset($product['discount_price']) && $product['discount_price'] > 0) { ?>
                                <span class="sale-tag">-<?php echo round(($product['product_price'] - $product['discount_price']) / $product['product_price'] * 100); ?>%</span>
                            <?php } ?>
                            <div class="button">
                                <a href="product-details.php?id=<?php echo isset($product['product_id']) ? $product['product_id'] : ''; ?>" class="btn"><i class="lni lni-cart"></i>Detail Product</a>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="category"><?php echo isset($product['product_category']) ? $product['product_category'] : ''; ?></span>
                            <h4 class="title">
                                <a href="product-details.php?id=<?php echo isset($product['product_id']) ? $product['product_id'] : ''; ?>"><?php echo isset($product['product_name']) ? $product['product_name'] : ''; ?></a>
                            </h4>                          
                            <div class="price">
                                <span>Rp <?php echo number_format(isset($product['product_price']) ? $product['product_price'] : 0, 0, ',', '.'); ?></span>
                                <?php if (isset($product['discount_price']) && $product['discount_price'] > 0) { ?>
                                    <span class="discount-price">Rp <?php echo number_format($product['discount_price'], 0, ',', '.'); ?></span>
                                <?php } ?>
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
    </section>
    <!-- End Trending Product Area -->

   
  

    <!-- Start Banner Area -->
    <section class="banner section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="single-banner" style="background-image:url('assets/images/banner/banner-4-bg.jpg')">
                        <div class="content">
                            <h2>Logitech G305 LIGHTSPEED</h2>
                            <p>Lightweight, fast, ultra-responsive. <br>PWireless precision for competitive gaming. </p>
                            <div class="button">
                                <a href="product-grids.php" class="btn">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="single-banner custom-responsive-margin"
                        style="background-image:url('assets/images/banner/banner-3-bg.jpg')">
                        <div class="content">
                            <h2>JBL Quantum 910 Headset</h2>
                            <p>Immersive sound, wireless freedom,  <br>pro precision.
                            Active noise canceling, clear voice chat.</p>
                            <div class="button">
                                <a href="product-grids.php" class="btn">Shop Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Banner Area -->

    <!-- Start Shipping Info -->
    <section class="shipping-info">
        <div class="container">
            <ul>
                <!-- Free Shipping -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-delivery"></i>
                    </div>
                    <div class="media-body">
                        <h5>Free Shipping</h5>
                        <span>On order over Rp. 500.000</span>
                    </div>
                </li>
                <!-- Money Return -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-support"></i>
                    </div>
                    <div class="media-body">
                        <h5>24/7 Support.</h5>
                        <span>Live Chat Or Call.</span>
                    </div>
                </li>
                <!-- Support 24/7 -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-credit-cards"></i>
                    </div>
                    <div class="media-body">
                        <h5>Online Payment.</h5>
                        <span>Secure Payment Services.</span>
                    </div>
                </li>
                <!-- Safe Payment -->
                <li>
                    <div class="media-icon">
                        <i class="lni lni-reload"></i>
                    </div>
                    <div class="media-body">
                        <h5>Easy Return.</h5>
                        <span>Hassle Free Shopping.</span>
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <!-- End Shipping Info -->

    <?php include 'includes/footer.php'; ?>

    <!-- ========================= scroll-top ========================= -->
    <a href="#" class="scroll-top">
        <i class="lni lni-chevron-up"></i>
    </a>

    <!-- ========================= JS here ========================= -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Aktifkan semua dropdown
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi dropdown Bootstrap
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            });
        });
    </script>
    <script type="text/javascript">
        //========= Hero Slider 
        tns({
            container: '.hero-slider',
            slideBy: 'page',
            autoplay: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            gutter: 0,
            items: 1,
            nav: false,
            controls: true,
            controlsText: ['<i class="lni lni-chevron-left"></i>', '<i class="lni lni-chevron-right"></i>'],
        });

        //======== Brand Slider
        tns({
            container: '.brands-logo-carousel',
            autoplay: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            gutter: 15,
            nav: false,
            controls: false,
            responsive: {
                0: {
                    items: 1,
                },
                540: {
                    items: 3,
                },
                768: {
                    items: 5,
                },
                992: {
                    items: 6,
                }
            }
        });
        
        // Remove item from cart
        function removeFromCart(productId) {
            if (confirm("Are you sure you want to remove this product from your cart?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "remove_from_cart.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                        // Reload page to reflect changes
                        window.location.reload();
                    }
                }
                xhr.send("product_id=" + productId);
            }
        }
    </script>
</body>

</html>