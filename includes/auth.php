<?php
require_once __DIR__ . '/config.php';

function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
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
