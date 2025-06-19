<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

$category_id = $_GET["id"];
$sql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST["category_name"];
    $category_description = $_POST["category_description"];
    $category_image = $_POST["category_image"];
    $sql = "UPDATE categories SET category_name = ?, category_description = ?, category_image = ? WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $category_name, $category_description, $category_image, $category_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: manage_categories.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Edit Category - Sister's Shop</title>
    <style>
        .sidebar { width: 250px; background:cadetblue ; color: white; padding: 20px; position: fixed; height: 100%; }
        .sidebar h2 { margin: 0 0 20px; font-size: 1.5em; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin-bottom: 5px; border-radius: 5px; transition: background 0.3s ease; }
        .sidebar a:hover { background: #34495e; }
        .content { margin-left: 270px; padding: 20px; flex-grow: 1; }
        .header {  padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; color: black; }
        .form-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        form { display: grid; gap: 10px; }
        input, textarea { padding: 10px; border: 2px solid #e1e1e1; border-radius: 5px; width: 100%; }
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
            <h1>Edit Category</h1>
        </div>
        <div class="form-container">
            <h2>Edit Category: <?php echo htmlspecialchars($category['category_name']); ?></h2>
            <form action="edit_category.php?id=<?php echo $category_id; ?>" method="post">
                <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                <textarea name="category_description"><?php echo htmlspecialchars($category['category_description']); ?></textarea>
                <input type="text" name="category_image" value="<?php echo htmlspecialchars($category['category_image']); ?>">
                <button type="submit">Update Category</button>
            </form>
        </div>
    </div>
</body>
</html>