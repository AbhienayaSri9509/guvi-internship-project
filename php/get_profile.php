<?php
// php/get_profile.php
require_once __DIR__ . '/db.php';

$token = get_token_from_request();
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Missing session token']);
    exit;
}

$session = fetch_session($redis, $token);
if (!$session) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired session']);
    exit;
}

$userId = intval($session['user_id']);

// fetch user profile from MySQL
try {
    $stmt = $pdo->prepare('SELECT id, name, email, age, dob, contact, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    echo json_encode(['success' => true, 'user' => $user]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
