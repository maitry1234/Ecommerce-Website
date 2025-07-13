<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Mailpit SMTP settings
    $mail->isSMTP();
    $mail->Host = 'localhost';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;  // Mailpit doesn't require auth

    // Sender and recipient
    $mail->setFrom('no-reply@fabrique.com', 'Fabrique Store');
    $mail->addAddress('test@example.com', 'Test User');

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Fabrique';
    $mail->Body    = '<h1>Hello!</h1><p>This is a test email sent via Mailpit.</p>';
    $mail->AltBody = 'This is a test email sent via Mailpit.';

    $mail->send();
    echo '✅ Test email sent successfully! Check Mailpit at http://localhost:8025';
} catch (Exception $e) {
    echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
