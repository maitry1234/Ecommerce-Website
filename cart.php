<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to log errors
function logError($message) {
    error_log(date('[Y-m-d H:i:s]') . " - Cart Error: " . $message . "\n", 3, "logs/cart_errors.log");
}

// Handle quantity updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security verification failed. Please try again.";
        logError("CSRF token verification failed");
        header("Location: cart.php");
        exit();
    }
    
    // Process quantity updates
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        $updates = 0;
        $removals = 0;
        
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            $product_id = filter_var($product_id, FILTER_VALIDATE_INT);
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
            
            if ($product_id === false) {
                continue; // Skip invalid product IDs
            }
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                foreach ($_SESSION['cart'] as $key => $item) {
                    if ($item['product_id'] == $product_id) {
                        unset($_SESSION['cart'][$key]);
                        $removals++;
                        break;
                    }
                }
                // Reindex array after removal
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            } else {
                try {
                    // Check stock availability
                    $stmt = $conn->prepare("SELECT stock_quantity, product_name FROM products WHERE product_id = ?");
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $product = $result->fetch_assoc();
                        $original_quantity = 0;
                        
                        // Find current quantity in cart
                        foreach ($_SESSION['cart'] as $item) {
                            if ($item['product_id'] == $product_id) {
                                $original_quantity = $item['quantity'];
                                break;
                            }
                        }
                        
                        if ($quantity > $product['stock_quantity']) {
                            $_SESSION['error_products'][] = "Only {$product['stock_quantity']} units of \"{$product['product_name']}\" are available.";
                            $quantity = $product['stock_quantity'];
                        }
                        
                        // Update quantity
                        foreach ($_SESSION['cart'] as &$item) {
                            if ($item['product_id'] == $product_id) {
                                if ($item['quantity'] != $quantity) {
                                    $item['quantity'] = $quantity;
                                    $updates++;
                                }
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    logError("Database error when updating cart: " . $e->getMessage());
                    $_SESSION['error'] = "An error occurred while updating your cart. Please try again.";
                }
            }
        }
        
        // Set appropriate success message
        if ($updates > 0 && $removals > 0) {
            $_SESSION['success'] = "Cart updated: $updates item(s) updated and $removals item(s) removed.";
        } elseif ($updates > 0) {
            $_SESSION['success'] = "Cart updated successfully.";
        } elseif ($removals > 0) {
            $_SESSION['success'] = "$removals item(s) removed from cart.";
        }
        
        if (isset($_SESSION['error_products']) && count($_SESSION['error_products']) > 0) {
            $_SESSION['error'] = "Some items were adjusted due to stock limitations:";
        }
    }
    
    header("Location: cart.php");
    exit();
}

// Handle item removal
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    // Verify CSRF token if provided in the URL
    if (!isset($_GET['token']) || $_GET['token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security verification failed. Please try again.";
        logError("CSRF token verification failed for item removal");
        header("Location: cart.php");
        exit();
    }
    
    $remove_id = filter_var($_GET['remove'], FILTER_VALIDATE_INT);
    $removed = false;
    $product_name = "";
    
    if ($remove_id !== false) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['product_id'] == $remove_id) {
                $product_name = $item['name'];
                unset($_SESSION['cart'][$key]);
                $removed = true;
                break;
            }
        }
        
        if ($removed) {
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            $_SESSION['success'] = "\"$product_name\" removed from cart.";
        }
    }
    
    header("Location: cart.php");
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart totals
$subtotal = 0;
$item_count = 0;
$unique_items = 0;

if (count($_SESSION['cart']) > 0) {
    $unique_items = count($_SESSION['cart']);
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $item_count += $item['quantity'];
    }
}

$shipping_cost = 100;     // Flat shipping cost
$tax_amount = 0;          // No tax
$total = $subtotal + $shipping_cost + $tax_amount;


// Preload all product images and stock information in one query
$product_info = [];
if (count($_SESSION['cart']) > 0) {
    try {
        $product_ids = array_column($_SESSION['cart'], 'product_id');
        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $types = str_repeat('i', count($product_ids));
            
            $stmt = $conn->prepare("SELECT product_id, product_image, stock_quantity FROM products WHERE product_id IN ($placeholders)");
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $product_info[$row['product_id']] = [
                    'image' => $row['product_image'],
                    'stock' => $row['stock_quantity']
                ];
            }
        }
    } catch (Exception $e) {
        logError("Error fetching product information: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Shopping Cart - Fabrique</title>
    <style>
        .cart-container {
            border-radius: 2%;
            background-color: white;
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;

        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .continue-shopping {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .continue-shopping:hover {
            background-color: #555;
        }
        
        .cart-empty {
            text-align: center;
            padding: 50px 0;
            color: #777;
        }
        
        .cart-empty p {
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .cart-table th {
            text-align: left;
            padding: 12px 10px;
            background-color: #f5f5f5;
            border-bottom: 2px solid #ddd;
        }
        
        .cart-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }
        
        .cart-product {
            display: flex;
            align-items: center;
        }
        
        .cart-product img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        
        .cart-product-name {
            font-weight: bold;
        }
        
        .cart-quantity {
            
            align-items: center;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
        }
        
        .quantity-btn {
            width: 28px;
            height: 28px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            user-select: none;
        }
        
        .quantity-input {
            width: 50px;
            height: 28px;
            padding: 0 5px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 5px;
            font-size: 14px;
        }
        
        .stock-warning {
            color: #e67e22;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .cart-remove {
            color: #e91e63;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .cart-remove:hover {
            text-decoration: underline;
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
      .cart-update {
    background-color: #4CAF50;
    margin-bottom: 20px;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
    font-size: 16px;
    font-weight: 500;
    left: 0 !important;
    transform: translate(0) !important;
    position: relative;
    margin: 0 !important;
    height: fit-content;
    
      }
.cart-update:hover {
    background-color: #45a049;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
   }
        
        .cart-clear {
            height: fit-content;
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .cart-clear:hover {
            background-color: #d32f2f;
        }
        
        .cart-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .shipping-message {
            font-size: 14px;
            color: #4CAF50;
            text-align: right;
            margin-top: -8px;
            margin-bottom: 8px;
        }
        
        .summary-total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .checkout-button {
            display: block;
            width: 100%;
            background-color: #e91e63;
            color: white;
            border: none;
            padding: 15px;
            font-size: 16px;
            margin-top: 20px;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .checkout-button:hover {
            background-color: #d81b60;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        .alert-error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        
        .alert-error ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        .alert-error li {
            margin-bottom: 3px;
        }
        
        @media (max-width: 768px) {
            .cart-product {
                flex-direction: column;
                align-items: flex-start;
                text-align: center;
            }
            
            .cart-product img {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .cart-table th:nth-child(2), 
            .cart-table td:nth-child(2) {
                display: none;
            }
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
        <a href="cart.php" class="cart-link">🛒 (<?php echo $item_count; ?>)</a>
    </nav>
    
    <div class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <a href="home.php" class="continue-shopping">Continue Shopping</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; ?>
                <?php if (isset($_SESSION['error_products']) && count($_SESSION['error_products']) > 0): ?>
                    <ul>
                        <?php foreach ($_SESSION['error_products'] as $error_product): ?>
                            <li><?php echo $error_product; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php 
                    unset($_SESSION['error_products']);
                endif; 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($_SESSION['cart']) || count($_SESSION['cart']) === 0): ?>
            <div class="cart-empty">
                <p>Your cart is empty</p>
                <a href="home.php" class="checkout-button">Browse Products</a>
            </div>
        <?php else: ?>
            <form action="cart.php" method="post" id="cart-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <?php 
                                $current_stock = isset($product_info[$item['product_id']]['stock']) ? 
                                    $product_info[$item['product_id']]['stock'] : 99;
                                $low_stock = $current_stock <= 5;
                            ?>
                            <tr>
                                <td>
                                    <div class="cart-product">
                                        <img src="<?php echo isset($product_info[$item['product_id']]['image']) ? 
                                            $product_info[$item['product_id']]['image'] : 'images/placeholder.jpg'; ?>" 
                                            alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <span class="cart-product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td>Rs <?php echo number_format($item['price'], 2); ?></td>
                                <td class="cart-quantity">
                                    <div class="quantity-control" data-max="<?php echo $current_stock; ?>">
                                        <span class="quantity-btn minus">−</span>
                                        <input type="number" class="quantity-input" name="quantity[<?php echo $item['product_id']; ?>]" 
                                            value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $current_stock; ?>" 
                                            data-product-id="<?php echo $item['product_id']; ?>">
                                        <span class="quantity-btn plus">+</span>
                                    </div>
                                    <?php if ($low_stock): ?>
                                        <div class="stock-warning">Only <?php echo $current_stock; ?> left</div>
                                    <?php endif; ?>
                                </td>
                                <td>Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['product_id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" 
                                       class="cart-remove">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="cart-update">Update Cart</button>
                    <a href="clear_cart.php?token=<?php echo $_SESSION['csrf_token']; ?>" class="cart-clear" 
                       onclick="return confirm('Are you sure you want to clear your cart?');">Clear Cart</a>
                </div>
            </form>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal (<?php echo $item_count; ?> items):</span>
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
                
                <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript for better user experience -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Quantity increment/decrement functionality
            const quantityControls = document.querySelectorAll('.quantity-control');
            
            quantityControls.forEach(function(control) {
                const input = control.querySelector('.quantity-input');
                const minusBtn = control.querySelector('.minus');
                const plusBtn = control.querySelector('.plus');
                const maxStock = parseInt(control.dataset.max || 99);
                
                minusBtn.addEventListener('click', function() {
                    let currentValue = parseInt(input.value);
                    if (currentValue > 0) {
                        input.value = currentValue - 1;
                        triggerInputChange(input);
                    }
                });
                
                plusBtn.addEventListener('click', function() {
                    let currentValue = parseInt(input.value);
                    if (currentValue < maxStock) {
                        input.value = currentValue + 1;
                        triggerInputChange(input);
                    }
                });
                
                input.addEventListener('change', function() {
                    let currentValue = parseInt(this.value);
                    
                    // Enforce min/max constraints
                    if (isNaN(currentValue) || currentValue < 0) {
                        this.value = 0;
                    } else if (currentValue > maxStock) {
                        this.value = maxStock;
                    }
                    
                    // Auto-update total if needed
                    updateItemTotal(this);
                });
            });
            
            // Function to trigger input change event
            function triggerInputChange(input) {
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
            
            // Auto-submit form when "enter" is pressed in quantity input
            document.querySelectorAll('.quantity-input').forEach(function(input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('cart-form').submit();
                    }
                });
            });
            
            // Function to update item total (can be implemented if needed)
            function updateItemTotal(input) {
                // This would need product price data to calculate in real-time
                // Left as placeholder for potential future enhancement
            }
        });
    </script>
</body>
</html>