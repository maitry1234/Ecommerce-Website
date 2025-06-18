<?php
session_start();
if (isset($_SESSION["user"])) {
   header("Location: home.php");
   exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="css/regstyle.css">
</head>
<body>
<div class="form-container">
    <h2>Sign Up</h2>
    <?php 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    if (isset($_POST["submit"])) {
        $fullName = $_POST["fullname"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $passwordRepeat = $_POST["repeat_password"]; 
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
        $errors = array();

        if (empty($fullName) || empty($email) || empty($password) || empty($passwordRepeat)) {
            array_push($errors, "All fields are required");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($errors, "Email is not valid");
        }
        
        if (strlen($password) < 8) {
            array_push($errors, "Password must be at least 8 characters long");
        }

        if ($password !== $passwordRepeat) {
            array_push($errors, "Passwords do not match");
        }
        
        require_once "database.php";
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $sql);
        $rowCount = mysqli_num_rows($result);
        if ($rowCount > 0) {
            array_push($errors, "Email already exists!");
        }
        
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                echo "<div style='color: red;'>$error</div>";
            }
        } else {
            $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_stmt_init($conn);
            $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
            if ($prepareStmt) {
                mysqli_stmt_bind_param($stmt, "sss", $fullName, $email, $passwordHash);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: login.php");
                exit();
            } else {
                echo "<div style='color: red;'>Something went wrong with the database query.</div>";
            }
        }
    }    
    ?>
    
    <form action="registration.php" method="post">
        <input type="text" id="name" name="fullname" placeholder="Full Name" required>
        <input type="email" id="email" name="email" placeholder="Email" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <input type="password" name="repeat_password" placeholder="Repeat Password" required> 
        <button type="submit" name="submit">Register</button> 
    </form>
</div>
</body>
</html>