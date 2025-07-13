<?php
session_start();
require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST["phone"], FILTER_SANITIZE_STRING);
    $subject = filter_var($_POST["subject"], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST["message"], FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION["error"] = "Please fill in all required fields.";
        header("Location: contact.php");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Please enter a valid email address.";
        header("Location: contact.php");
        exit();
    }

    // Insert into database using prepared statement
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

    if ($stmt->execute()) {
        $_SESSION["success"] = "Your message has been sent successfully!";
    } else {
        $_SESSION["error"] = "There was an error sending your message. Please try again.";
    }

    $stmt->close();
    header("Location: contact.php");
    exit();
} else {
    header("Location: contact.php");
    exit();
}
?>