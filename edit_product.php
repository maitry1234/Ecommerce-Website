<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

$product_id = $_GET["id"];
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST["category_id"];
    $product_name = $_POST["product_name"];
    $product_description = $_POST["product_description"];
    $price = $_POST["price"];
    $stock_quantity = $_POST["stock_quantity"];
    $product_image = $_POST["product_image"];
    $sql = "UPDATE products SET category_id = ?, product_name = ?, product_description = ?, price = ?, stock_quantity = ?, product_image = ? WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issdisi", $category_id, $product_name, $product_description, $price, $stock_quantity, $product_image, $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: manage_products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Edit Product - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background: cadetblue; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header { padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color:black }
        .form-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        form { display: grid; gap: 10px; }
        input, select, textarea { padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px; width: 100%; }
        button { position: relative; height: fit-content; width: fit-content; background: #4CAF50; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; }
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
            <h1>Edit Product</h1>
        </div>
        <div class="form-container">
            <h2>Edit Product: <?php echo htmlspecialchars($product['product_name']); ?></h2>
            <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post">
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $result = mysqli_query($conn, "SELECT * FROM categories");
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = $row['category_id'] == $product['category_id'] ? "selected" : "";
                        echo "<option value='{$row['category_id']}' $selected>{$row['category_name']}</option>";
                    }
                    ?>
                </select>
                <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                <textarea name="product_description" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
                <input type="number" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" required>
                <input type="text" name="product_image" value="<?php echo htmlspecialchars($product['product_image']); ?>" required>
                <button type="submit">Update Product</button>
            </form>
        </div>
    </div>
</body>
</html>