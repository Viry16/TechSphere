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

// Validate and format data for display
$user_name = isset($user['user_name']) ? htmlspecialchars($user['user_name']) : '';
$user_email = isset($user['user_email']) ? htmlspecialchars($user['user_email']) : '';
$user_phone_number = isset($user['user_phone_number']) ? htmlspecialchars($user['user_phone_number']) : '';
$user_gender = isset($user['user_gender']) ? htmlspecialchars($user['user_gender']) : '';
$user_bio = isset($user['user_bio']) ? htmlspecialchars($user['user_bio']) : '';
$user_birthdate = isset($user['user_birthdate']) && $user['user_birthdate'] != '0000-00-00' ? $user['user_birthdate'] : '';
$user_imgprofile = isset($user['user_imgprofile']) ? htmlspecialchars($user['user_imgprofile']) : '';

// CSS for default profile image
$initials = getInitials($user_name);
$bgColor = getColorFromName($user_name);
$defaultImageStyle = "background-color: " . $bgColor . "; color: white; display: flex; align-items: center; justify-content: center; font-size: 60px; font-weight: bold; width: 100%; height: 100%; border-radius: 50%;";

// Process profile update form
if(isset($_POST['update_profile'])) {
    // Validate input
    $user_name = isset($_POST['user_name']) ? mysqli_real_escape_string($conn, $_POST['user_name']) : '';
    $user_email = isset($_POST['user_email']) ? mysqli_real_escape_string($conn, $_POST['user_email']) : '';
    $user_phone_number = isset($_POST['user_phone_number']) ? mysqli_real_escape_string($conn, $_POST['user_phone_number']) : '';
    $user_gender = isset($_POST['user_gender']) ? mysqli_real_escape_string($conn, $_POST['user_gender']) : '';
    $user_bio = isset($_POST['user_bio']) ? mysqli_real_escape_string($conn, $_POST['user_bio']) : '';
    $user_birthdate = isset($_POST['user_birthdate']) ? mysqli_real_escape_string($conn, $_POST['user_birthdate']) : '';
    
    // Validate email
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Upload profile photo if provided
        if(isset($_FILES['user_imgprofile']) && $_FILES['user_imgprofile']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $filename = $_FILES['user_imgprofile']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if(in_array(strtolower($ext), $allowed)) {
                // Make sure profile directory exists
                $upload_dir = 'assets/images/profile/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $new_filename;
                
                if(move_uploaded_file($_FILES['user_imgprofile']['tmp_name'], $destination)) {
                    // Delete old image if exists and not default
                    if (!empty($user['user_imgprofile']) && file_exists($user['user_imgprofile']) && strpos($user['user_imgprofile'], 'default.jpg') === false) {
                        @unlink($user['user_imgprofile']);
                    }
                    
                    $user_imgprofile = $destination;
                    $update_img = ", user_imgprofile = '$user_imgprofile'";
                    
                    // Debug: Show file path
                    // echo "File successfully uploaded to: " . $destination;
                    // exit;
                } else {
                    $error = "Failed to upload image.";
                    $update_img = "";
                }
            } else {
                $error = "File format not allowed. Only JPG, JPEG, PNG, and GIF are permitted.";
                $update_img = "";
            }
        } else {
            $update_img = "";
        }
        
        // Update data in database using prepared statement
        $update_sql = "UPDATE users SET 
                      user_name = ?,
                      user_email = ?,
                      user_phone_number = ?,
                      user_gender = ?,
                      user_bio = ?,
                      user_birthdate = ?
                      $update_img
                      WHERE user_id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssssi", $user_name, $user_email, $user_phone_number, $user_gender, $user_bio, $user_birthdate, $user_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Edit Profile - TechSphere</title>
    <meta name="description" content="Update your TechSphere user profile" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.png" />

    <!-- CSS here -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <style>
        .edit-profile-container {
            padding: 50px 0;
        }
        
        .profile-card {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .profile-image-container {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border: 5px solid #fff;
        }
        
        .profile-image-container img {
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
            text-transform: uppercase;
        }
        
        .profile-image-upload {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.5);
            padding: 10px;
            text-align: center;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .profile-image-container:hover .profile-image-upload {
            transform: translateY(0);
        }
        
        .profile-image-upload label {
            color: white;
            cursor: pointer;
            display: block;
            font-size: 14px;
        }
        
        .profile-image-upload input[type="file"] {
            display: none;
        }
        
        .file-format-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            text-align: center;
        }
        
        .info-form {
            padding: 30px;
        }
        
        .info-form h4 {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
            color: #333;
            font-weight: 600;
        }
        
        .info-form h4:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: #0167F3;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
        }
        
        .form-control:focus {
            border-color: #0167F3;
            box-shadow: 0 0 0 0.2rem rgba(1, 103, 243, 0.15);
        }
        
        .btn-action-group {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        
        .btn-action-group .btn {
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        /* Form group spacing */
        .form-group {
            margin-bottom: 20px;
        }
        
        /* Gender radio buttons */
        .gender-options {
            display: flex;
            gap: 15px;
        }
        
        .gender-option {
            flex: 1;
            position: relative;
        }
        
        .gender-option input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .gender-option label {
            display: block;
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .gender-option input:checked + label {
            background-color: #0167F3;
            color: white;
            border-color: #0167F3;
            box-shadow: 0 0 10px rgba(1, 103, 243, 0.2);
        }
        
        .gender-option label:hover {
            border-color: #0167F3;
            background-color: rgba(1, 103, 243, 0.05);
        }
        
        /* Alert styling */
        .alert-danger {
            border-radius: 8px;
            border-left: 4px solid #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
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
                        <h1 class="page-title">Edit Profile</h1>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li>Edit Profile</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Edit Profile Area -->
    <section class="edit-profile-container section">
        <div class="container">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="lni lni-warning"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-12">
                        <!-- Profile Image Section -->
                        <div class="profile-card">
                            <div class="profile-image-container">
                                <?php if (!empty($user_imgprofile)): ?>
                                    <img src="<?php echo $user_imgprofile; ?>" id="imagePreview" alt="Profile Image">
                                <?php else: ?>
                                    <div class="default-profile-image" id="imagePreview" style="background-color: <?php echo $bgColor; ?>">
                                        <?php echo $initials; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="profile-image-upload">
                                    <label for="user_imgprofile">
                                        <i class="lni lni-camera"></i> Change Photo
                                    </label>
                                    <input type="file" id="user_imgprofile" name="user_imgprofile" accept="image/*" onchange="previewImage(this)">
                                </div>
                            </div>
                            <div class="file-format-info">
                                Format: JPG, JPEG, PNG, GIF. Max: 2MB
                            </div>
                            
                            <div class="info-form">
                                <h4>Tips</h4>
                                <ul class="ps-3">
                                    <li>Use your real photo</li>
                                    <li>Make sure your face is clearly visible</li>
                                    <li>Avoid images with complex textures</li>
                                    <li>Recommended photo size: 400 x 400 pixels</li>
                                </ul>
                                
                                <div class="mt-4">
                                    <a href="profile.php" class="btn btn-outline-secondary w-100">
                                        <i class="lni lni-arrow-left"></i> Back to Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8 col-md-12 col-12">
                        <!-- Personal Information Form -->
                        <div class="profile-card">
                            <div class="info-form">
                                <h4>Personal Information</h4>
                                
                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label for="user_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo $user_name; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 form-group">
                                        <label for="user_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="user_email" name="user_email" value="<?php echo $user_email; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 form-group">
                                        <label for="user_phone_number" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="user_phone_number" name="user_phone_number" value="<?php echo $user_phone_number; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 form-group">
                                        <label class="form-label d-block">Gender</label>
                                        <div class="gender-options">
                                            <div class="gender-option">
                                                <input class="form-check-input" type="radio" name="user_gender" id="genderM" value="M" <?php echo ($user_gender == 'M') ? 'checked' : ''; ?>>
                                                <label for="genderM">
                                                    <i class="lni lni-user"></i> Male
                                                </label>
                                            </div>
                                            <div class="gender-option">
                                                <input class="form-check-input" type="radio" name="user_gender" id="genderF" value="F" <?php echo ($user_gender == 'F') ? 'checked' : ''; ?>>
                                                <label for="genderF">
                                                    <i class="lni lni-user"></i> Female
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 form-group">
                                        <label for="user_birthdate" class="form-label">Birth Date</label>
                                        <input type="date" class="form-control" id="user_birthdate" name="user_birthdate" value="<?php echo $user_birthdate; ?>">
                                    </div>
                                    
                                    <div class="col-12 form-group">
                                        <label for="user_bio" class="form-label">Bio</label>
                                        <textarea class="form-control" id="user_bio" name="user_bio" rows="4" placeholder="Tell us a bit about yourself..."><?php echo $user_bio; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="btn-action-group">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="lni lni-save"></i> Save Changes
                                    </button>
                                    <a href="profile.php" class="btn btn-outline-secondary">
                                        <i class="lni lni-close"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!-- End Edit Profile Area -->

    <!-- Start Footer Area -->
    <?php include 'includes/footer.php'; ?>
    <!-- End Footer Area -->

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/tiny-slider.js"></script>
    <script src="assets/js/glightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Preview image before upload
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('imagePreview');
                    
                    // If it's a text-based display (div), create an image element
                    if (preview.tagName.toLowerCase() === 'div') {
                        // Create a new image element
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Profile Image';
                        
                        // Replace the div with the new image
                        preview.parentNode.replaceChild(img, preview);
                    } else {
                        // It's already an image, just update the src
                        preview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html> 