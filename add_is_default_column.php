<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "tech_sphere");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Tambahkan kolom is_default jika belum ada
$check_column_sql = "SHOW COLUMNS FROM `address` LIKE 'is_default'";
$check_column_result = mysqli_query($conn, $check_column_sql);

if (mysqli_num_rows($check_column_result) == 0) {
    // Kolom belum ada, tambahkan kolom
    $add_column_sql = "ALTER TABLE `address` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0";
    
    if (mysqli_query($conn, $add_column_sql)) {
        echo "Kolom is_default berhasil ditambahkan ke tabel address.<br>";
        
        // Set alamat pertama dari setiap pengguna sebagai default
        $users_sql = "SELECT DISTINCT user_id FROM address";
        $users_result = mysqli_query($conn, $users_sql);
        
        while ($user = mysqli_fetch_assoc($users_result)) {
            $user_id = $user['user_id'];
            
            // Ambil alamat pertama dari user
            $first_address_sql = "SELECT user_address_name FROM address WHERE user_id = $user_id LIMIT 1";
            $first_address_result = mysqli_query($conn, $first_address_sql);
            
            if (mysqli_num_rows($first_address_result) > 0) {
                $first_address = mysqli_fetch_assoc($first_address_result);
                $address_name = $first_address['user_address_name'];
                
                // Set alamat ini sebagai default
                $update_sql = "UPDATE address SET is_default = 1 
                              WHERE user_id = $user_id AND user_address_name = '$address_name'";
                
                if (mysqli_query($conn, $update_sql)) {
                    echo "Alamat default telah diatur untuk user ID: $user_id<br>";
                } else {
                    echo "Error mengatur alamat default untuk user ID $user_id: " . mysqli_error($conn) . "<br>";
                }
            }
        }
    } else {
        echo "Error menambahkan kolom is_default: " . mysqli_error($conn);
    }
} else {
    echo "Kolom is_default sudah ada di tabel address.";
}

mysqli_close($conn);
?> 