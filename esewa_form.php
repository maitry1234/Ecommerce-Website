<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_GET['order_id']) || !isset($_GET['amount']) || !isset($_GET['reference'])) {
    header("Location: checkout.php");
    exit();
}

$order_id = $_GET['order_id'];
$amount = $_GET['amount'];
$reference = $_GET['reference'];

// eSewa API configuration
$esewa_merchant_id = "YOUR_ESEWA_MERCHANT_ID"; // Replace with your actual eSewa Merchant ID
$esewa_service_url = "https://uat.esewa.com.np/epay/main"; // Use production URL in production: https://esewa.com.np/epay/main

// Set URLs for success and failure
$success_url = "https://" . $_SERVER['HTTP_HOST'] . "/esewa_success.php";
$failure_url = "https://" . $_SERVER['HTTP_HOST'] . "/esewa_failure.php";

// Store product details for verification
$_SESSION['esewa_payment'] = [
    'amount' => $amount,
    'order_id' => $order_id,
    'reference' => $reference
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/homestyle.css">
    <title>eSewa Payment - Fabrique</title>
    <style>
        .esewa-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .esewa-logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        
        .payment-details {
            margin: 30px 0;
            text-align: left;
            padding: 0 20px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
        }
        
        .pay-button {
            background-color: #60BB46; /* eSewa green */
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .pay-button:hover {
            background-color: #4e9e3a;
        }
        
        .back-link {
            display: block;
            margin-top: 20px;
            color: #777;
            text-decoration: none;
        }
        
        .back-link:hover {
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
        <button onclick="window.location.href='logout.php'">Log out</button>
        <a href="home.php#products">Products</a>
        <a href="home.php#contact">Contact</a>
        <a href="home.php#aboutus">About us</a>
        <a href="cart.php" class="cart-link">Cart</a>
    </nav>
    
    <div class="esewa-container">
        <img src="images/esewa_logo.png" alt="eSewa" class="esewa-logo" onerror="this.src='https://esewa.com.np/common/images/esewa_logo.png'; this.onerror='';">
        
        <h2>eSewa Payment</h2>
        <p>Please review your payment details before proceeding to eSewa.</p>
        
        <div class="payment-details">
            <div class="detail-row">
                <span>Order ID:</span>
                <span>FAB<?php echo $order_id; ?></span>
            </div>
            <div class="detail-row">
                <span>Reference:</span>
                <span><?php echo $reference; ?></span>
            </div>
            <div class="detail-row total-amount">
                <span>Total Amount:</span>
                <span>Rs <?php echo number_format($amount, 2); ?></span>
            </div>
        </div>
        
        <!-- eSewa Payment Form -->
        <form action="<?php echo $esewa_service_url; ?>" method="POST">
            <input value="<?php echo $amount; ?>" name="tAmt" type="hidden">
            <input value="<?php echo $amount; ?>" name="amt" type="hidden">
            <input value="0" name="txAmt" type="hidden">
            <input value="0" name="psc" type="hidden">
            <input value="0" name="pdc" type="hidden">
            <input value="<?php echo $esewa_merchant_id; ?>" name="scd" type="hidden">
            <input value="<?php echo $reference; ?>" name="pid" type="hidden">
            <input value="<?php echo $success_url; ?>" type="hidden" name="su">
            <input value="<?php echo $failure_url; ?>" type="hidden" name="fu">
            <input value="Pay with eSewa" type="submit" class="pay-button">
        </form>
        
        <a href="checkout.php" class="back-link">Back to Checkout</a>
    </div>
</body>
</html>