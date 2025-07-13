<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";
require_once "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$order_id = $_GET["order_id"];
$sql = "SELECT p.*, u.full_name, u.email, o.order_date, o.total_amount FROM payments p JOIN users u ON p.user_id = u.user_id JOIN orders o ON p.order_id = o.order_id WHERE p.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$payment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$payment) {
    header("Location: manage_orders.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_payment"])) {
    $payment_status = $_POST["payment_status"];
    $sql = "UPDATE payments SET payment_status = ? WHERE payment_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $payment_status, $payment['payment_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($payment_status == "approved") {
        $sql = "UPDATE orders SET status = 'Processing' WHERE order_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Email notification
        $mail = new PHPMailer(true);
        try {
            $email = $payment['email'] ?? 'adam@gmail.com'; // Fallback if email is missing
            error_log("Preparing email for order ID: $order_id, user: $email");
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPSecure = false;
            $mail->setFrom('no-reply@fabrique.com', 'Fabrique Store');
            $mail->addAddress($email, $payment['full_name'] ?? 'Adam');
            $mail->isHTML(true);
            $mail->Subject = 'Payment Approved for Your Order #' . $order_id;
            $mail->Body = '
                <h2>Payment Approved!</h2>
                <p>Dear ' . htmlspecialchars($payment['full_name'] ?? 'Adam') . ',</p>
                <p>We are pleased to inform you that your payment for Order #' . $order_id . ' has been approved.</p>
                <p><strong>Order Details:</strong></p>
                <ul>
                    <li>Order ID: ' . $order_id . '</li>
                    <li>Order Date: ' . $payment['order_date'] . '</li>
                    <li>Total Amount: Rs ' . number_format($payment['total_amount'], 2) . '</li>
                    <li>Payment Amount: Rs ' . number_format($payment['amount'], 2) . '</li>
                    <li>Reference: ' . htmlspecialchars($payment['reference']) . '</li>
                </ul>
                <p>Your order is now being processed. You will receive another email when it is shipped.</p>
                <p>Thank you for shopping with Fabrique!</p>
                <p>Best regards,<br>Fabrique Team</p>';
            $mail->AltBody = "Payment Approved!\nDear " . ($payment['full_name'] ?? 'Adam') . ",\nYour payment for Order #{$order_id} has been approved.\nOrder Details:\n- Order ID: {$order_id}\n- Order Date: {$payment['order_date']}\n- Total Amount: Rs " . number_format($payment['total_amount'], 2) . "\n- Payment Amount: Rs " . number_format($payment['amount'], 2) . "\n- Reference: {$payment['reference']}\nYour order is being processed. You will receive another email when it is shipped.\nThank you for shopping with Fabrique!\nBest regards,\nFabrique Team";
            $mail->send();
            error_log("Email sent successfully to: $email");
        } catch (Exception $e) {
            error_log("Email failed for order ID $order_id: " . $mail->ErrorInfo);
        }
    }

    header("Location: manage_orders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Verify Payment - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background: cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header { padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: black; }
        .payment-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .payment-container h2 { color: #4CAF50; margin-bottom: 20px; }
        .payment-details p { margin: 5px 0; }
        .payment-details img { max-width: 400px; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0; }
        form { margin-top: 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;width: 100px; }
        select { padding: 10px; border-radius: 5px; flex: 1; width: 100px; }
        .action-button { height: fit-content; width: fit-content; background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px 0; }
        .action-button:hover { background: #45a049; }
        @media (max-width: 768px) { .sidebar { width: 100%; height: auto; position: static; } .content { margin-left: 0; padding: 10px; } form { flex-direction: column; align-items: stretch; } select { width: 100%; margin-bottom: 10px; } .action-button { width: 100%; box-sizing: border-box; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_products.php">Manage Products</a>
        <a href="manage_categories.php">Manage Categories</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content">
        <div class="header">
            <h1>Verify Payment</h1>
        </div>
        <div class="payment-container">
            <h2>Payment for Order #<?php echo $order_id; ?></h2>
            <div class="payment-details">
                <p><strong>Payment ID:</strong> <?php echo $payment['payment_id']; ?></p>
                <p><strong>User:</strong> <?php echo htmlspecialchars($payment['full_name']); ?> (<?php echo htmlspecialchars($payment['email']); ?>)</p>
                <p><strong>Amount:</strong> Rs <?php echo number_format($payment['amount'], 2); ?></p>
                <p><strong>Reference:</strong> <?php echo htmlspecialchars($payment['reference']); ?></p>
                <p><strong>Status:</strong> <?php echo $payment['payment_status']; ?></p>
                <p><strong>Submitted At:</strong> <?php echo $payment['created_at']; ?></p>
                <p><strong>Screenshot:</strong></p>
                <img src="<?php echo htmlspecialchars($payment['screenshot_path']); ?>" alt="Payment Screenshot">
            </div>
            <form action="verify_payment.php?order_id=<?php echo $order_id; ?>" method="post">
                <select name="payment_status" required>
                    <option value="pending" <?php echo $payment['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $payment['payment_status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $payment['payment_status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <button type="submit" name="update_payment" class="action-button">Update Payment Status</button>
            </form>
        </div>
    </div>
</body>
</html>