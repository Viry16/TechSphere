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
$sql = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$result = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($result);

// Process form submission
if (isset($_POST['add_product'])) {
    // Get form data
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $model_number = !empty($_POST['model_number']) ? mysqli_real_escape_string($conn, $_POST['model_number']) : null;
    $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
    $product_brand = !empty($_POST['product_brand']) ? mysqli_real_escape_string($conn, $_POST['product_brand']) : null;
    
    // Physical specifications
    $dimensions = !empty($_POST['dimensions']) ? mysqli_real_escape_string($conn, $_POST['dimensions']) : null;
    $weight = !empty($_POST['weight']) ? mysqli_real_escape_string($conn, $_POST['weight']) : null;
    $color = !empty($_POST['color']) ? mysqli_real_escape_string($conn, $_POST['color']) : null;
    
    // Performance specs
    $release_date = !empty($_POST['release_date']) ? mysqli_real_escape_string($conn, $_POST['release_date']) : null;
    $warranty = !empty($_POST['warranty']) ? mysqli_real_escape_string($conn, $_POST['warranty']) : null;
    
    // CPU-specific
    $socket_type = !empty($_POST['socket_type']) ? mysqli_real_escape_string($conn, $_POST['socket_type']) : null;
    $core_count = !empty($_POST['core_count']) ? mysqli_real_escape_string($conn, $_POST['core_count']) : null;
    $thread_count = !empty($_POST['thread_count']) ? mysqli_real_escape_string($conn, $_POST['thread_count']) : null;
    $base_clock = !empty($_POST['base_clock']) ? mysqli_real_escape_string($conn, $_POST['base_clock']) : null;
    $boost_clock = !empty($_POST['boost_clock']) ? mysqli_real_escape_string($conn, $_POST['boost_clock']) : null;
    $tdp = !empty($_POST['tdp']) ? mysqli_real_escape_string($conn, $_POST['tdp']) : null;
    $integrated_graphics = !empty($_POST['integrated_graphics']) ? mysqli_real_escape_string($conn, $_POST['integrated_graphics']) : null;
    $l3_cache = !empty($_POST['l3_cache']) ? mysqli_real_escape_string($conn, $_POST['l3_cache']) : null;
    $manufacturing_process = !empty($_POST['manufacturing_process']) ? mysqli_real_escape_string($conn, $_POST['manufacturing_process']) : null;
    
    // GPU-specific
    $gpu_chipset = !empty($_POST['gpu_chipset']) ? mysqli_real_escape_string($conn, $_POST['gpu_chipset']) : null;
    $vram = !empty($_POST['vram']) ? mysqli_real_escape_string($conn, $_POST['vram']) : null;
    $vram_type = !empty($_POST['vram_type']) ? mysqli_real_escape_string($conn, $_POST['vram_type']) : null;
    $gpu_clock = !empty($_POST['gpu_clock']) ? mysqli_real_escape_string($conn, $_POST['gpu_clock']) : null;
    $boost_clock_gpu = !empty($_POST['boost_clock_gpu']) ? mysqli_real_escape_string($conn, $_POST['boost_clock_gpu']) : null;
    $memory_interface = !empty($_POST['memory_interface']) ? mysqli_real_escape_string($conn, $_POST['memory_interface']) : null;
    $gpu_length = !empty($_POST['gpu_length']) ? mysqli_real_escape_string($conn, $_POST['gpu_length']) : null;
    $power_connectors = !empty($_POST['power_connectors']) ? mysqli_real_escape_string($conn, $_POST['power_connectors']) : null;
    $recommended_psu = !empty($_POST['recommended_psu']) ? mysqli_real_escape_string($conn, $_POST['recommended_psu']) : null;
    
    // RAM-specific
    $ram_type = !empty($_POST['ram_type']) ? mysqli_real_escape_string($conn, $_POST['ram_type']) : null;
    $ram_speed = !empty($_POST['ram_speed']) ? mysqli_real_escape_string($conn, $_POST['ram_speed']) : null;
    $ram_capacity = !empty($_POST['ram_capacity']) ? mysqli_real_escape_string($conn, $_POST['ram_capacity']) : null;
    $ram_latency = !empty($_POST['ram_latency']) ? mysqli_real_escape_string($conn, $_POST['ram_latency']) : null;
    $ram_voltage = !empty($_POST['ram_voltage']) ? mysqli_real_escape_string($conn, $_POST['ram_voltage']) : null;
    $ram_modules = !empty($_POST['ram_modules']) ? mysqli_real_escape_string($conn, $_POST['ram_modules']) : null;
    $ecc_support = isset($_POST['ecc_support']) ? 1 : 0;
    $rgb_support = isset($_POST['rgb_support']) ? 1 : 0;
    
    // Motherboard-specific
    $motherboard_form_factor = !empty($_POST['motherboard_form_factor']) ? mysqli_real_escape_string($conn, $_POST['motherboard_form_factor']) : null;
    $motherboard_chipset = !empty($_POST['motherboard_chipset']) ? mysqli_real_escape_string($conn, $_POST['motherboard_chipset']) : null;
    $memory_slots = !empty($_POST['memory_slots']) ? mysqli_real_escape_string($conn, $_POST['memory_slots']) : null;
    $max_memory = !empty($_POST['max_memory']) ? mysqli_real_escape_string($conn, $_POST['max_memory']) : null;
    $sata_ports = !empty($_POST['sata_ports']) ? mysqli_real_escape_string($conn, $_POST['sata_ports']) : null;
    $m2_slots = !empty($_POST['m2_slots']) ? mysqli_real_escape_string($conn, $_POST['m2_slots']) : null;
    $pcie_slots = !empty($_POST['pcie_slots']) ? mysqli_real_escape_string($conn, $_POST['pcie_slots']) : null;
    $wifi_support = isset($_POST['wifi_support']) ? 1 : 0;
    $bluetooth_support = isset($_POST['bluetooth_support']) ? 1 : 0;
    
    // Storage-specific
    $storage_type = !empty($_POST['storage_type']) ? mysqli_real_escape_string($conn, $_POST['storage_type']) : null;
    $storage_capacity = !empty($_POST['storage_capacity']) ? mysqli_real_escape_string($conn, $_POST['storage_capacity']) : null;
    $read_speed = !empty($_POST['read_speed']) ? mysqli_real_escape_string($conn, $_POST['read_speed']) : null;
    $write_speed = !empty($_POST['write_speed']) ? mysqli_real_escape_string($conn, $_POST['write_speed']) : null;
    $interface = !empty($_POST['interface']) ? mysqli_real_escape_string($conn, $_POST['interface']) : null;
    $rpm = !empty($_POST['rpm']) ? mysqli_real_escape_string($conn, $_POST['rpm']) : null;
    $endurance_rating = !empty($_POST['endurance_rating']) ? mysqli_real_escape_string($conn, $_POST['endurance_rating']) : null;
    $nvme_protocol = !empty($_POST['nvme_protocol']) ? mysqli_real_escape_string($conn, $_POST['nvme_protocol']) : null;
    
    // PSU-specific
    $psu_wattage = !empty($_POST['psu_wattage']) ? mysqli_real_escape_string($conn, $_POST['psu_wattage']) : null;
    $psu_efficiency = !empty($_POST['psu_efficiency']) ? mysqli_real_escape_string($conn, $_POST['psu_efficiency']) : null;
    $psu_modular = !empty($_POST['psu_modular']) ? mysqli_real_escape_string($conn, $_POST['psu_modular']) : null;
    $psu_form_factor = !empty($_POST['psu_form_factor']) ? mysqli_real_escape_string($conn, $_POST['psu_form_factor']) : null;
    $pcie_connectors = !empty($_POST['pcie_connectors']) ? mysqli_real_escape_string($conn, $_POST['pcie_connectors']) : null;
    $sata_connectors = !empty($_POST['sata_connectors']) ? mysqli_real_escape_string($conn, $_POST['sata_connectors']) : null;
    $cpu_connectors = !empty($_POST['cpu_connectors']) ? mysqli_real_escape_string($conn, $_POST['cpu_connectors']) : null;
    
    // Case-specific
    $case_type = !empty($_POST['case_type']) ? mysqli_real_escape_string($conn, $_POST['case_type']) : null;
    $case_form_factor = !empty($_POST['case_form_factor']) ? mysqli_real_escape_string($conn, $_POST['case_form_factor']) : null;
    $expansion_slots = !empty($_POST['expansion_slots']) ? mysqli_real_escape_string($conn, $_POST['expansion_slots']) : null;
    $drive_bays = !empty($_POST['drive_bays']) ? mysqli_real_escape_string($conn, $_POST['drive_bays']) : null;
    $radiator_support = !empty($_POST['radiator_support']) ? mysqli_real_escape_string($conn, $_POST['radiator_support']) : null;
    $included_fans = !empty($_POST['included_fans']) ? mysqli_real_escape_string($conn, $_POST['included_fans']) : null;
    $max_gpu_length = !empty($_POST['max_gpu_length']) ? mysqli_real_escape_string($conn, $_POST['max_gpu_length']) : null;
    $max_cpu_cooler_height = !empty($_POST['max_cpu_cooler_height']) ? mysqli_real_escape_string($conn, $_POST['max_cpu_cooler_height']) : null;
    $side_panel_type = !empty($_POST['side_panel_type']) ? mysqli_real_escape_string($conn, $_POST['side_panel_type']) : null;
    
    // Cooling-specific
    $cooling_type = !empty($_POST['cooling_type']) ? mysqli_real_escape_string($conn, $_POST['cooling_type']) : null;
    $fan_size = !empty($_POST['fan_size']) ? mysqli_real_escape_string($conn, $_POST['fan_size']) : null;
    $noise_level = !empty($_POST['noise_level']) ? mysqli_real_escape_string($conn, $_POST['noise_level']) : null;
    $radiator_size = !empty($_POST['radiator_size']) ? mysqli_real_escape_string($conn, $_POST['radiator_size']) : null;
    $compatibility = !empty($_POST['compatibility']) ? mysqli_real_escape_string($conn, $_POST['compatibility']) : null;
    $max_tdp = !empty($_POST['max_tdp']) ? mysqli_real_escape_string($conn, $_POST['max_tdp']) : null;
    $cooling_rgb_support = isset($_POST['cooling_rgb_support']) ? 1 : 0;
    
    // Monitor-specific
    $screen_size = !empty($_POST['screen_size']) ? mysqli_real_escape_string($conn, $_POST['screen_size']) : null;
    $resolution = !empty($_POST['resolution']) ? mysqli_real_escape_string($conn, $_POST['resolution']) : null;
    $refresh_rate = !empty($_POST['refresh_rate']) ? mysqli_real_escape_string($conn, $_POST['refresh_rate']) : null;
    $response_time = !empty($_POST['response_time']) ? mysqli_real_escape_string($conn, $_POST['response_time']) : null;
    $panel_type = !empty($_POST['panel_type']) ? mysqli_real_escape_string($conn, $_POST['panel_type']) : null;
    $aspect_ratio = !empty($_POST['aspect_ratio']) ? mysqli_real_escape_string($conn, $_POST['aspect_ratio']) : null;
    $adaptive_sync = !empty($_POST['adaptive_sync']) ? mysqli_real_escape_string($conn, $_POST['adaptive_sync']) : null;
    $hdr_support = isset($_POST['hdr_support']) ? 1 : 0;
    $curved = isset($_POST['curved']) ? 1 : 0;
    $vesa_mount = !empty($_POST['vesa_mount']) ? mysqli_real_escape_string($conn, $_POST['vesa_mount']) : null;
    
    // Accessories-specific
    $accessory_type = !empty($_POST['accessory_type']) ? mysqli_real_escape_string($conn, $_POST['accessory_type']) : null;
    $included_accessories = !empty($_POST['included_accessories']) ? mysqli_real_escape_string($conn, $_POST['included_accessories']) : null;
    $connectivity = !empty($_POST['connectivity']) ? mysqli_real_escape_string($conn, $_POST['connectivity']) : null;
    $material = !empty($_POST['material']) ? mysqli_real_escape_string($conn, $_POST['material']) : null;
    
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $product_stock_quantity = mysqli_real_escape_string($conn, $_POST['product_stock_quantity']);
    $product_sold_quantity = !empty($_POST['product_sold_quantity']) ? mysqli_real_escape_string($conn, $_POST['product_sold_quantity']) : 0;
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    
    // Process image upload
    $product_image_url = null;
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
                    $product_image_url = $target_file;
                } else {
                    $error_message = "Failed to upload image. Error: " . $_FILES['product_image']['error'];
                }
            } else {
                $error_message = "Only JPG, JPEG, PNG and GIF files are allowed";
            }
        }
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert into main product table
        $insert_sql = "INSERT INTO product (
            product_name, model_number, product_type, product_brand, 
            dimensions, weight, color, release_date, warranty,
            product_description, product_stock_quantity, product_sold_quantity, product_price, 
            product_image_url, created_by_admin_id
        ) VALUES (
            '$product_name', 
            " . ($model_number ? "'$model_number'" : "NULL") . ", 
            '$product_type', 
            " . ($product_brand ? "'$product_brand'" : "NULL") . ", 
            " . (!empty($_POST['dimensions']) ? "'" . mysqli_real_escape_string($conn, $_POST['dimensions']) . "'" : "NULL") . ", 
            " . (!empty($_POST['weight']) ? "'" . mysqli_real_escape_string($conn, $_POST['weight']) . "'" : "NULL") . ", 
            " . (!empty($_POST['color']) ? "'" . mysqli_real_escape_string($conn, $_POST['color']) . "'" : "NULL") . ", 
            " . (!empty($_POST['release_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['release_date']) . "'" : "NULL") . ", 
            " . (!empty($_POST['warranty']) ? "'" . mysqli_real_escape_string($conn, $_POST['warranty']) . "'" : "NULL") . ", 
            '$product_description',
            '$product_stock_quantity',
            '$product_sold_quantity',
            '$product_price', 
            " . ($product_image_url ? "'$product_image_url'" : "NULL") . ", 
            '$admin_id'
        )";
        
        if (mysqli_query($conn, $insert_sql)) {
            $product_id = mysqli_insert_id($conn);
            
            // Insert into specific product type tables based on product type
            switch($product_type) {
                case 'CPU':
                    $cpu_sql = "INSERT INTO product_cpu (
                        product_id, socket_type, core_count,
                        thread_count, base_clock, boost_clock,
                        tdp, integrated_graphics, l3_cache,
                        manufacturing_process
                    ) VALUES (
                        $product_id,
                        " . (!empty($_POST['socket_type']) ? "'" . mysqli_real_escape_string($conn, $_POST['socket_type']) . "'" : "NULL") . ",
                        " . (!empty($_POST['core_count']) ? mysqli_real_escape_string($conn, $_POST['core_count']) : "NULL") . ",
                        " . (!empty($_POST['thread_count']) ? mysqli_real_escape_string($conn, $_POST['thread_count']) : "NULL") . ",
                        " . (!empty($_POST['base_clock']) ? "'" . mysqli_real_escape_string($conn, $_POST['base_clock']) . "'" : "NULL") . ",
                        " . (!empty($_POST['boost_clock']) ? "'" . mysqli_real_escape_string($conn, $_POST['boost_clock']) . "'" : "NULL") . ",
                        " . (!empty($_POST['tdp']) ? mysqli_real_escape_string($conn, $_POST['tdp']) : "NULL") . ",
                        " . (!empty($_POST['integrated_graphics']) ? "'" . mysqli_real_escape_string($conn, $_POST['integrated_graphics']) . "'" : "NULL") . ",
                        " . (!empty($_POST['l3_cache']) ? "'" . mysqli_real_escape_string($conn, $_POST['l3_cache']) . "'" : "NULL") . ",
                        " . (!empty($_POST['manufacturing_process']) ? "'" . mysqli_real_escape_string($conn, $_POST['manufacturing_process']) . "'" : "NULL") . "
                    )";
                    mysqli_query($conn, $cpu_sql);
                    break;
                    
                case 'GPU':
                    $gpu_sql = "INSERT INTO product_gpu (
                        product_id, gpu_chipset, vram, 
                        vram_type, gpu_clock, boost_clock_gpu, 
                        memory_interface, gpu_length, power_connectors,
                        recommended_psu
                    ) VALUES (
                        $product_id,
                        " . (!empty($_POST['gpu_chipset']) ? "'" . mysqli_real_escape_string($conn, $_POST['gpu_chipset']) . "'" : "NULL") . ",
                        " . (!empty($_POST['vram']) ? "'" . mysqli_real_escape_string($conn, $_POST['vram']) . "'" : "NULL") . ",
                        " . (!empty($_POST['vram_type']) ? "'" . mysqli_real_escape_string($conn, $_POST['vram_type']) . "'" : "NULL") . ",
                        " . (!empty($_POST['gpu_clock']) ? "'" . mysqli_real_escape_string($conn, $_POST['gpu_clock']) . "'" : "NULL") . ",
                        " . (!empty($_POST['boost_clock_gpu']) ? "'" . mysqli_real_escape_string($conn, $_POST['boost_clock_gpu']) . "'" : "NULL") . ",
                        " . (!empty($_POST['memory_interface']) ? "'" . mysqli_real_escape_string($conn, $_POST['memory_interface']) . "'" : "NULL") . ",
                        " . (!empty($_POST['gpu_length']) ? mysqli_real_escape_string($conn, $_POST['gpu_length']) : "NULL") . ",
                        " . (!empty($_POST['power_connectors']) ? "'" . mysqli_real_escape_string($conn, $_POST['power_connectors']) . "'" : "NULL") . ",
                        " . (!empty($_POST['recommended_psu']) ? mysqli_real_escape_string($conn, $_POST['recommended_psu']) : "NULL") . "
                    )";
                    mysqli_query($conn, $gpu_sql);
                    break;
                    
                case 'RAM':
                    $ram_sql = "INSERT INTO product_ram (
                        product_id, ram_type, ram_speed,
                        ram_capacity, ram_latency, ram_voltage,
                        ram_modules, ecc_support, rgb_support
                    ) VALUES (
                        $product_id,
                        " . (!empty($_POST['ram_type']) ? "'" . mysqli_real_escape_string($conn, $_POST['ram_type']) . "'" : "NULL") . ",
                        " . (!empty($_POST['ram_speed']) ? "'" . mysqli_real_escape_string($conn, $_POST['ram_speed']) . "'" : "NULL") . ",
                        " . (!empty($_POST['ram_capacity']) ? "'" . mysqli_real_escape_string($conn, $_POST['ram_capacity']) . "'" : "NULL") . ",
                        " . (!empty($_POST['ram_latency']) ? "'" . mysqli_real_escape_string($conn, $_POST['ram_latency']) . "'" : "NULL") . ",
                        " . (!empty($_POST['ram_voltage']) ? "'" . mysqli_real_escape_string($conn, $_POST['ram_voltage']) . "'" : "NULL") . ",
                        " . (!empty($_POST['ram_modules']) ? mysqli_real_escape_string($conn, $_POST['ram_modules']) : "NULL") . ",
                        " . (isset($_POST['ecc_support']) && $_POST['ecc_support'] == 1 ? "'1'" : "'0'") . ",
                        " . (isset($_POST['rgb_support']) && $_POST['rgb_support'] == 1 ? "'1'" : "'0'") . "
                    )";
                    mysqli_query($conn, $ram_sql);
                    break;
                    
                case 'Motherboard':
                    $motherboard_sql = "INSERT INTO product_motherboard (
                        product_id, motherboard_form_factor, motherboard_chipset, 
                        socket_type, memory_slots, max_memory, 
                        sata_ports, m2_slots, pcie_slots, 
                        wifi_support, bluetooth_support
                    ) VALUES (
                        $product_id,
                        " . (!empty($_POST['motherboard_form_factor']) ? "'" . mysqli_real_escape_string($conn, $_POST['motherboard_form_factor']) . "'" : "NULL") . ",
                        " . (!empty($_POST['motherboard_chipset']) ? "'" . mysqli_real_escape_string($conn, $_POST['motherboard_chipset']) . "'" : "NULL") . ",
                        " . (!empty($_POST['socket_type']) ? "'" . mysqli_real_escape_string($conn, $_POST['socket_type']) . "'" : "NULL") . ",
                        " . (!empty($_POST['memory_slots']) ? mysqli_real_escape_string($conn, $_POST['memory_slots']) : "NULL") . ",
                        " . (!empty($_POST['max_memory']) ? "'" . mysqli_real_escape_string($conn, $_POST['max_memory']) . "'" : "NULL") . ",
                        " . (!empty($_POST['sata_ports']) ? mysqli_real_escape_string($conn, $_POST['sata_ports']) : "NULL") . ",
                        " . (!empty($_POST['m2_slots']) ? mysqli_real_escape_string($conn, $_POST['m2_slots']) : "NULL") . ",
                        " . (!empty($_POST['pcie_slots']) ? "'" . mysqli_real_escape_string($conn, $_POST['pcie_slots']) . "'" : "NULL") . ",
                        " . (isset($_POST['wifi_support']) && $_POST['wifi_support'] == 1 ? "'1'" : "'0'") . ",
                        " . (isset($_POST['bluetooth_support']) && $_POST['bluetooth_support'] == 1 ? "'1'" : "'0'") . "
                    )";
                    mysqli_query($conn, $motherboard_sql);
                    break;
                    
                case 'Storage':
                    $storage_sql = "INSERT INTO product_storage (
                        product_id, storage_type, storage_capacity, 
                        read_speed, write_speed, interface, 
                        rpm, endurance_rating, nvme_protocol
                    ) VALUES (
                        $product_id,
                        " . (!empty($_POST['storage_type']) ? "'" . mysqli_real_escape_string($conn, $_POST['storage_type']) . "'" : "NULL") . ",
                        " . (!empty($_POST['storage_capacity']) ? "'" . mysqli_real_escape_string($conn, $_POST['storage_capacity']) . "'" : "NULL") . ",
                        " . (!empty($_POST['read_speed']) ? "'" . mysqli_real_escape_string($conn, $_POST['read_speed']) . "'" : "NULL") . ",
                        " . (!empty($_POST['write_speed']) ? "'" . mysqli_real_escape_string($conn, $_POST['write_speed']) . "'" : "NULL") . ",
                        " . (!empty($_POST['interface']) ? "'" . mysqli_real_escape_string($conn, $_POST['interface']) . "'" : "NULL") . ",
                        " . (!empty($_POST['rpm']) ? mysqli_real_escape_string($conn, $_POST['rpm']) : "NULL") . ",
                        " . (!empty($_POST['endurance_rating']) ? "'" . mysqli_real_escape_string($conn, $_POST['endurance_rating']) . "'" : "NULL") . ",
                        " . (!empty($_POST['nvme_protocol']) ? "'" . mysqli_real_escape_string($conn, $_POST['nvme_protocol']) . "'" : "NULL") . "
                    )";
                    mysqli_query($conn, $storage_sql);
                    break;
                    
                case 'PSU':
                    $psu_sql = "INSERT INTO product_psu (
                        product_id, psu_wattage, psu_efficiency, 
                        psu_modular, psu_form_factor, pcie_connectors, 
                        sata_connectors, cpu_connectors
                    ) VALUES (
                        $product_id,
                        " . (!empty($_POST['psu_wattage']) ? mysqli_real_escape_string($conn, $_POST['psu_wattage']) : "NULL") . ",
                        " . (!empty($_POST['psu_efficiency']) ? "'" . mysqli_real_escape_string($conn, $_POST['psu_efficiency']) . "'" : "NULL") . ",
                        " . (!empty($_POST['psu_modular']) ? "'" . mysqli_real_escape_string($conn, $_POST['psu_modular']) . "'" : "NULL") . ",
                        " . (!empty($_POST['psu_form_factor']) ? "'" . mysqli_real_escape_string($conn, $_POST['psu_form_factor']) . "'" : "NULL") . ",
                        " . (!empty($_POST['pcie_connectors']) ? mysqli_real_escape_string($conn, $_POST['pcie_connectors']) : "NULL") . ",
                        " . (!empty($_POST['sata_connectors']) ? mysqli_real_escape_string($conn, $_POST['sata_connectors']) : "NULL") . ",
                        " . (!empty($_POST['cpu_connectors']) ? "'" . mysqli_real_escape_string($conn, $_POST['cpu_connectors']) . "'" : "NULL") . "
                    )";
                    mysqli_query($conn, $psu_sql);
                    break;
                    
                case 'Case':
                    $case_sql = "INSERT INTO product_case (
                        product_id, case_type, case_form_factor, expansion_slots, drive_bays,
                        radiator_support, included_fans, max_gpu_length, max_cpu_cooler_height, side_panel_type
                    ) VALUES (
                        $product_id,
                        " . ($case_type ? "'$case_type'" : "NULL") . ",
                        " . ($case_form_factor ? "'$case_form_factor'" : "NULL") . ",
                        " . ($expansion_slots ? "$expansion_slots" : "NULL") . ",
                        " . ($drive_bays ? "'$drive_bays'" : "NULL") . ",
                        " . ($radiator_support ? "'$radiator_support'" : "NULL") . ",
                        " . ($included_fans ? "'$included_fans'" : "NULL") . ",
                        " . ($max_gpu_length ? "$max_gpu_length" : "NULL") . ",
                        " . ($max_cpu_cooler_height ? "$max_cpu_cooler_height" : "NULL") . ",
                        " . ($side_panel_type ? "'$side_panel_type'" : "NULL") . "
                    )";
                    mysqli_query($conn, $case_sql);
                    break;
                    
                case 'Cooling':
                    $cooling_sql = "INSERT INTO product_cooling (
                        product_id, cooling_type, fan_size, noise_level, radiator_size,
                        compatibility, max_tdp, rgb_support
                    ) VALUES (
                        $product_id,
                        " . ($cooling_type ? "'$cooling_type'" : "NULL") . ",
                        " . ($fan_size ? "'$fan_size'" : "NULL") . ",
                        " . ($noise_level ? "'$noise_level'" : "NULL") . ",
                        " . ($radiator_size ? "'$radiator_size'" : "NULL") . ",
                        " . ($compatibility ? "'$compatibility'" : "NULL") . ",
                        " . ($max_tdp ? "$max_tdp" : "NULL") . ",
                        $cooling_rgb_support
                    )";
                    mysqli_query($conn, $cooling_sql);
                    break;
                    
                case 'Monitor':
                    $monitor_sql = "INSERT INTO product_monitor (
                        product_id, screen_size, resolution, refresh_rate, response_time,
                        panel_type, aspect_ratio, adaptive_sync, hdr_support, curved, vesa_mount
                    ) VALUES (
                        $product_id,
                        " . ($screen_size ? "'$screen_size'" : "NULL") . ",
                        " . ($resolution ? "'$resolution'" : "NULL") . ",
                        " . ($refresh_rate ? "'$refresh_rate'" : "NULL") . ",
                        " . ($response_time ? "'$response_time'" : "NULL") . ",
                        " . ($panel_type ? "'$panel_type'" : "NULL") . ",
                        " . ($aspect_ratio ? "'$aspect_ratio'" : "NULL") . ",
                        " . ($adaptive_sync ? "'$adaptive_sync'" : "NULL") . ",
                        $hdr_support,
                        $curved,
                        " . ($vesa_mount ? "'$vesa_mount'" : "NULL") . "
                    )";
                    mysqli_query($conn, $monitor_sql);
                    break;
                    
                case 'Accessories':
                    $specific_fields = [
                        'accessory_type' => $accessory_type,
                        'included_accessories' => $included_accessories,
                        'connectivity' => $connectivity,
                        'material' => $material
                    ];
                    $specific_table = 'product_accessories';
                    break;
            }
            
            // Insert into specific product type table if we have table and fields defined
            if (isset($specific_table) && !empty($specific_fields)) {
                $field_names = array_keys($specific_fields);
                $field_names[] = 'product_id'; // Add product_id to the field names
                
                $field_values = [];
                foreach ($specific_fields as $value) {
                    if ($value === null) {
                        $field_values[] = "NULL";
                    } else {
                        $field_values[] = "'$value'";
                    }
                }
                $field_values[] = $product_id; // Add product_id value
                
                $specific_insert_sql = "INSERT INTO $specific_table (" . implode(", ", $field_names) . ") VALUES (" . implode(", ", $field_values) . ")";
                
                if (!mysqli_query($conn, $specific_insert_sql)) {
                    throw new Exception("Error inserting into $specific_table: " . mysqli_error($conn));
                }
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            $success_message = "Product added successfully!";
            
            // Redirect to product list
            header("Location: products.php?success=" . urlencode($success_message));
            exit();
            
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $error_message = "Error adding product: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Tech Sphere Admin</title>
    
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
            <div class="col-md-9 col-lg-10 ml-auto content-container">
                <!-- Header -->
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Add New Product</h4>
                    <div class="admin-info">
                        <span class="me-2">Welcome, <?php echo $admin['admin_name']; ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['admin_name']); ?>&size=32&background=random" class="rounded-circle" alt="Admin">
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <!-- Basic Product Information -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="product_name" class="form-label">Product Name *</label>
                                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="model_number" class="form-label">Model Number/SKU *</label>
                                            <input type="text" class="form-control" id="model_number" name="model_number" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="product_type" class="form-label">Product Type *</label>
                                            <select class="form-select" id="product_type" name="product_type" required>
                                                <option value="">Select Type</option>
                                                <option value="CPU">CPU</option>
                                                <option value="GPU">GPU</option>
                                                <option value="RAM">RAM</option>
                                                <option value="Motherboard">Motherboard</option>
                                                <option value="Storage">Storage</option>
                                                <option value="PSU">PSU</option>
                                                <option value="Case">Case</option>
                                                <option value="Cooling">Cooling</option>
                                                <option value="Monitor">Monitor</option>
                                                <option value="Accessories">Accessories</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="product_brand" class="form-label">Brand</label>
                                            <input type="text" class="form-control" id="product_brand" name="product_brand">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="dimensions" class="form-label">Dimensions (LxWxH in mm)</label>
                                            <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="e.g., 120x50x250">
                                        </div>

                                        <div class="mb-3">
                                            <label for="weight" class="form-label">Weight (grams)</label>
                                            <input type="number" step="0.01" class="form-control" id="weight" name="weight" placeholder="e.g., 450">
                                        </div>

                                        <div class="mb-3">
                                            <label for="color" class="form-label">Color</label>
                                            <input type="text" class="form-control" id="color" name="color" placeholder="e.g., Black, RGB">
                                        </div>

                                        <div class="mb-3">
                                            <label for="release_date" class="form-label">Release Date</label>
                                            <input type="date" class="form-control" id="release_date" name="release_date">
                                        </div>

                                        <div class="mb-3">
                                            <label for="warranty" class="form-label">Warranty</label>
                                            <input type="text" class="form-control" id="warranty" name="warranty" placeholder="e.g., 3 years limited">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="product_stock_quantity" class="form-label">Stock Quantity *</label>
                                            <input type="number" class="form-control" id="product_stock_quantity" name="product_stock_quantity" min="0" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="product_price" class="form-label">Price (Rp) *</label>
                                            <input type="number" class="form-control" id="product_price" name="product_price" min="0" step="0.01" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="product_image" class="form-label">Product Image</label>
                                            <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                                        </div>
                                    </div>
                                    
                                    <!-- Product Specifications -->
                                    <div class="col-md-6">
                                        <div class="specs cpu-specs">
                                            <div class="mb-3">
                                                <label for="socket_type" class="form-label">Socket Type</label>
                                                <input type="text" class="form-control" id="socket_type" name="socket_type" placeholder="e.g., AM5, LGA1700">
                                            </div>
                                            <div class="mb-3">
                                                <label for="core_count" class="form-label">Core Count</label>
                                                <input type="number" class="form-control" id="core_count" name="core_count" min="1" placeholder="e.g., 8, 16">
                                            </div>
                                            <div class="mb-3">
                                                <label for="thread_count" class="form-label">Thread Count</label>
                                                <input type="number" class="form-control" id="thread_count" name="thread_count" min="1" placeholder="e.g., 16, 32">
                                            </div>
                                            <div class="mb-3">
                                                <label for="base_clock" class="form-label">Base Clock (GHz)</label>
                                                <input type="text" class="form-control" id="base_clock" name="base_clock" placeholder="e.g., 3.5">
                                            </div>
                                            <div class="mb-3">
                                                <label for="boost_clock" class="form-label">Boost Clock (GHz)</label>
                                                <input type="text" class="form-control" id="boost_clock" name="boost_clock" placeholder="e.g., 5.0">
                                            </div>
                                            <div class="mb-3">
                                                <label for="tdp" class="form-label">TDP (W)</label>
                                                <input type="number" class="form-control" id="tdp" name="tdp" min="1" placeholder="e.g., 65, 105">
                                            </div>
                                            <div class="mb-3">
                                                <label for="integrated_graphics" class="form-label">Integrated Graphics</label>
                                                <input type="text" class="form-control" id="integrated_graphics" name="integrated_graphics" placeholder="e.g., UHD 770, Radeon Vega 8">
                                            </div>
                                            <div class="mb-3">
                                                <label for="l3_cache" class="form-label">L3 Cache (MB)</label>
                                                <input type="text" class="form-control" id="l3_cache" name="l3_cache" placeholder="e.g., 32, 64">
                                            </div>
                                            <div class="mb-3">
                                                <label for="manufacturing_process" class="form-label">Manufacturing Process (nm)</label>
                                                <input type="text" class="form-control" id="manufacturing_process" name="manufacturing_process" placeholder="e.g., 7, 5, 4">
                                            </div>
                                        </div>
                                        
                                        <div class="specs gpu-specs">
                                            <div class="mb-3">
                                                <label for="gpu_chipset" class="form-label">GPU Chipset</label>
                                                <input type="text" class="form-control" id="gpu_chipset" name="gpu_chipset" placeholder="e.g., RTX 4080, RX 7900 XT">
                                            </div>
                                            <div class="mb-3">
                                                <label for="vram" class="form-label">VRAM (GB)</label>
                                                <input type="text" class="form-control" id="vram" name="vram" placeholder="e.g., 16, 24">
                                            </div>
                                            <div class="mb-3">
                                                <label for="vram_type" class="form-label">VRAM Type</label>
                                                <select class="form-select" id="vram_type" name="vram_type">
                                                    <option value="">Select Memory Type</option>
                                                    <option value="GDDR6">GDDR6</option>
                                                    <option value="GDDR6X">GDDR6X</option>
                                                    <option value="GDDR5">GDDR5</option>
                                                    <option value="HBM2">HBM2</option>
                                                    <option value="HBM3">HBM3</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="gpu_clock" class="form-label">GPU Clock (MHz)</label>
                                                <input type="text" class="form-control" id="gpu_clock" name="gpu_clock" placeholder="e.g., 2310">
                                            </div>
                                            <div class="mb-3">
                                                <label for="boost_clock_gpu" class="form-label">Boost Clock (MHz)</label>
                                                <input type="text" class="form-control" id="boost_clock_gpu" name="boost_clock_gpu" placeholder="e.g., 2610">
                                            </div>
                                            <div class="mb-3">
                                                <label for="memory_interface" class="form-label">Memory Interface (bit)</label>
                                                <input type="text" class="form-control" id="memory_interface" name="memory_interface" placeholder="e.g., 256, 384">
                                            </div>
                                            <div class="mb-3">
                                                <label for="gpu_length" class="form-label">GPU Length (mm)</label>
                                                <input type="number" class="form-control" id="gpu_length" name="gpu_length" min="0" placeholder="e.g., 280, 320">
                                            </div>
                                            <div class="mb-3">
                                                <label for="power_connectors" class="form-label">Power Connectors</label>
                                                <input type="text" class="form-control" id="power_connectors" name="power_connectors" placeholder="e.g., 2x 8-pin, 1x 16-pin">
                                            </div>
                                            <div class="mb-3">
                                                <label for="recommended_psu" class="form-label">Recommended PSU (W)</label>
                                                <input type="number" class="form-control" id="recommended_psu" name="recommended_psu" min="0" placeholder="e.g., 750, 850">
                                            </div>
                                        </div>
                                        
                                        <div class="specs motherboard-specs">
                                            <div class="mb-3">
                                                <label for="motherboard_form_factor" class="form-label">Form Factor</label>
                                                <select class="form-select" id="motherboard_form_factor" name="motherboard_form_factor">
                                                    <option value="">Select Form Factor</option>
                                                    <option value="ATX">ATX</option>
                                                    <option value="Micro-ATX">Micro-ATX</option>
                                                    <option value="Mini-ITX">Mini-ITX</option>
                                                    <option value="E-ATX">E-ATX</option>
                                                    <option value="XL-ATX">XL-ATX</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="motherboard_chipset" class="form-label">Chipset</label>
                                                <input type="text" class="form-control" id="motherboard_chipset" name="motherboard_chipset" placeholder="e.g., Z790, X670, B650">
                                            </div>
                                            <div class="mb-3">
                                                <label for="socket_type" class="form-label">Socket Type</label>
                                                <input type="text" class="form-control" id="socket_type" name="socket_type" placeholder="e.g., AM5, LGA1700">
                                            </div>
                                            <div class="mb-3">
                                                <label for="memory_slots" class="form-label">Memory Slots</label>
                                                <input type="number" class="form-control" id="memory_slots" name="memory_slots" min="1" placeholder="e.g., 2, 4">
                                            </div>
                                            <div class="mb-3">
                                                <label for="max_memory" class="form-label">Max Memory (GB)</label>
                                                <input type="text" class="form-control" id="max_memory" name="max_memory" placeholder="e.g., 128, 192">
                                            </div>
                                            <div class="mb-3">
                                                <label for="sata_ports" class="form-label">SATA Ports</label>
                                                <input type="number" class="form-control" id="sata_ports" name="sata_ports" min="0" placeholder="e.g., 4, 6">
                                            </div>
                                            <div class="mb-3">
                                                <label for="m2_slots" class="form-label">M.2 Slots</label>
                                                <input type="number" class="form-control" id="m2_slots" name="m2_slots" min="0" placeholder="e.g., 2, 3">
                                            </div>
                                            <div class="mb-3">
                                                <label for="pcie_slots" class="form-label">PCIe Slots</label>
                                                <input type="text" class="form-control" id="pcie_slots" name="pcie_slots" placeholder="e.g., 1x PCIe 5.0 x16, 2x PCIe 4.0 x16">
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="wifi_support" name="wifi_support" value="1">
                                                    <label class="form-check-label" for="wifi_support">
                                                        WiFi Support
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="bluetooth_support" name="bluetooth_support" value="1">
                                                    <label class="form-check-label" for="bluetooth_support">
                                                        Bluetooth Support
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="specs ram-specs">
                                            <div class="mb-3">
                                                <label for="ram_type" class="form-label">RAM Type</label>
                                                <select class="form-select" id="ram_type" name="ram_type">
                                                    <option value="">Select Type</option>
                                                    <option value="DDR4">DDR4</option>
                                                    <option value="DDR5">DDR5</option>
                                                    <option value="DDR3">DDR3</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="ram_speed" class="form-label">Speed (MHz)</label>
                                                <input type="text" class="form-control" id="ram_speed" name="ram_speed" placeholder="e.g., 3200, 6000">
                                            </div>
                                            <div class="mb-3">
                                                <label for="ram_capacity" class="form-label">Capacity (GB)</label>
                                                <input type="text" class="form-control" id="ram_capacity" name="ram_capacity" placeholder="e.g., 16, 32">
                                            </div>
                                            <div class="mb-3">
                                                <label for="ram_latency" class="form-label">CAS Latency</label>
                                                <input type="text" class="form-control" id="ram_latency" name="ram_latency" placeholder="e.g., CL16, CL36">
                                            </div>
                                            <div class="mb-3">
                                                <label for="ram_voltage" class="form-label">Voltage (V)</label>
                                                <input type="text" class="form-control" id="ram_voltage" name="ram_voltage" placeholder="e.g., 1.35">
                                            </div>
                                            <div class="mb-3">
                                                <label for="ram_modules" class="form-label">Modules (Number of sticks)</label>
                                                <input type="number" class="form-control" id="ram_modules" name="ram_modules" min="1" placeholder="e.g., 2, 4">
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="ecc_support" name="ecc_support" value="1">
                                                    <label class="form-check-label" for="ecc_support">
                                                        ECC Support
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="rgb_support" name="rgb_support" value="1">
                                                    <label class="form-check-label" for="rgb_support">
                                                        RGB Support
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="specs storage-specs">
                                            <div class="mb-3">
                                                <label for="storage_type" class="form-label">Storage Type</label>
                                                <select class="form-select" id="storage_type" name="storage_type">
                                                    <option value="">Select Type</option>
                                                    <option value="HDD">HDD</option>
                                                    <option value="SSD">SSD</option>
                                                    <option value="NVMe">NVMe</option>
                                                    <option value="Hybrid">Hybrid</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="storage_capacity" class="form-label">Capacity</label>
                                                <input type="text" class="form-control" id="storage_capacity" name="storage_capacity" placeholder="e.g., 1TB, 500GB">
                                            </div>
                                            <div class="mb-3">
                                                <label for="read_speed" class="form-label">Read Speed (MB/s)</label>
                                                <input type="number" class="form-control" id="read_speed" name="read_speed" placeholder="e.g., 3500">
                                            </div>
                                            <div class="mb-3">
                                                <label for="write_speed" class="form-label">Write Speed (MB/s)</label>
                                                <input type="number" class="form-control" id="write_speed" name="write_speed" placeholder="e.g., 2500">
                                            </div>
                                            <div class="mb-3">
                                                <label for="interface" class="form-label">Interface</label>
                                                <input type="text" class="form-control" id="interface" name="interface" placeholder="e.g., SATA III, PCIe 4.0 x4">
                                            </div>
                                            <div class="mb-3">
                                                <label for="rpm" class="form-label">RPM (for HDD)</label>
                                                <input type="number" class="form-control" id="rpm" name="rpm" placeholder="e.g., 7200">
                                            </div>
                                            <div class="mb-3">
                                                <label for="endurance_rating" class="form-label">Endurance Rating (TBW)</label>
                                                <input type="text" class="form-control" id="endurance_rating" name="endurance_rating" placeholder="e.g., 600">
                                            </div>
                                            <div class="mb-3">
                                                <label for="nvme_protocol" class="form-label">NVMe Protocol</label>
                                                <input type="text" class="form-control" id="nvme_protocol" name="nvme_protocol" placeholder="e.g., NVMe 1.4">
                                            </div>
                                        </div>
                                        
                                        <div class="specs psu-specs">
                                            <div class="mb-3">
                                                <label for="psu_wattage" class="form-label">Wattage (W)</label>
                                                <input type="number" class="form-control" id="psu_wattage" name="psu_wattage" placeholder="e.g., 750">
                                            </div>
                                            <div class="mb-3">
                                                <label for="psu_efficiency" class="form-label">Efficiency Rating</label>
                                                <select class="form-select" id="psu_efficiency" name="psu_efficiency">
                                                    <option value="">Select Efficiency</option>
                                                    <option value="80+ White">80+ White</option>
                                                    <option value="80+ Bronze">80+ Bronze</option>
                                                    <option value="80+ Silver">80+ Silver</option>
                                                    <option value="80+ Gold">80+ Gold</option>
                                                    <option value="80+ Platinum">80+ Platinum</option>
                                                    <option value="80+ Titanium">80+ Titanium</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="psu_modular" class="form-label">Modularity</label>
                                                <select class="form-select" id="psu_modular" name="psu_modular">
                                                    <option value="">Select Type</option>
                                                    <option value="Non-modular">Non-modular</option>
                                                    <option value="Semi-modular">Semi-modular</option>
                                                    <option value="Full-modular">Full-modular</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="psu_form_factor" class="form-label">Form Factor</label>
                                                <select class="form-select" id="psu_form_factor" name="psu_form_factor">
                                                    <option value="">Select Form Factor</option>
                                                    <option value="ATX">ATX</option>
                                                    <option value="SFX">SFX</option>
                                                    <option value="SFX-L">SFX-L</option>
                                                    <option value="TFX">TFX</option>
                                                    <option value="Flex ATX">Flex ATX</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="pcie_connectors" class="form-label">PCIe Connectors</label>
                                                <input type="number" class="form-control" id="pcie_connectors" name="pcie_connectors" placeholder="e.g., 4">
                                            </div>
                                            <div class="mb-3">
                                                <label for="sata_connectors" class="form-label">SATA Connectors</label>
                                                <input type="number" class="form-control" id="sata_connectors" name="sata_connectors" placeholder="e.g., 8">
                                            </div>
                                            <div class="mb-3">
                                                <label for="cpu_connectors" class="form-label">CPU Power Connectors</label>
                                                <input type="text" class="form-control" id="cpu_connectors" name="cpu_connectors" placeholder="e.g., 1x 8-pin, 2x 4+4-pin">
                                            </div>
                                        </div>
                                        
                                        <div class="specs monitor-specs">
                                            <div class="mb-3">
                                                <label for="screen_size" class="form-label">Screen Size (inches)</label>
                                                <input type="text" class="form-control" id="screen_size" name="screen_size" placeholder="e.g., 27, 32">
                                            </div>
                                            <div class="mb-3">
                                                <label for="resolution" class="form-label">Resolution</label>
                                                <input type="text" class="form-control" id="resolution" name="resolution" placeholder="e.g., 1920x1080, 3840x2160">
                                            </div>
                                            <div class="mb-3">
                                                <label for="refresh_rate" class="form-label">Refresh Rate (Hz)</label>
                                                <input type="text" class="form-control" id="refresh_rate" name="refresh_rate" placeholder="e.g., 144, 240">
                                            </div>
                                            <div class="mb-3">
                                                <label for="response_time" class="form-label">Response Time (ms)</label>
                                                <input type="text" class="form-control" id="response_time" name="response_time" placeholder="e.g., 1, 4">
                                            </div>
                                            <div class="mb-3">
                                                <label for="panel_type" class="form-label">Panel Type</label>
                                                <select class="form-select" id="panel_type" name="panel_type">
                                                    <option value="">Select Panel Type</option>
                                                    <option value="IPS">IPS</option>
                                                    <option value="VA">VA</option>
                                                    <option value="TN">TN</option>
                                                    <option value="OLED">OLED</option>
                                                    <option value="Mini-LED">Mini-LED</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="aspect_ratio" class="form-label">Aspect Ratio</label>
                                                <input type="text" class="form-control" id="aspect_ratio" name="aspect_ratio" placeholder="e.g., 16:9, 21:9">
                                            </div>
                                            <div class="mb-3">
                                                <label for="adaptive_sync" class="form-label">Adaptive Sync</label>
                                                <select class="form-select" id="adaptive_sync" name="adaptive_sync">
                                                    <option value="">Select Adaptive Sync</option>
                                                    <option value="G-Sync">G-Sync</option>
                                                    <option value="FreeSync">FreeSync</option>
                                                    <option value="G-Sync Compatible">G-Sync Compatible</option>
                                                    <option value="None">None</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="hdr_support" name="hdr_support" value="1">
                                                    <label class="form-check-label" for="hdr_support">
                                                        HDR Support
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="curved" name="curved" value="1">
                                                    <label class="form-check-label" for="curved">
                                                        Curved Display
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="vesa_mount" class="form-label">VESA Mount</label>
                                                <input type="text" class="form-control" id="vesa_mount" name="vesa_mount" placeholder="e.g., 100x100, 200x200">
                                            </div>
                                        </div>
                                        
                                        <div class="specs case-specs">
                                            <div class="mb-3">
                                                <label for="case_type" class="form-label">Case Type</label>
                                                <select class="form-select" id="case_type" name="case_type">
                                                    <option value="">Select Case Type</option>
                                                    <option value="Mid Tower">Mid Tower</option>
                                                    <option value="Full Tower">Full Tower</option>
                                                    <option value="Mini-ITX">Mini-ITX</option>
                                                    <option value="Micro-ATX">Micro-ATX</option>
                                                    <option value="HTPC">HTPC</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="case_form_factor" class="form-label">Supported Form Factors</label>
                                                <input type="text" class="form-control" id="case_form_factor" name="case_form_factor" placeholder="e.g., ATX, Micro-ATX, Mini-ITX">
                                            </div>
                                            <div class="mb-3">
                                                <label for="expansion_slots" class="form-label">Expansion Slots</label>
                                                <input type="number" class="form-control" id="expansion_slots" name="expansion_slots" min="0" placeholder="e.g., 7, 9">
                                            </div>
                                            <div class="mb-3">
                                                <label for="drive_bays" class="form-label">Drive Bays</label>
                                                <input type="text" class="form-control" id="drive_bays" name="drive_bays" placeholder="e.g., 2x 3.5&quot;, 2x 2.5&quot;">
                                            </div>
                                            <div class="mb-3">
                                                <label for="radiator_support" class="form-label">Radiator Support</label>
                                                <input type="text" class="form-control" id="radiator_support" name="radiator_support" placeholder="e.g., Front: 240mm, Top: 360mm">
                                            </div>
                                            <div class="mb-3">
                                                <label for="included_fans" class="form-label">Included Fans</label>
                                                <input type="text" class="form-control" id="included_fans" name="included_fans" placeholder="e.g., 2x 120mm RGB">
                                            </div>
                                            <div class="mb-3">
                                                <label for="max_gpu_length" class="form-label">Max GPU Length (mm)</label>
                                                <input type="number" class="form-control" id="max_gpu_length" name="max_gpu_length" min="0" placeholder="e.g., 330, 360">
                                            </div>
                                            <div class="mb-3">
                                                <label for="max_cpu_cooler_height" class="form-label">Max CPU Cooler Height (mm)</label>
                                                <input type="number" class="form-control" id="max_cpu_cooler_height" name="max_cpu_cooler_height" min="0" placeholder="e.g., 160, 170">
                                            </div>
                                            <div class="mb-3">
                                                <label for="side_panel_type" class="form-label">Side Panel Type</label>
                                                <select class="form-select" id="side_panel_type" name="side_panel_type">
                                                    <option value="">Select Panel Type</option>
                                                    <option value="Tempered Glass">Tempered Glass</option>
                                                    <option value="Acrylic">Acrylic</option>
                                                    <option value="Steel">Steel</option>
                                                    <option value="Mesh">Mesh</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="specs cooling-specs">
                                            <div class="mb-3">
                                                <label for="cooling_type" class="form-label">Cooling Type</label>
                                                <select class="form-select" id="cooling_type" name="cooling_type">
                                                    <option value="">Select Cooling Type</option>
                                                    <option value="Air">Air</option>
                                                    <option value="Liquid">Liquid</option>
                                                    <option value="Hybrid">Hybrid</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="fan_size" class="form-label">Fan Size (mm)</label>
                                                <input type="text" class="form-control" id="fan_size" name="fan_size" placeholder="e.g., 120, 140, 240">
                                            </div>
                                            <div class="mb-3">
                                                <label for="noise_level" class="form-label">Noise Level (dB)</label>
                                                <input type="text" class="form-control" id="noise_level" name="noise_level" placeholder="e.g., 25, 35">
                                            </div>
                                            <div class="mb-3">
                                                <label for="radiator_size" class="form-label">Radiator Size (mm)</label>
                                                <input type="text" class="form-control" id="radiator_size" name="radiator_size" placeholder="e.g., 240, 280, 360">
                                            </div>
                                            <div class="mb-3">
                                                <label for="compatibility" class="form-label">Compatibility</label>
                                                <input type="text" class="form-control" id="compatibility" name="compatibility" placeholder="e.g., Intel LGA1700, AMD AM5">
                                            </div>
                                            <div class="mb-3">
                                                <label for="max_tdp" class="form-label">Max TDP Support (W)</label>
                                                <input type="number" class="form-control" id="max_tdp" name="max_tdp" min="0" placeholder="e.g., 150, 250">
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="rgb_support" name="rgb_support" value="1">
                                                    <label class="form-check-label" for="rgb_support">
                                                        RGB Support
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="specs accessories-specs">
                                            <div class="mb-3">
                                                <label for="accessory_type" class="form-label">Accessory Type</label>
                                                <input type="text" class="form-control" id="accessory_type" name="accessory_type" placeholder="e.g., Keyboard, Mouse, Headset">
                                            </div>
                                            <div class="mb-3">
                                                <label for="included_accessories" class="form-label">Included Accessories</label>
                                                <textarea class="form-control" id="included_accessories" name="included_accessories" rows="2" placeholder="e.g., USB receiver, extra keycaps, cables"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="connectivity" class="form-label">Connectivity</label>
                                                <input type="text" class="form-control" id="connectivity" name="connectivity" placeholder="e.g., Bluetooth 5.0, USB Type-C, Wireless 2.4GHz">
                                            </div>
                                            <div class="mb-3">
                                                <label for="material" class="form-label">Material</label>
                                                <input type="text" class="form-control" id="material" name="material" placeholder="e.g., Aluminum, ABS Plastic, PBT">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Common fields for all products -->
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="product_description" class="form-label">Product Description *</label>
                                            <textarea class="form-control" id="product_description" name="product_description" rows="5" required></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4 d-flex justify-content-between">
                                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Show/hide specific fields based on product type
        document.addEventListener('DOMContentLoaded', function() {
            const productTypeSelect = document.getElementById('product_type');
            const allSpecs = document.querySelectorAll('.specs');
            
            function toggleSpecFields() {
                // Hide all spec fields first
                allSpecs.forEach(spec => {
                    spec.style.display = 'none';
                });
                
                // Show relevant fields based on selection
                const selectedType = productTypeSelect.value;
                
                if (selectedType === 'CPU') {
                    document.querySelector('.cpu-specs').style.display = 'block';
                } else if (selectedType === 'GPU') {
                    document.querySelector('.gpu-specs').style.display = 'block';
                } else if (selectedType === 'Motherboard') {
                    document.querySelector('.motherboard-specs').style.display = 'block';
                } else if (selectedType === 'RAM') {
                    document.querySelector('.ram-specs').style.display = 'block';
                } else if (selectedType === 'Storage') {
                    document.querySelector('.storage-specs').style.display = 'block';
                } else if (selectedType === 'PSU') {
                    document.querySelector('.psu-specs').style.display = 'block';
                } else if (selectedType === 'Case') {
                    document.querySelector('.case-specs').style.display = 'block';
                } else if (selectedType === 'Cooling') {
                    document.querySelector('.cooling-specs').style.display = 'block';
                } else if (selectedType === 'Monitor') {
                    document.querySelector('.monitor-specs').style.display = 'block';
                } else if (selectedType === 'Accessories') {
                    document.querySelector('.accessories-specs').style.display = 'block';
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