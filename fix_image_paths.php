<?php
// Start session
session_start();

// Connect to database
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Include image helper
include_once 'image_helper.php';

echo "<html>
<head>
    <title>Fix Image Paths</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; text-align: left; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Fix Product Image Paths</h1>";

// Action to fix paths
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_paths'])) {
    echo "<h2>Fix Results</h2>";
    
    // Loop through all products
    $sql = "SELECT product_id, product_name, product_image_url FROM product";
    $result = mysqli_query($conn, $sql);
    
    $count_updated = 0;
    $count_skipped = 0;
    
    echo "<table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Old URL</th>
            <th>New URL</th>
            <th>Status</th>
        </tr>";
    
    while ($product = mysqli_fetch_assoc($result)) {
        $product_id = $product['product_id'];
        $old_url = $product['product_image_url'];
        $product_name = $product['product_name'];
        
        if (empty($old_url)) {
            echo "<tr>
                <td>{$product_id}</td>
                <td>{$product_name}</td>
                <td><em>Empty</em></td>
                <td><em>Empty</em></td>
                <td class='warning'>Skipped (empty URL)</td>
            </tr>";
            $count_skipped++;
            continue;
        }
        
        // Check each possible path
        $basename = basename($old_url);
        $valid_path = null;
        
        $possible_paths = [
            'uploads/products/' . $basename,
            'uploads/' . $basename,
            'assets/images/products/' . basename($old_url)
        ];
        
        // Find path with existing file
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                $valid_path = $path;
                break;
            }
        }
        
        // If no valid path found, use default
        if ($valid_path === null) {
            echo "<tr>
                <td>{$product_id}</td>
                <td>{$product_name}</td>
                <td>{$old_url}</td>
                <td><em>Not changed</em></td>
                <td class='error'>File not found</td>
            </tr>";
            $count_skipped++;
            continue;
        }
        
        // If URL is already correct, skip
        if ($old_url === $valid_path) {
            echo "<tr>
                <td>{$product_id}</td>
                <td>{$product_name}</td>
                <td>{$old_url}</td>
                <td>{$valid_path}</td>
                <td class='warning'>Skipped (already correct)</td>
            </tr>";
            $count_skipped++;
            continue;
        }
        
        // Update database with correct path
        $update_sql = "UPDATE product SET product_image_url = ? WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "si", $valid_path, $product_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<tr>
                <td>{$product_id}</td>
                <td>{$product_name}</td>
                <td>{$old_url}</td>
                <td>{$valid_path}</td>
                <td class='success'>Fixed</td>
            </tr>";
            $count_updated++;
        } else {
            echo "<tr>
                <td>{$product_id}</td>
                <td>{$product_name}</td>
                <td>{$old_url}</td>
                <td>{$valid_path}</td>
                <td class='error'>Update failed: " . mysqli_error($conn) . "</td>
            </tr>";
            $count_skipped++;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    echo "</table>";
    echo "<p><strong>Results:</strong> {$count_updated} products fixed, {$count_skipped} products skipped.</p>";
}

// Display product image status
echo "<h2>Product Image Status</h2>";

$sql = "SELECT product_id, product_name, product_image_url FROM product";
$result = mysqli_query($conn, $sql);

echo "<table>
    <tr>
        <th>ID</th>
        <th>Product Name</th>
        <th>Image URL</th>
        <th>Status</th>
        <th>Preview</th>
    </tr>";

while ($product = mysqli_fetch_assoc($result)) {
    $product_id = $product['product_id'];
    $image_url = $product['product_image_url'];
    $product_name = $product['product_name'];
    
    $actual_path = getValidImageUrl($image_url);
    $is_default = ($actual_path === 'assets/images/products/product-1.jpg');
    
    $status_class = $is_default ? 'error' : 'success';
    $status_text = $is_default ? 'Using default' : 'OK';
    
    echo "<tr>
        <td>{$product_id}</td>
        <td>{$product_name}</td>
        <td>{$image_url}</td>
        <td class='{$status_class}'>{$status_text}</td>
        <td><img src='{$actual_path}' style='max-width: 100px; max-height: 100px;'></td>
    </tr>";
}

echo "</table>";

// Form to fix image paths
echo "<h2>Fix Image Paths</h2>
<p>Click the button below to fix all product image URLs in the database.</p>
<form method='post' action=''>
    <input type='hidden' name='fix_paths' value='1'>
    <button type='submit' style='padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>
        Fix Image Paths
    </button>
</form>";

echo "<h2>Upload Folder</h2>";
echo "<p>Make sure all product images are uploaded to the <code>uploads/products/</code> folder.</p>";

// Check upload folder
$upload_dir = "uploads";
$products_dir = "uploads/products";

echo "<p><strong>Folder status:</strong><br>";
echo "Uploads folder exists: " . (is_dir($upload_dir) ? "Yes" : "No") . "<br>";
echo "Products folder exists: " . (is_dir($products_dir) ? "Yes" : "No") . "</p>";

// Close connection
mysqli_close($conn);

echo "</body></html>";
?> 