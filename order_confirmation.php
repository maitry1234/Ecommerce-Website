<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user']['user_id'];

// Fetch order details ensuring it belongs to the current user
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Order not found or doesn't belong to current user
    header("Location: home.php");
    exit();
}

$order = $result->fetch_assoc();

// Fetch order items
$stmt = $conn->prepare("SELECT oi.*, p.product_name, p.product_image 
                       FROM order_items oi
                       JOIN products p ON oi.product_id = p.product_id
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$order_items = [];
$subtotal = 0;

while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
    $subtotal += $item['price'] * $item['quantity'];
}

// Calculate totals
$shipping_cost = 5.99;
$tax_rate = 0.07; // 7% tax
$tax_amount = $subtotal * $tax_rate;
$total = $subtotal + $shipping_cost + $tax_amount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="homestyle.css">
    <title>Order Confirmation - Fabrique</title>
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .confirmation-header h1 {
            margin-bottom: 10px;
        }
        
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .order-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .order-info h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .order-items {
            margin-bottom: 30px;
        }
        
        .order-items h2 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .item-list {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .item {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .item-price, .item-quantity {
            color: #777;
            font-size: 14px;
            margin-bottom: 3px;
        }
        
        .item-total {
            font-weight: bold;
            color: #e91e63;
        }
        
        .order-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .continue-shopping {
            display: block;
            width: 200px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px 15px;
            margin: 30px auto 0;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .continue-shopping:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <h1>Fabrique</h1>
        <p>Your one-stop shop for premium bedsheets, blankets and sleeping dress.</p>
    </header>
    
    <nav>
        <button onclick="window.location.href='logout.php'">Log out</button>
        <a href="home.php#products">Products</a>
        <a href="home.php#contact">Contact</a>
        <a href="home.php#aboutus">About us</a>
        <a href="cart.php" class="cart-link">Cart</a>
    </nav>
    
    <div class="confirmation-container">
        <div class="confirmation-header">
            <h1>Order Confirmation</h1>
            <p>Thank you for your purchase!</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="order-info">
            <h2>Order Details</h2>
            <div class="info-row">
                <span class="info-label">Order Number:</span>
                <span>#<?php echo $order_id; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Order Date:</span>
                <span><?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Order Status:</span>
                <span><?php echo $order['status']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Shipping Address:</span>
                <span><?php echo $order['shipping_address']; ?></span>
            </div>
        </div>
        
        <div class="order-items">
            <h2>Items Ordered</h2>
            <div class="item-list">
                <?php foreach ($order_items as $item): ?>
                    <div class="item">
                        <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" class="item-image">
                        <div class="item-details">
                            <div class="item-name"><?php echo $item['product_name']; ?></div>
                            <div class="item-price">Price: Rs<?php echo number_format($item['price'], 2); ?></div>
                            <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                            <div class="item-total">Total: Rs<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>Rs <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping:</span>
                <span>Rs <?php echo number_format($shipping_cost, 2); ?></span>
            </div>
            <div class="summary-row summary-total">
                <span>Total:</span>
                <span>Rs <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
        
        <a href="home.php" class="continue-shopping">Continue Shopping</a>
    </div>
</body>
</html>