<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user   = require_auth();
$db     = Database::get();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
if ($method !== 'GET') verify_csrf();

// ── GET session details ────────────────────────────────────
if ($method === 'GET' && $action === 'get') {
    $sid = (int)($_GET['session_id'] ?? 0);

    $stmt = $db->prepare("
        SELECT rs.*,
               r.name AS respondent_name, r.age, r.sex, r.type AS respondent_type,
               h.hh_id, h.barangay, h.municipality
        FROM recall_sessions rs
        JOIN respondents r ON rs.respondent_id = r.id
        JOIN households  h ON rs.household_id  = h.id
        WHERE rs.id = ? AND rs.interviewer_id = ?
    ");
    $stmt->execute([$sid, $user['id']]);
    $session = $stmt->fetch();
    if (!$session) json_response(['error' => 'Session not found'], 404);

    $stmt2 = $db->prepare("
        SELECT rfi.*, f.food_name AS fct_food_name, f.food_group,
               f.measure_1_desc, f.measure_1_grams,
               f.measure_2_desc, f.measure_2_grams,
               f.measure_3_desc, f.measure_3_grams
        FROM recall_food_items rfi
        LEFT JOIN fct_foods f ON rfi.fct_food_id = f.id
        WHERE rfi.session_id = ? AND rfi.is_deleted = 0
        ORDER BY rfi.sequence_no
    ");
    $stmt2->execute([$sid]);
    $foodItems = $stmt2->fetchAll();

    // Attach added ingredients to each food item
    if (!empty($foodItems)) {
        $ids   = implode(',', array_map('intval', array_column($foodItems, 'id')));
        $stmt3 = $db->query("
            SELECT * FROM food_added_ingredients
            WHERE food_item_id IN ($ids) ORDER BY food_item_id, sort_order, id
        ");
        $allIngs = $stmt3->fetchAll();
        $byItem  = [];
        foreach ($allIngs as $ing) {
            $byItem[(int)$ing['food_item_id']][] = $ing;
        }
        foreach ($foodItems as &$fi) {
            $fi['added_ingredients'] = $byItem[(int)$fi['id']] ?? [];
        }
        unset($fi);
    }

    $session['food_items'] = $foodItems;
    json_response(['session' => $session]);
}

// ── POST start / resume session ────────────────────────────
if ($method === 'POST' && $action === 'start') {
    $data          = get_json_body();
    $respondent_id = (int)$data['respondent_id'];
    $household_id  = (int)$data['household_id'];
    $is_day2       = (int)($data['is_day2'] ?? 0);

    // Resume if incomplete session exists
    $stmt = $db->prepare("
        SELECT id FROM recall_sessions
        WHERE respondent_id = ? AND interviewer_id = ? AND status != 'completed'
        LIMIT 1
    ");
    $stmt->execute([$respondent_id, $user['id']]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        json_response(['session_id' => $existing, 'resumed' => true]);
    }

    $stmt = $db->prepare("
        INSERT INTO recall_sessions (respondent_id, household_id, interviewer_id, recall_date, is_day2)
        VALUES (?, ?, ?, CURDATE(), ?)
    ");
    $stmt->execute([$respondent_id, $household_id, $user['id'], $is_day2]);
    $sid = $db->lastInsertId();

    $db->prepare("UPDATE households SET status = 'in_progress' WHERE id = ? AND status = 'pending'")
       ->execute([$household_id]);

    $db->prepare("UPDATE respondents SET recall_status = 'in_progress' WHERE id = ?")
       ->execute([$respondent_id]);

    json_response(['session_id' => $sid, 'resumed' => false]);
}

// ── POST add food item ─────────────────────────────────────
if ($method === 'POST' && $action === 'add_food') {
    $data = get_json_body();
    $sid  = (int)$data['session_id'];

    $stmt = $db->prepare("SELECT COALESCE(MAX(sequence_no), 0) + 1 FROM recall_food_items WHERE session_id = ?");
    $stmt->execute([$sid]);
    $seq = (int)$stmt->fetchColumn();

    $stmt = $db->prepare("
        INSERT INTO recall_food_items (session_id, sequence_no, quick_list_name, source, added_pass)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $sid, $seq,
        $data['quick_list_name'],
        $data['source'] ?? 'quick_list',
        $data['pass'] ?? 1,
    ]);

    json_response(['success' => true, 'id' => $db->lastInsertId(), 'sequence_no' => $seq]);
}

// ── PUT update food item (passes 3, 4, 5) ─────────────────
if ($method === 'PUT' && $action === 'update_food') {
    $data    = get_json_body();
    $item_id = (int)$data['id'];

    $allowed = [
        'meal_occasion', 'meal_time', 'place_of_consumption',
        'fct_food_id', 'food_name', 'brand_name',
        'cooking_method', 'amount_grams', 'household_measure_desc',
        'household_measure_amount', 'energy_kcal', 'protein_g', 'fat_g',
        'carbs_g', 'fiber_g',
    ];

    $sets = [];
    $vals = [];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $data)) {
            $sets[] = "$f = ?";
            $vals[] = $data[$f];
        }
    }

    if (empty($sets)) json_response(['error' => 'No fields to update'], 400);

    $vals[] = $item_id;
    $db->prepare("UPDATE recall_food_items SET " . implode(', ', $sets) . " WHERE id = ?")
       ->execute($vals);

    json_response(['success' => true]);
}

// ── DELETE (soft) food item ────────────────────────────────
if ($method === 'DELETE' && $action === 'delete_food') {
    $item_id = (int)($_GET['item_id'] ?? 0);
    $db->prepare("UPDATE recall_food_items SET is_deleted = 1 WHERE id = ?")
       ->execute([$item_id]);
    json_response(['success' => true]);
}

// ── POST advance pass ──────────────────────────────────────
if ($method === 'POST' && $action === 'advance_pass') {
    $data      = get_json_body();
    $sid       = (int)$data['session_id'];
    $next_pass = (int)$data['next_pass'];

    $status_map = [
        2 => 'forgotten_foods',
        3 => 'time_occasion',
        4 => 'detail_cycle',
        5 => 'review',
        6 => 'completed',
    ];
    $status = $status_map[$next_pass] ?? 'completed';

    if ($status === 'completed') {
        $db->prepare("
            UPDATE recall_sessions
            SET current_pass = ?, status = 'completed', completed_at = NOW()
            WHERE id = ?
        ")->execute([$next_pass, $sid]);

        // Mark respondent completed
        $db->prepare("
            UPDATE respondents r
            JOIN recall_sessions rs ON rs.respondent_id = r.id
            SET r.recall_status = 'completed'
            WHERE rs.id = ?
        ")->execute([$sid]);

        // Check if all respondents in household are done
        checkHouseholdCompletion($db, $sid);

        // Day 2 scheduling — 20% probability, 3–10 day offset
        if (rand(1, 100) <= 20) {
            scheduleDay2($db, $sid);
        }
    } else {
        $db->prepare("
            UPDATE recall_sessions SET current_pass = ?, status = ? WHERE id = ?
        ")->execute([$next_pass, $status, $sid]);
    }

    json_response(['success' => true, 'status' => $status]);
}

// ── POST update session notes ──────────────────────────────
if ($method === 'POST' && $action === 'save_notes') {
    $data = get_json_body();
    $db->prepare("UPDATE recall_sessions SET notes = ? WHERE id = ? AND interviewer_id = ?")
       ->execute([$data['notes'], (int)$data['session_id'], $user['id']]);
    json_response(['success' => true]);
}

json_response(['error' => 'Not found'], 404);

// ─────────────────────────────────────────────────────────
function checkHouseholdCompletion(PDO $db, int $session_id): void {
    $row = $db->prepare("SELECT household_id FROM recall_sessions WHERE id = ?");
    $row->execute([$session_id]);
    $hid = $row->fetchColumn();
    if (!$hid) return;

    $stmt = $db->prepare("
        SELECT COUNT(*) FROM respondents WHERE household_id = ? AND recall_status != 'completed'
    ");
    $stmt->execute([$hid]);
    if ((int)$stmt->fetchColumn() === 0) {
        $db->prepare("UPDATE households SET status = 'completed' WHERE id = ?")
           ->execute([$hid]);
    }
}

function scheduleDay2(PDO $db, int $session_id): void {
    $row = $db->prepare("SELECT household_id FROM recall_sessions WHERE id = ?");
    $row->execute([$session_id]);
    $hid = $row->fetchColumn();
    if (!$hid) return;

    $offset = rand(3, 10);
    $date   = date('Y-m-d', strtotime("+$offset days"));

    $db->prepare("
        UPDATE households
        SET day2_eligible = 1, day2_scheduled_date = ?
        WHERE id = ? AND day2_eligible = 0
    ")->execute([$date, $hid]);
}
