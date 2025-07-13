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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $order_id = $_POST["order_id"];
    $status = $_POST["status"];
    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($status == "Shipped") {
        $sql = "SELECT o.*, u.email, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $mail = new PHPMailer(true);
        try {
            $email = $order['email'] ?? 'adam@gmail.com'; // Fallback if email is missing
            error_log("Preparing email for order ID: $order_id, user: $email");
            $mail->isSMTP();
            $mail->Host = 'localhost';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPSecure = false;
            $mail->setFrom('no-reply@fabrique.com', 'Fabrique Store');
            $mail->addAddress($email, $order['full_name'] ?? 'Adam');
            $mail->isHTML(true);
            $mail->Subject = 'Your Order #' . $order_id . ' Has Been Shipped!';
            $mail->Body = '
                <h2>Order Shipped!</h2>
                <p>Dear ' . htmlspecialchars($order['full_name'] ?? 'Adam') . ',</p>
                <p>We are excited to inform you that your Order #' . $order_id . ' has been shipped.</p>
                <p><strong>Order Details:</strong></p>
                <ul>
                    <li>Order ID: ' . $order_id . '</li>
                    <li>Order Date: ' . $order['order_date'] . '</li>
                    <li>Total Amount: Rs ' . number_format($order['total_amount'], 2) . '</li>
                    <li>Shipping Address: ' . htmlspecialchars($order['shipping_address']) . '</li>
                </ul>
                <p>You will receive your order soon. Thank you for shopping with Fabrique!</p>
                <p>Best regards,<br>Fabrique Team</p>';
            $mail->AltBody = "Order Shipped!\nDear " . ($order['full_name'] ?? 'Adam') . ",\nYour Order #{$order_id} has been shipped.\nOrder Details:\n- Order ID: {$order_id}\n- Order Date: {$order['order_date']}\n- Total Amount: Rs " . number_format($order['total_amount'], 2) . "\n- Shipping Address: {$order['shipping_address']}\nYou will receive your order soon.\nThank you for shopping with Fabrique!\nBest regards,\nFabrique Team";
            $mail->send();
            error_log("Email sent to: $email");
        } catch (Exception $e) {
            error_log("Email failed for order ID $order_id: " . $mail->ErrorInfo);
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
    <title>Manage Orders - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background: cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header { padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: black; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
        td select { padding: 5px; border-radius: 5px; width: 80%; box-sizing: border-box; }
        .action-button { font-size: 10px; position: relative; height: fit-content; width: fit-content; margin-top:5px; margin-right: 30px; background: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .action-button:hover { background: #45a049; }
        .no-payment { color: #999; }
        @media (max-width: 768px) { .sidebar { width: 100%; height: auto; position: static; } .content { margin-left: 0; } }
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
            <h1>Manage Orders</h1>
        </div>
        <div class="table-container">
            <h2>Order List</h2>
            <table>
                <tr><th>ID</th><th>User</th><th>Date</th><th>Total</th><th>Status</th><th>Payment Status</th><th>Actions</th></tr>
                <?php
                $result = mysqli_query($conn, "SELECT o.*, u.full_name, p.payment_status FROM orders o JOIN users u ON o.user_id = u.user_id LEFT JOIN payments p ON o.order_id = p.order_id");
                while ($row = mysqli_fetch_assoc($result)) {
                    $payment_status = $row['payment_status'] ?? 'No Payment';
                    echo "<tr>
                        <td>{$row['order_id']}</td>
                        <td>" . htmlspecialchars($row['full_name']) . "</td>
                        <td>{$row['order_date']}</td>
                        <td>Rs " . number_format($row['total_amount'], 2) . "</td>
                        <td>
                            <form action='manage_orders.php' method='post'>
                                <input type='hidden' name='order_id' value='{$row['order_id']}'>
                                <select name='status'>
                                    <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                                    <option value='Processing' " . ($row['status'] == 'Processing' ? 'selected' : '') . ">Processing</option>
                                    <option value='Shipped' " . ($row['status'] == 'Shipped' ? 'selected' : '') . ">Shipped</option>
                                    <option value='Delivered' " . ($row['status'] == 'Delivered' ? 'selected' : '') . ">Delivered</option>
                                </select>
                                <button type='submit' name='update_status' class='action-button'>Update</button>
                            </form>
                        </td>
                        <td>$payment_status</td>
                        <td>";
                    if ($payment_status != 'No Payment' && $payment_status != 'approved') {
                        echo "<a href='verify_payment.php?order_id={$row['order_id']}' class='action-button'>Verify Payment</a>";
                    } else {
                        echo "<span class='no-payment'>No Action</span>";
                    }
                    echo "</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>