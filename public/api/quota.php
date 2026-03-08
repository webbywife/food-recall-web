<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user   = require_role('supervisor');
$db     = Database::get();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Interviewer progress
    $stmt = $db->prepare("
        SELECT u.id, u.full_name, u.username, u.assignment_area,
               COALESCE(q.target_hh, 0)         AS target_hh,
               COALESCE(q.target_wra, 0)         AS target_wra,
               COALESCE(q.target_children, 0)    AS target_children,
               q.period_start, q.period_end,
               COUNT(DISTINCT h.id)                                                     AS total_hh,
               SUM(CASE WHEN h.status = 'completed' THEN 1 ELSE 0 END)                 AS completed_hh,
               SUM(CASE WHEN h.day2_eligible = 1 THEN 1 ELSE 0 END)                    AS day2_eligible,
               SUM(CASE WHEN h.day2_completed = 1 THEN 1 ELSE 0 END)                   AS day2_done,
               SUM(CASE WHEN r.type = 'wra' AND r.recall_status = 'completed' THEN 1 ELSE 0 END)         AS completed_wra,
               SUM(CASE WHEN r.type = 'child_0_5' AND r.recall_status = 'completed' THEN 1 ELSE 0 END)  AS completed_children
        FROM users u
        LEFT JOIN quota q        ON q.interviewer_id = u.id
        LEFT JOIN households h   ON h.assigned_interviewer_id = u.id
        LEFT JOIN respondents r  ON r.household_id = h.id
        WHERE u.role = 'interviewer' AND u.is_active = 1
        GROUP BY u.id
        ORDER BY u.full_name
    ");
    $stmt->execute();

    // Day 2 pending list
    $stmt2 = $db->prepare("
        SELECT h.*, u.full_name AS interviewer_name
        FROM households h
        JOIN users u ON h.assigned_interviewer_id = u.id
        WHERE h.day2_eligible = 1 AND h.day2_completed = 0
        ORDER BY h.day2_scheduled_date
    ");
    $stmt2->execute();

    json_response([
        'interviewers'  => $stmt->fetchAll(),
        'day2_pending'  => $stmt2->fetchAll(),
    ]);
}

// PUT — set quota for an interviewer
if ($method === 'PUT') {
    $data = get_json_body();
    $stmt = $db->prepare("
        INSERT INTO quota (interviewer_id, target_hh, target_wra, target_children, period_start, period_end)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            target_hh       = VALUES(target_hh),
            target_wra      = VALUES(target_wra),
            target_children = VALUES(target_children),
            period_start    = VALUES(period_start),
            period_end      = VALUES(period_end)
    ");
    $stmt->execute([
        (int)$data['interviewer_id'],
        (int)($data['target_hh'] ?? 0),
        (int)($data['target_wra'] ?? 0),
        (int)($data['target_children'] ?? 0),
        $data['period_start'] ?? null,
        $data['period_end']   ?? null,
    ]);
    json_response(['success' => true]);
}

// POST — mark day2 completed
if ($method === 'POST') {
    $data = get_json_body();
    $db->prepare("UPDATE households SET day2_completed = 1 WHERE id = ?")
       ->execute([(int)$data['household_id']]);
    json_response(['success' => true]);
}

json_response(['error' => 'Not found'], 404);
