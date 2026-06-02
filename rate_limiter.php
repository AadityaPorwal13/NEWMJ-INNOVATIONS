<?php
// rate_limiter.php
// IP-based rate limiting using flat files in a rate_limit/ directory.

$RATE_LIMIT_DIR = __DIR__ . '/rate_limit';

if (!is_dir($RATE_LIMIT_DIR)) {
    mkdir($RATE_LIMIT_DIR, 0755);
}

// Cleanup old rate limit files (older than 2 hours)
// Runs randomly on ~2% of requests to avoid performance overhead
if (random_int(1, 50) === 1) {
    $cutoff = time() - 7200;
    foreach (glob($RATE_LIMIT_DIR . '/*.txt') as $file) {
        if (filemtime($file) < $cutoff) {
            @unlink($file);
        }
    }
}

function check_rate_limit(int $max_per_hour): ?array {
    global $RATE_LIMIT_DIR;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = $RATE_LIMIT_DIR . '/' . md5($ip) . '.txt';
    $now = time();
    $cutoff = $now - 3600;

    $timestamps = [];
    if (file_exists($file)) {
        $timestamps = array_filter(
            array_map('intval', file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)),
            fn($ts) => $ts >= $cutoff
        );
    }

    if (count($timestamps) >= $max_per_hour) {
        http_response_code(429);
        echo json_encode(["status" => "error", "message" => "Too many requests. Please try again later."]);
        return null;
    }

    // Record this request
    $timestamps[] = $now;
    file_put_contents($file, implode("\n", $timestamps) . "\n", LOCK_EX);

    return $timestamps;
}
