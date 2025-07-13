<?php
include_once 'database.php'; 
?>

<footer>
    <div class="footer-container">
        <div class="footer-section about">
            <div class="logo">
                <img src="assets/logo.png" alt="Fabrique Logo" width="200">
            </div>
            <p>
                Discover a diverse range of high-quality and stylish products at Fabrique. Explore comfortable bedding, elegant living essentials, exquisite table linen, women's fashion, kids' items, and winter essentials. Elevate your home and personal style with our curated collection. Shop now!
            </p>
            <p class="more-info"><strong>More Information About Fabrique</strong></p>
            <div class="social-icons">
                <a href="#"><img src="assets/facebook.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/maitry_bajracharya/"><img src="assets/instagram.png" alt="Instagram"></a>
            </div>
        </div>
        
        <div class="footer-section links">
            <h3>Quick Links</h3>
            <ul>
                <?php
                // Fetch categories for footer links
                $cat_sql = "SELECT category_id, category_name FROM categories LIMIT 3";
                $cat_result = $conn->query($cat_sql);
                
                if ($cat_result && $cat_result->num_rows > 0) {
                    while($cat = $cat_result->fetch_assoc()) {
                        echo '<li><a href="category.php?id=' . $cat['category_id'] . '">' . $cat['category_name'] . '</a></li>';
                    }
                }
                ?>
            </ul>
        </div>

        <div class="footer-section links">
            <h3>Useful Links</h3>
            <ul>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </div>

        <div class="footer-section contact">
            <h3>Store Information</h3>
            <p>📍 Gabahal, Lalitpur, Nepal</p>
            <p>📞 9803778895</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2023. Fabrique. All Rights Reserved</p>
    </div>
</footer>