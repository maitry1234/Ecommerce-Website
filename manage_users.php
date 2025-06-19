<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

if (isset($_GET["delete"])) {
    $user_id = $_GET["delete"];
    $sql = "DELETE FROM users WHERE user_id = ? AND is_admin = 0";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
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
    <title>Manage Users - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background:cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header {  padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color:rgb(0, 0, 0); }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
        .action-links a { color: #4CAF50; margin-right: 10px; text-decoration: none; }
        .action-links a:hover { text-decoration: underline; }
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
            <h1>Manage Users</h1>
        </div>
        <div class="table-container">
            <h2>User List</h2>
            <table>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Admin</th><th>Actions</th></tr>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM users");
                while ($row = mysqli_fetch_assoc($result)) {
                    $is_admin = $row['is_admin'] ? "Yes" : "No";
                    $delete_link = $row['is_admin'] ? "-" : "<a href='manage_users.php?delete={$row['user_id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
                    echo "<tr>
                        <td>{$row['user_id']}</td>
                        <td>" . htmlspecialchars($row['full_name']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>$is_admin</td>
                        <td class='action-links'>$delete_link</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>