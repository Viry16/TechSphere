# TechSphere
## Project Description
TechSphere is a smart e-commerce web application developed by Group 8—Excel Viryan, Kevin Syonin, and Gerald Darryl Joseph Manurung—as part of a Database and SSIP project. The platform is designed to assist beginners in the world of PC building by offering an intuitive shopping experience, product compatibility guidance, and educational resources.

With features like product browsing, shopping cart management, and order placement, TechSphere simplifies the process of assembling and purchasing custom-built computer components, making it accessible even for users with little to no technical background.

## Features

- **User Authentication**
  - Secure registration and login system
  - Profile management with password change

- **Product Browsing**
  - View products in a grid layout and detailed product pages
  - Add items to the shopping cart

- **Shopping Cart**
  - Add, update, and remove items from the cart
  - View cart summary and proceed to checkout

- **Order Management**
  - Place orders and view order history
  - Track order status

- **Profile Management**
  - Edit user profiles and manage addresses
  - Change password and update personal information

- **Admin Panel**
  - Administrative functionalities for managing the application

## Tech Stack

- **Backend**
  - PHP 7.4+
  - MySQL 5.7+
  - Apache/Nginx

- **Frontend**
  - HTML5
  - CSS
  - JavaScript

## Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Viry16/TechSphere.git
   cd techsphere
   ```

2. **Database Setup**
   - Create a MySQL database named 'techsphere'
   - Import the database structure from `database.php`
   - Update database credentials in `database.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'techsphere');
     ```

3. **Web Server Setup**
   - Configure your web server to point to the project directory
   - Ensure .htaccess is enabled
   - Set proper file permissions:
     ```bash
     chmod 755 -R techsphere
     ```

## Directory Structure

```
techsphere/
├── admin/             # Admin panel files
│   ├── users.php      # User management
│   ├── profile.php    # Admin profile management
│   ├── products.php   # Product management
│   ├── orders.php     # Order management
│   ├── edit_product.php # Edit product functionality
│   ├── dashboard.php  # Admin dashboard
│   ├── css/           # CSS files for admin panel
│   └── add_product.php # Add product functionality
├── assets/            # Static assets like images, CSS, and JavaScript
│   ├── js/            # JavaScript files
│   ├── images/        # Image files
│   ├── fonts/         # Font files
│   └── css/           # CSS files
├── includes/          # Common PHP files and utilities
│   ├── header.php     # Common header
│   ├── footer.php     # Common footer
│   ├── cart_functions.php # Cart functionality
│   └── db_connect.php # Database connection
├── index.php          # Main entry point
├── login.php          # User login page
├── register.php       # User registration page
├── cart.php           # Shopping cart management
├── checkout.php       # Order checkout process
├── profile.php        # User profile management
├── orders.php         # Order history and details
├── database.php       # Database connection and configuration
├── utils.php          # Utility functions
├── update_cart.php    # Update cart functionality
├── set_default_address.php # Set default address functionality
├── toggle-view.js     # Toggle view functionality
├── remove_from_cart.php # Remove items from cart
├── register_process.php # Process registration
├── product-details.php # Product details page
├── product-grids.php  # Product grid view
├── logout.php         # Logout functionality
├── order-success.php  # Order success page
├── login_process.php  # Process login
├── get_cart_items.php # Get cart items
├── image_helper.php   # Image handling utilities
├── get_cart_count.php # Get cart count
├── edit_profile.php   # Edit user profile
├── fix_image_paths.php # Fix image paths
├── debug_images.php   # Debug image functionality
├── delete_address.php # Delete address functionality
├── clear_view_preference.php # Clear view preference
├── create_admin.php   # Create admin functionality
├── check_payment_table.php # Check payment table
├── change_password.php # Change password functionality
├── add_is_default_column.php # Add default column
├── add_to_cart.php    # Add items to cart
└── add_address.php    # Add address functionality
```

## Security Features

- Password hashing using PHP's password_hash()
- CSRF protection with tokens
- SQL injection prevention with prepared statements
- XSS protection through input sanitization
- Secure session handling

## Usage

1. **Registration**
   - Visit `/register`
   - Enter username, email, password

2. **Login**
   - Use `/login` to access your account

3. **Shopping**
   - Browse products on `/product-grids`
   - Add items to your cart

4. **Checkout**
   - Complete your purchase on `/checkout`


## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Thanks to all contributors and supporters of the project.

## Support

For support, email excel.viryan@student.president.ac.id, kevin.syonin@student.president.ac.id, gerald.manurung@student.president.ac.id or open an issue in the repository.
>>>>>>> 6b25a34 (Documentation on README.md)
