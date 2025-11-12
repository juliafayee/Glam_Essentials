<?php
// $baseUrl is already defined in header.php
?>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/assets/logo2.png" alt="GlamEssentials" class="logo-img">
                </div>
                <p class="footer-tagline">Your trusted destination for professional salon supplies.</p>
            </div>

            <!-- Collections Column -->
            <div class="footer-column">
                <h4 class="footer-title">Collections</h4>
                <ul class="footer-links">
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/products.php">All Products</a></li>
                    <?php
                    // Check if database connection exists
                    if (isset($conn) && $conn) {
                        $cat_query = "SELECT category_id, category_name FROM categories ORDER BY category_id";
                        $cat_result = $conn->query($cat_query);
                        if ($cat_result && $cat_result->num_rows > 0) {
                            while ($cat = $cat_result->fetch_assoc()) {
                                echo '<li><a href="' . htmlspecialchars($baseUrl) . '/products.php?category=' . $cat['category_id'] . '">' . htmlspecialchars($cat['category_name']) . '</a></li>';
                            }
                        }
                    }
                    ?>
                </ul>
            </div>

            <!-- Information Column -->
            <div class="footer-column">
                <h4 class="footer-title">Information</h4>
                <ul class="footer-links">
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/about.php">About</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/contact.php">Contact Us</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/shipping.php">Shipping & Delivery</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/faq.php">FAQ</a></li>
                </ul>
            </div>

            <!-- Account Column -->
            <div class="footer-column">
                <h4 class="footer-title">Account</h4>
                <ul class="footer-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/user/profile.php">My Profile</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/user/myorders.php">Order History</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/cart/view.php">Shopping Cart</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/user/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/user/login.php">Login/Register</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/cart/view.php">Shopping Cart</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/products.php">Browse Products</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> GlamEssentials. All Rights Reserved.</p>
            <div class="footer-legal">
                <a href="<?= htmlspecialchars($baseUrl) ?>/privacy.php">Privacy Policy</a>
                <span class="separator">|</span>
                <a href="<?= htmlspecialchars($baseUrl) ?>/terms.php">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>

<style>
/* --- Prestige-style main footer --- */
.footer {
    background: #000;
    color: #fff;
    padding: 60px 0 30px;
    margin-top: 80px;
    font-family: 'Helvetica Neue', Arial, sans-serif;
}
.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}
.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
}
.footer-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 3px;
    margin-bottom: 12px;
    color: #fff;
}
.footer-tagline {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.6;
    margin-bottom: 10px;
    font-style: italic;
}
.footer-social {
    display: flex;
    gap: 15px;
}
/* .footer-social a {
    width: 36px;
    height: 36px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.7);
    transition: all 0.3s ease;
}
.footer-social a:hover {
    background: #fff;
    color: #0a0a0a;
    border-color: #fff;
    transform: translateY(-2px);
} */
.footer-title {
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 auto 25px;
    color: #fff;
    text-align: center;
    display: block;
    width: 100%;
}
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.footer-links li {
    margin-bottom: 12px;
    text-align: center;
    width: 100%;
}
.footer-links a {
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
    width: 100%;
    text-align: center;
}
.footer-links a:hover {
    color: #fff;
    transform: translateY(-3px);
}
.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}
.footer-bottom p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}
.footer-legal {
    display: flex;
    gap: 15px;
    align-items: center;
}
.footer-legal a {
    color: rgba(255, 255, 255, 0.5);
    font-size: 12px;
    text-decoration: none;
    transition: color 0.3s ease;
}
.footer-legal a:hover {
    color: #fff;
}
.footer-legal .separator {
    color: rgba(255, 255, 255, 0.3);
    font-size: 12px;
}
@media (max-width: 768px) {
    .footer {
        padding: 40px 0 20px;
        margin-top: 60px;
    }
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }
    .footer-bottom {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
}
@media (max-width: 480px) {
    .footer-grid {
        grid-template-columns: 1fr;
    }
}
</style>