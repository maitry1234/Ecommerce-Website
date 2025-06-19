<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

$order_id = $_GET["id"];
$sql = "SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>View Order - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background:cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header { background: white; padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: #4CAF50; }
        .order-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .order-container h2 { color: #4CAF50; margin-bottom: 20px; }
        .order-details p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
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
            <h1>Order Details</h1>
        </div>
        <div class="order-container">
            <h2>Order #<?php echo $order['order_id']; ?></h2>
            <div class="order-details">
                <p><strong>User:</strong> <?php echo htmlspecialchars($order['full_name']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
                <p><strong>Date:</strong> <?php echo $order['order_date']; ?></p>
                <p><strong>Total Amount:</strong> Rs <?php echo number_format($order['total_amount'], 2); ?></p>
                <p><strong>Status:</strong> <?php echo $order['status']; ?></p>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
            </div>
            <h3>Order Items</h3>
            <table>
                <tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>
                <?php
                $sql = "SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $order_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($item = mysqli_fetch_assoc($result)) {
                    $total = $item['quantity'] * $item['price'];
                    echo "<tr>
                        <td>" . htmlspecialchars($item['product_name']) . "</td>
                        <td>{$item['quantity']}</td>
                        <td>Rs " . number_format($item['price'], 2) . "</td>
                        <td>Rs " . number_format($total, 2) . "</td>
                    </tr>";
                }
                mysqli_stmt_close($stmt);
                ?>
            </table>
        </div>
    </div>
</body>
</html>