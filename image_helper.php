<?php
/**
 * File helper untuk penanganan gambar
 */

/**
 * Fungsi untuk mendapatkan URL gambar yang valid
 * 
 * @param string $image_url URL gambar dari database
 * @param string $default_image URL gambar default jika gambar tidak ditemukan
 * @return string URL gambar yang valid
 */
function getValidImageUrl($image_url, $default_image = 'assets/images/products/product-1.jpg') {
    // Jika URL gambar kosong, kembalikan gambar default
    if(empty($image_url)) {
        return $default_image;
    }
    
    // Debug
    // error_log("Memeriksa gambar: " . $image_url);
    
    // Cek apakah URL adalah URL lengkap (http atau https)
    if(filter_var($image_url, FILTER_VALIDATE_URL)) {
        return $image_url;
    }
    
    // Daftar semua kemungkinan path
    $possible_paths = [
        $image_url,                                      // Path asli
        'uploads/' . basename($image_url),               // Di folder uploads
        'uploads/products/' . basename($image_url),      // Di folder uploads/products
        'assets/images/products/' . basename($image_url), // Di folder assets
        'admin/uploads/' . basename($image_url),         // Di folder admin/uploads
        'admin/uploads/products/' . basename($image_url), // Di folder admin/uploads/products
        '../uploads/' . basename($image_url),            // Di folder parent/uploads
        '../uploads/products/' . basename($image_url),   // Di folder parent/uploads/products
    ];
    
    // Periksa setiap kemungkinan path
    foreach ($possible_paths as $path) {
        if(file_exists($path)) {
            // error_log("Gambar ditemukan di: " . $path);
            return $path;
        }
    }
    
    // Jika tidak ditemukan, gunakan gambar default dari assets
    return $default_image;
}

/**
 * Fungsi untuk mendebug path gambar
 * 
 * @param string $image_url URL gambar dari database
 * @return array Informasi debug tentang path gambar
 */
function debugImagePath($image_url) {
    $result = [
        'original' => $image_url,
        'basename' => basename($image_url),
        'paths_checked' => [],
    ];
    
    // Daftar semua kemungkinan path
    $possible_paths = [
        $image_url,                                      // Path asli
        'uploads/' . basename($image_url),               // Di folder uploads
        'uploads/products/' . basename($image_url),      // Di folder uploads/products
        'assets/images/products/' . basename($image_url), // Di folder assets
        'admin/uploads/' . basename($image_url),         // Di folder admin/uploads
        'admin/uploads/products/' . basename($image_url), // Di folder admin/uploads/products
    ];
    
    // Periksa setiap kemungkinan path
    foreach ($possible_paths as $path) {
        $result['paths_checked'][$path] = file_exists($path);
    }
    
    return $result;
}
?> 