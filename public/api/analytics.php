<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user = require_auth();
if ($user['role'] !== 'supervisor') json_response(['error' => 'Forbidden'], 403);

$db = Database::get();

// 1. Summary counts
$summary = $db->query("
    SELECT
        (SELECT COUNT(*) FROM households) AS total_hh,
        (SELECT COUNT(*) FROM households WHERE status='completed') AS completed_hh,
        (SELECT COUNT(*) FROM respondents) AS total_respondents,
        (SELECT COUNT(*) FROM respondents WHERE recall_status='completed') AS completed_respondents,
        (SELECT COUNT(*) FROM respondents WHERE type='wra') AS total_wra,
        (SELECT COUNT(*) FROM respondents WHERE type='wra' AND recall_status='completed') AS completed_wra,
        (SELECT COUNT(*) FROM respondents WHERE type='child_0_5') AS total_children,
        (SELECT COUNT(*) FROM respondents WHERE type='child_0_5' AND recall_status='completed') AS completed_children,
        (SELECT COUNT(*) FROM recall_sessions WHERE status='completed') AS total_sessions
")->fetch();

// 2. Coverage by municipality
$coverage = $db->query("
    SELECT
        COALESCE(h.municipality, 'Unknown') AS municipality,
        COALESCE(h.region, '—') AS region,
        COUNT(DISTINCT h.id) AS total_hh,
        SUM(CASE WHEN h.status='completed' THEN 1 ELSE 0 END) AS completed_hh,
        COUNT(DISTINCT r.id) AS total_respondents,
        SUM(CASE WHEN r.recall_status='completed' THEN 1 ELSE 0 END) AS completed_respondents
    FROM households h
    LEFT JOIN respondents r ON r.household_id = h.id
    GROUP BY h.municipality, h.region
    ORDER BY h.region, h.municipality
")->fetchAll();

// 3. Mean daily intakes by respondent type (completed sessions only)
$mean_intakes = $db->query("
    SELECT
        resp.type AS respondent_type,
        COUNT(DISTINCT rs.id) AS n,
        ROUND(AVG(d.energy_kcal), 1) AS mean_energy,
        ROUND(AVG(d.protein_g),   2) AS mean_protein,
        ROUND(AVG(d.fat_g),       2) AS mean_fat,
        ROUND(AVG(d.carbs_g),     2) AS mean_carbs,
        ROUND(AVG(d.fiber_g),     2) AS mean_fiber
    FROM recall_sessions rs
    JOIN respondents resp ON rs.respondent_id = resp.id
    JOIN (
        SELECT session_id,
               SUM(energy_kcal) AS energy_kcal,
               SUM(protein_g)   AS protein_g,
               SUM(fat_g)       AS fat_g,
               SUM(carbs_g)     AS carbs_g,
               SUM(fiber_g)     AS fiber_g
        FROM recall_food_items
        WHERE is_deleted = 0
        GROUP BY session_id
    ) d ON d.session_id = rs.id
    WHERE rs.status = 'completed'
    GROUP BY resp.type
")->fetchAll();

// 4. Energy adequacy vs RENI (WRA: 2000 kcal, Children 0-5: ~1000 kcal average)
// Adequate ≥90%, Below 66-89%, Deficient <66%
$energy_adequacy = $db->query("
    SELECT
        resp.type,
        COUNT(*) AS n,
        SUM(CASE
            WHEN resp.type='wra'       AND d.energy >= 1800 THEN 1
            WHEN resp.type='child_0_5' AND d.energy >= 900  THEN 1
            ELSE 0 END) AS adequate,
        SUM(CASE
            WHEN resp.type='wra'       AND d.energy BETWEEN 1320 AND 1799 THEN 1
            WHEN resp.type='child_0_5' AND d.energy BETWEEN 660  AND 899  THEN 1
            ELSE 0 END) AS below,
        SUM(CASE
            WHEN resp.type='wra'       AND d.energy < 1320 THEN 1
            WHEN resp.type='child_0_5' AND d.energy < 660  THEN 1
            ELSE 0 END) AS deficient
    FROM recall_sessions rs
    JOIN respondents resp ON rs.respondent_id = resp.id
    JOIN (
        SELECT session_id, SUM(energy_kcal) AS energy
        FROM recall_food_items WHERE is_deleted=0
        GROUP BY session_id
    ) d ON d.session_id = rs.id
    WHERE rs.status='completed' AND resp.type IN ('wra','child_0_5')
    GROUP BY resp.type
")->fetchAll();

// 5. Food group consumption frequency
$food_groups = $db->query("
    SELECT
        COALESCE(f.food_group, 'Unclassified') AS food_group,
        COUNT(*) AS frequency,
        COUNT(DISTINCT rfi.session_id) AS sessions_with
    FROM recall_food_items rfi
    JOIN fct_foods f ON rfi.fct_food_id = f.id
    WHERE rfi.is_deleted = 0 AND rfi.fct_food_id IS NOT NULL
    GROUP BY f.food_group
    ORDER BY frequency DESC
    LIMIT 15
")->fetchAll();

// 6. Meal occasion distribution
$meal_occasions = $db->query("
    SELECT
        meal_occasion,
        COUNT(*) AS count,
        COUNT(DISTINCT session_id) AS sessions
    FROM recall_food_items
    WHERE is_deleted=0 AND meal_occasion IS NOT NULL
    GROUP BY meal_occasion
    ORDER BY FIELD(meal_occasion,
        'early_morning','breakfast','morning_snack','lunch',
        'afternoon_snack','dinner','evening_snack','overnight','other')
")->fetchAll();

// 7. Place of consumption
$places = $db->query("
    SELECT
        COALESCE(place_of_consumption, 'Not specified') AS place,
        COUNT(*) AS count
    FROM recall_food_items
    WHERE is_deleted=0
    GROUP BY place_of_consumption
    ORDER BY count DESC
")->fetchAll();

// 8. Top 10 most consumed foods
$top_foods = $db->query("
    SELECT
        COALESCE(rfi.food_name, rfi.quick_list_name) AS food_name,
        f.food_group,
        COUNT(*) AS frequency,
        ROUND(AVG(rfi.amount_grams), 1) AS avg_grams
    FROM recall_food_items rfi
    LEFT JOIN fct_foods f ON rfi.fct_food_id = f.id
    WHERE rfi.is_deleted=0
      AND (rfi.food_name IS NOT NULL OR rfi.quick_list_name IS NOT NULL)
    GROUP BY rfi.food_name, rfi.quick_list_name, f.food_group
    ORDER BY frequency DESC
    LIMIT 10
")->fetchAll();

// 9. Macronutrient distribution (% energy from protein, fat, carbs)
$macro_dist = $db->query("
    SELECT
        resp.type,
        ROUND(AVG(d.protein_g * 4 / NULLIF(d.energy_kcal, 0) * 100), 1) AS pct_protein,
        ROUND(AVG(d.fat_g    * 9 / NULLIF(d.energy_kcal, 0) * 100), 1) AS pct_fat,
        ROUND(AVG(d.carbs_g  * 4 / NULLIF(d.energy_kcal, 0) * 100), 1) AS pct_carbs
    FROM recall_sessions rs
    JOIN respondents resp ON rs.respondent_id = resp.id
    JOIN (
        SELECT session_id,
               SUM(energy_kcal) AS energy_kcal,
               SUM(protein_g)   AS protein_g,
               SUM(fat_g)       AS fat_g,
               SUM(carbs_g)     AS carbs_g
        FROM recall_food_items WHERE is_deleted=0
        GROUP BY session_id
    ) d ON d.session_id = rs.id
    WHERE rs.status='completed'
    GROUP BY resp.type
")->fetchAll();

// 10. Interviewer performance
$interviewer_perf = $db->query("
    SELECT
        u.full_name, u.username, u.assignment_area,
        COUNT(DISTINCT h.id) AS total_hh,
        SUM(CASE WHEN h.status='completed' THEN 1 ELSE 0 END) AS completed_hh,
        COUNT(DISTINCT r.id) AS total_respondents,
        SUM(CASE WHEN r.recall_status='completed' THEN 1 ELSE 0 END) AS completed_respondents
    FROM users u
    LEFT JOIN households h ON h.assigned_interviewer_id = u.id
    LEFT JOIN respondents r ON r.household_id = h.id
    WHERE u.role = 'interviewer'
    GROUP BY u.id
    ORDER BY completed_hh DESC
")->fetchAll();

json_response([
    'summary'           => $summary,
    'coverage'          => $coverage,
    'mean_intakes'      => $mean_intakes,
    'energy_adequacy'   => $energy_adequacy,
    'food_groups'       => $food_groups,
    'meal_occasions'    => $meal_occasions,
    'places'            => $places,
    'top_foods'         => $top_foods,
    'macro_dist'        => $macro_dist,
    'interviewer_perf'  => $interviewer_perf,
]);
