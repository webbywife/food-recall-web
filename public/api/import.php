<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

start_session();
header('Content-Type: application/json');

$user = require_auth();
if ($user['role'] !== 'supervisor') json_response(['error' => 'Forbidden'], 403);
verify_csrf();

$db = Database::get();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'POST required'], 405);
if (empty($_FILES['file'])) json_response(['error' => 'No file uploaded'], 400);

$handle = fopen($_FILES['file']['tmp_name'], 'r');
if (!$handle) json_response(['error' => 'Cannot read file'], 500);

// Read and normalize header row
$rawHeader = fgetcsv($handle);
if (!$rawHeader) json_response(['error' => 'Empty CSV'], 400);
$header = array_map(fn($h) => trim(strtolower(str_replace([' ', '-'], '_', $h))), $rawHeader);
$col    = array_flip($header);

// Validate required columns
foreach (['hh_id', 'respondent_name'] as $req) {
    if (!isset($col[$req])) json_response(['error' => "Missing required column: $req"], 400);
}

// Cache interviewers by username
$interviewers = [];
foreach ($db->query("SELECT id, username FROM users WHERE role='interviewer'")->fetchAll() as $u) {
    $interviewers[strtolower($u['username'])] = $u['id'];
}

$createdHH   = 0;
$createdResp = 0;
$skipped     = 0;
$errors      = [];
$hhCache     = [];
$rowNum      = 1;

$get = fn($row, $k) => isset($col[$k], $row[$col[$k]]) ? trim($row[$col[$k]]) : '';

while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;
    $hh_id     = $get($row, 'hh_id');
    $resp_name = $get($row, 'respondent_name');

    if (!$hh_id) { $errors[] = "Row $rowNum: missing hh_id"; $skipped++; continue; }

    // Find or create household
    if (!isset($hhCache[$hh_id])) {
        $stmt = $db->prepare("SELECT id FROM households WHERE hh_id = ?");
        $stmt->execute([$hh_id]);
        $eid = $stmt->fetchColumn();

        if ($eid) {
            $hhCache[$hh_id] = (int)$eid;
        } else {
            $iUser = strtolower($get($row, 'interviewer_username'));
            $iid   = $iUser ? ($interviewers[$iUser] ?? null) : null;
            $hhSize = $get($row, 'household_size');
            $db->prepare("
                INSERT INTO households (hh_id, region, province, municipality, barangay, address, household_size, assigned_interviewer_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $hh_id,
                $get($row, 'region')       ?: null,
                $get($row, 'province')     ?: null,
                $get($row, 'municipality') ?: null,
                $get($row, 'barangay')     ?: null,
                $get($row, 'address')      ?: null,
                ($hhSize !== '') ? (int)$hhSize : null,
                $iid,
            ]);
            $hhCache[$hh_id] = (int)$db->lastInsertId();
            $createdHH++;
        }
    }

    // Add respondent (skip if name missing)
    if (!$resp_name) { $skipped++; continue; }

    $age  = $get($row, 'respondent_age');
    $sex  = $get($row, 'respondent_sex');
    $type = $get($row, 'respondent_type') ?: 'other';
    if (!in_array($type, ['wra', 'child_0_5', 'other'])) $type = 'other';
    if (!in_array($sex,  ['male', 'female']))              $sex  = null;

    $db->prepare("
        INSERT INTO respondents (household_id, name, age, sex, type)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([
        $hhCache[$hh_id],
        $resp_name,
        ($age !== '') ? (int)$age : null,
        $sex,
        $type,
    ]);
    $createdResp++;
}

fclose($handle);

json_response([
    'success'             => true,
    'created_households'  => $createdHH,
    'created_respondents' => $createdResp,
    'skipped'             => $skipped,
    'errors'              => array_slice($errors, 0, 10),
]);
