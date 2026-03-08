<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user   = require_auth();
$db     = Database::get();
$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// GET /api/households.php  — list
// GET /api/households.php?id=N — single with respondents
if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM households WHERE id = ?");
        $stmt->execute([$id]);
        $hh = $stmt->fetch();
        if (!$hh) json_response(['error' => 'Not found'], 404);

        $stmt2 = $db->prepare("
            SELECT r.*,
                   rs.id            AS session_id,
                   rs.status        AS session_status,
                   rs.current_pass  AS session_pass,
                   rs.is_day2
            FROM respondents r
            LEFT JOIN recall_sessions rs
                ON rs.respondent_id = r.id AND rs.interviewer_id = ?
            WHERE r.household_id = ?
            ORDER BY r.type, r.name
        ");
        $stmt2->execute([$user['id'], $id]);
        $hh['respondents'] = $stmt2->fetchAll();
        json_response(['household' => $hh]);
    }

    if ($user['role'] === 'supervisor') {
        $stmt = $db->prepare("
            SELECT h.*,
                   u.full_name                                                 AS interviewer_name,
                   COUNT(DISTINCT r.id)                                        AS respondent_count,
                   SUM(CASE WHEN r.recall_status = 'completed' THEN 1 ELSE 0 END) AS completed_respondents
            FROM households h
            LEFT JOIN users u ON h.assigned_interviewer_id = u.id
            LEFT JOIN respondents r ON r.household_id = h.id
            GROUP BY h.id
            ORDER BY h.hh_id
        ");
        $stmt->execute();
    } else {
        $stmt = $db->prepare("
            SELECT h.*,
                   COUNT(DISTINCT r.id)                                            AS respondent_count,
                   SUM(CASE WHEN r.recall_status = 'completed' THEN 1 ELSE 0 END) AS completed_respondents
            FROM households h
            LEFT JOIN respondents r ON r.household_id = h.id
            WHERE h.assigned_interviewer_id = ?
            GROUP BY h.id
            ORDER BY h.status DESC, h.hh_id
        ");
        $stmt->execute([$user['id']]);
    }

    json_response(['households' => $stmt->fetchAll()]);
}

// POST — create (supervisor only)
if ($method === 'POST' && $user['role'] === 'supervisor') {
    $data = get_json_body();
    $stmt = $db->prepare("
        INSERT INTO households (hh_id, region, province, municipality, barangay, address, assigned_interviewer_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['hh_id'], $data['region'], $data['province'],
        $data['municipality'], $data['barangay'], $data['address'],
        $data['assigned_interviewer_id'] ?? null,
    ]);
    json_response(['success' => true, 'id' => $db->lastInsertId()]);
}

json_response(['error' => 'Not found'], 404);
