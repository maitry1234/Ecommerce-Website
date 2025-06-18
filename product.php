<?php
session_start();
require_once "database.php";

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch cart item count for logged-in users
$cart_count = 0;
if (isset($_SESSION['user']) && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Fetch product details
$stmt = $conn->prepare("SELECT p.*, c.category_name, c.category_id 
                       FROM products p 
                       JOIN categories c ON p.category_id = c.category_id 
                       WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// If product doesn't exist, redirect to home
if ($result->num_rows === 0) {
    header("Location: ss.php");
    exit();
}

$product = $result->fetch_assoc();

// Fetch related products (same category but not the current product)
$stmt = $conn->prepare("SELECT * FROM products 
                       WHERE category_id = ? AND product_id != ? 
                       LIMIT 3");
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$related_result = $stmt->get_result();

$related_products = [];
while ($row = $related_result->fetch_assoc()) {
    $related_products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/product.css">
    <title><?php echo $product['product_name']; ?> - Fabrique</title>
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
        <a href="ss.php#products">Products</a>
        <a href="ss.php#contact">Contact</a>
        <a href="ss.php#aboutus">About us</a>
    </nav>
    <div class="product-container">
        <div class="breadcrumb">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <a href="ss.php">Home</a> > 
            <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a> > 
            <?php echo $product['product_name']; ?>
        </div>
        <div class="product-image">
            <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
        </div>
        <div class="product-details">
            <div class="product-category"><?php echo $product['category_name']; ?></div>
            <h1 class="product-title"><?php echo $product['product_name']; ?></h1>
            <div class="product-price">Rs <?php echo number_format($product['price'], 2); ?></div>
            <p class="product-description"><?php echo $product['product_description']; ?></p>
            <?php if ($product['stock_quantity'] > 0): ?>
                <p class="product-stock">In Stock (<?php echo $product['stock_quantity']; ?> available)</p>
                <form action="add_to_cart.php" method="post" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <button type="submit" class="add-to-cart">Add to Cart</button>
                </form>
            <?php else: ?>
                <p class="product-stock" style="color: red;">Out of Stock</p>
            <?php endif; ?>
        </div>
        <?php if (!empty($related_products)): ?>
            <div class="related-products">
                <h2 class="related-title">You May Also Like</h2>
                <div class="related-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="related-product" onclick="window.location.href='product.php?id=<?php echo $related['product_id']; ?>'">
                            <img src="<?php echo $related['product_image']; ?>" alt="<?php echo $related['product_name']; ?>">
                            <div class="related-info">
                                <h3 class="related-name"><?php echo $related['product_name']; ?></h3>
                                <p class="related-price">Rs <?php echo number_format($related['price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script>
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const quantityInput = document.getElementById('quantity');
            const maxQuantity = parseInt(quantityInput.getAttribute('max'));
            const value = parseInt(quantityInput.value);
            if (isNaN(value) || value < 1) {
                e.preventDefault();
                alert('Please enter a valid quantity');
            } else if (value > maxQuantity) {
                e.preventDefault();
                alert('Sorry, we only have ' + maxQuantity + ' items in stock');
            }
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>