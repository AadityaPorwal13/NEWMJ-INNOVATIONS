<?php
// get-demo.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Honeypot Spam Protection
    if (!empty($_POST['_honey'])) {
        echo json_encode(["status" => "success", "message" => "Demo request received"]);
        exit;
    }

    // 2. Collect and sanitize input
    $firstName = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $lastName  = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $company   = htmlspecialchars(trim($_POST['company'] ?? ''));
    $cameras   = htmlspecialchars(trim($_POST['camera_range'] ?? 'Not specified'));
    $industry  = htmlspecialchars(trim($_POST['industry'] ?? 'Not specified'));
    $timePref  = htmlspecialchars(trim($_POST['preferred_time'] ?? 'Not specified'));

    // 3. Basic Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($company)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit;
    }

    // 4. Require PHPMailer classes
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    try {
        // 5. Server settings (SMTP Configuration)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'apshow96@gmail.com';
        $mail->Password   = 'ynqi xvpb brjb thvk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // 6. Recipients
        $mail->setFrom('apshow96@gmail.com', 'MJ Innovations Demo Requests');
        $mail->addAddress('porwalaaditya6@gmail.com', 'MJ Innovations Sales');
        $mail->addReplyTo($email, "$firstName $lastName");

        // 7. Email Formatting
        $mail->isHTML(true);
        $mail->Subject = "NEW DEMO REQUEST: $firstName $lastName ($company)";
        
        $body = "
        <div style='font-family: Inter, Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
            <div style='background-color: #0B192C; padding: 25px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 20px; font-weight: 600; letter-spacing: 1px;'>New Demo Booking</h2>
            </div>
            <div style='padding: 30px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 8px 8px;'>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b; width: 150px;'><strong>Name</strong></td>
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
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Cameras</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'>$cameras</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Industry</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'>$industry</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #64748b;'><strong>Preferred Time</strong></td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; color: #0f172a;'>$timePref</td>
                    </tr>
                </table>
            </div>
            <div style='text-align: center; font-size: 12px; color: #94a3b8; margin-top: 20px;'>
                Priority Demo Request via MJ Innovations Platform
            </div>
        </div>
        ";

        $mail->Body    = $body;
        $mail->AltBody = "New Demo Request\n\nName: $firstName $lastName\nEmail: $email\nCompany: $company\nCameras: $cameras\nIndustry: $industry\nPreferred Time: $timePref";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Demo request sent successfully"]);
    } catch (Exception $e) {
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        echo json_encode(["status" => "error", "message" => "Request could not be sent. Error: " . $mail->ErrorInfo]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}
?>
