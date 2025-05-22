<!-- Start Footer Area -->
<footer class="footer">
            <div class="container">
                    <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="single-footer">
                            <div class="footer-logo">
                    <img src="assets/images/logo/logo.png" alt="Logo">
                    </div>
                    <p>Your one-stop shop for all computer hardware needs. We provide high-quality components and accessories for your PC building journey.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="single-footer">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                                </ul>
                            </div>
                        </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="single-footer">
                    <h3 class="footer-title">Contact Info</h3>
                    <ul class="contact-info">
                        <li><i class="lni lni-map-marker"></i> Cikarang, Bekasi, Indonesia</li>
                        <li><i class="lni lni-phone"></i> +62 812-9155-3241</li>
                        <li><i class="lni lni-envelope"></i> support@techsphere.com</li>
                                </ul>
                </div>
            </div>
                            </div>
                        </div>
                            <div class="copyright">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="copyright-content">
                        <p>© <?php echo date('Y'); ?> TechSphere. All Rights Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </footer>
<!-- End Footer Area -->

<style>
    .h3{
        color: #0167F3 !important;
    }
.footer {
    background: linear-gradient(135deg,rgb(255, 255, 255) 0%,rgb(255, 255, 255) 100%);
    padding: 80px 0 0;
    margin-top: 50px;
    color: #333;
}

.single-footer {
    margin-bottom: 30px;
}

.footer-logo {
    margin-bottom: 20px;
}

.footer-logo img {
    max-width: 150px;
    height: auto;
}

.footer-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 25px;
    color: #0167F3;
}

.single-footer p {
    color: #666;
    line-height: 1.8;
    margin-bottom: 20px;
}

.social {
    display: flex;
    gap: 15px;
}

.social a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    background: #f8f9fa;
    border-radius: 50%;
    color: #333;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.social a:hover {
    background: #0167F3;
    color: #fff;
    transform: translateY(-3px);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #666;
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: #0167F3;
    padding-left: 5px;
}

.contact-info {
    list-style: none;
    padding: 0;
    margin: 0;
}

.contact-info li {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    color: #666;
}

.contact-info i {
    margin-right: 10px;
    color: #0167F3;
}

.copyright {
    background-color: #f8f9fa;
    padding: 20px 0;
    margin-top: 50px;
    border-top: 1px solid #eee;
}

.copyright-content {
    text-align: center;
    color: #666;
}

@media (max-width: 768px) {
    .footer {
        padding: 50px 0 0;
    }
    
    .single-footer {
        margin-bottom: 40px;
    }
    
    .footer-logo img {
        max-width: 120px;
    }
}
</style>

<!-- Cart Functions -->
<script src="assets/js/cart.js"></script>