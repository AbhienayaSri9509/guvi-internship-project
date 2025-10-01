<?php
// php/db.php
header('Content-Type: application/json; charset=utf-8');

// --- Configuration ---
$dbConfig = [
    'host' => '127.0.0.1',
    'dbname' => 'guvi_intern',
    'user' => 'root',
    'pass' => '', // <-- set your MySQL password
    'charset' => 'utf8mb4'
];

$redisConfig = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'timeout' => 0
];

$mongoConfig = [
    'uri' => 'mongodb://127.0.0.1:27017',
    'db'  => 'guvi_intern'
];

try {
    // PDO (MySQL)
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection error: ' . $e->getMessage()]);
    exit;
}

// Redis (phpredis extension)
$redis = null;
try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect($redisConfig['host'], $redisConfig['port'], $redisConfig['timeout']);
        // optional: auth if needed: $redis->auth('yourpassword');
    } else {
        // Redis extension missing
        // We'll keep $redis as null and validate later.
    }
} catch (Exception $e) {
    // non-fatal here, but sessions won't work without Redis
    $redis = null;
}

// MongoDB (optional)
$mongo = null;
try {
    if (class_exists('MongoDB\\Driver\\Manager')) {
        $mongo = new MongoDB\Driver\Manager($mongoConfig['uri']);
    } else {
        $mongo = null;
    }
} catch (Exception $e) {
    $mongo = null;
}

// ---------- Helper functions ----------

/**
 * Read JSON body from request
 */
function get_json_body() {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Generate secure session token
 */
function generate_token($length = 64) {
    return bin2hex(random_bytes($length/2));
}

/**
 * Store session in Redis
 */
function store_session($redis, $token, $data, $ttl = 86400) {
    if (!$redis) return false;
    $payload = json_encode($data);
    // Use SETEX for TTL
    return $redis->setex("session:$token", $ttl, $payload);
}

/**
 * Fetch session from Redis
 */
function fetch_session($redis, $token) {
    if (!$redis) return null;
    $val = $redis->get("session:$token");
    if (!$val) return null;
    $data = json_decode($val, true);
    return is_array($data) ? $data : null;
}

/**
 * Delete session
 */
function delete_session($redis, $token) {
    if (!$redis) return false;
    return $redis->del("session:$token");
}

/**
 * Validate token from request (checks header X-Session-Token or Authorization Bearer)
 */
function get_token_from_request() {
    $headers = getallheaders();
    if (!empty($headers['X-Session-Token'])) return $headers['X-Session-Token'];
    if (!empty($headers['x-session-token'])) return $headers['x-session-token'];
    if (!empty($headers['Authorization'])) {
        // Expect: Bearer <token>
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $m)) return $m[1];
    }
    if (!empty($headers['authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['authorization'], $m)) return $m[1];
    }
    // fallback: token via POST/GET param (not preferred)
    $body = get_json_body();
    if (!empty($body['token'])) return $body['token'];
    if (!empty($_GET['token'])) return $_GET['token'];
    return null;
}
