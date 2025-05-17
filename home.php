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

// Fetch categories from database
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
    <link rel="stylesheet" href="css/homestyle.css">
    <title>Fabrique - Bedsheet Store</title>
    <style>
        .success-message {
            display: <?php echo !empty($successMessage) ? 'block' : 'none'; ?>;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            margin: 20px auto;
            text-align: center;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 18px;
            font-weight: bold;
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
        <div class="success-message"><?php echo $successMessage; ?></div>
    <?php endif; ?>
  <header>
        <h1>Welcome to Fabrique</h1>
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

