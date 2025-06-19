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
    <title>Manage Orders - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background: cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header { background: white; padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: #4CAF50; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
        select { padding: 5px; border-radius: 5px; }
        button { position: relative; height: fit-content; width: fit-content; background: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #45a049; }
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
                <tr><th>ID</th><th>User</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr>
                <?php
                $result = mysqli_query($conn, "SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$row['order_id']}</td>
                        <td>{$row['full_name']}</td>
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
                               
                            </form>
                        </td>
                        <td><a href='view_order.php?id={$row['order_id']}'>View Details</a></td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>