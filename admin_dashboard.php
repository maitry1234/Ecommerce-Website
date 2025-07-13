<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sister's Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .sidebar {
            width: 250px;
            background: cadetblue;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
        }
        .sidebar h2 {
            margin: 0 0 20px;
            font-size: 1.5em;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .sidebar a:hover {
            background: #34495e;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            flex-grow: 1;
        }
        .header {
            border-radius: 10px;
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            color: #4CAF50;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px;
            color: #4CAF50;
        }
        .stat-card p {
            font-size: 1.5em;
            margin: 0;
            color: #333;
        }
        .messages-section {
            margin-top: 40px;
        }
        .messages-section h2 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        .messages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .message-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-top: 4px solid #4CAF50;
        }
        .message-card h3 {
            margin: 0 0 10px;
            color: #4CAF50;
            font-size: 1.3em;
        }
        .message-card p {
            color: #333;
            margin: 5px 0;
            line-height: 1.6;
        }
        .message-card .message-text {
            color: #666;
            font-style: italic;
        }
        .message-card .submitted-at {
            color: #999;
            font-size: 0.9em;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }
            .content {
                margin-left: 0;
            }
        }
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
            <h1>Admin Dashboard</h1>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?></span>
        </div>
        <div class="stats-grid">
            <?php
            $stats = [
                ["title" => "Total Products", "query" => "SELECT COUNT(*) FROM products"],
                ["title" => "Total Categories", "query" => "SELECT COUNT(*) FROM categories"],
                ["title" => "Total Orders", "query" => "SELECT COUNT(*) FROM orders"],
                ["title" => "Total Users", "query" => "SELECT COUNT(*) FROM users"]
            ];
            foreach ($stats as $stat) {
                $result = mysqli_query($conn, $stat["query"]);
                $count = mysqli_fetch_array($result)[0];
                echo "<div class='stat-card'><h3>{$stat['title']}</h3><p>$count</p></div>";
            }
            ?>
        </div>
        <div class="messages-section">
            <h2>Messages & Reviews</h2>
            <div class="messages-grid">
                <?php
                $result = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY submitted_at DESC");
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='message-card'>";
                        echo "<h3>" . htmlspecialchars($row['subject']) . "</h3>";
                        echo "<p><strong>From:</strong> " . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['email']) . ")</p>";
                        if (!empty($row['phone'])) {
                            echo "<p><strong>Phone:</strong> " . htmlspecialchars($row['phone']) . "</p>";
                        }
                        echo "<p class='message-text'>" . htmlspecialchars($row['message']) . "</p>";
                        echo "<p class='submitted-at'>Submitted: " . $row['submitted_at'] . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No messages or reviews yet.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>