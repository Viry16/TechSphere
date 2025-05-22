<?php
session_start();
// Cek apakah user sudah login
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

$user_id = $_SESSION['user_id'];

// Cek apakah ID alamat diberikan
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $address_name = urldecode($_GET['id']);
    
    // Debug info
    error_log("Menghapus alamat: user_id=$user_id, address_name='$address_name'");
    
    // Verifikasi bahwa alamat tersebut milik user yang sedang login
    $verify_sql = "SELECT * FROM address WHERE user_id = ? AND user_address_name = ?";
    $stmt = mysqli_prepare($conn, $verify_sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $address_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        $address = mysqli_fetch_assoc($result);
        $is_default = isset($address['is_default']) && $address['is_default'] == 1;
        
        // Hapus alamat
        $delete_sql = "DELETE FROM address WHERE user_id = ? AND user_address_name = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "is", $user_id, $address_name);
        
        if(mysqli_stmt_execute($delete_stmt)) {
            // Jika alamat yang dihapus adalah default, atur alamat lain sebagai default
            if($is_default) {
                // Cari alamat lain yang dimiliki user
                $other_address_sql = "SELECT * FROM address WHERE user_id = ? LIMIT 1";
                $other_stmt = mysqli_prepare($conn, $other_address_sql);
                mysqli_stmt_bind_param($other_stmt, "i", $user_id);
                mysqli_stmt_execute($other_stmt);
                $other_result = mysqli_stmt_get_result($other_stmt);
                
                if(mysqli_num_rows($other_result) > 0) {
                    $other_address = mysqli_fetch_assoc($other_result);
                    $other_address_name = $other_address['user_address_name'];
                    
                    // Set alamat lain sebagai default
                    $update_sql = "UPDATE address SET is_default = 1 WHERE user_id = ? AND user_address_name = ?";
                    $update_stmt = mysqli_prepare($conn, $update_sql);
                    mysqli_stmt_bind_param($update_stmt, "is", $user_id, $other_address_name);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }
                mysqli_stmt_close($other_stmt);
            }
            
            $_SESSION['message'] = "Alamat berhasil dihapus.";
        } else {
            $_SESSION['message'] = "Gagal menghapus alamat: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($delete_stmt);
    } else {
        $_SESSION['message'] = "Alamat dengan nama '$address_name' tidak ditemukan untuk akun Anda.";
        error_log("Alamat tidak ditemukan: user_id=$user_id, address_name='$address_name'");
    }
    
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['message'] = "Parameter ID alamat tidak diberikan. Pastikan URL memiliki parameter '?id=nama_alamat'.";
    error_log("Parameter ID alamat tidak valid atau kosong");
}

mysqli_close($conn);

// Redirect kembali ke halaman profil
header("Location: profile.php");
exit();
?> 