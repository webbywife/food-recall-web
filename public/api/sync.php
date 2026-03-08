<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user = require_auth();
$db   = Database::get();
$type = $_GET['type'] ?? '';

// GET /api/sync.php?type=fct  — return all FCT foods (cached once in IDB)
if ($type === 'fct') {
    $stmt = $db->query("SELECT * FROM fct_foods WHERE is_active = 1 ORDER BY food_name");
    json_response(['fct_foods' => $stmt->fetchAll()]);
}

json_response(['error' => 'Unknown sync type'], 400);
