<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_GET['order_id']) || !isset($_GET['amount']) || !isset($_GET['reference'])) {
    header("Location: checkout.php");
    exit();
}

$order_id = $_GET['order_id'];
$amount = $_GET['amount'];
$reference = $_GET['reference'];

// eSewa QR Code details
$esewa_merchant_id = "9860308373"; // Replace with your actual eSewa Merchant ID
$esewa_number = "9860308373"; // Replace with your actual eSewa number

// Store payment details for verification
$_SESSION['esewa_payment'] = [
    'amount' => $amount,
    'order_id' => $order_id,
    'reference' => $reference
];

// Handle screenshot upload
$error = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['payment_screenshot'])) {
    $upload_dir = 'payment_screenshots/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['payment_screenshot']['name'], PATHINFO_EXTENSION);
    $new_filename = 'payment_' . $order_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Validate file
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
        $error = "Invalid file format. Please upload JPG, PNG, or GIF files.";
    } elseif ($_FILES['payment_screenshot']['size'] > $max_size) {
        $error = "File too large. Please upload files under 5MB.";
    } elseif ($_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload error. Please try again.";
    } else {
        if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $upload_path)) {
            // Insert payment record into database
            require_once "database.php";
            
            $sql = "INSERT INTO payments (order_id, user_id, amount, reference, screenshot_path, payment_status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iisss", $order_id, $_SESSION["user_id"], $amount, $reference, $upload_path);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['payment_success'] = "Payment screenshot uploaded successfully. Your payment is under verification.";
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: home.php");
                exit();
            } else {
                $error = "Database error: Unable to save payment details. Please try again.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Failed to upload screenshot. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>eSewa QR Payment - Fabrique</title>
    <style>
        .esewa-container {
            max-width: 600px;
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
        
        .qr-section {
            background-color: #f9f9f9;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 2px dashed #60BB46;
        }
        
        .qr-code {
            width: 200px;
            height: 200px;
            margin: 20px auto;
            background-color: white;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .esewa-details {
            background-color: #60BB46;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .upload-section {
            margin: 30px 0;
            padding: 30px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }
        
        .file-input {
            margin: 10px 0;
            padding: 10px;
            border: 2px dashed #ccc;
            border-radius: 5px;
            background-color: white;
        }
        
        .submit-button {
            background-color: #60BB46;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            justify-content: center;
            width: fit-content;
            height: fit-content;
            padding: 12px;
            display: block;
            margin: 20px auto 0 auto;
            position: relative;
            left: 0;
            margin-right: 20px;
        }
        
        .submit-button:hover {
            background-color:rgb(46, 141, 22);
            color: white;
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
        
        .error {
            color: red;
            margin: 10px 0;
            padding: 10px;
            background-color: #ffe6e6;
            border-radius: 4px;
        }
        
        .instructions {
            text-align: left;
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        
        .instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin: 8px 0;
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
        <a href="cart.php" class="cart-link">🛒 </a>
    </nav>
    
    <div class="esewa-container">
        <img src="images/esewa_logo.png" alt="eSewa" class="esewa-logo" onerror="this.src='https://esewa.com.np/common/images/esewa_logo.png'; this.onerror='';">
        
        <h2>eSewa QR Payment</h2>
        
        <div class="payment-details">
            <div class="detail-row">
                <span>Order ID:</span>
                <span>FAB<?php echo htmlspecialchars($order_id); ?></span>
            </div>
            <div class="detail-row">
                <span>Reference:</span>
                <span><?php echo htmlspecialchars($reference); ?></span>
            </div>
            <div class="detail-row total-amount">
                <span>Total Amount:</span>
                <span>Rs <?php echo number_format($amount, 2); ?></span>
            </div>
        </div>
        
        <div class="qr-section">
            <h3>Scan QR Code to Pay</h3>
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=esewa://pay?pa=<?php echo urlencode($esewa_number); ?>&pn=Fabrique&am=<?php echo urlencode($amount); ?>&cu=NPR&tn=Order%20FAB<?php echo urlencode($order_id); ?>" alt="eSewa QR Code">
            </div>
            <div class="esewa-details">
                <strong>eSewa ID:</strong> <?php echo htmlspecialchars($esewa_number); ?><br>
                <strong>Amount:</strong> Rs <?php echo number_format($amount, 2); ?><br>
                <strong>Reference:</strong> FAB<?php echo htmlspecialchars($order_id); ?>
            </div>
        </div>
        
        <div class="instructions">
            <h4>Payment Instructions:</h4>
            <ol>
                <li>Open your eSewa mobile app</li>
                <li>Scan the QR code above OR send money to eSewa ID: <strong><?php echo htmlspecialchars($esewa_number); ?></strong></li>
                <li>Enter amount: <strong>Rs <?php echo number_format($amount, 2); ?></strong></li>
                <li>Add reference: <strong>FAB<?php echo htmlspecialchars($order_id); ?></strong></li>
                <li>Complete the payment</li>
                <li>Take a screenshot of the successful payment</li>
                <li>Upload the screenshot below and submit</li>
            </ol>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="upload-section">
            <h3>Upload Payment Screenshot</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="file-input">
                    <label for="payment_screenshot"><strong>Select Screenshot:</strong></label><br>
                    <input type="file" id="payment_screenshot" name="payment_screenshot" accept="image/*" required>
                    <br>
                    <small>Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                  
            </form>
                <button type="submit" class="submit-button">Upload & Submit Payment</button>
                </div>
                
        </div>
        
        <a href="checkout.php" class="back-link">Back to Checkout</a>
    </div>
</body>
</html>