<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

// Clear the cart
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['success'] = "Your cart has been cleared";
}

// Redirect back to cart page
header("Location: cart.php");
exit();
?>