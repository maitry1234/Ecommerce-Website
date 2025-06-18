<?php
session_start();
require_once "database.php";

// Fetch cart item count for logged-in users
$cart_count = 0;
if (isset($_SESSION['user']) && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Bedsheet Store</title>
</head>
<body>
    <header>
        <h1>Fabrique</h1>
        <p>Your one-stop shop for premium bedsheets, blankets and sleeping dress.</p>
    </header>
    <nav>
        <?php if (isset($_SESSION['user'])): ?>
            <button onclick="window.location.href='logout.php'">Log out</button>
            <a href="cart.php" class="cart-link">🛒 (<?php echo $cart_count; ?>)</a>
        <?php else: ?>
            <button onclick="window.location.href='login.php'">Login</button>
            <a href="login.php" class="cart-link">🛒 (0)</a>
        <?php endif; ?>
        <a href="#products">Products</a>
        <a href="#contact">Contact</a>
        <a href="#aboutus">About us</a>
    </nav>
    <div class="container">
        <section id="products">
            <h2>Our Products</h2>
            <div class="products">
                <div class="product">
                    <a href="category.php?id=1">
                        <img src="assets/bed1.jpg" alt="Bedsheet 1">
                        <h3>Bedsheets</h3>
                        <p>Variety of designs</p>
                    </a>
                </div>
                <div class="product">
                    <a href="category.php?id=2">
                        <img src="assets/Totebag.jpg" alt="Totebag">
                        <h3>Totebags</h3>
                        <p>Hand Painted</p>
                    </a>
                </div>
                <div class="product">
                    <a href="category.php?id=3">
                        <img src="assets/blanket1.jpg" alt="Blanket">
                        <h3>Blankets</h3>
                        <p>Nepali pure cotton</p>
                    </a>
                </div>
            </div>
        </section>   
    </div>
   
    <?php include 'footer.php'; ?>
</body>
</html>