<?php
// php/signup.php
require_once __DIR__ . '/db.php';

$input = get_json_body();
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'name, email and password are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// check if email exists
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :ph)');
    $insert->execute([
        'name' => $name,
        'email' => $email,
        'ph' => $password_hash
    ]);
    $userId = $pdo->lastInsertId();

    // Optionally store a copy in MongoDB if available
    if ($mongo) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $doc = [
                'user_id' => (int)$userId,
                'name' => $name,
                'email' => $email,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ];
            $bulk->insert($doc);
            $mongo->executeBulkWrite("{$mongoConfig['db']}.users", $bulk);
        } catch (Exception $e) {
            // non-fatal, ignore
        }
    }

    echo json_encode(['success' => true, 'message' => 'Registered successfully. Please login.']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
