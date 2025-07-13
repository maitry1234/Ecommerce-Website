<?php
session_start();
if (isset($_SESSION["user"])) {
    if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: home.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Sister's Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/signstyle.css"> 
</head>
<style>
  .password-wrapper {
    position: relative;
    width: fit-content;
  }

  .password-wrapper input {
    padding-right: 40px; /* space for eye icon */
  }

  .toggle-eye {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #555;
  }
</style>
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
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
            if ($user) {
                if (password_verify($password, $user["password"])) {
                    $_SESSION["user"] = "yes";
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["full_name"] = $user["full_name"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["is_admin"] = $user["is_admin"];
                    $_SESSION["login_success"] = "Welcome, " . $user["full_name"] . "! You have successfully logged in.";
                    if ($user["is_admin"] == 1) {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: home.php");
                    }
                    die();
                } else {
                    echo "<div class='alert alert-danger'>Password does not match</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Email does not match</div>";
            }
            mysqli_stmt_close($stmt);
        }
        ?>
        <form action="login.php" method="post">
        <input type="email" name="email" placeholder="Enter your email" required>

          <div style="position: relative;">
             <input type="password" id="password" name="password" placeholder="Enter your password" required style="width: 100%; padding-right: 35px;">

             <i class="fa-regular fa-eye" id="togglePassword"
               style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
              cursor: pointer; color: #aaa; font-size: 16px;"></i>
              </div>

          <button type="submit" name="login">Sign In</button>
        </form>

<script>
  const togglePassword = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  togglePassword.addEventListener("click", function () {
    const isPassword = passwordInput.type === "password";
    passwordInput.type = isPassword ? "text" : "password";

    this.classList.toggle("fa-eye");
    this.classList.toggle("fa-eye-slash");
  });
</script>


        <p>Don't have an account? <a href="registration.php">Sign Up</a></p>
    </div>
    <footer>
        <p>© Sister's Shop. All rights reserved.</p>
    </footer>
</body>
</html>