<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

require_auth();

$db     = Database::get();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') json_response(['error' => 'Method not allowed'], 405);

// GET single food by id
if (isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM fct_foods WHERE id = ? AND is_active = 1");
    $stmt->execute([(int)$_GET['id']]);
    json_response(['food' => $stmt->fetch()]);
}

// GET list of food groups
if (isset($_GET['groups'])) {
    $stmt = $db->query("SELECT DISTINCT food_group FROM fct_foods WHERE is_active = 1 ORDER BY food_group");
    json_response(['groups' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
}

// GET search by query and/or group
$q     = trim($_GET['q'] ?? '');
$group = $_GET['group'] ?? '';
$limit = min((int)($_GET['limit'] ?? 25), 60);

if ($q) {
    $like  = "%$q%";
    $start = "$q%";
    $stmt  = $db->prepare("
        SELECT id, food_code, food_name, local_name, food_group,
               energy_kcal, protein_g, fat_g, carbs_g, fiber_g,
               measure_1_desc, measure_1_grams,
               measure_2_desc, measure_2_grams,
               measure_3_desc, measure_3_grams
        FROM fct_foods
        WHERE is_active = 1
          AND (food_name LIKE ? OR local_name LIKE ?)
        ORDER BY
            CASE WHEN food_name LIKE ? THEN 0 ELSE 1 END,
            food_name
        LIMIT ?
    ");
    $stmt->execute([$like, $like, $start, $limit]);
} elseif ($group) {
    $stmt = $db->prepare("
        SELECT id, food_code, food_name, local_name, food_group,
               energy_kcal, protein_g, fat_g, carbs_g, fiber_g,
               measure_1_desc, measure_1_grams,
               measure_2_desc, measure_2_grams,
               measure_3_desc, measure_3_grams
        FROM fct_foods
        WHERE is_active = 1 AND food_group = ?
        ORDER BY food_name
        LIMIT ?
    ");
    $stmt->execute([$group, $limit]);
} else {
    // Return first 20 alphabetically as a default
    $stmt = $db->prepare("
        SELECT id, food_code, food_name, local_name, food_group,
               energy_kcal, protein_g, fat_g, carbs_g, fiber_g,
               measure_1_desc, measure_1_grams,
               measure_2_desc, measure_2_grams,
               measure_3_desc, measure_3_grams
        FROM fct_foods WHERE is_active = 1 ORDER BY food_name LIMIT ?
    ");
    $stmt->execute([$limit]);
}

json_response(['foods' => $stmt->fetchAll()]);
