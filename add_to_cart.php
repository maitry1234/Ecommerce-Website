<?php
session_start();
if (!isset($_SESSION["user"])) {
   header("Location: login.php");
   exit();
}

require_once "database.php";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    // Basic validation
    if ($product_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = "Invalid product or quantity";
        header("Location: product.php?id=" . $product_id);
        exit();
    }
    
    // Check if product exists and has enough stock
    $stmt = $conn->prepare("SELECT product_name, price, stock_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Product not found";
        header("Location: home.php");
        exit();
    }
    
    $product = $result->fetch_assoc();
    
    if ($quantity > $product['stock_quantity']) {
        $_SESSION['error'] = "Sorry, we only have " . $product['stock_quantity'] . " items in stock";
        header("Location: product.php?id=" . $product_id);
        exit();
    }
    
    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add to cart or update quantity if already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'name' => $product['product_name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }
    
    $_SESSION['success'] = "Added " . $quantity . " x " . $product['product_name'] . " to your cart";
    header("Location: product.php?id=" . $product_id);
    exit();
}

// If accessed directly without POST, redirect to home
header("Location: home.php");
exit();
?>