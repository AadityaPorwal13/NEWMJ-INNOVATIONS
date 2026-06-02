<?php
// get-demo.php

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Session security flags — must be set before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();

// Simple .env loader — reads key=value pairs into $_ENV
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

// CSRF validation
$token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Invalid CSRF token."]);
    exit;
}

// IP-based rate limiting: max 5 submissions per hour
require __DIR__ . '/rate_limiter.php';
if (check_rate_limit(5) === null) exit;

// Honeypot spam protection
if (!empty($_POST['_honey'])) {
    echo json_encode(["status" => "success", "message" => "Demo request received"]);
    exit;
}

// Collect and sanitize input
$firstName    = htmlspecialchars(trim($_POST['first_name'] ?? ''));
$lastName     = htmlspecialchars(trim($_POST['last_name'] ?? ''));
$email        = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$company      = htmlspecialchars(trim($_POST['company'] ?? ''));
$jobTitle     = htmlspecialchars(trim($_POST['job_title'] ?? 'Not specified'));
$cameras      = htmlspecialchars(trim($_POST['camera_range'] ?? 'Not specified'));
$industry     = htmlspecialchars(trim($_POST['industry'] ?? 'Not specified'));
$cameraType   = htmlspecialchars(trim($_POST['camera_type'] ?? 'Not specified'));
$challenge    = htmlspecialchars(trim($_POST['challenge'] ?? 'Not specified'));
$timePref     = htmlspecialchars(trim($_POST['preferred_time'] ?? 'Not specified'));
$daysPref     = htmlspecialchars(trim($_POST['preferred_days'] ?? 'Not specified'));
$demoFormat   = htmlspecialchars(trim($_POST['demo_format'] ?? 'Not specified'));
$notes        = htmlspecialchars(trim($_POST['notes'] ?? 'None'));

// Basic validation
if (empty($firstName) || empty($lastName) || empty($email) || empty($company)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email format."]);
    exit;
}

// Require PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER'];
    $mail->Password   = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom($_ENV['SMTP_USER'], 'MJ Innovations Demo Requests');
    $mail->addAddress('porwalaaditya6@gmail.com', 'MJ Innovations Sales');
    $mail->addReplyTo($email, "$firstName $lastName");

    // Email formatting
    $mail->isHTML(true);
    $mail->Subject = "NEW DEMO REQUEST: $firstName $lastName ($company)";

    $body = "
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0a0f1a; padding:40px 0;'>
      <tr><td align='center'>
        <table width='600' cellpadding='0' cellspacing='0' style='border-radius:12px; overflow:hidden; border:1px solid #1a2744;'>

          <!-- Header -->
          <tr>
            <td style='background:linear-gradient(135deg,#0B192C 0%,#112240 100%); padding:32px 40px; text-align:center;'>
              <div style='font-size:11px; color:#3b82f6; letter-spacing:3px; text-transform:uppercase; margin-bottom:8px; font-weight:600;'>MJ Innovations FZ LLC</div>
              <div style='font-size:22px; color:#ffffff; font-weight:700; letter-spacing:0.5px;'>New Demo Request</div>
              <div style='width:50px; height:3px; background:#3b82f6; margin:16px auto 0; border-radius:2px;'></div>
            </td>
          </tr>

          <!-- Priority badge -->
          <tr>
            <td style='background-color:#112240; padding:16px 40px; border-bottom:1px solid #1a2744;'>
              <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                  <td style='font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:1px;'>Request Type</td>
                  <td align='right'><span style='background:#3b82f6; color:#fff; padding:5px 16px; border-radius:20px; font-size:13px; font-weight:600;'>Demo Booking</span></td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Contact details -->
          <tr>
            <td style='background-color:#0f1a2e; padding:30px 40px;'>
              <div style='font-size:11px; color:#3b82f6; letter-spacing:2px; text-transform:uppercase; margin-bottom:18px; font-weight:600;'>Contact Details</div>
              <table width='100%' cellpadding='0' cellspacing='0' style='font-size:14px;'>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#64748b; width:130px;'>Name</td>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#e2e8f0; font-weight:600;'>$firstName $lastName</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#64748b;'>Email</td>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744;'><a href='mailto:$email' style='color:#60a5fa; text-decoration:none;'>$email</a></td>
                </tr>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#64748b;'>Company</td>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#e2e8f0;'>$company</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; color:#64748b;'>Job Title</td>
                  <td style='padding:10px 0; color:#e2e8f0;'>$jobTitle</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Requirements -->
          <tr>
            <td style='background-color:#0f1a2e; padding:30px 40px; border-top:1px solid #1a2744;'>
              <div style='font-size:11px; color:#3b82f6; letter-spacing:2px; text-transform:uppercase; margin-bottom:18px; font-weight:600;'>Requirements</div>
              <table width='100%' cellpadding='0' cellspacing='0' style='font-size:14px;'>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#64748b; width:130px;'>Camera Range</td>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#e2e8f0;'>$cameras</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#64748b;'>Industry</td>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#e2e8f0;'>$industry</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; color:#64748b;'>Camera Type</td>
                  <td style='padding:10px 0; color:#e2e8f0;'>$cameraType</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Challenge -->
          <tr>
            <td style='background-color:#0f1a2e; padding:0 40px 30px; border-top:1px solid #1a2744;'>
              <div style='font-size:11px; color:#3b82f6; letter-spacing:2px; text-transform:uppercase; margin-bottom:14px; font-weight:600;'>Current Challenge</div>
              <div style='background:#112240; padding:20px; border-left:3px solid #3b82f6; border-radius:0 8px 8px 0; color:#cbd5e1; font-size:14px; line-height:1.7;'>
                " . nl2br($challenge) . "
              </div>
            </td>
          </tr>

          <!-- Scheduling -->
          <tr>
            <td style='background-color:#0f1a2e; padding:30px 40px; border-top:1px solid #1a2744;'>
              <div style='font-size:11px; color:#3b82f6; letter-spacing:2px; text-transform:uppercase; margin-bottom:18px; font-weight:600;'>Scheduling</div>
              <table width='100%' cellpadding='0' cellspacing='0' style='font-size:14px;'>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#64748b; width:130px;'>Preferred Time</td>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#e2e8f0;'>$timePref</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#64748b;'>Preferred Days</td>
                  <td style='padding:10px 0; border-bottom:1px solid #1a2744; color:#e2e8f0;'>$daysPref</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; color:#64748b;'>Demo Format</td>
                  <td style='padding:10px 0; color:#e2e8f0;'>$demoFormat</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Notes -->
          <tr>
            <td style='background-color:#0f1a2e; padding:0 40px 30px; border-top:1px solid #1a2744;'>
              <div style='font-size:11px; color:#3b82f6; letter-spacing:2px; text-transform:uppercase; margin-bottom:14px; font-weight:600;'>Additional Notes</div>
              <div style='background:#112240; padding:20px; border-left:3px solid #3b82f6; border-radius:0 8px 8px 0; color:#cbd5e1; font-size:14px; line-height:1.7;'>
                " . nl2br($notes) . "
              </div>
            </td>
          </tr>

          <!-- Reply CTA -->
          <tr>
            <td style='background-color:#0f1a2e; padding:0 40px 30px; text-align:center;'>
              <a href='mailto:$email?subject=Re: Demo Request - MJ Innovations' style='display:inline-block; background:#3b82f6; color:#ffffff; padding:12px 36px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; letter-spacing:0.5px;'>Reply to $firstName</a>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style='background:#0B192C; padding:20px 40px; border-top:1px solid #1a2744; text-align:center;'>
              <div style='font-size:11px; color:#475569; line-height:1.6;'>
                Received " . date('d M Y \a\t g:i A') . " &bull; MJ Innovations FZ LLC<br>
                <span style='color:#334155;'>This demo request was submitted securely via your website.</span>
              </div>
            </td>
          </tr>

        </table>
      </td></tr>
    </table>
    ";

    $mail->Body    = $body;
    $mail->AltBody = "New Demo Request\n\nName: $firstName $lastName\nEmail: $email\nCompany: $company\nJob Title: $jobTitle\n\nCamera Range: $cameras\nIndustry: $industry\nCamera Type: $cameraType\n\nChallenge: $challenge\n\nPreferred Time: $timePref\nPreferred Days: $daysPref\nDemo Format: $demoFormat\n\nNotes: $notes";

    $mail->send();

    // Auto-reply to the user
    $reply = new PHPMailer(true);
    $reply->isSMTP();
    $reply->Host       = 'smtp.gmail.com';
    $reply->SMTPAuth   = true;
    $reply->Username   = $_ENV['SMTP_USER'];
    $reply->Password   = $_ENV['SMTP_PASS'];
    $reply->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $reply->Port       = 465;
    $reply->setFrom($_ENV['SMTP_USER'], 'MJ Innovations FZ LLC');
    $reply->addAddress($email, "$firstName $lastName");
    $reply->isHTML(true);
    $reply->Subject = "Your MJ Innovations Demo Request";

    $replyBody = "
    <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#0a0f1a; padding:40px 0;'>
      <tr><td align='center'>
        <table width='600' cellpadding='0' cellspacing='0' style='border-radius:12px; overflow:hidden; border:1px solid #1a2744;'>

          <tr>
            <td style='background:#0B192C; padding:32px 40px; text-align:center;'>
              <div style='font-size:11px; color:#42A5F5; letter-spacing:3px; text-transform:uppercase; margin-bottom:8px; font-weight:600;'>MJ Innovations FZ LLC</div>
              <div style='font-size:22px; color:#ffffff; font-weight:700; letter-spacing:0.5px;'>Thank You, $firstName</div>
              <div style='width:50px; height:3px; background:#42A5F5; margin:16px auto 0; border-radius:2px;'></div>
            </td>
          </tr>

          <tr>
            <td style='background-color:#033279; padding:30px 40px;'>
              <div style='font-size:15px; color:#ffffff; line-height:1.7; margin-bottom:20px;'>
                We received your demo request and are excited to show you our AI surveillance platform in action.
              </div>
              <div style='background:#042D6B; padding:20px; border-left:3px solid #42A5F5; border-radius:0 8px 8px 0; color:#B0BEC5; font-size:14px; line-height:1.7;'>
                A member of our team will reach out to you shortly to confirm your demo slot. We typically respond within <strong style='color:#fff;'>1 business day</strong>.
              </div>
            </td>
          </tr>

          <tr>
            <td style='background-color:#033279; padding:0 40px 30px;'>
              <div style='font-size:11px; color:#42A5F5; letter-spacing:2px; text-transform:uppercase; margin-bottom:14px; font-weight:600;'>Your Demo Details</div>
              <table width='100%' cellpadding='0' cellspacing='0' style='font-size:14px;'>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid rgba(66,165,245,0.12); color:#90CAF9; width:130px;'>Name</td>
                  <td style='padding:10px 0; border-bottom:1px solid rgba(66,165,245,0.12); color:#ffffff;'>$firstName $lastName</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid rgba(66,165,245,0.12); color:#90CAF9;'>Company</td>
                  <td style='padding:10px 0; border-bottom:1px solid rgba(66,165,245,0.12); color:#ffffff;'>$company</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; border-bottom:1px solid rgba(66,165,245,0.12); color:#90CAF9;'>Preferred Time</td>
                  <td style='padding:10px 0; border-bottom:1px solid rgba(66,165,245,0.12); color:#ffffff;'>$timePref</td>
                </tr>
                <tr>
                  <td style='padding:10px 0; color:#90CAF9;'>Preferred Days</td>
                  <td style='padding:10px 0; color:#ffffff;'>$daysPref</td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style='background-color:#033279; padding:0 40px 10px;'>
              <div style='font-size:13px; color:#90CAF9; line-height:1.6; text-align:center;'>
                In the meantime, feel free to explore our <a href='https://mj-innovations.com/solutions' style='color:#42A5F5; text-decoration:none;'>solutions page</a> to learn more.
              </div>
            </td>
          </tr>

          <tr>
            <td style='background:#031E50; padding:20px 40px; border-top:1px solid rgba(66,165,245,0.12); text-align:center;'>
              <div style='font-size:11px; color:#475569; line-height:1.6;'>
                MJ Innovations FZ LLC &bull; AI-Powered Video Surveillance<br>
                <span style='color:#334155;'>This is an automated confirmation. Please do not reply to this email.</span>
              </div>
            </td>
          </tr>

        </table>
      </td></tr>
    </table>
    ";
    $reply->Body    = $replyBody;
    $reply->AltBody = "Thank you, $firstName!\n\nWe received your demo request and are excited to show you our AI surveillance platform in action.\n\nA member of our team will reach out to you shortly to confirm your demo slot. We typically respond within 1 business day.\n\nYour Demo Details:\nName: $firstName $lastName\nCompany: $company\nPreferred Time: $timePref\nPreferred Days: $daysPref\n\nMJ Innovations FZ LLC";
    $reply->send();

    // Rotate CSRF token after successful submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    echo json_encode(["status" => "success", "message" => "Demo request sent successfully"]);
} catch (Exception $e) {
    error_log("PHPMailer Error: {$mail->ErrorInfo}");
    echo json_encode(["status" => "error", "message" => "Request could not be sent. Please try again later."]);
}
?>
