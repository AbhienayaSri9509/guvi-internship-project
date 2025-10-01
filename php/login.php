<?php
// php/login.php
require_once __DIR__ . '/db.php';

$input = get_json_body();
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'email and password required']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // Generate session token and save in Redis
    if (!$redis) {
        // Redis not available — respond with error (spec requires Redis usage)
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Redis is required for session storage but not available on server']);
        exit;
    }

    $token = generate_token(64);
    $sessionData = [
        'user_id' => intval($user['id']),
        'email' => $user['email'],
        'name' => $user['name'],
        'created_at' => time()
    ];

    $ok = store_session($redis, $token, $sessionData, 86400); // 1 day TTL
    if (!$ok) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to store session in Redis']);
        exit;
    }

    // return token and minimal user info
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => intval($user['id']),
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
