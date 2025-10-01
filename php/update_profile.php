<?php
// php/update_profile.php
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
$input = get_json_body();

// Acceptable fields: age, dob, contact
$age = isset($input['age']) ? (int)$input['age'] : null;
$dob = isset($input['dob']) && $input['dob'] !== '' ? $input['dob'] : null;
$contact = isset($input['contact']) ? trim($input['contact']) : null;

if ($contact !== null && !preg_match('/^\d{10}$/', $contact)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Contact must be a 10-digit number']);
    exit;
}

// Build update dynamically
$fields = [];
$params = ['id' => $userId];
if ($age !== null) { $fields[] = 'age = :age'; $params['age'] = $age; }
if ($dob !== null) { $fields[] = 'dob = :dob'; $params['dob'] = $dob; }
if ($contact !== null) { $fields[] = 'contact = :contact'; $params['contact'] = $contact; }

if (empty($fields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit;
}

try {
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Optionally update Mongo copy
    if ($mongo) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $filter = ['user_id' => $userId];
            $updateDoc = ['$set' => array_filter([
                'age' => $age !== null ? $age : null,
                'dob' => $dob !== null ? $dob : null,
                'contact' => $contact !== null ? $contact : null,
            ])];
            $bulk->update($filter, $updateDoc, ['upsert' => false]);
            $mongo->executeBulkWrite("{$mongoConfig['db']}.users", $bulk);
        } catch (Exception $e) {
            // ignore Mongo errors
        }
    }

    echo json_encode(['success' => true, 'message' => 'Profile updated']);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
