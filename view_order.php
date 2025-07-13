<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
    $order_id = $_POST["order_id"];
    $status = $_POST["status"];
    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
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
        .header { padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color:black; }
        .order-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
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
                                <button type='submit' name='update_status'>Update</button>
                            </form>
                        </td>
                        <td>$payment_status</td>
                        <td class='action-links'>";
                    if ($payment_status != 'No Payment') {
                        echo "<a href='verify_payment.php?order_id={$row['order_id']}'>Verify Payment</a>";
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