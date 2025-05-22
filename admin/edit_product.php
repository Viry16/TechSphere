<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin WHERE admin_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php?error=Invalid product ID");
    exit();
}

$product_id = $_GET['id'];

// Get product data
$product_sql = "SELECT * FROM product WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $product_sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$product_result = mysqli_stmt_get_result($stmt);

if (!$product_result || mysqli_num_rows($product_result) == 0) {
    header("Location: products.php?error=Product not found");
    exit();
}

$product = mysqli_fetch_assoc($product_result);

// Get specific product data based on type
$product_type = $product['product_type'];
$type_data = null;

switch($product_type) {
    case 'CPU':
        $type_sql = "SELECT * FROM product_cpu WHERE product_id = '$product_id'";
        break;
    case 'GPU':
        $type_sql = "SELECT * FROM product_gpu WHERE product_id = '$product_id'";
        break;
    case 'RAM':
        $type_sql = "SELECT * FROM product_ram WHERE product_id = '$product_id'";
        break;
    case 'Motherboard':
        $type_sql = "SELECT * FROM product_motherboard WHERE product_id = '$product_id'";
        break;
    case 'Storage':
        $type_sql = "SELECT * FROM product_storage WHERE product_id = '$product_id'";
        break;
    case 'PSU':
        $type_sql = "SELECT * FROM product_psu WHERE product_id = '$product_id'";
        break;
    case 'Case':
        $type_sql = "SELECT * FROM product_case WHERE product_id = '$product_id'";
        break;
    case 'Cooling':
        $type_sql = "SELECT * FROM product_cooling WHERE product_id = '$product_id'";
        break;
    case 'Monitor':
        $type_sql = "SELECT * FROM product_monitor WHERE product_id = '$product_id'";
        break;
    case 'Accessories':
        $type_sql = "SELECT * FROM product_accessories WHERE product_id = '$product_id'";
        break;
}

if (isset($type_sql)) {
    $type_result = mysqli_query($conn, $type_sql);
    if ($type_result && mysqli_num_rows($type_result) > 0) {
        $type_data = mysqli_fetch_assoc($type_result);
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $model_number = mysqli_real_escape_string($conn, $_POST['model_number']);
    $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
    $product_brand = !empty($_POST['product_brand']) ? mysqli_real_escape_string($conn, $_POST['product_brand']) : null;
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_stock_quantity = mysqli_real_escape_string($conn, $_POST['product_stock_quantity']);
    $dimensions = !empty($_POST['dimensions']) ? mysqli_real_escape_string($conn, $_POST['dimensions']) : null;
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $color = !empty($_POST['color']) ? mysqli_real_escape_string($conn, $_POST['color']) : null;
    $release_date = !empty($_POST['release_date']) ? mysqli_real_escape_string($conn, $_POST['release_date']) : null;
    $warranty = !empty($_POST['warranty']) ? mysqli_real_escape_string($conn, $_POST['warranty']) : null;
    
    // Get type-specific data based on product type
    $specific_fields = [];
    
    switch($product_type) {
        case 'CPU':
            $specific_fields['socket_type'] = !empty($_POST['socket_type']) ? mysqli_real_escape_string($conn, $_POST['socket_type']) : null;
            $specific_fields['core_count'] = !empty($_POST['core_count']) ? mysqli_real_escape_string($conn, $_POST['core_count']) : null;
            $specific_fields['thread_count'] = !empty($_POST['thread_count']) ? mysqli_real_escape_string($conn, $_POST['thread_count']) : null;
            $specific_fields['base_clock'] = !empty($_POST['base_clock']) ? mysqli_real_escape_string($conn, $_POST['base_clock']) : null;
            $specific_fields['boost_clock'] = !empty($_POST['boost_clock']) ? mysqli_real_escape_string($conn, $_POST['boost_clock']) : null;
            $specific_fields['tdp'] = !empty($_POST['tdp']) ? mysqli_real_escape_string($conn, $_POST['tdp']) : null;
            $specific_fields['integrated_graphics'] = !empty($_POST['integrated_graphics']) ? mysqli_real_escape_string($conn, $_POST['integrated_graphics']) : null;
            $specific_fields['l3_cache'] = !empty($_POST['l3_cache']) ? mysqli_real_escape_string($conn, $_POST['l3_cache']) : null;
            $specific_fields['manufacturing_process'] = !empty($_POST['manufacturing_process']) ? mysqli_real_escape_string($conn, $_POST['manufacturing_process']) : null;
            break;
            
        case 'GPU':
            $specific_fields['gpu_chipset'] = !empty($_POST['gpu_chipset']) ? mysqli_real_escape_string($conn, $_POST['gpu_chipset']) : null;
            $specific_fields['vram'] = !empty($_POST['vram']) ? mysqli_real_escape_string($conn, $_POST['vram']) : null;
            $specific_fields['vram_type'] = !empty($_POST['vram_type']) ? mysqli_real_escape_string($conn, $_POST['vram_type']) : null;
            $specific_fields['gpu_clock'] = !empty($_POST['gpu_clock']) ? mysqli_real_escape_string($conn, $_POST['gpu_clock']) : null;
            $specific_fields['boost_clock_gpu'] = !empty($_POST['boost_clock_gpu']) ? mysqli_real_escape_string($conn, $_POST['boost_clock_gpu']) : null;
            $specific_fields['memory_interface'] = !empty($_POST['memory_interface']) ? mysqli_real_escape_string($conn, $_POST['memory_interface']) : null;
            $specific_fields['gpu_length'] = !empty($_POST['gpu_length']) ? mysqli_real_escape_string($conn, $_POST['gpu_length']) : null;
            $specific_fields['power_connectors'] = !empty($_POST['power_connectors']) ? mysqli_real_escape_string($conn, $_POST['power_connectors']) : null;
            $specific_fields['recommended_psu'] = !empty($_POST['recommended_psu']) ? mysqli_real_escape_string($conn, $_POST['recommended_psu']) : null;
            break;
            
        case 'RAM':
            $specific_fields['ram_type'] = !empty($_POST['ram_type']) ? mysqli_real_escape_string($conn, $_POST['ram_type']) : null;
            $specific_fields['ram_speed'] = !empty($_POST['ram_speed']) ? mysqli_real_escape_string($conn, $_POST['ram_speed']) : null;
            $specific_fields['ram_capacity'] = !empty($_POST['ram_capacity']) ? mysqli_real_escape_string($conn, $_POST['ram_capacity']) : null;
            $specific_fields['ram_latency'] = !empty($_POST['ram_latency']) ? mysqli_real_escape_string($conn, $_POST['ram_latency']) : null;
            $specific_fields['ram_voltage'] = !empty($_POST['ram_voltage']) ? mysqli_real_escape_string($conn, $_POST['ram_voltage']) : null;
            $specific_fields['ram_modules'] = !empty($_POST['ram_modules']) ? mysqli_real_escape_string($conn, $_POST['ram_modules']) : null;
            $specific_fields['ecc_support'] = isset($_POST['ecc_support']) ? 1 : 0;
            $specific_fields['rgb_support'] = isset($_POST['rgb_support']) ? 1 : 0;
            break;
            
        case 'Motherboard':
            $specific_fields['motherboard_form_factor'] = !empty($_POST['motherboard_form_factor']) ? mysqli_real_escape_string($conn, $_POST['motherboard_form_factor']) : null;
            $specific_fields['motherboard_chipset'] = !empty($_POST['motherboard_chipset']) ? mysqli_real_escape_string($conn, $_POST['motherboard_chipset']) : null;
            $specific_fields['socket_type'] = !empty($_POST['socket_type']) ? mysqli_real_escape_string($conn, $_POST['socket_type']) : null;
            $specific_fields['memory_slots'] = !empty($_POST['memory_slots']) ? mysqli_real_escape_string($conn, $_POST['memory_slots']) : null;
            $specific_fields['max_memory'] = !empty($_POST['max_memory']) ? mysqli_real_escape_string($conn, $_POST['max_memory']) : null;
            $specific_fields['sata_ports'] = !empty($_POST['sata_ports']) ? mysqli_real_escape_string($conn, $_POST['sata_ports']) : null;
            $specific_fields['m2_slots'] = !empty($_POST['m2_slots']) ? mysqli_real_escape_string($conn, $_POST['m2_slots']) : null;
            $specific_fields['pcie_slots'] = !empty($_POST['pcie_slots']) ? mysqli_real_escape_string($conn, $_POST['pcie_slots']) : null;
            $specific_fields['wifi_support'] = isset($_POST['wifi_support']) ? 1 : 0;
            $specific_fields['bluetooth_support'] = isset($_POST['bluetooth_support']) ? 1 : 0;
            break;
            
        case 'Storage':
            $specific_fields['storage_type'] = !empty($_POST['storage_type']) ? mysqli_real_escape_string($conn, $_POST['storage_type']) : null;
            $specific_fields['storage_capacity'] = !empty($_POST['storage_capacity']) ? mysqli_real_escape_string($conn, $_POST['storage_capacity']) : null;
            $specific_fields['read_speed'] = !empty($_POST['read_speed']) ? mysqli_real_escape_string($conn, $_POST['read_speed']) : null;
            $specific_fields['write_speed'] = !empty($_POST['write_speed']) ? mysqli_real_escape_string($conn, $_POST['write_speed']) : null;
            $specific_fields['interface'] = !empty($_POST['interface']) ? mysqli_real_escape_string($conn, $_POST['interface']) : null;
            $specific_fields['rpm'] = !empty($_POST['rpm']) ? mysqli_real_escape_string($conn, $_POST['rpm']) : null;
            $specific_fields['endurance_rating'] = !empty($_POST['endurance_rating']) ? mysqli_real_escape_string($conn, $_POST['endurance_rating']) : null;
            $specific_fields['nvme_protocol'] = !empty($_POST['nvme_protocol']) ? mysqli_real_escape_string($conn, $_POST['nvme_protocol']) : null;
            break;
            
        case 'PSU':
            $specific_fields['psu_wattage'] = !empty($_POST['psu_wattage']) ? (int)$_POST['psu_wattage'] : 0;
            $specific_fields['psu_efficiency'] = !empty($_POST['psu_efficiency']) ? $_POST['psu_efficiency'] : '80+ White';
            $specific_fields['psu_modular'] = !empty($_POST['psu_modular']) ? $_POST['psu_modular'] : 'Non-modular';
            $specific_fields['psu_form_factor'] = !empty($_POST['psu_form_factor']) ? $_POST['psu_form_factor'] : 'ATX';
            $specific_fields['pcie_connectors'] = !empty($_POST['pcie_connectors']) ? (int)$_POST['pcie_connectors'] : 0;
            $specific_fields['sata_connectors'] = !empty($_POST['sata_connectors']) ? (int)$_POST['sata_connectors'] : 0;
            $specific_fields['cpu_connectors'] = !empty($_POST['cpu_connectors']) ? $_POST['cpu_connectors'] : '4+4 pin';
            break;
            
        case 'Case':
            $specific_fields['case_type'] = !empty($_POST['case_type']) ? mysqli_real_escape_string($conn, $_POST['case_type']) : null;
            $specific_fields['case_form_factor'] = !empty($_POST['case_form_factor']) ? mysqli_real_escape_string($conn, $_POST['case_form_factor']) : null;
            $specific_fields['expansion_slots'] = !empty($_POST['expansion_slots']) ? mysqli_real_escape_string($conn, $_POST['expansion_slots']) : null;
            $specific_fields['drive_bays'] = !empty($_POST['drive_bays']) ? mysqli_real_escape_string($conn, $_POST['drive_bays']) : null;
            $specific_fields['radiator_support'] = !empty($_POST['radiator_support']) ? mysqli_real_escape_string($conn, $_POST['radiator_support']) : null;
            $specific_fields['included_fans'] = !empty($_POST['included_fans']) ? mysqli_real_escape_string($conn, $_POST['included_fans']) : null;
            $specific_fields['max_gpu_length'] = !empty($_POST['max_gpu_length']) ? mysqli_real_escape_string($conn, $_POST['max_gpu_length']) : null;
            $specific_fields['max_cpu_cooler_height'] = !empty($_POST['max_cpu_cooler_height']) ? mysqli_real_escape_string($conn, $_POST['max_cpu_cooler_height']) : null;
            $specific_fields['side_panel_type'] = !empty($_POST['side_panel_type']) ? mysqli_real_escape_string($conn, $_POST['side_panel_type']) : null;
            break;
            
        case 'Cooling':
            $specific_fields['cooling_type'] = !empty($_POST['cooling_type']) ? mysqli_real_escape_string($conn, $_POST['cooling_type']) : null;
            $specific_fields['fan_size'] = !empty($_POST['fan_size']) ? mysqli_real_escape_string($conn, $_POST['fan_size']) : null;
            $specific_fields['noise_level'] = !empty($_POST['noise_level']) ? mysqli_real_escape_string($conn, $_POST['noise_level']) : null;
            $specific_fields['radiator_size'] = !empty($_POST['radiator_size']) ? mysqli_real_escape_string($conn, $_POST['radiator_size']) : null;
            $specific_fields['compatibility'] = !empty($_POST['compatibility']) ? mysqli_real_escape_string($conn, $_POST['compatibility']) : null;
            $specific_fields['max_tdp'] = !empty($_POST['max_tdp']) ? mysqli_real_escape_string($conn, $_POST['max_tdp']) : null;
            $specific_fields['rgb_support'] = isset($_POST['rgb_support']) ? 1 : 0;
            break;
            
        case 'Monitor':
            $specific_fields['screen_size'] = !empty($_POST['screen_size']) ? mysqli_real_escape_string($conn, $_POST['screen_size']) : null;
            $specific_fields['resolution'] = !empty($_POST['resolution']) ? mysqli_real_escape_string($conn, $_POST['resolution']) : null;
            $specific_fields['refresh_rate'] = !empty($_POST['refresh_rate']) ? mysqli_real_escape_string($conn, $_POST['refresh_rate']) : null;
            $specific_fields['response_time'] = !empty($_POST['response_time']) ? mysqli_real_escape_string($conn, $_POST['response_time']) : null;
            $specific_fields['panel_type'] = !empty($_POST['panel_type']) ? mysqli_real_escape_string($conn, $_POST['panel_type']) : null;
            $specific_fields['aspect_ratio'] = !empty($_POST['aspect_ratio']) ? mysqli_real_escape_string($conn, $_POST['aspect_ratio']) : null;
            $specific_fields['adaptive_sync'] = !empty($_POST['adaptive_sync']) ? mysqli_real_escape_string($conn, $_POST['adaptive_sync']) : null;
            $specific_fields['hdr_support'] = isset($_POST['hdr_support']) ? 1 : 0;
            $specific_fields['curved'] = isset($_POST['curved']) ? 1 : 0;
            $specific_fields['vesa_mount'] = !empty($_POST['vesa_mount']) ? mysqli_real_escape_string($conn, $_POST['vesa_mount']) : null;
            break;
            
        case 'Accessories':
            $specific_fields['accessory_type'] = !empty($_POST['accessory_type']) ? mysqli_real_escape_string($conn, $_POST['accessory_type']) : null;
            $specific_fields['compatibility_notes'] = !empty($_POST['compatibility_notes']) ? mysqli_real_escape_string($conn, $_POST['compatibility_notes']) : null;
            $specific_fields['included_accessories'] = !empty($_POST['included_accessories']) ? mysqli_real_escape_string($conn, $_POST['included_accessories']) : null;
            $specific_fields['connectivity'] = !empty($_POST['connectivity']) ? mysqli_real_escape_string($conn, $_POST['connectivity']) : null;
            $specific_fields['material'] = !empty($_POST['material']) ? mysqli_real_escape_string($conn, $_POST['material']) : null;
            break;
    }
    
    // Handle image upload
    $product_image_url = $product['product_image_url']; // Default to existing image
    
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "../assets/images/products/";
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                $error_message = "Failed to create upload directory: " . $target_dir;
            }
        }
        
        if (!isset($error_message)) {
            $file_extension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            
            // Validate file extension
            if (in_array($file_extension, $allowed_extensions)) {
                $file_name = uniqid('product_') . '.' . $file_extension;
                $target_file = $target_dir . $file_name;
                
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                    // Delete old image if it exists
                    if (!empty($product['product_image_url']) && file_exists($product['product_image_url']) && $product['product_image_url'] != $target_file) {
                        @unlink($product['product_image_url']);
                    }
                    $product_image_url = $target_file;
                } else {
                    $error_message = "Failed to upload image. Error: " . $_FILES['product_image']['error'];
                }
            } else {
                $error_message = "Only JPG, JPEG, PNG and GIF files are allowed";
            }
        }
    }
    
    if (!isset($error_message)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Update product in main product table
            $update_sql = "UPDATE product SET 
                          product_name = ?, 
                          model_number = ?, 
                          product_type = ?,
                          product_brand = ?,
                          dimensions = ?,
                          weight = ?,
                          color = ?,
                          release_date = ?,
                          warranty = ?,
                          product_description = ?, 
                          product_price = ?, 
                          product_stock_quantity = ?, 
                          product_image_url = ?
                          WHERE product_id = ?";
            
            $stmt = mysqli_prepare($conn, $update_sql);
            
            // Prepare values for binding
            mysqli_stmt_bind_param($stmt, "ssssssssssdssi", 
                $product_name,
                $model_number,
                $product_type,
                $product_brand,
                $dimensions,
                $weight,
                $color,
                $release_date,
                $warranty,
                $product_description,
                $product_price,
                $product_stock_quantity,
                $product_image_url,
                $product_id
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error updating product: " . mysqli_error($conn));
            }
            
            // Update specific product type data if available
            if (!empty($specific_fields)) {
                $type_table = 'product_' . strtolower($product_type);
                
                // Check if entry exists in type table
                $check_sql = "SELECT * FROM $type_table WHERE product_id = ?";
                $stmt = mysqli_prepare($conn, $check_sql);
                mysqli_stmt_bind_param($stmt, "i", $product_id);
                mysqli_stmt_execute($stmt);
                $check_result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($check_result) > 0) {
                    // Update existing record
                    $update_fields = [];
                    $types = "";
                    $values = [];
                    
                    foreach ($specific_fields as $field => $value) {
                        $update_fields[] = "$field = ?";
                        $types .= "s";
                        $values[] = $value;
                    }
                    
                    $types .= "i"; // for product_id
                    $values[] = $product_id;
                    
                    $type_update_sql = "UPDATE $type_table SET " . implode(", ", $update_fields) . " WHERE product_id = ?";
                    $stmt = mysqli_prepare($conn, $type_update_sql);
                    mysqli_stmt_bind_param($stmt, $types, ...$values);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error updating $type_table: " . mysqli_error($conn));
                    }
                } else {
                    // Insert new record
                    $field_names = array_keys($specific_fields);
                    $field_names[] = 'product_id';
                    
                    $placeholders = array_fill(0, count($field_names), '?');
                    $types = str_repeat('s', count($field_names) - 1) . 'i';
                    
                    $values = array_values($specific_fields);
                    $values[] = $product_id;
                    
                    $type_insert_sql = "INSERT INTO $type_table (" . implode(", ", $field_names) . ") VALUES (" . implode(", ", $placeholders) . ")";
                    $stmt = mysqli_prepare($conn, $type_insert_sql);
                    mysqli_stmt_bind_param($stmt, $types, ...$values);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Error inserting into $type_table: " . mysqli_error($conn));
                    }
                }
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Redirect to product list with success message
            header("Location: products.php?success=Product updated successfully");
            exit();
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($conn);
            $error_message = "Error updating product: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Tech Sphere Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            background-color: #343a40;
        }
        .sidebar-link {
            color: #ffffff;
            padding: 15px;
            display: block;
            text-decoration: none;
            border-bottom: 1px solid #444;
        }
        .sidebar-link:hover {
            background-color: #495057;
            color: #ffffff;
        }
        .sidebar-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .sidebar-link i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
        }
        .admin-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        .product-image-preview {
            max-height: 150px;
            max-width: 200px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3 text-center text-white">
                    <h4>TECH SPHERE</h4>
                    <p class="mb-0">Admin Panel</p>
                </div>
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="products.php" class="sidebar-link active">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php" class="sidebar-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php" class="sidebar-link">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="profile.php" class="sidebar-link">
                    <i class="fas fa-user-cog"></i> Admin Profile
                </a>
                <a href="../logout.php" class="sidebar-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ml-auto">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Product</h4>
                    <div class="admin-info">
                        <span class="me-2">Welcome, <?php echo $admin['admin_name']; ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['admin_name']); ?>&size=32&background=random" class="rounded-circle" alt="Admin">
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Edit Product: <?php echo htmlspecialchars($product['product_name']); ?></h5>
                        </div>
                        <div class="card-body">
                            <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="product_name" class="form-label">Product Name *</label>
                                        <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="model_number" class="form-label">Model Number/SKU *</label>
                                        <input type="text" class="form-control" id="model_number" name="model_number" value="<?php echo htmlspecialchars($product['model_number'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="product_type" class="form-label">Product Type *</label>
                                        <select class="form-select" id="product_type" name="product_type" required>
                                            <option value="">Select Type</option>
                                            <option value="CPU" <?= ($product['product_type'] == 'CPU') ? 'selected' : '' ?>>CPU</option>
                                            <option value="GPU" <?= ($product['product_type'] == 'GPU') ? 'selected' : '' ?>>GPU</option>
                                            <option value="RAM" <?= ($product['product_type'] == 'RAM') ? 'selected' : '' ?>>RAM</option>
                                            <option value="Motherboard" <?= ($product['product_type'] == 'Motherboard') ? 'selected' : '' ?>>Motherboard</option>
                                            <option value="Storage" <?= ($product['product_type'] == 'Storage') ? 'selected' : '' ?>>Storage</option>
                                            <option value="PSU" <?= ($product['product_type'] == 'PSU') ? 'selected' : '' ?>>PSU</option>
                                            <option value="Case" <?= ($product['product_type'] == 'Case') ? 'selected' : '' ?>>Case</option>
                                            <option value="Cooling" <?= ($product['product_type'] == 'Cooling') ? 'selected' : '' ?>>Cooling</option>
                                            <option value="Monitor" <?= ($product['product_type'] == 'Monitor') ? 'selected' : '' ?>>Monitor</option>
                                            <option value="Accessories" <?= ($product['product_type'] == 'Accessories') ? 'selected' : '' ?>>Accessories</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label for="product_description" class="form-label">Description *</label>
                                        <textarea class="form-control" id="product_description" name="product_description" rows="4" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="product_price" class="form-label">Price (Rp) *</label>
                                        <input type="number" class="form-control" id="product_price" name="product_price" min="0" step="0.01" value="<?php echo htmlspecialchars($product['product_price']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="product_stock_quantity" class="form-label">Stock Quantity *</label>
                                        <input type="number" class="form-control" id="product_stock_quantity" name="product_stock_quantity" min="0" value="<?php echo htmlspecialchars($product['product_stock_quantity'] ?? 0); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="dimensions" class="form-label">Dimensions (LxWxH in mm)</label>
                                        <input type="text" class="form-control" id="dimensions" name="dimensions" value="<?php echo htmlspecialchars($product['dimensions'] ?? ''); ?>" placeholder="e.g., 150x150x86">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="weight" class="form-label">Weight (grams)</label>
                                        <input type="number" class="form-control" id="weight" name="weight" min="0" step="0.01" value="<?php echo htmlspecialchars($product['weight'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="color" class="form-label">Color</label>
                                        <input type="text" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($product['color'] ?? ''); ?>" placeholder="e.g., Black, White">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="release_date" class="form-label">Release Date</label>
                                        <input type="date" class="form-control" id="release_date" name="release_date" value="<?php echo htmlspecialchars($product['release_date'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="warranty" class="form-label">Warranty</label>
                                        <input type="text" class="form-control" id="warranty" name="warranty" value="<?php echo htmlspecialchars($product['warranty'] ?? ''); ?>" placeholder="e.g., 3 years, 5 years">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="product_brand" class="form-label">Brand</label>
                                        <input type="text" class="form-control" id="product_brand" name="product_brand" value="<?php echo htmlspecialchars($product['product_brand'] ?? ''); ?>" placeholder="e.g., Corsair, ASUS">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="product_image" class="form-label">Product Image</label>
                                        <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                                        <small class="text-muted">Recommended size: 800x800 pixels</small>
                                        
                                        <?php if (!empty($product['product_image_url'])): ?>
                                            <div class="mt-2">
                                                <p>Current Image:</p>
                                                <img src="<?php echo htmlspecialchars($product['product_image_url']); ?>" alt="Product Image" class="product-image-preview">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="specs cpu-specs">
                                        <div class="mb-3">
                                            <label for="socket_type" class="form-label">Socket Type</label>
                                            <input type="text" class="form-control" id="socket_type" name="socket_type" value="<?= $type_data['socket_type'] ?? '' ?>" placeholder="e.g., AM5, LGA1700">
                                        </div>
                                        <div class="mb-3">
                                            <label for="core_count" class="form-label">Core Count</label>
                                            <input type="number" class="form-control" id="core_count" name="core_count" value="<?= $type_data['core_count'] ?? '' ?>" placeholder="e.g., 8, 16">
                                        </div>
                                        <div class="mb-3">
                                            <label for="thread_count" class="form-label">Thread Count</label>
                                            <input type="number" class="form-control" id="thread_count" name="thread_count" value="<?= $type_data['thread_count'] ?? '' ?>" placeholder="e.g., 16, 32">
                                        </div>
                                        <div class="mb-3">
                                            <label for="base_clock" class="form-label">Base Clock (GHz)</label>
                                            <input type="text" class="form-control" id="base_clock" name="base_clock" value="<?= $type_data['base_clock'] ?? '' ?>" placeholder="e.g., 3.5, 4.0">
                                        </div>
                                        <div class="mb-3">
                                            <label for="boost_clock" class="form-label">Boost Clock (GHz)</label>
                                            <input type="text" class="form-control" id="boost_clock" name="boost_clock" value="<?= $type_data['boost_clock'] ?? '' ?>" placeholder="e.g., 4.8, 5.2">
                                        </div>
                                        <div class="mb-3">
                                            <label for="tdp" class="form-label">TDP (W)</label>
                                            <input type="number" class="form-control" id="tdp" name="tdp" value="<?= $type_data['tdp'] ?? '' ?>" placeholder="e.g., 65, 105">
                                        </div>
                                        <div class="mb-3">
                                            <label for="integrated_graphics" class="form-label">Integrated Graphics</label>
                                            <input type="text" class="form-control" id="integrated_graphics" name="integrated_graphics" value="<?= $type_data['integrated_graphics'] ?? '' ?>" placeholder="e.g., UHD 770, Vega 8">
                                        </div>
                                        <div class="mb-3">
                                            <label for="l3_cache" class="form-label">L3 Cache (MB)</label>
                                            <input type="text" class="form-control" id="l3_cache" name="l3_cache" value="<?= $type_data['l3_cache'] ?? '' ?>" placeholder="e.g., 32, 64">
                                        </div>
                                        <div class="mb-3">
                                            <label for="manufacturing_process" class="form-label">Manufacturing Process (nm)</label>
                                            <input type="text" class="form-control" id="manufacturing_process" name="manufacturing_process" value="<?= $type_data['manufacturing_process'] ?? '' ?>" placeholder="e.g., 7, 5, 4">
                                        </div>
                                    </div>
                                    
                                    <div class="specs gpu-specs">
                                        <div class="mb-3">
                                            <label for="gpu_chipset" class="form-label">GPU Chipset</label>
                                            <input type="text" class="form-control" id="gpu_chipset" name="gpu_chipset" value="<?= $type_data['gpu_chipset'] ?? '' ?>" placeholder="e.g., RTX 4080, RX 7900 XT">
                                        </div>
                                        <div class="mb-3">
                                            <label for="vram" class="form-label">VRAM</label>
                                            <input type="text" class="form-control" id="vram" name="vram" value="<?= $type_data['vram'] ?? '' ?>" placeholder="e.g., 16GB, 24GB">
                                        </div>
                                        <div class="mb-3">
                                            <label for="vram_type" class="form-label">VRAM Type</label>
                                            <input type="text" class="form-control" id="vram_type" name="vram_type" value="<?= $type_data['vram_type'] ?? '' ?>" placeholder="e.g., GDDR6X, GDDR6">
                                        </div>
                                        <div class="mb-3">
                                            <label for="gpu_clock" class="form-label">GPU Clock</label>
                                            <input type="text" class="form-control" id="gpu_clock" name="gpu_clock" value="<?= $type_data['gpu_clock'] ?? '' ?>" placeholder="e.g., 2310 MHz">
                                        </div>
                                        <div class="mb-3">
                                            <label for="boost_clock_gpu" class="form-label">Boost Clock</label>
                                            <input type="text" class="form-control" id="boost_clock_gpu" name="boost_clock_gpu" value="<?= $type_data['boost_clock_gpu'] ?? '' ?>" placeholder="e.g., 2610 MHz">
                                        </div>
                                        <div class="mb-3">
                                            <label for="memory_interface" class="form-label">Memory Interface</label>
                                            <input type="text" class="form-control" id="memory_interface" name="memory_interface" value="<?= $type_data['memory_interface'] ?? '' ?>" placeholder="e.g., 256-bit, 384-bit">
                                        </div>
                                        <div class="mb-3">
                                            <label for="gpu_length" class="form-label">Card Length (mm)</label>
                                            <input type="number" class="form-control" id="gpu_length" name="gpu_length" value="<?= $type_data['gpu_length'] ?? '' ?>" placeholder="e.g., 285">
                                        </div>
                                        <div class="mb-3">
                                            <label for="power_connectors" class="form-label">Power Connectors</label>
                                            <input type="text" class="form-control" id="power_connectors" name="power_connectors" value="<?= $type_data['power_connectors'] ?? '' ?>" placeholder="e.g., 2x 8-pin, 1x 16-pin">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recommended_psu" class="form-label">Recommended PSU (W)</label>
                                            <input type="number" class="form-control" id="recommended_psu" name="recommended_psu" value="<?= $type_data['recommended_psu'] ?? '' ?>" placeholder="e.g., 750, 850">
                                        </div>
                                    </div>

                                    <div class="specs ram-specs">
                                        <div class="mb-3">
                                            <label for="ram_type" class="form-label">RAM Type</label>
                                            <input type="text" class="form-control" id="ram_type" name="ram_type" value="<?= $type_data['ram_type'] ?? '' ?>" placeholder="e.g., DDR4, DDR5" data-required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ram_speed" class="form-label">RAM Speed (MHz)</label>
                                            <input type="text" class="form-control" id="ram_speed" name="ram_speed" value="<?= $type_data['ram_speed'] ?? '' ?>" placeholder="e.g., 3200, 6000" data-required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ram_capacity" class="form-label">RAM Capacity (GB)</label>
                                            <input type="text" class="form-control" id="ram_capacity" name="ram_capacity" value="<?= $type_data['ram_capacity'] ?? '' ?>" placeholder="e.g., 16, 32" data-required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ram_latency" class="form-label">RAM Latency</label>
                                            <input type="text" class="form-control" id="ram_latency" name="ram_latency" value="<?= $type_data['ram_latency'] ?? '' ?>" placeholder="e.g., CL16, CL36" data-required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ram_voltage" class="form-label">RAM Voltage (V)</label>
                                            <input type="text" class="form-control" id="ram_voltage" name="ram_voltage" value="<?= $type_data['ram_voltage'] ?? '' ?>" placeholder="e.g., 1.35, 1.2" data-required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ram_modules" class="form-label">RAM Modules</label>
                                            <input type="number" class="form-control" id="ram_modules" name="ram_modules" value="<?= $type_data['ram_modules'] ?? '' ?>" placeholder="e.g., 2, 4" data-required>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="ecc_support" name="ecc_support" value="1" <?= (!empty($type_data['ecc_support']) && $type_data['ecc_support'] == 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="ecc_support">
                                                    ECC Support
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="rgb_support" name="rgb_support" value="1" <?= (!empty($type_data['rgb_support']) && $type_data['rgb_support'] == 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="rgb_support">
                                                    RGB Support
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="specs motherboard-specs">
                                        <div class="mb-3">
                                            <label for="socket_type" class="form-label">Socket Type</label>
                                            <input type="text" class="form-control" id="socket_type" name="socket_type" value="<?= $type_data['socket_type'] ?? '' ?>" placeholder="e.g., AM4, LGA1700">
                                        </div>
                                        <div class="mb-3">
                                            <label for="chipset" class="form-label">Chipset</label>
                                            <input type="text" class="form-control" id="chipset" name="chipset" value="<?= $type_data['chipset'] ?? '' ?>" placeholder="e.g., B550, Z690">
                                        </div>
                                        <div class="mb-3">
                                            <label for="form_factor" class="form-label">Form Factor</label>
                                            <input type="text" class="form-control" id="form_factor" name="form_factor" value="<?= $type_data['form_factor'] ?? '' ?>" placeholder="e.g., ATX, Micro-ATX">
                                        </div>
                                        <div class="mb-3">
                                            <label for="memory_slots" class="form-label">Memory Slots</label>
                                            <input type="number" class="form-control" id="memory_slots" name="memory_slots" value="<?= $type_data['memory_slots'] ?? '' ?>" placeholder="e.g., 4, 2">
                                        </div>
                                        <div class="mb-3">
                                            <label for="max_memory" class="form-label">Max Memory Support</label>
                                            <input type="text" class="form-control" id="max_memory" name="max_memory" value="<?= $type_data['max_memory'] ?? '' ?>" placeholder="e.g., 128GB, 64GB">
                                        </div>
                                        <div class="mb-3">
                                            <label for="pcie_slots" class="form-label">PCIe Slots</label>
                                            <textarea class="form-control" id="pcie_slots" name="pcie_slots" rows="2" placeholder="e.g., 2x PCIe 4.0 x16, 3x PCIe 3.0 x1"><?= $type_data['pcie_slots'] ?? '' ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sata_ports" class="form-label">SATA Ports</label>
                                            <input type="number" class="form-control" id="sata_ports" name="sata_ports" value="<?= $type_data['sata_ports'] ?? '' ?>" placeholder="e.g., 6, 4">
                                        </div>
                                        <div class="mb-3">
                                            <label for="m2_slots" class="form-label">M.2 Slots</label>
                                            <input type="number" class="form-control" id="m2_slots" name="m2_slots" value="<?= $type_data['m2_slots'] ?? '' ?>" placeholder="e.g., 2, 3">
                                        </div>
                                        <div class="mb-3">
                                            <label for="usb_ports" class="form-label">USB Ports</label>
                                            <textarea class="form-control" id="usb_ports" name="usb_ports" rows="2" placeholder="e.g., 2x USB 3.2 Gen 2, 4x USB 3.0"><?= $type_data['usb_ports'] ?? '' ?></textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="specs psu-specs">
                                        <div class="mb-3">
                                            <label for="psu_wattage" class="form-label">Wattage *</label>
                                            <input type="number" class="form-control" id="psu_wattage" name="psu_wattage" value="<?= $type_data['psu_wattage'] ?? '' ?>" placeholder="e.g., 750, 850" data-required min="0">
                                        </div>
                                        <div class="mb-3">
                                            <label for="psu_efficiency" class="form-label">Efficiency Rating *</label>
                                            <select class="form-select" id="psu_efficiency" name="psu_efficiency" data-required>
                                                <option value="">Select Rating</option>
                                                <option value="80+ White" <?= ($type_data['psu_efficiency'] ?? '') == '80+ White' ? 'selected' : '' ?>>80+ White</option>
                                                <option value="80+ Bronze" <?= ($type_data['psu_efficiency'] ?? '') == '80+ Bronze' ? 'selected' : '' ?>>80+ Bronze</option>
                                                <option value="80+ Silver" <?= ($type_data['psu_efficiency'] ?? '') == '80+ Silver' ? 'selected' : '' ?>>80+ Silver</option>
                                                <option value="80+ Gold" <?= ($type_data['psu_efficiency'] ?? '') == '80+ Gold' ? 'selected' : '' ?>>80+ Gold</option>
                                                <option value="80+ Platinum" <?= ($type_data['psu_efficiency'] ?? '') == '80+ Platinum' ? 'selected' : '' ?>>80+ Platinum</option>
                                                <option value="80+ Titanium" <?= ($type_data['psu_efficiency'] ?? '') == '80+ Titanium' ? 'selected' : '' ?>>80+ Titanium</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="psu_modular" class="form-label">Modularity *</label>
                                            <select class="form-select" id="psu_modular" name="psu_modular" data-required>
                                                <option value="">Select Type</option>
                                                <option value="Non-modular" <?= ($type_data['psu_modular'] ?? '') == 'Non-modular' ? 'selected' : '' ?>>Non-Modular</option>
                                                <option value="Semi-modular" <?= ($type_data['psu_modular'] ?? '') == 'Semi-modular' ? 'selected' : '' ?>>Semi-Modular</option>
                                                <option value="Full-modular" <?= ($type_data['psu_modular'] ?? '') == 'Full-modular' ? 'selected' : '' ?>>Fully Modular</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="psu_form_factor" class="form-label">Form Factor *</label>
                                            <input type="text" class="form-control" id="psu_form_factor" name="psu_form_factor" value="<?= $type_data['psu_form_factor'] ?? '' ?>" placeholder="e.g., ATX, SFX" data-required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="pcie_connectors" class="form-label">PCIe Connectors</label>
                                            <input type="number" class="form-control" id="pcie_connectors" name="pcie_connectors" value="<?= $type_data['pcie_connectors'] ?? '' ?>" placeholder="e.g., 2, 4" min="0">
                                        </div>
                                        <div class="mb-3">
                                            <label for="sata_connectors" class="form-label">SATA Connectors</label>
                                            <input type="number" class="form-control" id="sata_connectors" name="sata_connectors" value="<?= $type_data['sata_connectors'] ?? '' ?>" placeholder="e.g., 4, 6" min="0">
                                        </div>
                                        <div class="mb-3">
                                            <label for="cpu_connectors" class="form-label">CPU Connectors</label>
                                            <input type="text" class="form-control" id="cpu_connectors" name="cpu_connectors" value="<?= $type_data['cpu_connectors'] ?? '' ?>" placeholder="e.g., 4+4 pin, 8 pin">
                                        </div>
                                    </div>
                                    
                                    <div class="specs case-specs">
                                        <div class="mb-3">
                                            <label for="case_type" class="form-label">Case Type</label>
                                            <input type="text" class="form-control" id="case_type" name="case_type" value="<?= $type_data['case_type'] ?? '' ?>" placeholder="e.g., Mid Tower, Full Tower, Mini-ITX">
                                        </div>
                                        <div class="mb-3">
                                            <label for="case_form_factor" class="form-label">Supported Form Factors</label>
                                            <input type="text" class="form-control" id="case_form_factor" name="case_form_factor" value="<?= $type_data['case_form_factor'] ?? '' ?>" placeholder="e.g., ATX, Micro-ATX, Mini-ITX">
                                        </div>
                                        <div class="mb-3">
                                            <label for="expansion_slots" class="form-label">Expansion Slots</label>
                                            <input type="number" class="form-control" id="expansion_slots" name="expansion_slots" value="<?= $type_data['expansion_slots'] ?? '' ?>" min="0">
                                        </div>
                                        <div class="mb-3">
                                            <label for="drive_bays" class="form-label">Drive Bays</label>
                                            <input type="text" class="form-control" id="drive_bays" name="drive_bays" value="<?= $type_data['drive_bays'] ?? '' ?>" placeholder="e.g., 2x 3.5&quot;, 2x 2.5&quot;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="radiator_support" class="form-label">Radiator Support</label>
                                            <input type="text" class="form-control" id="radiator_support" name="radiator_support" value="<?= $type_data['radiator_support'] ?? '' ?>" placeholder="e.g., Front: 240mm, Top: 360mm">
                                        </div>
                                        <div class="mb-3">
                                            <label for="included_fans" class="form-label">Included Fans</label>
                                            <input type="text" class="form-control" id="included_fans" name="included_fans" value="<?= $type_data['included_fans'] ?? '' ?>" placeholder="e.g., 2x 120mm RGB">
                                        </div>
                                    </div>
                                    
                                    <div class="specs cooling-specs">
                                        <div class="mb-3">
                                            <label for="cooling_type" class="form-label">Cooling Type</label>
                                            <select class="form-select" id="cooling_type" name="cooling_type">
                                                <option value="">Select Type</option>
                                                <option value="Air" <?= ($type_data['cooling_type'] ?? '') == 'Air' ? 'selected' : '' ?>>Air</option>
                                                <option value="Liquid" <?= ($type_data['cooling_type'] ?? '') == 'Liquid' ? 'selected' : '' ?>>Liquid</option>
                                                <option value="Hybrid" <?= ($type_data['cooling_type'] ?? '') == 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fan_size" class="form-label">Fan Size (mm)</label>
                                            <input type="text" class="form-control" id="fan_size" name="fan_size" value="<?= $type_data['fan_size'] ?? '' ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="noise_level" class="form-label">Noise Level (dB)</label>
                                            <input type="text" class="form-control" id="noise_level" name="noise_level" value="<?= $type_data['noise_level'] ?? '' ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="radiator_size" class="form-label">Radiator Size</label>
                                            <input type="text" class="form-control" id="radiator_size" name="radiator_size" value="<?= $type_data['radiator_size'] ?? '' ?>" placeholder="e.g., 240mm, 360mm">
                                        </div>
                                        <div class="mb-3">
                                            <label for="socket_type" class="form-label">Compatible Sockets</label>
                                            <input type="text" class="form-control" id="socket_type" name="socket_type" value="<?= $type_data['socket_type'] ?? '' ?>" placeholder="e.g., AM4, LGA1700, LGA1200">
                                        </div>
                                    </div>
                                    
                                    <div class="specs monitor-specs">
                                        <div class="mb-3">
                                            <label for="monitor_type" class="form-label">Monitor Type</label>
                                            <input type="text" class="form-control" id="monitor_type" name="monitor_type" value="<?= $type_data['monitor_type'] ?? '' ?>" placeholder="e.g., LED, OLED, QLED">
                                        </div>
                                        <div class="mb-3">
                                            <label for="resolution" class="form-label">Resolution</label>
                                            <input type="text" class="form-control" id="resolution" name="resolution" value="<?= $type_data['resolution'] ?? '' ?>" placeholder="e.g., 1080p, 4K">
                                        </div>
                                        <div class="mb-3">
                                            <label for="refresh_rate" class="form-label">Refresh Rate (Hz)</label>
                                            <input type="text" class="form-control" id="refresh_rate" name="refresh_rate" value="<?= $type_data['refresh_rate'] ?? '' ?>" placeholder="e.g., 60, 144">
                                        </div>
                                        <div class="mb-3">
                                            <label for="response_time" class="form-label">Response Time (ms)</label>
                                            <input type="text" class="form-control" id="response_time" name="response_time" value="<?= $type_data['response_time'] ?? '' ?>" placeholder="e.g., 1, 0.5">
                                        </div>
                                        <div class="mb-3">
                                            <label for="aspect_ratio" class="form-label">Aspect Ratio</label>
                                            <input type="text" class="form-control" id="aspect_ratio" name="aspect_ratio" value="<?= $type_data['aspect_ratio'] ?? '' ?>" placeholder="e.g., 16:9, 21:9">
                                        </div>
                                    </div>
                                    
                                    <div class="specs accessories-specs">
                                        <div class="mb-3">
                                            <label for="accessory_type" class="form-label">Accessory Type</label>
                                            <input type="text" class="form-control" id="accessory_type" name="accessory_type" value="<?= $type_data['accessory_type'] ?? '' ?>" placeholder="e.g., Keyboard, Mouse, Headset">
                                        </div>
                                        <div class="mb-3">
                                            <label for="compatibility_notes" class="form-label">Compatibility Notes</label>
                                            <textarea class="form-control" id="compatibility_notes" name="compatibility_notes" rows="2" placeholder="e.g., Compatible with Windows 10/11, macOS"><?= $type_data['compatibility_notes'] ?? '' ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="included_accessories" class="form-label">Included Accessories</label>
                                            <textarea class="form-control" id="included_accessories" name="included_accessories" rows="2" placeholder="e.g., USB receiver, extra keycaps, cables"><?= $type_data['included_accessories'] ?? '' ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="connectivity" class="form-label">Connectivity</label>
                                            <input type="text" class="form-control" id="connectivity" name="connectivity" value="<?= $type_data['connectivity'] ?? '' ?>" placeholder="e.g., Bluetooth 5.0, USB Type-C, Wireless 2.4GHz">
                                        </div>
                                        <div class="mb-3">
                                            <label for="material" class="form-label">Material</label>
                                            <input type="text" class="form-control" id="material" name="material" value="<?= $type_data['material'] ?? '' ?>" placeholder="e.g., Aluminum, ABS Plastic, PBT">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="products.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Products
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productTypeSelect = document.getElementById('product_type');
            const allSpecs = document.querySelectorAll('.specs');
            
            function toggleSpecFields() {
                // Hide all spec fields first
                allSpecs.forEach(spec => {
                    spec.style.display = 'none';
                    // Remove required attribute from all inputs in hidden sections
                    const inputs = spec.querySelectorAll('input[required], select[required], textarea[required]');
                    inputs.forEach(input => {
                        input.removeAttribute('required');
                    });
                });
                
                // Show relevant fields based on selection
                const selectedType = productTypeSelect.value;
                let activeSpecs = null;
                
                if (selectedType === 'CPU') {
                    activeSpecs = document.querySelector('.cpu-specs');
                } else if (selectedType === 'GPU') {
                    activeSpecs = document.querySelector('.gpu-specs');
                } else if (selectedType === 'RAM') {
                    activeSpecs = document.querySelector('.ram-specs');
                } else if (selectedType === 'Motherboard') {
                    activeSpecs = document.querySelector('.motherboard-specs');
                } else if (selectedType === 'Storage') {
                    activeSpecs = document.querySelector('.storage-specs');
                } else if (selectedType === 'PSU') {
                    activeSpecs = document.querySelector('.psu-specs');
                } else if (selectedType === 'Case') {
                    activeSpecs = document.querySelector('.case-specs');
                } else if (selectedType === 'Cooling') {
                    activeSpecs = document.querySelector('.cooling-specs');
                } else if (selectedType === 'Monitor') {
                    activeSpecs = document.querySelector('.monitor-specs');
                } else if (selectedType === 'Accessories') {
                    activeSpecs = document.querySelector('.accessories-specs');
                }
                
                if (activeSpecs) {
                    activeSpecs.style.display = 'block';
                    // Add required attribute to inputs in active section
                    const inputs = activeSpecs.querySelectorAll('input[data-required], select[data-required], textarea[data-required]');
                    inputs.forEach(input => {
                        input.setAttribute('required', 'required');
                    });
                }
            }
            
            // Initial toggle
            toggleSpecFields();
            
            // Toggle on change
            productTypeSelect.addEventListener('change', toggleSpecFields);
        });
    </script>
</body>
</html> 