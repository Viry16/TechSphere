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

// Function to get initials from name
function getInitials($name) {
    $words = explode(" ", $name);
    $initials = "";
    
    foreach ($words as $w) {
        if (!empty($w[0])) {
            $initials .= strtoupper($w[0]);
            if (strlen($initials) == 2) break;
        }
    }
    
    if (strlen($initials) < 2 && !empty($words[0][1])) {
        $initials .= strtoupper($words[0][1]);
    }
    
    return strlen($initials) > 0 ? $initials : "U";
}

// Function to get background color based on name
function getColorFromName($name) {
    $colors = [
        '#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#34495e',
        '#16a085', '#27ae60', '#2980b9', '#8e44ad', '#2c3e50',
        '#f1c40f', '#e67e22', '#e74c3c', '#95a5a6', '#f39c12',
        '#d35400', '#c0392b', '#7f8c8d'
    ];
    
    $hash = 0;
    for ($i = 0; $i < strlen($name); $i++) {
        $hash = ord($name[$i]) + (($hash << 5) - $hash);
    }
    
    return $colors[abs($hash) % count($colors)];
}

// Get user data from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT user_name, user_email, user_phone_number, user_gender, user_bio, user_birthdate, user_imgprofile 
        FROM users 
        WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Validate and format data
$user_name = isset($user['user_name']) ? htmlspecialchars($user['user_name']) : '';
$user_email = isset($user['user_email']) ? htmlspecialchars($user['user_email']) : '';
$user_phone_number = isset($user['user_phone_number']) ? htmlspecialchars($user['user_phone_number']) : '-';
$user_gender = isset($user['user_gender']) ? htmlspecialchars($user['user_gender']) : '';
$user_bio = isset($user['user_bio']) ? htmlspecialchars($user['user_bio']) : '';
$user_birthdate = isset($user['user_birthdate']) ? htmlspecialchars($user['user_birthdate']) : '';
$user_imgprofile = isset($user['user_imgprofile']) ? htmlspecialchars($user['user_imgprofile']) : '';

// Format birthdate
if (!empty($user_birthdate) && $user_birthdate != '0000-00-00') {
    $birthdate = new DateTime($user_birthdate);
    $formatted_birthdate = $birthdate->format('d F Y');
} else {
    $formatted_birthdate = 'Not set';
}

// Format gender
$gender_display = '';
if ($user_gender == 'M') {
    $gender_display = 'Male';
} elseif ($user_gender == 'F') {
    $gender_display = 'Female';
} else {
    $gender_display = '-';
}

// Get address data
$sql_address = "SELECT * FROM address WHERE user_id = ?";
$stmt_address = mysqli_prepare($conn, $sql_address);
mysqli_stmt_bind_param($stmt_address, "i", $user_id);
mysqli_stmt_execute($stmt_address);
$result_address = mysqli_stmt_get_result($stmt_address);
mysqli_stmt_close($stmt_address);

// Get message
$success_message = '';
if(isset($_SESSION['message'])) {
    $success_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// CSS for default profile image
$initials = getInitials($user_name);
$bgColor = getColorFromName($user_name);
$defaultImageStyle = "background-color: " . $bgColor . "; color: white; display: flex; align-items: center; justify-content: center; font-size: 60px; font-weight: bold; width: 100%; height: 100%; border-radius: 50%;";
?>

<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>User Profile - TechSphere</title>
    <meta name="description" content="TechSphere user profile page" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- CSS here -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <style>
        .profile-container {
            padding: 50px 0;
        }
        
        .profile-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #0167F3, #3f84ff);
            height: 120px;
            position: relative;
        }
        
        .profile-image-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
            margin-top: -75px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .default-profile-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        
        .profile-body {
            padding: 85px 25px 25px;
            text-align: center;
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .profile-email {
            color: #777;
            margin-bottom: 25px;
        }
        
        .profile-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }
        
        .profile-actions .btn {
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
        }
        
        .info-card {
            padding: 30px;
        }
        
        .info-card h4 {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: #333;
            font-weight: 600;
        }
        
        .info-card h4:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: #0167F3;
        }
        
        .data-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: flex-start;
        }
        
        .data-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .data-label {
            font-weight: 600;
            color: #333;
            width: 140px;
            margin-right: 15px;
        }
        
        .data-value {
            flex: 1;
            color: #555;
        }
        
        .address-card {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
            position: relative;
        }
        
        .address-card h5 {
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }
        
        .address-card .address-badges {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .address-card .address-badges .badge {
            margin-left: 5px;
            padding: 5px 10px;
            font-size: 11px;
        }
        
        .address-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .address-actions .btn {
            padding: 6px 15px;
            font-size: 12px;
        }

        /* Alert styling */
        .alert-success {
            border-radius: 8px;
            border-left: 4px solid #28a745;
            background-color: rgba(40, 167, 69, 0.1);
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
    <?php include 'includes/header.php'; ?>
    <!-- End Header Area -->

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="breadcrumbs-content">
                        <h1 class="page-title">User Profile</h1>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                        <li>Profile</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Profile Area -->
    <section class="profile-container section">
        <div class="container">
            <?php if(!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <i class="lni lni-checkmark-circle"></i> <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-4 col-md-12 col-12">
                    <!-- Profile Card -->
                    <div class="profile-card">
                        <div class="profile-header"></div>
                        <div class="profile-image-wrapper">
                            <?php if (!empty($user_imgprofile)): ?>
                                <img src="<?php echo $user_imgprofile; ?>" class="profile-image" alt="Profile Image">
                            <?php else: ?>
                                <div class="default-profile-image" style="<?php echo $defaultImageStyle; ?>">
                                    <?php echo $initials; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-body">
                            <div class="profile-name">
                                <?php echo $user_name; ?>
                            </div>
                            <div class="profile-info">
                                <?php if(!empty($user_phone_number) && $user_phone_number != '-'): ?>
                                    <div class="info-item">
                                        <div class="info-icon"><i class="lni lni-phone"></i></div>
                                        <div class="info-text"><?php echo $user_phone_number; ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="profile-actions">
                                <a href="edit_profile.php" class="btn btn-primary">
                                    <i class="lni lni-pencil me-2"></i>Edit Profile
                                </a>
                                <a href="change_password.php" class="btn btn-outline-primary">
                                    <i class="lni lni-lock me-2"></i>Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Card -->
                    <div class="profile-card info-card">
                        <h4>Activity</h4>
                        <div class="data-item">
                            <div class="data-label">Orders</div>
                            <div class="data-value">
                                <a href="orders.php" class="text-primary">View My Orders</a>
                            </div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Reviews</div>
                            <div class="data-value">0</div>
                        </div>
                        <div class="data-item">
                            <div class="data-label">Wishlist</div>
                            <div class="data-value">0</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8 col-md-12 col-12">
                    <!-- Personal Information -->
                    <div class="profile-card info-card">
                        <h4>Personal Information</h4>
                        
                        <div class="data-item">
                            <div class="data-label">Full Name</div>
                            <div class="data-value"><?php echo $user_name; ?></div>
                        </div>
                        
                        <div class="data-item">
                            <div class="data-label">Email</div>
                            <div class="data-value"><?php echo $user_email; ?></div>
                        </div>
                        
                        <div class="data-item">
                            <div class="data-label">Phone Number</div>
                            <div class="data-value"><?php echo $user_phone_number; ?></div>
                        </div>
                        
                        <div class="data-item">
                            <div class="data-label">Gender</div>
                            <div class="data-value"><?php echo $gender_display; ?></div>
                        </div>
                        
                        <div class="data-item">
                            <div class="data-label">Birth Date</div>
                            <div class="data-value"><?php echo $formatted_birthdate; ?></div>
                        </div>
                        
                        <div class="data-item">
                            <div class="data-label">Bio</div>
                            <div class="data-value"><?php echo !empty($user_bio) ? $user_bio : '-'; ?></div>
                        </div>
                    </div>
                    
                    <!-- Addresses -->
                    <div class="profile-card info-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Addresses</h4>
                            <a href="add_address.php" class="btn btn-sm btn-primary">
                                <i class="lni lni-plus"></i> Add New Address
                            </a>
                        </div>
                        
                        <?php if(mysqli_num_rows($result_address) > 0): ?>
                            <?php while($address = mysqli_fetch_assoc($result_address)): ?>
                                <div class="address-card">
                                    <div class="address-badges">
                                        <?php if(isset($address['is_default']) && $address['is_default'] == 1): ?>
                                        <span class="badge bg-success">Default</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h5><?php echo isset($address['user_address_name']) ? $address['user_address_name'] : ''; ?></h5>
                                    <p>
                                        <?php echo isset($address['address_location']) ? $address['address_location'] : ''; ?><br>
                                        Number: <?php echo isset($address['address_number']) ? $address['address_number'] : ''; ?><br>
                                        <?php echo isset($address['address_city']) ? $address['address_city'] : ''; ?>, <?php echo isset($address['address_postal_code']) ? $address['address_postal_code'] : ''; ?><br>
                                        <?php echo isset($address['address_country']) ? $address['address_country'] : ''; ?>
                                    </p>
                                    
                                    <div class="address-actions">
                                        <a href="edit_address.php?id=<?php echo isset($address['user_address_name']) ? urlencode($address['user_address_name']) : ''; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="lni lni-pencil"></i> Edit
                                        </a>
                                        <a href="delete_address.php?id=<?php echo isset($address['user_address_name']) ? urlencode($address['user_address_name']) : ''; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this address?');">
                                            <i class="lni lni-trash"></i> Delete
                                        </a>
                                        <?php if(!isset($address['is_default']) || $address['is_default'] != 1): ?>
                                        <a href="set_default_address.php?id=<?php echo isset($address['user_address_name']) ? urlencode($address['user_address_name']) : ''; ?>" class="btn btn-sm btn-outline-success">
                                            <i class="lni lni-checkmark"></i> Set as Default
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <img src="assets/images/empty-address.svg" alt="No Addresses" style="max-width: 120px; opacity: 0.6;">
                                <p class="mt-3">No saved addresses yet</p>
                                <a href="add_address.php" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="lni lni-plus"></i> Add Address
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Profile Area -->

    <!-- Start Footer Area -->
    <?php include 'includes/footer.php'; ?>
    <!-- End Footer Area -->

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html> 