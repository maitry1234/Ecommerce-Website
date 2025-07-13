<?php
session_start();
if (!isset($_SESSION["user"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}
require_once "database.php";

// Handle delete operation
if (isset($_GET["delete"])) {
    $user_id = $_GET["delete"];
    $sql = "DELETE FROM users WHERE user_id = ? AND is_admin = 0";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: manage_users.php");
    exit();
}

// Handle update operation
if (isset($_POST["update_user"])) {
    $user_id = $_POST["user_id"];
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];
    $is_admin = isset($_POST["is_admin"]) ? 1 : 0;
    
    $sql = "UPDATE users SET full_name = ?, email = ?, is_admin = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $full_name, $email, $is_admin, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: manage_users.php");
    exit();
}

// Get user data for editing
$edit_user = null;
if (isset($_GET["edit"])) {
    $user_id = $_GET["edit"];
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_user = mysqli_fetch_assoc($result);
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
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: rgb(0, 0, 0);
        }
        
        .edit-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .edit-form h2 {
            margin: 0 0 20px;
            color: #4CAF50;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background 0.3s ease;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .table-container h2 {
            margin: 0 0 20px;
            color: #4CAF50;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
        }
        
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        th {
            background: #4CAF50;
            color: white;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .action-links a {
            color: #4CAF50;
            margin-right: 10px;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background 0.3s ease;
        }
        
        .action-links a:hover {
            text-decoration: underline;
        }
        
        .edit-btn {
            background:rgb(7, 170, 64);
            color: white !important;
        }
        
        .edit-btn:hover {
            background:rgb(11, 153, 89);
            text-decoration: none !important;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white !important;
        }
        
        .delete-btn:hover {
            background: #c82333;
            text-decoration: none !important;
        }
        
        /* Mobile Menu Button - Hidden by default */
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: cadetblue;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                transform: translateY(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateY(0);
            }
            
            .content {
                margin-left: 0;
                padding-top: 70px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 8px;
            }
            
            .action-links a {
                display: block;
                margin-bottom: 5px;
            }
        }
        
        @media (max-width: 480px) {
            .content {
                padding: 70px 10px 10px;
            }
            
            .table-container,
            .edit-form {
                padding: 15px;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
    
    <div class="sidebar" id="sidebar">
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

        <?php if ($edit_user): ?>
        <div class="edit-form">
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_admin" name="is_admin" <?php echo $edit_user['is_admin'] ? 'checked' : ''; ?>>
                        <label for="is_admin">Admin User</label>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                    <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <h2>User List</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id ASC");
                while ($row = mysqli_fetch_assoc($result)) {
                    $is_admin = $row['is_admin'] ? "Yes" : "No";
                    echo "<tr>
                        <td>{$row['user_id']}</td>
                        <td>" . htmlspecialchars($row['full_name']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>$is_admin</td>
                        <td class='action-links'>
                            <a href='manage_users.php?edit={$row['user_id']}' class='edit-btn'>Edit</a>";
                    
                    if (!$row['is_admin']) {
                        echo "<a href='manage_users.php?delete={$row['user_id']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                    }
                    
                    echo "</td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
            
            // Close sidebar when clicking outside on mobile
            if (sidebar.classList.contains('show')) {
                document.addEventListener('click', closeSidebarOnOutsideClick);
            } else {
                document.removeEventListener('click', closeSidebarOnOutsideClick);
            }
        }

        function closeSidebarOnOutsideClick(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                sidebar.classList.remove('show');
                document.removeEventListener('click', closeSidebarOnOutsideClick);
            }
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                document.removeEventListener('click', closeSidebarOnOutsideClick);
            }
        });
    </script>
</body>
</html>