<?php
session_start();
require_once "database.php";

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch cart item count for logged-in users
$cart_count = 0;
if (isset($_SESSION['user']) && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Fetch category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category_result = $stmt->get_result();

// If category doesn't exist, redirect to home
if ($category_result->num_rows === 0) {
    header("Location: ss.php");
    exit();
}

$category = $category_result->fetch_assoc();
$category_name = $category['category_name'];

// Fetch products from this category
$stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$product_result = $stmt->get_result();

$products = [];
while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title><?php echo $category_name; ?> - Fabrique</title>
    <style>
        nav a {
        text-decoration: none;
        }
       
        .category-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .category-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            background-color: white;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-name {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .product-price {
            font-weight: bold;
            color: green;
            margin-bottom: 10px;
        }
        .product-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .view-button {
            display: block;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 8px 0;
            border-radius: 4px;
            text-decoration: none;
        }
        .back-to-home {
            display: inline-block;
            margin-bottom: 20px;
            color: #333;
            text-decoration: none;
        }
        .back-to-home:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Fabrique</h1>
        <p>Your one-stop shop for premium bedsheets, blankets and sleeping dress.</p>
    </header>
    <nav>
        <?php if (isset($_SESSION['user'])): ?>
            <button onclick="window.location.href='logout.php'">Log out</button>
            <a href="cart.php" class="cart-link">🛒  (<?php echo $cart_count; ?>)</a>
        <?php else: ?>
            <button onclick="window.location.href='login.php'">Login</button>
            <a href="login.php" class="cart-link">🛒  (0)</a>
        <?php endif; ?>
        <a href="ss.php#products">Products</a>
        <a href="ss.php#contact">Contact</a>
        <a href="ss.php#aboutus">About us</a>
    </nav>
    <div class="category-container">
        <a href="home.php" class="back-to-home">← Back to Home</a>
        <div class="category-header">
            <h1><?php echo $category_name; ?></h1>
            <p><?php echo $category['category_description']; ?></p>
        </div>
        <?php if (empty($products)): ?>
            <p>No products found in this category.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card" onclick="window.location.href='product.php?id=<?php echo $product['product_id']; ?>'">
                        <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                        <div class="product-info">
                            <h3 class="product-name"><?php echo $product['product_name']; ?></h3>
                            <p class="product-price">Rs.<?php echo number_format($product['price'], 2); ?></p>
                            <p class="product-description"><?php echo $product['product_description']; ?></p>
                            <a href="product.php?id=<?php echo $product['product_id']; ?>" class="view-button">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>