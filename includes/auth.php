<?php
require_once __DIR__ . '/config.php';

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);

        // Harden session cookie
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();

        // Generate CSRF token once per session
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

function csrf_token(): string {
    start_session();
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Validate the CSRF token sent in the X-CSRF-Token request header.
 * Call this in every state-changing API endpoint (POST/PUT/DELETE).
 */
function verify_csrf(): void {
    $token = $_SESSION['csrf_token'] ?? '';
    $sent  = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$token || !$sent || !hash_equals($token, $sent)) {
        json_response(['error' => 'Invalid CSRF token'], 403);
    }
}

function current_user(): ?array {
    start_session();
    return $_SESSION['user'] ?? null;
}

function require_auth(): array {
    $user = current_user();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return $user;
}

function require_role(string ...$roles): array {
    $user = require_auth();
    if (!in_array($user['role'], $roles)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    return $user;
}

function json_response(mixed $data, int $status = 200): void {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function get_json_body(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function redirect_if_logged_in(): void {
    $user = current_user();
    if ($user) {
        $to = $user['role'] === 'supervisor' ? 'supervisor.php' : 'interviewer.php';
        header("Location: $to");
        exit;
    }
}
