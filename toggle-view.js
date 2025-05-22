/**
 * Toggle View Script for TechSphere
 * Fungsi untuk menyimpan preferensi tampilan (grid/list) ke localStorage
 */
document.addEventListener('DOMContentLoaded', function() {
    // Ambil element tombol view mode
    const gridViewBtn = document.querySelector('.view-mode a:first-child');
    const listViewBtn = document.querySelector('.view-mode a:last-child');
    
    // Fungsi untuk mengatur preferensi view mode
    function setViewMode(mode) {
        localStorage.setItem('viewMode', mode);
    }
    
    // Tambahkan event listener ke tombol Grid View
    if (gridViewBtn) {
        gridViewBtn.addEventListener('click', function(e) {
            setViewMode('grid');
            // Jika tombol grid sudah memiliki href ke halaman grid, biarkan browser handle navigasi
            // Tidak perlu redirect manual
        });
    }
    
    // Tambahkan event listener ke tombol List View
    if (listViewBtn) {
        listViewBtn.addEventListener('click', function(e) {
            setViewMode('list');
            // Jika tombol list sudah memiliki href ke halaman list, biarkan browser handle navigasi
            // Tidak perlu redirect manual
        });
    }
    
    // Hapus fitur auto-load view yang menyebabkan redirect otomatis
    // Pengguna harus mengklik tombol secara manual untuk beralih tampilan
}); 