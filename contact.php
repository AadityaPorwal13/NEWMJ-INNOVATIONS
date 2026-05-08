<?php
// contact.php

// Define namespaces for PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set header for JSON response
header('Content-Type: application/json');

// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Honeypot Spam Protection
    // If the hidden '_honey' field is filled, it's a bot. Silently exit with success to fool the bot.
    if (!empty($_POST['_honey'])) {
        echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
        exit;
    }

    // 2. Collect and sanitize input
    $firstName = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $lastName  = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $company   = htmlspecialchars(trim($_POST['company'] ?? ''));
    $role      = htmlspecialchars(trim($_POST['role'] ?? ''));
    $industry  = htmlspecialchars(trim($_POST['industry'] ?? 'Not specified'));
    $enquiry   = htmlspecialchars(trim($_POST['enquiry_type'] ?? 'Not specified'));
    $message   = htmlspecialchars(trim($_POST['message'] ?? 'No message provided.'));

    // 3. Basic Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($company) || empty($role)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    // 4. Require PHPMailer classes
    // Note: If you use Composer, simply: require 'vendor/autoload.php';
    // If you upload PHPMailer manually, make sure the 'PHPMailer/src' folder is in the same directory:
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        // 5. Server settings (SMTP Configuration)
        $mail->isSMTP();                                    // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';               // Gmail SMTP server
        $mail->SMTPAuth   = true;                           // Enable SMTP authentication
        $mail->Username   = 'apshow96@gmail.com';           // Your Gmail address
        $mail->Password   = 'ynqi xvpb brjb thvk';           // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;    // Enable implicit TLS encryption
        $mail->Port       = 465;                            // TCP port to connect to

        // 6. Recipients
        $mail->setFrom('apshow96@gmail.com', 'MJ Innovations Website'); 
        $mail->addAddress('porwalaaditya6@gmail.com', 'MJ Innovations Sales'); 
        $mail->addReplyTo($email, "$firstName $lastName");                 // Allows you to hit "Reply" and email the user directly

        // 7. Email Formatting (Clean Professional Template)
        $mail->isHTML(true);
        $mail->Subject = "New Enquiry: $enquiry from $firstName $lastName ($company)";
        
        $body = "
        <div style='font-family: Inter, Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <div style='background-color: #0B192C; padding: 25px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 20px; font-weight: 600; letter-spacing: 1px;'>New Website Submission</h2>
            </div>
            <div style='padding: 30px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px;'>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b; width: 120px;'><strong>Name</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'>$firstName $lastName</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Email</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0;'><a href='mailto:$email' style='color: #3b82f6; text-decoration: none;'>$email</a></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Company</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'>$company</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Role / Title</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'>$role</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Industry</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'>$industry</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Enquiry Type</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'><strong>$enquiry</strong></td>
                    </tr>
                </table>
                <h4 style='margin-top: 25px; margin-bottom: 12px; color: #0f172a; font-size: 15px;'>Message:</h4>
                <div style='background-color: #ffffff; padding: 18px; border-left: 4px solid #3b82f6; color: #334155; font-style: italic; font-size: 14px; line-height: 1.6; border-radius: 0 4px 4px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);'>
                    " . nl2br($message) . "
                </div>
            </div>
            <div style='text-align: center; font-size: 12px; color: #94a3b8; margin-top: 20px;'>
                Securely routed from the MJ Innovations Platform
            </div>
        </div>
        ";

        // Plain text fallback
        $mail->Body    = $body;
        $mail->AltBody = "New Contact Submission\n\nName: $firstName $lastName\nEmail: $email\nCompany: $company\nRole: $role\nIndustry: $industry\nEnquiry Type: $enquiry\n\nMessage:\n$message";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
    } catch (Exception $e) {
        // Log error securely on server
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        echo json_encode(["status" => "error", "message" => "Message could not be sent. Error: " . $mail->ErrorInfo]);
    }

} else {
    // If someone tries to visit contact.php directly in the browser
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}
?>
