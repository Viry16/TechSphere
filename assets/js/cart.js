/**
 * Cart management functions
 */

// Function to add product to cart
function addToCart(productId, quantity = null) {
    // Get quantity from input if not provided
    if (quantity === null) {
        const quantityInput = document.getElementById('quantity');
        quantity = quantityInput ? parseInt(quantityInput.value) : 1;
    }
    
    if (!quantity || quantity < 1) {
        showNotification('Silakan masukkan jumlah yang valid', 'error');
        return;
    }
    
    // Add to cart using AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'add_to_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    showNotification('Produk berhasil ditambahkan ke keranjang', 'success');
                    
                    // Update cart count in header
                    const cartCountElements = document.querySelectorAll('.cart-items .total-items');
                    cartCountElements.forEach(element => {
                        element.textContent = response.cart_count;
                    });
                    
                    // Update dropdown cart header count
                    const dropdownCartHeader = document.querySelector('.dropdown-cart-header span');
                    if (dropdownCartHeader) {
                        dropdownCartHeader.textContent = response.cart_count + ' Items';
                    }
                    
                    // Optional: reload page to see updated cart or redirect to cart
                    // window.location.href = 'cart.php';
                } else {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        showNotification(response.message || 'Gagal menambahkan produk ke keranjang', 'error');
                    }
                }
            } catch (e) {
                console.error('Error parsing JSON response:', e);
                showNotification('Terjadi kesalahan saat memproses permintaan', 'error');
            }
        } else {
            showNotification('Terjadi kesalahan pada server', 'error');
        }
    };
    xhr.onerror = function() {
        showNotification('Tidak dapat terhubung ke server', 'error');
    };
    xhr.send(`product_id=${productId}&quantity=${quantity}`);
}

// Function to remove product from cart
function removeFromCart(productId) {
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
        return;
    }
    
    // Remove from cart using AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'remove_from_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    showNotification('Produk berhasil dihapus dari keranjang', 'success');
                    
                    // Update cart UI
                    const cartItem = document.querySelector(`.cart-single-list[data-product-id="${productId}"]`);
                    if (cartItem) {
                        cartItem.remove();
                    }
                    
                    // Update dropdown counts
                    updateCartCountsInUI(response.cart_count);
                    
                    // Update total price
                    updateCartTotalInUI(response.cart_total_formatted);
                    
                    // If cart is empty, show empty message
                    if (response.cart_count === 0) {
                        const cartListBody = document.querySelector('.cart-list-body');
                        if (cartListBody) {
                            cartListBody.innerHTML = '<div class="empty-cart-message">Keranjang belanja Anda kosong</div>';
                        }
                    }
                    
                    // If on cart.php page, reload to update the cart list
                    if (window.location.pathname.includes('cart.php')) {
                        window.location.reload();
                    }
                } else {
                    showNotification(response.message || 'Gagal menghapus produk dari keranjang', 'error');
                }
            } catch (e) {
                console.error('Error parsing JSON response:', e);
                showNotification('Terjadi kesalahan saat memproses permintaan', 'error');
            }
        } else {
            showNotification('Terjadi kesalahan pada server', 'error');
        }
    };
    xhr.onerror = function() {
        showNotification('Tidak dapat terhubung ke server', 'error');
    };
    xhr.send(`product_id=${productId}`);
}

// Function to update product quantity in cart
function updateCartQuantity(productId, quantity) {
    if (quantity < 1) {
        showNotification('Jumlah minimal adalah 1', 'error');
        return;
    }
    
    // Update cart using AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    // If deleted (quantity was set to 0)
                    if (response.action === 'delete') {
                        const cartItem = document.querySelector(`.cart-single-list[data-product-id="${productId}"]`);
                        if (cartItem) {
                            cartItem.remove();
                        }
                        
                        showNotification('Produk dihapus dari keranjang', 'success');
                        
                        // If cart is empty, show empty message
                        if (response.cart_count === 0) {
                            const cartListBody = document.querySelector('.cart-list-body');
                            if (cartListBody) {
                                cartListBody.innerHTML = '<div class="empty-cart-message">Keranjang belanja Anda kosong</div>';
                            }
                        }
                    } else {
                        // Update subtotal in cart item
                        const subtotalElement = document.querySelector(`.cart-single-list[data-product-id="${productId}"] .total-price`);
                        if (subtotalElement) {
                            subtotalElement.textContent = response.subtotal_formatted;
                        }
                        
                        showNotification('Keranjang berhasil diperbarui', 'success');
                    }
                    
                    // Update cart counts and total
                    updateCartCountsInUI(response.cart_count);
                    updateCartTotalInUI(response.cart_total_formatted);
                } else {
                    showNotification(response.message || 'Gagal memperbarui keranjang', 'error');
                    
                    // Reset the input value to original
                    const quantityInput = document.querySelector(`.cart-single-list[data-product-id="${productId}"] .quantity-input`);
                    if (quantityInput) {
                        quantityInput.value = quantityInput.defaultValue;
                    }
                }
            } catch (e) {
                console.error('Error parsing JSON response:', e);
                showNotification('Terjadi kesalahan saat memproses permintaan', 'error');
            }
        } else {
            showNotification('Terjadi kesalahan pada server', 'error');
        }
    };
    xhr.onerror = function() {
        showNotification('Tidak dapat terhubung ke server', 'error');
    };
    xhr.send(`product_id=${productId}&quantity=${quantity}`);
}

// Helper function to update cart counts in UI
function updateCartCountsInUI(count) {
    // Update cart count in header
    const cartCountElements = document.querySelectorAll('.cart-items .total-items');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
    
    // Update dropdown cart header count
    const dropdownCartHeader = document.querySelector('.dropdown-cart-header span');
    if (dropdownCartHeader) {
        dropdownCartHeader.textContent = count + ' Items';
    }
}

// Helper function to update cart total in UI
function updateCartTotalInUI(formattedTotal) {
    // Update total in dropdown
    const totalAmountElements = document.querySelectorAll('.total-amount');
    totalAmountElements.forEach(element => {
        element.textContent = formattedTotal;
    });
    
    // Update cart summary total
    const cartSummaryTotal = document.querySelector('.cart-summary .total-amount');
    if (cartSummaryTotal) {
        cartSummaryTotal.textContent = formattedTotal;
    }
}

// Function to increment quantity input
function incrementQuantity(button, maxStock) {
    const input = button.parentNode.querySelector('.quantity-input');
    let value = parseInt(input.value);
    if (value < maxStock) {
        input.value = value + 1;
        
        // If auto-update is enabled, trigger update
        if (input.hasAttribute('data-auto-update')) {
            const productId = input.closest('.cart-single-list').getAttribute('data-product-id');
            updateCartQuantity(productId, input.value);
        }
    } else {
        showNotification('Stok maksimum tercapai', 'warning');
    }
}

// Function to decrement quantity input
function decrementQuantity(button) {
    const input = button.parentNode.querySelector('.quantity-input');
    let value = parseInt(input.value);
    if (value > 1) {
        input.value = value - 1;
        
        // If auto-update is enabled, trigger update
        if (input.hasAttribute('data-auto-update')) {
            const productId = input.closest('.cart-single-list').getAttribute('data-product-id');
            updateCartQuantity(productId, input.value);
        }
    }
}

// Function to show notification
function showNotification(message, type = 'info') {
    // Check if notification container exists, if not create it
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        
        // Apply styles to notification container
        Object.assign(notificationContainer.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: '9999',
            width: '300px'
        });
        
        document.body.appendChild(notificationContainer);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Apply styles to notification
    Object.assign(notification.style, {
        padding: '12px 20px',
        marginBottom: '10px',
        borderRadius: '5px',
        boxShadow: '0 4px 8px rgba(0,0,0,0.1)',
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        animation: 'fadeIn 0.3s, fadeOut 0.3s 2.7s',
        transition: 'opacity 0.3s, transform 0.3s',
        opacity: '0',
        transform: 'translateY(-20px)',
        fontSize: '14px',
        fontWeight: '500'
    });
    
    // Set background color based on type
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#4CAF50';
            notification.style.color = 'white';
            break;
        case 'error':
            notification.style.backgroundColor = '#F44336';
            notification.style.color = 'white';
            break;
        case 'warning':
            notification.style.backgroundColor = '#FF9800';
            notification.style.color = 'white';
            break;
        default:
            notification.style.backgroundColor = '#2196F3';
            notification.style.color = 'white';
    }
    
    // Create message element
    const messageElement = document.createElement('span');
    messageElement.textContent = message;
    
    // Create close button
    const closeButton = document.createElement('span');
    closeButton.innerHTML = '&times;';
    closeButton.style.marginLeft = '10px';
    closeButton.style.cursor = 'pointer';
    closeButton.style.fontWeight = 'bold';
    closeButton.style.fontSize = '20px';
    
    // Add event listener to close button
    closeButton.addEventListener('click', function() {
        notificationContainer.removeChild(notification);
    });
    
    // Add elements to notification
    notification.appendChild(messageElement);
    notification.appendChild(closeButton);
    
    // Add notification to container
    notificationContainer.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            if (notification.parentNode === notificationContainer) {
                notificationContainer.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Add CSS for notifications
(function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-20px); }
        }
    `;
    document.head.appendChild(style);
})(); 