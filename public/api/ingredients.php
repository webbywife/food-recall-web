<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

require_auth();

$db     = Database::get();
$method = $_SERVER['REQUEST_METHOD'];

// GET — list by food_item_id
if ($method === 'GET') {
    $fid  = (int)($_GET['food_item_id'] ?? 0);
    if (!$fid) json_response(['error' => 'food_item_id required'], 400);

    $stmt = $db->prepare("
        SELECT * FROM food_added_ingredients
        WHERE food_item_id = ? ORDER BY sort_order, id
    ");
    $stmt->execute([$fid]);
    json_response(['ingredients' => $stmt->fetchAll()]);
}

// POST — batch save (replaces all for a food_item_id)
if ($method === 'POST') {
    $data  = get_json_body();
    $fid   = (int)($data['food_item_id'] ?? 0);
    $items = $data['ingredients'] ?? [];

    if (!$fid) json_response(['error' => 'food_item_id required'], 400);

    // Clear existing, then insert fresh
    $db->prepare("DELETE FROM food_added_ingredients WHERE food_item_id = ?")
       ->execute([$fid]);

    if (!empty($items)) {
        $stmt = $db->prepare("
            INSERT INTO food_added_ingredients
                (food_item_id, ingredient_name, fct_food_id, amount_desc,
                 amount_grams, energy_kcal, protein_g, fat_g, carbs_g, fiber_g, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($items as $idx => $ing) {
            $stmt->execute([
                $fid,
                $ing['ingredient_name'],
                $ing['fct_food_id']  ?? null,
                $ing['amount_desc']  ?? null,
                (float)($ing['amount_grams']  ?? 0),
                (float)($ing['energy_kcal']   ?? 0),
                (float)($ing['protein_g']     ?? 0),
                (float)($ing['fat_g']         ?? 0),
                (float)($ing['carbs_g']       ?? 0),
                (float)($ing['fiber_g']       ?? 0),
                $idx,
            ]);
        }
    }

    json_response(['success' => true]);
}

json_response(['error' => 'Not found'], 404);
