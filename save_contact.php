<?php
session_start();
// Use the Composer autoloader for PHPMailer
require 'vendor/autoload.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php'; // makes $conn available

    // Get data from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $form_submitted = false;

    // --- Part 1: Save the message to the database (as a reliable backup) ---
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $form_submitted = true;
    }
    $stmt->close();
    $conn->close();

    // --- Part 2: Send the Email Notification to You ---
    if ($form_submitted) {
        $mail = new PHPMailer(true);

        try {
            // --- CONFIGURE YOUR GMAIL SMTP SETTINGS HERE ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'clarksulit5@gmail.com'; // Your Gmail address that will send the email
            $mail->Password   = 'icrx kxbn sccg dfec';   // Your 16-character Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // --- CONFIGURE THE EMAIL CONTENT ---

            //Recipients
            $mail->setFrom('contact@bagaburger.com', 'Baga Burger Contact Form');
            $mail->addAddress('clarksulit5@gmail.com', 'Baga Burger Admin'); // <<-- YOUR GMAIL where you want to receive the email
            
            // This is a great feature: when you hit "Reply" in Gmail, it will reply to the user, not your own email.
            $mail->addReplyTo($email, $name);

            //Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'New Contact Form Submission from ' . $name;
            $mail->Body    = "<h2>You have a new message from your website contact form:</h2>
                              <p><strong>Name:</strong> {$name}</p>
                              <p><strong>Email:</strong> {$email}</p>
                              <hr>
                              <p><strong>Message:</strong><br>" . nl2br($message) . "</p>";
            $mail->AltBody = "Name: {$name}\nEmail: {$email}\nMessage: {$message}";

            $mail->send();
        } catch (Exception $e) {
            // Silently log the error if the email fails to send.
            // The message is already saved in the database, so the user doesn't need to know about the email failure.
            // error_log("PHPMailer contact form error: {$mail->ErrorInfo}");
        }
    }
    
    // --- Part 3: Redirect the user back to the contact page with a success message ---
    if ($form_submitted) {
         // Redirect to a success page to prevent form resubmission on refresh
         header("Location: contact.php?sent=1");
         exit;
    } else {
        // Handle database error
        echo "<script>
                alert('An error occurred. Please try again.');
                window.location.href = 'contact.php';
            </script>";
    }
}
?>