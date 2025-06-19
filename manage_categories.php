<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_category"])) {
    $category_name = $_POST["category_name"];
    $category_description = $_POST["category_description"];
    $category_image = $_POST["category_image"];
    $sql = "INSERT INTO categories (category_name, category_description, category_image) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $category_name, $category_description, $category_image);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

if (isset($_GET["delete"])) {
    $category_id = $_GET["delete"];
    $sql = "DELETE FROM categories WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
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
    <title>Manage Categories - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background: cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header { padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color:black; }
        .form-container, .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin-bottom: 20px; }
        form { display: grid; gap: 10px; }
        input, textarea { padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px; width: 100%; }
        button { position: relative; height: fit-content; width: fit-content; background: #4CAF50; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #45a049; }
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
            <h1>Manage Categories</h1>
        </div>
        <div class="form-container">
            <h2>Add New Category</h2>
            <form action="manage_categories.php" method="post">
                <input type="text" name="category_name" placeholder="Category Name" required>
                <textarea name="category_description" placeholder="Category Description"></textarea>
                <input type="text" name="category_image" placeholder="Image Path (e.g., assets/image.jpg)">
                <button type="submit" name="add_category">Add Category</button>
            </form>
        </div>
        <div class="table-container">
            <h2>Category List</h2>
            <table>
                <tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM categories");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$row['category_id']}</td>
                        <td>{$row['category_name']}</td>
                        <td>" . (empty($row['category_description']) ? "-" : htmlspecialchars($row['category_description'])) . "</td>
                        <td class='action-links'>
                            <a href='edit_category.php?id={$row['category_id']}'>Edit</a>
                            <a href='manage_categories.php?delete={$row['category_id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                        </td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>