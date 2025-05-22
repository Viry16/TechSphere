<?php
session_start();
// Check if user is not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Process add address form
if(isset($_POST['add_address'])) {
    $user_address_name = mysqli_real_escape_string($conn, $_POST['user_address_name']);
    $address_location = mysqli_real_escape_string($conn, $_POST['address_location']);
    $address_number = mysqli_real_escape_string($conn, $_POST['address_number']);
    $address_postal_code = mysqli_real_escape_string($conn, $_POST['address_postal_code']);
    $address_city = mysqli_real_escape_string($conn, $_POST['address_city']);
    $address_country = mysqli_real_escape_string($conn, $_POST['address_country']);
    
    // Check if address name is already used
    $check_sql = "SELECT * FROM address WHERE user_id = '$user_id' AND user_address_name = '$user_address_name'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Address name already in use. Please choose another name.";
    } else {
        // Insert new address
        $insert_sql = "INSERT INTO address (user_id, user_address_name, address_location, address_number, address_postal_code, address_city, address_country) 
                       VALUES ('$user_id', '$user_address_name', '$address_location', '$address_number', '$address_postal_code', '$address_city', '$address_country')";
        
        if(mysqli_query($conn, $insert_sql)) {
            $_SESSION['message'] = "Address added successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Add Address - tech_sphere</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- CSS here -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <style>
        .add-address-container {
            padding: 30px 0;
        }
        .form-section {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-section h4 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
    </style>
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
    <header class="header navbar-area">
        <!-- Header content will be added here -->
        <!-- Use the same header as index.php -->
    </header>
    <!-- End Header Area -->

    <!-- Start Add Address Area -->
    <section class="add-address-container section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h2>Add New Address</h2>
                        <p>Add a new address for shipping</p>
                    </div>
                </div>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="form-section">
                            <h4>Address Information</h4>
                            
                            <div class="mb-3">
                                <label for="user_address_name" class="form-label">Address Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="user_address_name" name="user_address_name" placeholder="Example: Home, Office, etc" required>
                                <small class="text-muted">Name to identify this address</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address_location" class="form-label">Complete Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address_location" name="address_location" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address_number" class="form-label">Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="address_number" name="address_number" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="address_city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="address_city" name="address_city" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="address_postal_code" class="form-label">Postal Code <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="address_postal_code" name="address_postal_code" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address_country" name="address_country" value="Indonesia" required>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" name="add_address" class="btn btn-primary">Save Address</button>
                                <a href="profile.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!-- End Add Address Area -->

    <!-- Start Footer Area -->
    <footer class="footer">
        <!-- Footer content will be added here -->
        <!-- Use the same footer as index.php -->
    </footer>
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
</body>

</html> 