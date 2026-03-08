<?php
require_once __DIR__ . '/../includes/auth.php';
start_session();
$user = current_user();
if (!$user || $user['role'] !== 'supervisor') {
    header('Location: index.php'); exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics | 24HR Food Recall</title>
<link rel="stylesheet" href="assets/css/app.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="app-page">

<nav class="topnav">
  <div class="topnav-left">
    <a href="supervisor.php" class="nav-back-link">← Dashboard</a>
    <span class="nav-title">Analytics Dashboard</span>
  </div>
  <div class="topnav-right">
    <span class="nav-user"><?= htmlspecialchars($user['full_name']) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</nav>

<div class="supervisor-wrap" id="analyticsWrap">
  <div class="anlt-loading">Loading analytics…</div>
</div>

<script src="assets/js/app.js"></script>
<script src="assets/js/analytics.js"></script>
</body>
</html>
