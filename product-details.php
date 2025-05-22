<?php
// Start session at the beginning of the file, before any output
session_start();
// Include image helper
include_once 'image_helper.php';

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: product-grids.php");
    exit();
}

// Connect to database
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Get product details
$product_id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT * FROM product WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: product-grids.php");
    exit();
}

    $product = mysqli_fetch_assoc($result);
$product_type = $product['product_type'];

// Get specific product details based on type
$specific_details = array();
switch ($product_type) {
    case 'CPU':
        $sql = "SELECT * FROM product_cpu WHERE product_id = ?";
        break;
    case 'GPU':
        $sql = "SELECT * FROM product_gpu WHERE product_id = ?";
        break;
    case 'RAM':
        $sql = "SELECT * FROM product_ram WHERE product_id = ?";
        break;
    case 'Motherboard':
        $sql = "SELECT * FROM product_motherboard WHERE product_id = ?";
        break;
    case 'Storage':
        $sql = "SELECT * FROM product_storage WHERE product_id = ?";
        break;
    case 'PSU':
        $sql = "SELECT * FROM product_psu WHERE product_id = ?";
        break;
    case 'Case':
        $sql = "SELECT * FROM product_case WHERE product_id = ?";
        break;
    case 'Cooling':
        $sql = "SELECT * FROM product_cooling WHERE product_id = ?";
        break;
    case 'Monitor':
        $sql = "SELECT * FROM product_monitor WHERE product_id = ?";
        break;
    case 'Accessories':
        $sql = "SELECT * FROM product_accessories WHERE product_id = ?";
        break;
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$specific_details = mysqli_fetch_assoc($result);

// Get product image
$image_url = !empty($product['product_image_url']) ? 
    (strpos($product['product_image_url'], 'http') === 0 ? 
        $product['product_image_url'] : 
        'assets/images/products/' . basename($product['product_image_url'])) : 
    'assets/images/products/default.jpg';

// Get related products
$related_sql = "SELECT p.*, 
                CASE 
                    WHEN p.product_type = 'PSU' THEN ps.psu_wattage
                    WHEN p.product_type = 'CPU' THEN pc.core_count
                    WHEN p.product_type = 'GPU' THEN pg.vram
                    WHEN p.product_type = 'RAM' THEN pr.ram_capacity
                    WHEN p.product_type = 'Storage' THEN ps2.storage_capacity
                    WHEN p.product_type = 'Motherboard' THEN pm.motherboard_chipset
                    WHEN p.product_type = 'Case' THEN pc2.case_form_factor
                    WHEN p.product_type = 'Cooling' THEN pc3.cooling_type
                    WHEN p.product_type = 'Monitor' THEN pm2.screen_size
                    WHEN p.product_type = 'Accessories' THEN pa.accessory_type
                    ELSE NULL
                END as main_spec,
                CASE 
                    WHEN p.product_type = 'PSU' THEN CONCAT(ps.psu_wattage, 'W')
                    WHEN p.product_type = 'CPU' THEN CONCAT(pc.core_count, ' Cores')
                    WHEN p.product_type = 'GPU' THEN CONCAT(pg.vram, 'GB VRAM')
                    WHEN p.product_type = 'RAM' THEN CONCAT(pr.ram_capacity, 'GB')
                    WHEN p.product_type = 'Storage' THEN ps2.storage_capacity
                    WHEN p.product_type = 'Motherboard' THEN pm.motherboard_chipset
                    WHEN p.product_type = 'Case' THEN pc2.case_form_factor
                    WHEN p.product_type = 'Cooling' THEN CONCAT(pc3.fan_size, 'mm')
                    WHEN p.product_type = 'Monitor' THEN CONCAT(pm2.screen_size, ' inch')
                    WHEN p.product_type = 'Accessories' THEN pa.accessory_type
                    ELSE NULL
                END as spec_display
                FROM product p
                LEFT JOIN product_psu ps ON p.product_id = ps.product_id
                LEFT JOIN product_cpu pc ON p.product_id = pc.product_id
                LEFT JOIN product_gpu pg ON p.product_id = pg.product_id
                LEFT JOIN product_ram pr ON p.product_id = pr.product_id
                LEFT JOIN product_storage ps2 ON p.product_id = ps2.product_id
                LEFT JOIN product_motherboard pm ON p.product_id = pm.product_id
                LEFT JOIN product_case pc2 ON p.product_id = pc2.product_id
                LEFT JOIN product_cooling pc3 ON p.product_id = pc3.product_id
                LEFT JOIN product_monitor pm2 ON p.product_id = pm2.product_id
                LEFT JOIN product_accessories pa ON p.product_id = pa.product_id
                WHERE p.product_type = ? AND p.product_id != ?
                LIMIT 4";
$stmt = mysqli_prepare($conn, $related_sql);
mysqli_stmt_bind_param($stmt, "si", $product_type, $product_id);
mysqli_stmt_execute($stmt);
$related_products = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html class="no-js" lang="zxx">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?php echo htmlspecialchars($product['product_name']); ?> - TechSphere</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link
      rel="shortcut icon"
      type="image/x-icon"
      href="assets/images/favicon.png"
    />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.3.0.css" />
    <link rel="stylesheet" href="assets/css/tiny-slider.css" />
    <link rel="stylesheet" href="assets/css/glightbox.min.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
      .product-details {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
      }
      .product-image {
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        position: relative;
      }
      .product-image img {
        width: 100%;
        height: auto;
        object-fit: cover;
        transition: transform 0.3s ease;
      }
      .product-image img:hover {
        transform: scale(1.05);
      }
      .product-info h2 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #333;
      }
      .product-meta {
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
      }
      .product-meta span {
        color: #666;
        font-size: 14px;
        padding: 5px 10px;
        background: #f8f9fa;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 5px;
      }
      .product-meta span i {
        color: #2563eb;
      }
      .product-price {
        font-size: 24px;
        font-weight: 700;
        color: #2563eb;
        margin-bottom: 20px;
      }
      .product-description {
        margin-bottom: 30px;
        color: #666;
        line-height: 1.6;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
      }
      .product-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 30px;
      }
      .detail-card {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
      }
      .detail-card:hover {
        transform: translateY(-5px);
      }
      .detail-card h4 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .detail-list {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      .detail-list li {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .detail-list li:last-child {
        border-bottom: none;
      }
      .detail-label {
        color: #666;
        font-weight: 500;
      }
      .detail-value {
        color: #333;
        font-weight: 600;
        text-align: right;
      }
      .add-to-cart {
        display: flex;
        gap: 20px;
        margin-top: 30px;
        align-items: center;
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
      }
      .quantity {
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .quantity input {
        width: 60px;
        padding: 8px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
      }
      .btn-add-cart {
        background: #2563eb;
        color: #fff;
        padding: 12px 30px;
        border-radius: 8px;
        border: none;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .btn-add-cart:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
      }
      .stock-info {
        color: #666;
        font-size: 14px;
        margin-top: 10px;
      }
      .stock-info.available {
        color: #10b981;
      }
      .stock-info.low {
        color: #f59e0b;
      }
      .stock-info.out {
        color: #ef4444;
      }
      .technical-specs {
        margin-top: 30px;
      }
      .technical-specs .detail-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
      }
      .technical-specs .detail-list li {
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        border: none;
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
              <h1 class="page-title">Product Details</h1>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-12">
            <ul class="breadcrumb-nav">
              <li><a href="index.php">Home</a></li>
              <li><a href="product-grids.php">Products</a></li>
              <li><?php echo htmlspecialchars($product['product_name']); ?></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Start Product Details -->
    <section class="product-details section">
      <div class="container">
                <div class="row">
          <div class="col-lg-6 col-md-6 col-12">
            <div class="product-image">
              <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                      </div>
            <div class="add-to-cart">
                <div class="quantity">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['product_stock_quantity']; ?>">
                      </div>
                <button class="btn-add-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                    <i class="lni lni-cart"></i> Add to Cart
                          </button>
                <?php
                $stock_class = 'available';
                if ($product['product_stock_quantity'] <= 0) {
                    $stock_class = 'out';
                } elseif ($product['product_stock_quantity'] <= 5) {
                    $stock_class = 'low';
                }
                ?>
                <div class="stock-info <?php echo $stock_class; ?>">
                    <?php
                    if ($product['product_stock_quantity'] <= 0) {
                        echo 'Out of Stock';
                    } elseif ($product['product_stock_quantity'] <= 5) {
                        echo 'Low Stock - Only ' . $product['product_stock_quantity'] . ' left!';
                    } else {
                        echo 'In Stock';
                    }
                    ?>
                      </div>
                    </div>
                      </div>
          <div class="col-lg-6 col-md-6 col-12">
            <div class="product-info">
              <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
              <div class="product-meta">
                <span><i class="lni lni-tag"></i> <?php echo htmlspecialchars($product['product_type']); ?></span>
                <span><i class="lni lni-box"></i> Stock: <?php echo $product['product_stock_quantity']; ?></span>
                <?php if(!empty($product['product_brand'])): ?>
                <span><i class="lni lni-briefcase"></i> <?php echo htmlspecialchars($product['product_brand']); ?></span>
                <?php endif; ?>
                <?php if(!empty($product['model_number'])): ?>
                <span><i class="lni lni-barcode"></i> <?php echo htmlspecialchars($product['model_number']); ?></span>
                <?php endif; ?>
                    </div>
              <div class="product-price">
                Rp <?php echo number_format($product['product_price'], 0, ',', '.'); ?>
                      </div>
              <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
                    </div>
              
              <!-- General Information Card -->
              <div class="detail-card">
                <h4><i class="lni lni-info"></i> General Information</h4>
                <ul class="detail-list">
                  <?php if(!empty($product['dimensions'])): ?>
                  <li>
                    <span class="detail-label">Dimensions:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($product['dimensions']); ?> mm</span>
                    </li>
                  <?php endif; ?>
                  <?php if(!empty($product['weight'])): ?>
                  <li>
                    <span class="detail-label">Weight:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($product['weight']); ?> grams</span>
                    </li>
                  <?php endif; ?>
                  <?php if(!empty($product['color'])): ?>
                    <li>
                    <span class="detail-label">Color:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($product['color']); ?></span>
                    </li>
                  <?php endif; ?>
                  <?php if(!empty($product['release_date'])): ?>
                  <li>
                    <span class="detail-label">Release Date:</span>
                    <span class="detail-value"><?php echo date('F j, Y', strtotime($product['release_date'])); ?></span>
                  </li>
                  <?php endif; ?>
                  <?php if(!empty($product['warranty'])): ?>
                  <li>
                    <span class="detail-label">Warranty:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($product['warranty']); ?></span>
                  </li>
                  <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        
        <!-- Technical Specifications Card -->
        <div class="technical-specs">
          <div class="detail-card">
            <h4><i class="lni lni-cog"></i> Technical Specifications</h4>
            <ul class="detail-list">
              <?php
              if ($specific_details) {
                switch ($product_type) {
                  case 'CPU':
                    echo '<li><span class="detail-label">Socket Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['socket_type']) . '</span></li>';
                    echo '<li><span class="detail-label">Core Count:</span><span class="detail-value">' . htmlspecialchars($specific_details['core_count']) . '</span></li>';
                    echo '<li><span class="detail-label">Thread Count:</span><span class="detail-value">' . htmlspecialchars($specific_details['thread_count']) . '</span></li>';
                    echo '<li><span class="detail-label">Base Clock:</span><span class="detail-value">' . htmlspecialchars($specific_details['base_clock']) . ' GHz</span></li>';
                    echo '<li><span class="detail-label">Boost Clock:</span><span class="detail-value">' . htmlspecialchars($specific_details['boost_clock']) . ' GHz</span></li>';
                    echo '<li><span class="detail-label">TDP:</span><span class="detail-value">' . htmlspecialchars($specific_details['tdp']) . ' Watts</span></li>';
                    echo '<li><span class="detail-label">L3 Cache:</span><span class="detail-value">' . htmlspecialchars($specific_details['l3_cache']) . ' MB</span></li>';
                    break;

                  case 'GPU':
                    echo '<li><span class="detail-label">GPU Chipset:</span><span class="detail-value">' . htmlspecialchars($specific_details['gpu_chipset']) . '</span></li>';
                    echo '<li><span class="detail-label">VRAM:</span><span class="detail-value">' . htmlspecialchars($specific_details['vram']) . ' GB</span></li>';
                    echo '<li><span class="detail-label">VRAM Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['vram_type']) . '</span></li>';
                    echo '<li><span class="detail-label">GPU Clock:</span><span class="detail-value">' . htmlspecialchars($specific_details['gpu_clock']) . ' MHz</span></li>';
                    echo '<li><span class="detail-label">Boost Clock:</span><span class="detail-value">' . htmlspecialchars($specific_details['boost_clock_gpu']) . ' MHz</span></li>';
                    echo '<li><span class="detail-label">Memory Interface:</span><span class="detail-value">' . htmlspecialchars($specific_details['memory_interface']) . ' bit</span></li>';
                    echo '<li><span class="detail-label">GPU Length:</span><span class="detail-value">' . htmlspecialchars($specific_details['gpu_length']) . ' mm</span></li>';
                    break;

                  case 'RAM':
                    echo '<li><span class="detail-label">RAM Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['ram_type']) . '</span></li>';
                    echo '<li><span class="detail-label">RAM Speed:</span><span class="detail-value">' . htmlspecialchars($specific_details['ram_speed']) . ' MHz</span></li>';
                    echo '<li><span class="detail-label">RAM Capacity:</span><span class="detail-value">' . htmlspecialchars($specific_details['ram_capacity']) . ' GB</span></li>';
                    echo '<li><span class="detail-label">RAM Latency:</span><span class="detail-value">' . htmlspecialchars($specific_details['ram_latency']) . '</span></li>';
                    echo '<li><span class="detail-label">RAM Voltage:</span><span class="detail-value">' . htmlspecialchars($specific_details['ram_voltage']) . ' V</span></li>';
                    echo '<li><span class="detail-label">RAM Modules:</span><span class="detail-value">' . htmlspecialchars($specific_details['ram_modules']) . '</span></li>';
                    break;

                  case 'Motherboard':
                    echo '<li><span class="detail-label">Form Factor:</span><span class="detail-value">' . htmlspecialchars($specific_details['motherboard_form_factor']) . '</span></li>';
                    echo '<li><span class="detail-label">Chipset:</span><span class="detail-value">' . htmlspecialchars($specific_details['motherboard_chipset']) . '</span></li>';
                    echo '<li><span class="detail-label">Socket Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['socket_type']) . '</span></li>';
                    echo '<li><span class="detail-label">Memory Slots:</span><span class="detail-value">' . htmlspecialchars($specific_details['memory_slots']) . '</span></li>';
                    echo '<li><span class="detail-label">Max Memory:</span><span class="detail-value">' . htmlspecialchars($specific_details['max_memory']) . ' GB</span></li>';
                    echo '<li><span class="detail-label">SATA Ports:</span><span class="detail-value">' . htmlspecialchars($specific_details['sata_ports']) . '</span></li>';
                    echo '<li><span class="detail-label">M.2 Slots:</span><span class="detail-value">' . htmlspecialchars($specific_details['m2_slots']) . '</span></li>';
                    break;

                  case 'Storage':
                    echo '<li><span class="detail-label">Storage Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['storage_type']) . '</span></li>';
                    echo '<li><span class="detail-label">Capacity:</span><span class="detail-value">' . htmlspecialchars($specific_details['storage_capacity']) . '</span></li>';
                    echo '<li><span class="detail-label">Read Speed:</span><span class="detail-value">' . htmlspecialchars($specific_details['read_speed']) . ' MB/s</span></li>';
                    echo '<li><span class="detail-label">Write Speed:</span><span class="detail-value">' . htmlspecialchars($specific_details['write_speed']) . ' MB/s</span></li>';
                    echo '<li><span class="detail-label">Interface:</span><span class="detail-value">' . htmlspecialchars($specific_details['interface']) . '</span></li>';
                    if ($specific_details['storage_type'] == 'HDD') {
                      echo '<li><span class="detail-label">RPM:</span><span class="detail-value">' . htmlspecialchars($specific_details['rpm']) . '</span></li>';
                    }
                    break;

                  case 'PSU':
                    echo '<li><span class="detail-label">Wattage:</span><span class="detail-value">' . htmlspecialchars($specific_details['psu_wattage']) . ' Watts</span></li>';
                    echo '<li><span class="detail-label">Efficiency Rating:</span><span class="detail-value">' . htmlspecialchars($specific_details['psu_efficiency']) . '</span></li>';
                    echo '<li><span class="detail-label">Modularity:</span><span class="detail-value">' . htmlspecialchars($specific_details['psu_modular']) . '</span></li>';
                    echo '<li><span class="detail-label">Form Factor:</span><span class="detail-value">' . htmlspecialchars($specific_details['psu_form_factor']) . '</span></li>';
                    echo '<li><span class="detail-label">PCIe Connectors:</span><span class="detail-value">' . htmlspecialchars($specific_details['pcie_connectors']) . 'x 8-pin</span></li>';
                    echo '<li><span class="detail-label">SATA Connectors:</span><span class="detail-value">' . htmlspecialchars($specific_details['sata_connectors']) . 'x SATA</span></li>';
                    echo '<li><span class="detail-label">CPU Connectors:</span><span class="detail-value">' . htmlspecialchars($specific_details['cpu_connectors']) . '</span></li>';
                    break;

                  case 'Case':
                    echo '<li><span class="detail-label">Case Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['case_type']) . '</span></li>';
                    echo '<li><span class="detail-label">Form Factor:</span><span class="detail-value">' . htmlspecialchars($specific_details['case_form_factor']) . '</span></li>';
                    echo '<li><span class="detail-label">Expansion Slots:</span><span class="detail-value">' . htmlspecialchars($specific_details['expansion_slots']) . '</span></li>';
                    echo '<li><span class="detail-label">Drive Bays:</span><span class="detail-value">' . htmlspecialchars($specific_details['drive_bays']) . '</span></li>';
                    echo '<li><span class="detail-label">Max GPU Length:</span><span class="detail-value">' . htmlspecialchars($specific_details['max_gpu_length']) . ' mm</span></li>';
                    echo '<li><span class="detail-label">Max CPU Cooler Height:</span><span class="detail-value">' . htmlspecialchars($specific_details['max_cpu_cooler_height']) . ' mm</span></li>';
                    break;

                  case 'Cooling':
                    echo '<li><span class="detail-label">Cooling Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['cooling_type']) . '</span></li>';
                    echo '<li><span class="detail-label">Fan Size:</span><span class="detail-value">' . htmlspecialchars($specific_details['fan_size']) . ' mm</span></li>';
                    echo '<li><span class="detail-label">Noise Level:</span><span class="detail-value">' . htmlspecialchars($specific_details['noise_level']) . ' dB</span></li>';
                    echo '<li><span class="detail-label">Max TDP:</span><span class="detail-value">' . htmlspecialchars($specific_details['max_tdp']) . ' Watts</span></li>';
                    echo '<li><span class="detail-label">RGB Support:</span><span class="detail-value">' . ($specific_details['rgb_support'] ? 'Yes' : 'No') . '</span></li>';
                    break;

                  case 'Monitor':
                    echo '<li><span class="detail-label">Screen Size:</span><span class="detail-value">' . htmlspecialchars($specific_details['screen_size']) . ' inches</span></li>';
                    echo '<li><span class="detail-label">Resolution:</span><span class="detail-value">' . htmlspecialchars($specific_details['resolution']) . '</span></li>';
                    echo '<li><span class="detail-label">Refresh Rate:</span><span class="detail-value">' . htmlspecialchars($specific_details['refresh_rate']) . ' Hz</span></li>';
                    echo '<li><span class="detail-label">Response Time:</span><span class="detail-value">' . htmlspecialchars($specific_details['response_time']) . ' ms</span></li>';
                    echo '<li><span class="detail-label">Panel Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['panel_type']) . '</span></li>';
                    echo '<li><span class="detail-label">HDR Support:</span><span class="detail-value">' . ($specific_details['hdr_support'] ? 'Yes' : 'No') . '</span></li>';
                    break;

                  case 'Accessories':
                    echo '<li><span class="detail-label">Accessory Type:</span><span class="detail-value">' . htmlspecialchars($specific_details['accessory_type']) . '</span></li>';
                    echo '<li><span class="detail-label">Compatibility Notes:</span><span class="detail-value">' . htmlspecialchars($specific_details['compatibility_notes']) . '</span></li>';
                    echo '<li><span class="detail-label">Included Accessories:</span><span class="detail-value">' . htmlspecialchars($specific_details['included_accessories']) . '</span></li>';
                    echo '<li><span class="detail-label">Connectivity:</span><span class="detail-value">' . htmlspecialchars($specific_details['connectivity']) . '</span></li>';
                    echo '<li><span class="detail-label">Material:</span><span class="detail-value">' . htmlspecialchars($specific_details['material']) . '</span></li>';
                    break;
                }
              }
              ?>
            </ul>
          </div>
                </div>
              </div>
    </section>
    <!-- End Product Details -->

    <!-- Start Related Products -->
    <section class="related-products section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h2>Related Products</h2>
                </div>
              </div>
                      </div>
            <div class="row">
                <?php while($related = mysqli_fetch_assoc($related_products)): 
                    $related_image_url = !empty($related['product_image_url']) ? 
                        (strpos($related['product_image_url'], 'http') === 0 ? 
                            $related['product_image_url'] : 
                            'assets/images/products/' . basename($related['product_image_url'])) : 
                        'assets/images/products/default.jpg';
                ?>
              <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-product">
                        <div class="product-image">
                            <img src="<?php echo $related_image_url; ?>" alt="<?php echo htmlspecialchars($related['product_name']); ?>">
                            <div class="button">
                                <a href="product-details.php?id=<?php echo $related['product_id']; ?>" class="btn">
                                    <i class="lni lni-eye"></i> View Details
                                </a>
                </div>
              </div>
                        <div class="product-info">
                            <span class="category"><?php echo htmlspecialchars($related['product_type']); ?></span>
                            <h4 class="title">
                                <a href="product-details.php?id=<?php echo $related['product_id']; ?>">
                                    <?php echo htmlspecialchars($related['product_name']); ?>
                                </a>
                            </h4>
                            <?php if($related['spec_display']): ?>
                            <div class="spec">
                                <?php echo htmlspecialchars($related['spec_display']); ?>
                </div>
                            <?php endif; ?>
                            <div class="price">
                                <span>Rp <?php echo number_format($related['product_price'], 0, ',', '.'); ?></span>
                </div>
              </div>
                </div>
              </div>
                <?php endwhile; ?>
            </div>
          </div>
    </section>
    <!-- End Related Products -->

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
    <script src="assets/js/cart.js"></script>
    <script>
        // Initialize image gallery if needed
        const productImage = new tns({
            container: '.product-image-slider',
            items: 1,
            slideBy: 'page',
            autoplay: false,
            controlsContainer: '.slider-arrow',
            navContainer: '.slider-thumbnails',
            navAsThumbnails: true,
            arrowKeys: true,
            autoplayButtonOutput: false,
            mouseDrag: true,
            preventScrollOnTouch: true
        });
    </script>
  </body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>
