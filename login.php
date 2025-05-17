<?php
session_start();
if (isset($_SESSION["user"])) {
   header("Location: home.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Sister's Shop</title>
    <link rel="stylesheet" href="css/signstyle.css">
</head>

<body>
    <header>
        <h1>Start Shopping</h1>
        <p>Sign in to explore our premium bedsheets and more!</p>
    </header>
    <div class="form-container">
        <h2>Log in</h2>
        <?php
        if (isset($_POST["login"])) {
           $email = $_POST["email"];
           $password = $_POST["password"];
            require_once "database.php";
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $result = mysqli_query($conn, $sql);
            $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
            if ($user) {
                if (password_verify($password, $user["password"])) {
                    session_start();
                    $_SESSION["user"] = "yes";
                    $_SESSION["login_success"] = "Login Successful!"; // Store success message
                    header("Location: home.php");
                    die();
                } else {
                    echo "<div class='alert alert-danger'>Password does not match</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Email does not match</div>";
            }
            
        }
        ?>
        <form action="login.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit" value="Login" name="login">Sign In</button>
        </form>
        <p>Don't have an account? <a href="registration.php">Sign Up</a></p>
    </div>

    <footer>
        <p>&copy; Sister's Shop. All rights reserved.</p>
    </footer>
</body>
</html>