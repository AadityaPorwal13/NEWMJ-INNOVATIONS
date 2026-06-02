<?php
// csrf_token.php
// Returns a CSRF token as JSON. Call via fetch() from static HTML forms.

ini_set('display_errors', 0);

// Session security flags
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();

// Rate limit this endpoint too (20/hour — higher than form submits)
require __DIR__ . '/rate_limiter.php';
if (check_rate_limit(20) === null) exit;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header('Content-Type: application/json');
echo json_encode(['csrf_token' => $_SESSION['csrf_token']]);
