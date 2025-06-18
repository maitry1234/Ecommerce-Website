<?php
session_start();
if (!isset($_SESSION["user"])) {
   header("Location: login.php");
   exit();
}

require_once "database.php";

$successMessage = "";
if (isset($_SESSION["login_success"])) {
    $successMessage = $_SESSION["login_success"];
    unset($_SESSION["login_success"]);
}


$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
$categories = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Fabrique - Bedsheet Store</title>
    <style>
        /* Toast notification styles */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast-message {
    background: #ffffff;
    color: #333;
    padding: 16px 20px;
    margin-bottom: 10px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #4CAF50;
    max-width: 350px;
    min-width: 280px;
    font-size: 14px;
    font-weight: 500;
    position: relative;
    transform: translateX(400px);
    opacity: 0;
    animation: slideInRight 0.4s ease-out forwards;
}

.toast-message::before {
    content: "✓";
    display: inline-block;
    width: 20px;
    height: 20px;
    background: #4CAF50;
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 20px;
    font-size: 12px;
    font-weight: bold;
    margin-right: 12px;
    vertical-align: middle;
}

.toast-message .close-btn {
    position: absolute;
    top: 8px;
    right: 12px;
    background: none;
    border: none;
    font-size: 18px;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toast-message .close-btn:hover {
    color: #666;
}

.toast-message.success {
    border-left-color: #4CAF50;
}

.toast-message.success::before {
    background: #4CAF50;
}

/* Animation keyframes */
@keyframes slideInRight {
    0% {
        transform: translateX(400px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

    @keyframes slideOutRight {
    0% {
        transform: translateX(0);
        opacity: 1;
    }
    100% {
        transform: translateX(400px);
        opacity: 0;
    }
    }

        .toast-message.hiding {
        animation: slideOutRight 0.3s ease-in forwards;
        }
        .container {
            text-align: center;
            padding: 50px;
        }
        
        .product {
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .product:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

<?php if (!empty($successMessage)) : ?>
    <div class="toast-container">
        <div class="toast-message success" id="toastMessage">
            <?php echo $successMessage; ?>
            <button class="close-btn" onclick="closeToast()">&times;</button>
        </div>
    </div>
    <script>
        function closeToast() {
            const toast = document.getElementById('toastMessage');
            if (toast) {
                toast.classList.add('hiding');
                setTimeout(() => {
                    toast.parentElement.remove();
                }, 300);
            }
        }

        // Auto-hide the toast after 5 seconds
        setTimeout(function() {
            closeToast();
        }, 5000);

        // Optional: Click anywhere on toast to close
        document.getElementById('toastMessage').addEventListener('click', function(e) {
            if (e.target.classList.contains('close-btn')) return;
            closeToast();
        });
    </script>
<?php endif; ?>

<header>
    <h1>Welcome to Fabrique<?php echo isset($_SESSION["full_name"]) ? ', ' . $_SESSION["full_name"] : ''; ?>!</h1>
    <p>Your one-stop shop for premium bedsheets, blankets and sleeping dress.</p>
</header>
    <nav>
    <button onclick="goToPage()">Log out</button>
        <script>
            function goToPage() {
                window.location.href = "logout.php";  
            }
        </script>
        <a href="#products">Products</a>
        <a href="#contact">Contact</a>
        <a href="#aboutus">About us</a>
    </nav>
    <div class="container">
        <section id="products">
            <h2>Product Categories</h2>
            <div class="products">
                <?php foreach ($categories as $category): ?>
                <div class="product" onclick="window.location.href='category.php?id=<?php echo $category['category_id']; ?>'">
                    <img src="<?php echo $category['category_image']; ?>" alt="<?php echo $category['category_name']; ?>">
                    <h3><?php echo $category['category_name']; ?></h3>
                    <p><?php echo $category['category_description']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
         </section>   
     </div>
     <?php include 'footer.php'; ?>     
</body>
</html>

