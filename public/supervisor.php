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
<title>Supervisor Portal | 24HR Food Recall</title>
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="app-page">

<nav class="topnav">
  <div class="topnav-left">
    <span class="nav-title">Supervisor Dashboard</span>
  </div>
  <div class="topnav-right">
    <a href="analytics.php" class="nav-analytics-link">Analytics</a>
    <span class="nav-user"><?= htmlspecialchars($user['full_name']) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</nav>

<div class="supervisor-wrap">
  <!-- Summary cards -->
  <div class="stats-row" id="statsRow">
    <div class="stat-card"><div class="stat-value" id="statTotalHH">—</div><div class="stat-label">Total Households</div></div>
    <div class="stat-card accent"><div class="stat-value" id="statCompletedHH">—</div><div class="stat-label">Completed</div></div>
    <div class="stat-card gold"><div class="stat-value" id="statDay2">—</div><div class="stat-label">Day 2 Pending</div></div>
    <div class="stat-card"><div class="stat-value" id="statInterviewers">—</div><div class="stat-label">Interviewers</div></div>
  </div>

  <!-- Manage Households -->
  <section class="sv-section">
    <div class="sv-section-header">
      <h3>Manage Households</h3>
      <div style="display:flex;gap:.5rem">
        <button class="btn-sm btn-primary" onclick="Supervisor.openAddHHModal()">+ Add Household</button>
        <button class="btn-sm" onclick="Supervisor.openCSVModal()">Upload CSV</button>
      </div>
    </div>
    <div class="table-wrap">
      <table class="data-table" id="hhTable">
        <thead>
          <tr>
            <th>HH ID</th>
            <th>Municipality</th>
            <th>Barangay</th>
            <th>Interviewer</th>
            <th>Respondents</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="hhTableBody"></tbody>
      </table>
    </div>
  </section>

  <!-- Interviewer quota table -->
  <section class="sv-section">
    <div class="sv-section-header">
      <h3>Interviewer Quota &amp; Progress</h3>
      <button class="btn-sm" onclick="Supervisor.reload()">Refresh</button>
    </div>
    <div class="table-wrap">
      <table class="data-table" id="quotaTable">
        <thead>
          <tr>
            <th>Interviewer</th>
            <th>Area</th>
            <th>HH Completed</th>
            <th>WRA Completed</th>
            <th>Children Completed</th>
            <th>Day 2</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="quotaBody"></tbody>
      </table>
    </div>
  </section>

  <!-- Day 2 Pending -->
  <section class="sv-section">
    <div class="sv-section-header">
      <h3>Day 2 Recall — Pending</h3>
    </div>
    <div class="table-wrap">
      <table class="data-table" id="day2Table">
        <thead>
          <tr>
            <th>HH ID</th>
            <th>Municipality</th>
            <th>Barangay</th>
            <th>Interviewer</th>
            <th>Scheduled Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="day2Body"></tbody>
      </table>
    </div>
  </section>
</div>

<!-- Add Household Modal -->
<div id="addHHModal" class="modal-overlay" hidden onclick="if(event.target===this)Supervisor.closeAddHHModal()">
  <div class="modal">
    <div class="modal-header">
      <h3>Add Household</h3>
      <button class="modal-close" onclick="Supervisor.closeAddHHModal()">&#x2715;</button>
    </div>
    <div class="modal-body">
      <div class="field-row">
        <div class="field">
          <label>HH ID <span style="color:var(--red)">*</span></label>
          <input type="text" id="hhID" placeholder="e.g. NCR-MNL-010">
        </div>
        <div class="field">
          <label>Interviewer</label>
          <select id="hhInterviewer"><option value="">— None —</option></select>
        </div>
      </div>
      <div class="field-row">
        <div class="field">
          <label>Region</label>
          <input type="text" id="hhRegion" placeholder="e.g. NCR">
        </div>
        <div class="field">
          <label>Province</label>
          <input type="text" id="hhProvince" placeholder="e.g. Metro Manila">
        </div>
      </div>
      <div class="field-row">
        <div class="field">
          <label>Municipality / City</label>
          <input type="text" id="hhMunicipality" placeholder="e.g. Manila">
        </div>
        <div class="field">
          <label>Barangay</label>
          <input type="text" id="hhBarangay" placeholder="e.g. Paco">
        </div>
      </div>
      <div class="field">
        <label>Address</label>
        <input type="text" id="hhAddress" placeholder="Street address (optional)">
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin:1.25rem 0 .75rem;border-bottom:1px solid var(--border);padding-bottom:.5rem">
        <h4 style="margin:0;font-size:.9rem;font-weight:600">Respondents</h4>
        <button type="button" class="btn-sm" onclick="Supervisor.addRespondentRow()">+ Add Row</button>
      </div>
      <div id="respondentRows"></div>

      <button class="btn-primary btn-full" style="margin-top:1.25rem" onclick="Supervisor.saveHousehold()">Save Household</button>
    </div>
  </div>
</div>

<!-- CSV Upload Modal -->
<div id="csvModal" class="modal-overlay" hidden onclick="if(event.target===this)Supervisor.closeCSVModal()">
  <div class="modal">
    <div class="modal-header">
      <h3>Bulk Upload — CSV</h3>
      <button class="modal-close" onclick="Supervisor.closeCSVModal()">&#x2715;</button>
    </div>
    <div class="modal-body">
      <p style="font-size:.875rem;color:var(--text-muted);margin:0 0 .75rem">
        Required columns (one row per respondent; same <code>hh_id</code> = same household):
      </p>
      <code class="csv-col-hint">hh_id, region, province, municipality, barangay, address, interviewer_username, respondent_name, respondent_age, respondent_sex, respondent_type</code>
      <p style="font-size:.8rem;color:var(--text-muted);margin:.75rem 0 1rem">
        <strong>respondent_type</strong>: <code>wra</code> | <code>child_0_5</code> | <code>other</code> &nbsp;·&nbsp;
        <strong>respondent_sex</strong>: <code>male</code> | <code>female</code>
      </p>
      <div class="csv-upload-zone" id="csvUploadZone" onclick="document.getElementById('csvFileInput').click()">
        <div style="font-size:1.25rem">📄</div>
        <div>Click to select CSV file</div>
        <div style="font-size:.8rem;color:var(--text-muted)" id="csvFileName">No file selected</div>
      </div>
      <input type="file" id="csvFileInput" accept=".csv,text/csv" style="display:none" onchange="Supervisor.previewCSV(this)">
      <div id="csvPreview" style="display:none;margin-top:1rem"></div>
      <div id="csvResult"  style="display:none;margin-top:.75rem"></div>
      <button class="btn-primary btn-full" style="margin-top:1rem" id="csvImportBtn" disabled onclick="Supervisor.importCSV()">Import</button>
    </div>
  </div>
</div>

<!-- Set Quota Modal -->
<div id="quotaModal" class="modal-overlay" hidden onclick="if(event.target===this)Supervisor.closeQuotaModal()">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3>Set Quota — <span id="quotaModalName"></span></h3>
      <button class="modal-close" onclick="Supervisor.closeQuotaModal()">&#x2715;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="quotaInterviewerId">
      <div class="field">
        <label>Target Households</label>
        <input type="number" id="qTargetHH" min="0" value="20">
      </div>
      <div class="field">
        <label>Target WRA</label>
        <input type="number" id="qTargetWRA" min="0" value="15">
      </div>
      <div class="field">
        <label>Target Children (0–5)</label>
        <input type="number" id="qTargetChildren" min="0" value="10">
      </div>
      <div class="field-row">
        <div class="field">
          <label>Period Start</label>
          <input type="date" id="qPeriodStart">
        </div>
        <div class="field">
          <label>Period End</label>
          <input type="date" id="qPeriodEnd">
        </div>
      </div>
      <button class="btn-primary btn-full" onclick="Supervisor.saveQuota()">Save Quota</button>
    </div>
  </div>
</div>

<script src="assets/js/app.js"></script>
<script src="assets/js/supervisor.js"></script>
</body>
</html>
