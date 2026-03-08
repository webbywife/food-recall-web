<?php
require_once __DIR__ . '/../includes/auth.php';
start_session();
$user = current_user();
if (!$user) { header('Location: index.php'); exit; }
$back = $user['role'] === 'supervisor' ? 'supervisor.php' : 'interviewer.php';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Help & Documentation | 24HR Food Recall</title>
<link rel="stylesheet" href="assets/css/app.css">
<style>
.help-wrap   { max-width: 900px; margin: 2rem auto; padding: 0 1.5rem 4rem; }
.help-toc    { background: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.25rem 1.5rem; margin-bottom: 2rem; }
.help-toc h3 { margin: 0 0 .75rem; font-size: 1rem; }
.help-toc ol { margin: 0; padding-left: 1.25rem; line-height: 2; }
.help-toc a  { color: var(--green-800); text-decoration: none; font-size: .9rem; }
.help-toc a:hover { text-decoration: underline; }
.help-section        { margin-bottom: 2.5rem; }
.help-section h2     { font-size: 1.25rem; color: var(--green-900); border-bottom: 2px solid var(--green-100); padding-bottom: .5rem; margin-bottom: 1rem; }
.help-section h3     { font-size: 1rem; font-weight: 700; margin: 1.25rem 0 .5rem; color: var(--text); }
.help-section p      { line-height: 1.7; color: var(--text-2); margin-bottom: .75rem; font-size: .9rem; }
.help-section ul,
.help-section ol     { padding-left: 1.4rem; line-height: 1.9; color: var(--text-2); font-size: .9rem; margin-bottom: .75rem; }
.help-badge  { display: inline-block; padding: .15rem .55rem; border-radius: 20px; font-size: .75rem; font-weight: 700; vertical-align: middle; }
.hb-green    { background: var(--green-100); color: var(--green-900); }
.hb-gold     { background: var(--gold-light); color: #7a5c00; }
.hb-blue     { background: #e3f2fd; color: #1565c0; }
.hb-red      { background: var(--red-light); color: var(--red); }
.pass-card   { background: var(--white); border: 1px solid var(--border); border-left: 4px solid var(--green-600); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: .85rem; }
.pass-card h4 { margin: 0 0 .35rem; font-size: .95rem; color: var(--green-900); }
.pass-card p  { margin: 0; font-size: .875rem; color: var(--text-2); line-height: 1.6; }
.tip-box     { background: #fff8e1; border: 1px solid #ffe082; border-radius: var(--radius-sm); padding: .75rem 1rem; font-size: .85rem; color: #5d4037; margin: .75rem 0; }
.tip-box strong { color: #e65100; }
.kbd         { background: #f5f5f5; border: 1px solid #ccc; border-radius: 4px; padding: .1rem .4rem; font-family: monospace; font-size: .8rem; }
.csv-example { background: #f8f9fa; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: .75rem 1rem; font-family: monospace; font-size: .75rem; overflow-x: auto; white-space: nowrap; color: var(--text-2); margin: .75rem 0; }
table.help-table { width: 100%; border-collapse: collapse; font-size: .875rem; margin: .75rem 0; }
table.help-table th { background: var(--green-50); color: var(--green-900); text-align: left; padding: .5rem .75rem; border: 1px solid var(--border); font-weight: 600; }
table.help-table td { padding: .45rem .75rem; border: 1px solid var(--border); color: var(--text-2); vertical-align: top; }
</style>
</head>
<body class="app-page">

<nav class="topnav">
  <div class="topnav-left">
    <a href="<?= $back ?>" class="nav-back-link">← Back</a>
    <span class="nav-title">Help &amp; Documentation</span>
  </div>
  <div class="topnav-right">
    <span class="nav-user"><?= htmlspecialchars($user['full_name']) ?></span>
    <a href="logout.php" class="nav-logout">Logout</a>
  </div>
</nav>

<div class="help-wrap">

  <div class="help-toc">
    <h3>Table of Contents</h3>
    <ol>
      <li><a href="#overview">Overview</a></li>
      <li><a href="#roles">User Roles</a></li>
      <li><a href="#supervisor">Supervisor Guide</a>
        <ol>
          <li><a href="#manage-hh">Managing Households</a></li>
          <li><a href="#csv-upload">Bulk CSV Upload</a></li>
          <li><a href="#quotas">Setting Quotas</a></li>
          <li><a href="#day2">Day 2 Recall Tracking</a></li>
          <li><a href="#analytics">Analytics Dashboard</a></li>
        </ol>
      </li>
      <li><a href="#interviewer">Interviewer Guide</a>
        <ol>
          <li><a href="#pass1">Pass 1 — Quick List</a></li>
          <li><a href="#pass2">Pass 2 — Forgotten Foods</a></li>
          <li><a href="#pass3">Pass 3 — Time &amp; Occasion</a></li>
          <li><a href="#pass4">Pass 4 — Detail Cycle</a></li>
          <li><a href="#pass5">Pass 5 — Review</a></li>
        </ol>
      </li>
      <li><a href="#reni">RENI Adequacy Reference</a></li>
      <li><a href="#tips">Field Tips</a></li>
    </ol>
  </div>

  <!-- ── 1. Overview ─────────────────────────────────── -->
  <section class="help-section" id="overview">
    <h2>1. Overview</h2>
    <p>This system is a web-based <strong>24-Hour Dietary Recall (24HR)</strong> tool designed for the National Nutrition Survey (NNS) of the Philippines, developed in accordance with FNRI-DOST protocols. It collects individual dietary intake data from:</p>
    <ul>
      <li><span class="help-badge hb-green">WRA</span> Women of Reproductive Age (15–49 years)</li>
      <li><span class="help-badge hb-blue">Child 0–5</span> Children aged 0–5 years (proxy interview via mother/caregiver)</li>
    </ul>
    <p>The interview follows the <strong>5-Pass AMPM method</strong> and calculates nutrient intakes against the <strong>Philippine Recommended Energy and Nutrient Intakes (RENI 2015)</strong>. Food composition data is sourced from the <strong>FNRI Food Composition Tables (2013/2020)</strong>.</p>
  </section>

  <!-- ── 2. User Roles ───────────────────────────────── -->
  <section class="help-section" id="roles">
    <h2>2. User Roles</h2>
    <table class="help-table">
      <thead><tr><th>Role</th><th>Access</th><th>Responsibilities</th></tr></thead>
      <tbody>
        <tr>
          <td><span class="help-badge hb-gold">Supervisor</span></td>
          <td>Full access</td>
          <td>Add/import households, assign interviewers, set quotas, monitor progress, manage Day 2 recalls, view analytics</td>
        </tr>
        <tr>
          <td><span class="help-badge hb-green">Interviewer</span></td>
          <td>Assigned households only</td>
          <td>Conduct 24-hour dietary recall interviews using the 5-pass method</td>
        </tr>
      </tbody>
    </table>
  </section>

  <!-- ── 3. Supervisor Guide ────────────────────────── -->
  <section class="help-section" id="supervisor">
    <h2>3. Supervisor Guide</h2>

    <h3 id="manage-hh">3.1 Managing Households</h3>
    <p>From the Supervisor Dashboard, the <strong>Manage Households</strong> section lists all enrolled households. To add a new household manually:</p>
    <ol>
      <li>Click <strong>+ Add Household</strong></li>
      <li>Fill in the HH ID, location details (Region, Province, Municipality, Barangay), address, and household size</li>
      <li>Select the assigned interviewer</li>
      <li>Add one or more respondents using <strong>+ Add Row</strong> — enter name, age, sex, and type (WRA or Child 0–5)</li>
      <li>Click <strong>Save Household</strong></li>
    </ol>
    <div class="tip-box"><strong>HH ID format:</strong> Use a consistent format such as <code>NCR-MNL-001</code> (Region-Municipality-Number) for easy identification in reports.</div>

    <h3 id="csv-upload">3.2 Bulk CSV Upload</h3>
    <p>To enroll multiple households at once, click <strong>Upload CSV</strong>. The CSV must have the following columns (one row per respondent):</p>
    <div class="csv-example">hh_id, region, province, municipality, barangay, address, household_size, interviewer_username, respondent_name, respondent_age, respondent_sex, respondent_type</div>
    <ul>
      <li><strong>respondent_type:</strong> <code>wra</code>, <code>child_0_5</code>, or <code>other</code></li>
      <li><strong>respondent_sex:</strong> <code>male</code> or <code>female</code></li>
      <li>Multiple rows with the same <code>hh_id</code> = multiple respondents in the same household</li>
      <li>If a household already exists, only the respondent will be added (no duplicate household)</li>
    </ul>
    <p>After selecting the file, a preview of the first 5 rows appears. Click <strong>Import</strong> to proceed.</p>

    <h3 id="quotas">3.3 Setting Quotas</h3>
    <p>Click <strong>Set Quota</strong> next to any interviewer to define their targets:</p>
    <ul>
      <li><strong>Target Households</strong> — total HH to complete</li>
      <li><strong>Target WRA</strong> — number of WRA respondents</li>
      <li><strong>Target Children (0–5)</strong> — number of child respondents</li>
      <li><strong>Period Start / End</strong> — data collection window</li>
    </ul>
    <p>Progress bars update automatically as interviews are completed.</p>

    <h3 id="day2">3.4 Day 2 Recall Tracking</h3>
    <p>A 20% random sample of completed households is automatically scheduled for a second-day recall (3–10 days after Day 1). The <strong>Day 2 Recall — Pending</strong> table shows:</p>
    <ul>
      <li>Households selected for Day 2</li>
      <li>Scheduled interview date (highlighted in <span class="help-badge hb-red">red</span> if overdue)</li>
    </ul>
    <p>Click <strong>Mark Done</strong> once the Day 2 recall has been completed.</p>

    <h3 id="analytics">3.5 Analytics Dashboard</h3>
    <p>Click <strong>Analytics</strong> in the top navigation to access the analytics dashboard. It shows:</p>
    <table class="help-table">
      <thead><tr><th>Panel</th><th>What It Shows</th></tr></thead>
      <tbody>
        <tr><td>Field Coverage</td><td>Completion rate by municipality and barangay</td></tr>
        <tr><td>Interviewer Performance</td><td>HH and respondent completion rate per interviewer</td></tr>
        <tr><td>Mean Daily Intakes</td><td>Average energy, protein, fat, carbs, fiber vs. RENI by respondent group</td></tr>
        <tr><td>Energy Adequacy</td><td>% of respondents who are adequate, below, or deficient in energy vs. RENI</td></tr>
        <tr><td>Macronutrient Distribution</td><td>% of total energy from protein, fat, and carbohydrates</td></tr>
        <tr><td>Food Group Frequency</td><td>Most commonly reported FNRI food groups</td></tr>
        <tr><td>Meal Occasion Distribution</td><td>Breakfast, lunch, dinner, snack patterns</td></tr>
        <tr><td>Place of Consumption</td><td>Home, school, carenderia, street vendor, etc.</td></tr>
        <tr><td>Top 10 Foods</td><td>Most frequently reported foods with average portion sizes</td></tr>
      </tbody>
    </table>
  </section>

  <!-- ── 4. Interviewer Guide ───────────────────────── -->
  <section class="help-section" id="interviewer">
    <h2>4. Interviewer Guide</h2>
    <p>After logging in, you will see your assigned households. Select a household, then choose a respondent to start or resume a recall interview. The interview follows the <strong>5-Pass Method</strong>:</p>

    <div class="pass-card" id="pass1">
      <h4>Pass 1 — Quick List</h4>
      <p>Ask the respondent to freely recall <strong>all foods and drinks consumed in the past 24 hours</strong> (from midnight to midnight). Do not interrupt — record every item mentioned as a quick list entry. Click <strong>+ Add Food</strong> for each item. Do not ask for details yet.</p>
    </div>

    <div class="pass-card" id="pass2">
      <h4>Pass 2 — Forgotten Foods Probes</h4>
      <p>Use the provided Filipino-specific probes to help the respondent recall any <strong>forgotten items</strong>. Categories include beverages, snacks, sweets, condiments, street food, and — for children — breastmilk/formula. Click each probe category and ask the respondent if they consumed anything from that group.</p>
    </div>

    <div class="pass-card" id="pass3">
      <h4>Pass 3 — Time &amp; Occasion</h4>
      <p>For each food item, record:</p>
      <ul>
        <li><strong>Meal Occasion</strong> — Breakfast, Lunch, Dinner, Morning Snack, etc.</li>
        <li><strong>Time Eaten</strong> — Approximate time (e.g. 07:30)</li>
        <li><strong>Where Eaten</strong> — Home, School, Carenderia, Restaurant, Street Vendor, etc.</li>
      </ul>
    </div>

    <div class="pass-card" id="pass4">
      <h4>Pass 4 — Detail Cycle</h4>
      <p>For each food item, collect 5 levels of detail:</p>
      <ol>
        <li><strong>Food Identification</strong> — Search the FNRI Food Composition Table (FCT) to match the food. Type at least 2 characters to search. Select the best match.</li>
        <li><strong>Brand Name</strong> — Record brand if it is a packaged/commercial product (optional).</li>
        <li><strong>Amount &amp; Cooking Method</strong> — Record how the food was prepared (boiled, fried, raw, etc.) and the amount consumed using a household measure (e.g., 1 cup, 1 medium piece). The system will convert to grams automatically.</li>
        <li><strong>Added Ingredients</strong> — Record any oil, sugar, salt, soy sauce, fish sauce, or other condiments used in cooking. Use the quick-add buttons or search the FCT for less common ingredients.</li>
        <li><strong>Nutrients</strong> — The system automatically calculates energy, protein, fat, carbs, and fiber from the FCT values plus any added ingredients. Review and click Save.</li>
      </ol>
      <div class="tip-box"><strong>Tip:</strong> For mixed dishes (e.g. adobo, sinigang), identify the main ingredient as the primary food and add the other components as added ingredients.</div>
    </div>

    <div class="pass-card" id="pass5">
      <h4>Pass 5 — Review &amp; Complete</h4>
      <p>Show the respondent the complete list of all recorded food items for confirmation. Ask: <em>"Is there anything else you consumed that we may have missed?"</em> Add any final items if needed. The system will display a <strong>RENI adequacy summary</strong> showing whether the respondent's intake is adequate, below, or deficient for key nutrients. Click <strong>Complete Interview</strong> to finalize.</p>
    </div>
  </section>

  <!-- ── 5. RENI Reference ───────────────────────────── -->
  <section class="help-section" id="reni">
    <h2>5. RENI Adequacy Reference</h2>
    <p>Nutrient intakes are compared against the <strong>Philippine RENI 2015</strong>. Adequacy is classified as:</p>
    <table class="help-table">
      <thead><tr><th>Classification</th><th>% of RENI</th><th>Indicator</th></tr></thead>
      <tbody>
        <tr><td><span class="help-badge hb-green">Adequate</span></td><td>≥ 90%</td><td>Green bar</td></tr>
        <tr><td><span class="help-badge hb-gold">Below</span></td><td>66–89%</td><td>Yellow bar</td></tr>
        <tr><td><span class="help-badge hb-red">Deficient</span></td><td>&lt; 66%</td><td>Red bar</td></tr>
        <tr><td><span class="help-badge" style="background:#fff3e0;color:#e65100">Excess</span></td><td>&gt; 120%</td><td>Orange bar</td></tr>
      </tbody>
    </table>
    <table class="help-table" style="margin-top:1rem">
      <thead><tr><th>Respondent Group</th><th>Energy (kcal)</th><th>Protein (g)</th></tr></thead>
      <tbody>
        <tr><td>WRA 19–29 years</td><td>2,000</td><td>55</td></tr>
        <tr><td>WRA 30–49 years</td><td>1,900</td><td>55</td></tr>
        <tr><td>Infant 0–6 months</td><td>570</td><td>9.1</td></tr>
        <tr><td>Infant 7–12 months</td><td>700</td><td>13.5</td></tr>
        <tr><td>Child 1–3 years</td><td>1,000</td><td>15</td></tr>
        <tr><td>Child 3–5 years</td><td>1,300</td><td>20</td></tr>
      </tbody>
    </table>
    <p>Source: <em>Recommended Energy and Nutrient Intakes (RENI), FNRI-DOST, 2015 Edition.</em></p>
  </section>

  <!-- ── 6. Field Tips ──────────────────────────────── -->
  <section class="help-section" id="tips">
    <h2>6. Field Tips</h2>

    <h3>For Children 0–5 (Proxy Interview)</h3>
    <p>The mother or primary caregiver answers on behalf of the child. Remind the proxy to recall only what the <strong>child</strong> consumed, not the whole household. For infants under 6 months, always ask about breastmilk and/or formula separately using the forgotten foods probe.</p>

    <h3>Keyboard Shortcuts</h3>
    <ul>
      <li><kbd class="kbd">Esc</kbd> — Close any open modal</li>
      <li>Click the <strong>dark overlay</strong> behind a modal to close it</li>
    </ul>

    <h3>Resuming an Interview</h3>
    <p>If an interview is interrupted, it is automatically saved at the current pass. The interviewer can return to the household and select the same respondent to resume from where they left off.</p>

    <h3>Data Entry Best Practices</h3>
    <ul>
      <li>Always confirm the 24-hour period with the respondent before starting (yesterday midnight to today midnight)</li>
      <li>Use local food names when searching the FCT — many foods have both English and Filipino names</li>
      <li>If a food is not found in the FCT, record the quick list name and leave the FCT field blank; the food will still be counted in the recall</li>
      <li>For amounts, use the respondent's own utensils as reference when possible (e.g., their specific cup or bowl)</li>
      <li>Record cooking oil amounts even if small — they contribute significantly to energy and fat intake</li>
    </ul>
  </section>

</div>

<script src="assets/js/app.js"></script>
</body>
</html>
