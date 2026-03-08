<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'login') {
    $data     = get_json_body();
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (!$username || !$password) {
        json_response(['error' => 'Username and password required'], 400);
    }

    $db   = Database::get();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(['error' => 'Invalid username or password'], 401);
    }

    $_SESSION['user'] = [
        'id'              => $user['id'],
        'username'        => $user['username'],
        'full_name'       => $user['full_name'],
        'role'            => $user['role'],
        'assignment_area' => $user['assignment_area'],
    ];

    json_response([
        'success'  => true,
        'user'     => $_SESSION['user'],
        'redirect' => $user['role'] === 'supervisor' ? 'supervisor.php' : 'interviewer.php',
    ]);
}

if ($method === 'POST' && $action === 'logout') {
    session_destroy();
    json_response(['success' => true]);
}

if ($method === 'GET' && $action === 'me') {
    $user = current_user();
    json_response(['user' => $user]);
}

json_response(['error' => 'Not found'], 404);
