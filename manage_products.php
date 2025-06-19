<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_product"])) {
    $category_id = $_POST["category_id"];
    $product_name = $_POST["product_name"];
    $product_description = $_POST["product_description"];
    $price = $_POST["price"];
    $stock_quantity = $_POST["stock_quantity"];
    $product_image = $_POST["product_image"];
    $sql = "INSERT INTO products (category_id, product_name, product_description, price, stock_quantity, product_image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issdis", $category_id, $product_name, $product_description, $price, $stock_quantity, $product_image);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

if (isset($_GET["delete"])) {
    $product_id = $_GET["delete"];
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
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
    <title>Manage Products - Sister's Shop</title>
    
    <style>
        .sidebar { width: 250px; background: cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header { padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: black; }
        .form-container, .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin-bottom: 20px; }
        form { display: grid; gap: 10px; }
        input, select, textarea { padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px; width: 100%; }
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
            <h1>Manage Products</h1>
        </div>
        <div class="form-container">
            <h2>Add New Product</h2>
            <form action="manage_products.php" method="post">
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $result = mysqli_query($conn, "SELECT * FROM categories");
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                    }
                    ?>
                </select>
                <input type="text" name="product_name" placeholder="Product Name" required>
                <textarea name="product_description" placeholder="Product Description" required></textarea>
                <input type="number" name="price" placeholder="Price" step="0.01" required>
                <input type="number" name="stock_quantity" placeholder="Stock Quantity" required>
                <input type="text" name="product_image" placeholder="Image Path (e.g., assets/image.jpg)" required>
                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>
        <div class="table-container">
            <h2>Product List</h2>
            <table>
                <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
                <?php
                $result = mysqli_query($conn, "SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$row['product_id']}</td>
                        <td>{$row['product_name']}</td>
                        <td>{$row['category_name']}</td>
                        <td>Rs " . number_format($row['price'], 2) . "</td>
                        <td>{$row['stock_quantity']}</td>
                        <td class='action-links'>
                            <a href='edit_product.php?id={$row['product_id']}'>Edit</a>
                            <a href='manage_products.php?delete={$row['product_id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                        </td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>