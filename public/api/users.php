<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user   = require_role('supervisor');
$db     = Database::get();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') verify_csrf();

// GET — list all interviewers
if ($method === 'GET') {
    $stmt = $db->query("
        SELECT id, username, full_name, assignment_area, is_active, created_at
        FROM users
        WHERE role = 'interviewer'
        ORDER BY full_name
    ");
    json_response(['users' => $stmt->fetchAll()]);
}

// POST — create interviewer
if ($method === 'POST') {
    $data = get_json_body();

    $username = trim($data['username'] ?? '');
    $fullName = trim($data['full_name'] ?? '');
    $area     = trim($data['assignment_area'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$fullName || !$password) {
        json_response(['error' => 'username, full_name, and password are required.'], 422);
    }
    if (strlen($password) < 8) {
        json_response(['error' => 'Password must be at least 8 characters.'], 422);
    }

    // Check username taken
    $chk = $db->prepare("SELECT id FROM users WHERE username = ?");
    $chk->execute([$username]);
    if ($chk->fetch()) {
        json_response(['error' => 'Username already exists.'], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("
        INSERT INTO users (username, password_hash, full_name, role, assignment_area, is_active)
        VALUES (?, ?, ?, 'interviewer', ?, 1)
    ");
    $stmt->execute([$username, $hash, $fullName, $area ?: null]);

    json_response(['success' => true, 'id' => (int)$db->lastInsertId()]);
}

// PUT — reset password or toggle active
if ($method === 'PUT') {
    $data   = get_json_body();
    $id     = (int)($data['id'] ?? 0);
    $action = $data['action'] ?? '';

    if (!$id) json_response(['error' => 'Missing id.'], 422);

    // Only allow targeting interviewers
    $chk = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'interviewer'");
    $chk->execute([$id]);
    if (!$chk->fetch()) json_response(['error' => 'Interviewer not found.'], 404);

    if ($action === 'reset_password') {
        $password = $data['password'] ?? '';
        if (strlen($password) < 8) {
            json_response(['error' => 'Password must be at least 8 characters.'], 422);
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);
        json_response(['success' => true]);
    }

    if ($action === 'set_active') {
        $active = (int)(bool)($data['is_active'] ?? 1);
        $db->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$active, $id]);
        json_response(['success' => true]);
    }

    json_response(['error' => 'Unknown action.'], 422);
}

json_response(['error' => 'Method not allowed.'], 405);
