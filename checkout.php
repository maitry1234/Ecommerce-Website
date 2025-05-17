<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    $_SESSION['error'] = "Your cart is empty";
    header("Location: cart.php");
    exit();
}

// Calculate cart totals
$subtotal = 0;
$item_count = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}

// Set shipping cost and tax rate
$shipping_cost = 100;
$total = $subtotal + $shipping_cost;

// Process checkout form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    // Validate form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);

    $errors = [];
    
    // Basic validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($state)) $errors[] = "State is required";
    
    if (empty($errors)) {
        // Create a pending order first
        $conn->begin_transaction();
        
        try {
            // Create order record with 'Pending' status
            $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, total_amount, shipping_address, status) VALUES (?, NOW(), ?, ?, 'Pending')");
            
            // FIX: Get the user_id directly from session
            $user_id = $_SESSION['user'];
            
            $full_address = "$address, $city, $state ";
            $stmt->bind_param("ids", $user_id, $total, $full_address);
            $stmt->execute();
            
            $order_id = $conn->insert_id;
            
            // Insert order items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            
            foreach ($_SESSION['cart'] as $item) {
                // Check stock availability
                $check_stock = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
                $check_stock->bind_param("i", $item['product_id']);
                $check_stock->execute();
                $stock_result = $check_stock->get_result();
                $product = $stock_result->fetch_assoc();
                
                if ($product['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Not enough stock for " . $item['name']);
                }
                
                // Add order item
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
            }
            
            // Commit transaction for the pending order
            $conn->commit();
            
            // Store order_id in session for verification after eSewa payment
            $_SESSION['pending_order_id'] = $order_id;
            
            // Redirect to eSewa payment page
            $esewa_merchant_id = "YOUR_ESEWA_MERCHANT_ID"; // Replace with your actual eSewa Merchant ID
            $esewa_success_url = "https://" . $_SERVER['HTTP_HOST'] . "/esewa_success.php";
            $esewa_failure_url = "https://" . $_SERVER['HTTP_HOST'] . "/esewa_failure.php";
            
            // Generate a unique transaction reference
            $txn_reference = "FAB" . $order_id . time();
            $_SESSION['txn_reference'] = $txn_reference;
            
            // Store the amount in session for verification
            $_SESSION['order_amount'] = $total;
            
            // Redirect to eSewa payment form
            header("Location: esewa_form.php?order_id=$order_id&amount=$total&reference=$txn_reference");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['error'] = "Error processing your order: " . $e->getMessage();
        }
    } else {
        // Store errors in session
        $_SESSION['checkout_errors'] = $errors;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/homestyle.css">
    <title>Checkout - Fabrique</title>
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .checkout-header {
            margin-bottom: 30px;
        }
        
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .checkout-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .section-title {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .order-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        
        .cart-items {
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: bold;
        }
        
        .cart-item-price {
            color: #e91e63;
        }
        
        .cart-item-quantity {
            color: #777;
            font-size: 14px;
        }
        
        .place-order {
            text-align: center;
            background-color: cadetblue;
            color: white;
            border: none;
            width: auto;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 180px;
            position: static;
            margin-top: 20px;
            display: block;
        }

        .place-order:hover {
            background-color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        
        .error-list {
            margin: 10px 0 0 20px;
        }
        
        .payment-option {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .payment-option img {
            max-height: 40px;
            margin-top: 10px;
        }
        
        .esewa-btn {
            background-color: #60BB46;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
            display: block;
            width: auto;
            position: static;

        }
        
        .esewa-btn:hover {
            background-color: #4e9e3a;
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
        <a href="cart.php" class="cart-link">Cart (<?php echo $item_count; ?>)</a>
    </nav>
    
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Checkout</h1>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['checkout_errors']) && !empty($_SESSION['checkout_errors'])): ?>
            <div class="alert alert-error">
                <strong>Please correct the following errors:</strong>
                <ul class="error-list">
                    <?php foreach ($_SESSION['checkout_errors'] as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['checkout_errors']); ?>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="checkout-form">
                <form action="checkout.php" method="post" id="checkout-form">
                    <h2 class="section-title">Shipping Information</h2>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                    </div>
                    <h2 class="section-title">Payment Method</h2>
                    
                    <div class="payment-option">
                        <p>Pay securely with eSewa</p>
                        <img src="images/esewa_logo.png" alt="eSewa" onerror="this.src='https://esewa.com.np/common/images/esewa_logo.png'; this.onerror='';">
                    </div>               
                    <button type="submit" name="place_order" class="esewa-btn">Proceed to eSewa Payment</button>
                </form>
            </div>
            
            <div class="order-summary">
                <h2 class="section-title">Order Summary</h2>
                
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="cart-item">
                            <?php
                            // Fetch product image
                            $stmt = $conn->prepare("SELECT product_image FROM products WHERE product_id = ?");
                            $stmt->bind_param("i", $item['product_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $product = $result->fetch_assoc();
                            ?>
                            <img src="<?php echo $product['product_image']; ?>" alt="<?php echo $item['name']; ?>" class="cart-item-image">
                            <div class="cart-item-details">
                                <div class="cart-item-name"><?php echo $item['name']; ?></div>
                                <div class="cart-item-price">Rs <?php echo number_format($item['price'], 2); ?></div>
                                <div class="cart-item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
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
        </div>
    </div>
</body>
</html>