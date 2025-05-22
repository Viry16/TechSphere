<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Admin data
$admin_name = 'Admin Tech Sphere';
$admin_email = 'admin@tech_sphere.com';
$admin_password = 'admin123';

// Check if admin already exists
$check_query = "SELECT * FROM admin WHERE admin_email = '$admin_email'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "Admin dengan email $admin_email sudah ada!";
} else {
    // Create admin
    $query = "INSERT INTO admin (admin_name, admin_email, admin_password) 
              VALUES ('$admin_name', '$admin_email', '$admin_password')";
    
    if (mysqli_query($conn, $query)) {
        echo "Admin berhasil dibuat!<br>";
        echo "Email: $admin_email<br>";
        echo "Password: $admin_password<br>";
        echo "Silahkan <a href='login.php'>login</a> menggunakan akun admin ini.";
    } else {
        echo "Gagal membuat admin: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?> 