<?php
// Dapatkan query string jika ada
$query_string = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

// Set header untuk mengarahkan ke product-grids.php
header("Location: product-grids.php" . $query_string);
exit;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
    <script>
        // Hapus preferensi tampilan dari localStorage
        localStorage.removeItem('viewMode');
        
        // Arahkan ke halaman product-grids.php
        window.location.href = "product-grids.php<?php echo $query_string; ?>";
    </script>
</head>
<body>
    <p>Redirecting to product grid view...</p>
</body>
</html> 