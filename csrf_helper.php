<?php
// csrf_helper.php
// Include this at the top of any page that renders a form.
// It starts the session and generates a CSRF token for use in hidden inputs.

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
