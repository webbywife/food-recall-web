<?php
require_once __DIR__ . '/../includes/auth.php';
start_session();
$user = current_user();
if (!$user || $user['role'] !== 'interviewer') {
    header('Location: index.php'); exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<title>Interviewer Portal | 24HR Food Recall</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#1B5E20">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="app-page">

<!-- Offline bar -->
<div id="offlineBar" class="offline-bar" hidden>
  <span>⚡ No internet connection — interviews are saved locally on this device.</span>
  <div class="offline-bar-right">
    <span id="offlineBadge" class="offline-badge"></span>
    <button id="syncBtn" class="btn-sync" hidden onclick="Offline.sync()">↑ Sync</button>
  </div>
</div>

<!-- Top Nav -->
<nav class="topnav">
  <div class="topnav-left">
    <button class="nav-back" id="btnBack" onclick="App.goBack()" hidden>
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 1.414L6.414 9H15a1 1 0 110 2H6.414l3.293 3.293a1 1 0 010 1.414z"/></svg>
    </button>
    <span class="nav-title" id="navTitle">My Households</span>
  </div>
  <div class="topnav-right">
    <a href="help.php" class="nav-analytics-link">Help</a>
    <span class="nav-user"><?= htmlspecialchars($user['full_name']) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</nav>

<!-- ═══════════════════════════════════════════
     VIEW: Household List
══════════════════════════════════════════════ -->
<div id="viewHouseholds" class="view">
  <div class="view-header">
    <h2>Assigned Households</h2>
    <p class="view-subtitle"><?= htmlspecialchars($user['assignment_area'] ?? '') ?></p>
  </div>
  <div class="search-bar">
    <input type="search" id="hhSearch" placeholder="Search by ID, barangay…" oninput="App.filterHouseholds(this.value)">
  </div>
  <div id="hhList" class="card-grid"></div>
</div>

<!-- ═══════════════════════════════════════════
     VIEW: Household Detail / Respondents
══════════════════════════════════════════════ -->
<div id="viewHousehold" class="view" hidden>
  <div class="view-header">
    <h2 id="hhDetailTitle"></h2>
    <p id="hhDetailAddr" class="view-subtitle"></p>
  </div>
  <h3 class="section-heading">Respondents</h3>
  <div id="respondentList" class="respondent-list"></div>
</div>

<!-- ═══════════════════════════════════════════
     VIEW: 5-Pass Interview
══════════════════════════════════════════════ -->
<div id="viewInterview" class="view" hidden>
  <!-- Respondent banner -->
  <div class="respondent-banner">
    <div>
      <div class="respondent-banner-name" id="intRespondentName"></div>
      <div class="respondent-banner-meta" id="intRespondentMeta"></div>
    </div>
    <div class="respondent-banner-hh" id="intHHId"></div>
  </div>

  <!-- Pass progress stepper -->
  <div class="pass-stepper">
    <?php
    $passes = ['Quick List','Forgotten Foods','Time & Occasion','Detail Cycle','Review'];
    foreach ($passes as $i => $p): $n = $i + 1; ?>
    <div class="step" id="step<?= $n ?>" data-pass="<?= $n ?>">
      <div class="step-circle"><?= $n ?></div>
      <div class="step-label"><?= $p ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Pass content -->
  <div id="passContent" class="pass-content"></div>
</div>

<!-- ═══════════════════════════════════════════
     FCT Search Modal
══════════════════════════════════════════════ -->
<div id="fctModal" class="modal-overlay" hidden>
  <div class="modal">
    <div class="modal-header">
      <h3>Search Food (FNRI FCT)</h3>
      <button class="modal-close" onclick="App.closeFCT()">&#x2715;</button>
    </div>
    <div class="modal-body">
      <input type="search" id="fctSearch" placeholder="Type food name or local name…" autocomplete="off"
             oninput="App.searchFCT(this.value)">
      <div class="fct-groups" id="fctGroups"></div>
      <div id="fctResults" class="fct-results"></div>
    </div>
  </div>
</div>

<script>
window.APP_USER = <?= json_encode($user) ?>;
</script>
<script src="assets/js/app.js"></script>
<script src="assets/js/idb.js"></script>
<script src="assets/js/offline.js"></script>
<script src="assets/js/interview.js"></script>
<script>Offline.init();</script>
</body>
</html>
