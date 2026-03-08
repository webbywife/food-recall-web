<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user   = require_auth();
$db     = Database::get();
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') verify_csrf();

// POST — add respondent to household (supervisor only)
if ($method === 'POST' && $user['role'] === 'supervisor') {
    $data = get_json_body();
    $stmt = $db->prepare("
        INSERT INTO respondents (household_id, name, age, sex, type)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        (int)$data['household_id'],
        trim($data['name']),
        (isset($data['age']) && $data['age'] !== '') ? (int)$data['age'] : null,
        $data['sex']  ?? null,
        $data['type'] ?? 'other',
    ]);
    json_response(['success' => true, 'id' => $db->lastInsertId()]);
}

// DELETE — remove respondent (supervisor only)
if ($method === 'DELETE' && $user['role'] === 'supervisor') {
    $rid = (int)($_GET['id'] ?? 0);
    if (!$rid) json_response(['error' => 'id required'], 400);
    $db->prepare("DELETE FROM respondents WHERE id = ?")->execute([$rid]);
    json_response(['success' => true]);
}

json_response(['error' => 'Not found'], 404);
