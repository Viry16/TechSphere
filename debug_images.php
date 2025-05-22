<?php
// Include image helper
include_once 'image_helper.php';

// Connect to database
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Display header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Product Image Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; text-align: left; }
        img { max-width: 100px; max-height: 100px; }
        .path-info { font-size: 12px; color: #666; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .container { max-width: 1200px; margin: 0 auto; }
        .info-box { background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        h2 { border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-top: 30px; }
        .file-list { max-height: 300px; overflow-y: auto; background-color: #f5f5f5; padding: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Product Image Debug</h1>
        <div class='info-box'>
            <p>This page displays information about product images in the database and files on the server.
            Use this information to help diagnose issues with images that are not appearing.</p>
        </div>";

// Check upload directory permissions
echo "<h2>Directory Information</h2>";
echo "<div class='info-box'>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current script:</strong> " . __FILE__ . "</p>";

$upload_dir = "uploads";
$products_dir = "uploads/products";
$assets_dir = "assets/images/products";

echo "<p><strong>Directory status:</strong></p>
<ul>
    <li>Upload directory (uploads): " . (is_dir($upload_dir) ? "<span class='success'>Exists</span>" : "<span class='error'>Does not exist</span>") . "</li>
    <li>Products directory (uploads/products): " . (is_dir($products_dir) ? "<span class='success'>Exists</span>" : "<span class='error'>Does not exist</span>") . "</li>
    <li>Assets directory (assets/images/products): " . (is_dir($assets_dir) ? "<span class='success'>Exists</span>" : "<span class='error'>Does not exist</span>") . "</li>
</ul>";

echo "<p><strong>Directory permissions:</strong></p>
<ul>
    <li>Upload directory writable: " . (is_writable($upload_dir) ? "<span class='success'>Yes</span>" : "<span class='error'>No</span>") . "</li>
    <li>Products directory writable: " . (is_writable($products_dir) ? "<span class='success'>Yes</span>" : "<span class='error'>No</span>") . "</li>
    <li>Assets directory writable: " . (is_writable($assets_dir) ? "<span class='success'>Yes</span>" : "<span class='error'>No</span>") . "</li>
</ul>";
echo "</div>";

// List files in products directory
echo "<h2>Files in Products Directory</h2>";
echo "<div class='info-box'>";
if (is_dir($products_dir)) {
    $files = scandir($products_dir);
    if (count($files) > 2) { // More than . and ..
        echo "<div class='file-list'><ul>";
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $file_size = filesize($products_dir . "/" . $file);
                $file_size_formatted = number_format($file_size / 1024, 2) . " KB";
                echo "<li><strong>{$file}</strong> (size: {$file_size_formatted})</li>";
            }
        }
        echo "</ul></div>";
    } else {
        echo "<p class='warning'>Directory is empty. No files found.</p>";
    }
} else {
    echo "<p class='error'>Products directory does not exist.</p>";
}
echo "</div>";

// Check database entries
echo "<h2>Product Data in Database</h2>";
echo "<div class='info-box'>";

// Count products
$count_sql = "SELECT COUNT(*) as total FROM product";
$count_result = mysqli_query($conn, $count_sql);
$count_data = mysqli_fetch_assoc($count_result);
$total_products = $count_data['total'];

// Count products with images
$with_image_sql = "SELECT COUNT(*) as total FROM product WHERE product_image_url IS NOT NULL AND product_image_url != ''";
$with_image_result = mysqli_query($conn, $with_image_sql);
$with_image_data = mysqli_fetch_assoc($with_image_result);
$with_image = $with_image_data['total'];

echo "<p><strong>Total products:</strong> {$total_products}</p>";
echo "<p><strong>Products with image URL:</strong> {$with_image} (" . round(($with_image / $total_products) * 100) . "%)</p>";
echo "</div>";

// Display products and image paths
echo "<h2>Product Image Details</h2>";
echo "<table>
<tr>
    <th>ID</th>
    <th>Product Name</th>
    <th>URL in Database</th>
    <th>Actual URL</th>
    <th>Status</th>
    <th>Preview</th>
</tr>";

// Get products
$sql = "SELECT product_id, product_name, product_image_url FROM product ORDER BY product_id LIMIT 20";
$result = mysqli_query($conn, $sql);

while ($product = mysqli_fetch_assoc($result)) {
    $image_url_db = $product['product_image_url'];
    $actual_image_url = getValidImageUrl($image_url_db);
    $is_default = ($actual_image_url === 'assets/images/products/product-1.jpg');
    
    $status_class = $is_default ? 'error' : 'success';
    $status_text = $is_default ? 'Using default image' : 'OK';
    
    echo "<tr>";
    echo "<td>" . $product['product_id'] . "</td>";
    echo "<td>" . $product['product_name'] . "</td>";
    echo "<td>" . ($image_url_db ?: "<em>Empty</em>") . "</td>";
    echo "<td>" . $actual_image_url . "</td>";
    echo "<td class='{$status_class}'>" . $status_text . "</td>";
    echo "<td><img src='" . $actual_image_url . "' alt='" . $product['product_name'] . "'></td>";
    echo "</tr>";
}

echo "</table>";

// Path checks in detail
echo "<h2>Detailed Path Checks</h2>";
echo "<div class='info-box'>";
echo "<p>Below are detailed path checks for the first product with an image URL:</p>";

// Get first product with image URL
$first_product_sql = "SELECT product_id, product_name, product_image_url FROM product 
                    WHERE product_image_url IS NOT NULL AND product_image_url != '' 
                    LIMIT 1";
$first_product_result = mysqli_query($conn, $first_product_sql);

if (mysqli_num_rows($first_product_result) > 0) {
    $first_product = mysqli_fetch_assoc($first_product_result);
    $image_url = $first_product['product_image_url'];
    
    echo "<p><strong>Product:</strong> " . $first_product['product_name'] . " (ID: " . $first_product['product_id'] . ")</p>";
    echo "<p><strong>Image URL:</strong> " . $image_url . "</p>";
    
    // Debug path
    $debug = debugImagePath($image_url);
    
    echo "<p><strong>Basename:</strong> " . $debug['basename'] . "</p>";
    
    echo "<p><strong>Path checks:</strong></p>";
    echo "<ul>";
    foreach ($debug['paths_checked'] as $path => $exists) {
        $status_class = $exists ? 'success' : 'error';
        $status_text = $exists ? 'Found' : 'Not found';
        echo "<li>{$path}: <span class='{$status_class}'>{$status_text}</span></li>";
    }
    echo "</ul>";
} else {
    echo "<p class='warning'>No products with image URLs found.</p>";
}
echo "</div>";

// Recommendations
echo "<h2>Recommendations</h2>";
echo "<div class='info-box'>";
echo "<p>If images are not displaying:</p>";
echo "<ol>
    <li>Ensure the <code>uploads</code> and <code>uploads/products</code> folders exist and have correct permissions.</li>
    <li>Make sure images are uploaded to the <code>uploads/products</code> folder.</li>
    <li>Verify that image URLs in the database are correct and point to existing files.</li>
    <li>If needed, use the <a href='fix_image_paths.php'>Fix Image Paths</a> tool to correct image URLs in the database.</li>
</ol>";
echo "</div>";

echo "</div>
</body>
</html>";

// Close connection
mysqli_close($conn);
?> 