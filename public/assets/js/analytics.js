/* ============================================================
   analytics.js — Analytics Dashboard
   ============================================================ */

const GROUP_LABEL = { wra: 'WRA', child_0_5: 'Children 0–5', other: 'Other' };

const RENI = {
  wra:       { energy: 2000, protein: 55, fiber: 25 },
  child_0_5: { energy: 1000, protein: 15, fiber: 10 },
  other:     { energy: 2000, protein: 55, fiber: 25 },
};

const PALETTE = [
  '#2d6a4f','#52b788','#95d5b2','#f4a261',
  '#e9c46a','#e63946','#457b9d','#1d3557','#adb5bd','#7b2d8b',
];

async function loadAnalytics() {
  const wrap = document.getElementById('analyticsWrap');
  try {
    const data = await api.get('api/analytics.php');
    wrap.innerHTML = '';
    renderSummary(wrap, data.summary);
    renderCoverage(wrap, data.coverage);
    renderInterviewerPerf(wrap, data.interviewer_perf);
    renderMeanIntakes(wrap, data.mean_intakes);
    renderEnergyAdequacy(wrap, data.energy_adequacy);
    renderMacroDist(wrap, data.macro_dist);
    renderFoodGroups(wrap, data.food_groups);
    renderMealOccasions(wrap, data.meal_occasions);
    renderPlaces(wrap, data.places);
    renderTopFoods(wrap, data.top_foods);
  } catch (e) {
    wrap.innerHTML = `<div class="sv-section" style="text-align:center;padding:3rem;color:var(--text-muted)">Failed to load analytics. Check console for details.</div>`;
    console.error(e);
  }
}

// ── 1. Summary ────────────────────────────────────────────
function renderSummary(wrap, s) {
  if (!s) return;
  const total = +s.total_respondents || 0;
  const done  = +s.completed_respondents || 0;
  const pct   = total > 0 ? Math.round(done / total * 100) : 0;
  const div   = document.createElement('div');
  div.className = 'stats-row';
  div.innerHTML = `
    <div class="stat-card">
      <div class="stat-value">${s.total_hh || 0}</div>
      <div class="stat-label">Total Households</div>
    </div>
    <div class="stat-card accent">
      <div class="stat-value">${s.completed_hh || 0}</div>
      <div class="stat-label">HH Completed</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">${done}/${total}</div>
      <div class="stat-label">Respondents Recalled</div>
    </div>
    <div class="stat-card gold">
      <div class="stat-value">${pct}%</div>
      <div class="stat-label">Overall Completion</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">${s.completed_wra || 0}/${s.total_wra || 0}</div>
      <div class="stat-label">WRA Completed</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">${s.completed_children || 0}/${s.total_children || 0}</div>
      <div class="stat-label">Children Completed</div>
    </div>
  `;
  wrap.appendChild(div);
}

// ── 2. Field Coverage ─────────────────────────────────────
function renderCoverage(wrap, rows) {
  if (!rows || !rows.length) return;
  const sec = mkSection('Field Coverage by Municipality');
  sec.innerHTML += `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Region</th><th>Municipality</th>
            <th>HH Total</th><th>HH Done</th>
            <th>Respondents</th><th>Recalled</th><th>Coverage</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => {
            const pct = +r.total_respondents > 0
              ? Math.round(+r.completed_respondents / +r.total_respondents * 100) : 0;
            return `<tr>
              <td>${r.region || '—'}</td>
              <td>${r.municipality || '—'}</td>
              <td>${r.total_hh}</td>
              <td>${r.completed_hh}</td>
              <td>${r.total_respondents}</td>
              <td>${r.completed_respondents}</td>
              <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                  <div class="progress-bar-wrap" style="flex:1">
                    <div class="progress-bar-fill" style="width:${pct}%"></div>
                  </div>
                  <span style="font-size:.8rem;color:var(--text-muted);min-width:2.5rem">${pct}%</span>
                </div>
              </td>
            </tr>`;
          }).join('')}
        </tbody>
      </table>
    </div>
  `;
  wrap.appendChild(sec);
}

// ── 3. Interviewer Performance ────────────────────────────
function renderInterviewerPerf(wrap, rows) {
  if (!rows || !rows.length) return;
  const sec = mkSection('Interviewer Performance');
  sec.innerHTML += `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Interviewer</th><th>Area</th>
            <th>HH Assigned</th><th>HH Completed</th>
            <th>Respondents</th><th>Recalled</th><th>Rate</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => {
            const pct = +r.total_respondents > 0
              ? Math.round(+r.completed_respondents / +r.total_respondents * 100) : 0;
            return `<tr>
              <td>
                <strong>${r.full_name}</strong><br>
                <small style="color:var(--text-muted)">${r.username}</small>
              </td>
              <td>${r.assignment_area || '—'}</td>
              <td>${r.total_hh}</td>
              <td>${r.completed_hh}</td>
              <td>${r.total_respondents}</td>
              <td>${r.completed_respondents}</td>
              <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                  <div class="progress-bar-wrap" style="flex:1">
                    <div class="progress-bar-fill" style="width:${pct}%"></div>
                  </div>
                  <span style="font-size:.8rem;color:var(--text-muted);min-width:2.5rem">${pct}%</span>
                </div>
              </td>
            </tr>`;
          }).join('')}
        </tbody>
      </table>
    </div>
  `;
  wrap.appendChild(sec);
}

// ── 4. Mean Daily Intakes ─────────────────────────────────
function renderMeanIntakes(wrap, rows) {
  if (!rows || !rows.length) {
    appendEmpty(wrap, 'Mean Daily Nutrient Intakes', 'No completed recalls yet.');
    return;
  }
  const sec = mkSection('Mean Daily Nutrient Intakes (Completed Recalls)');
  sec.innerHTML += `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Group</th><th>n</th>
            <th>Energy (kcal)</th><th>Protein (g)</th>
            <th>Fat (g)</th><th>Carbs (g)</th><th>Fiber (g)</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => {
            const reni = RENI[r.respondent_type] || RENI.other;
            const ePct = reni.energy  ? Math.round(+r.mean_energy  / reni.energy  * 100) : null;
            const pPct = reni.protein ? Math.round(+r.mean_protein / reni.protein * 100) : null;
            return `<tr>
              <td><strong>${GROUP_LABEL[r.respondent_type] || r.respondent_type}</strong></td>
              <td>${r.n}</td>
              <td>${r.mean_energy || '—'}
                ${ePct !== null ? `<br><small class="${adqClass(ePct)}">${ePct}% RENI</small>` : ''}
              </td>
              <td>${r.mean_protein || '—'}
                ${pPct !== null ? `<br><small class="${adqClass(pPct)}">${pPct}% RENI</small>` : ''}
              </td>
              <td>${r.mean_fat   || '—'}</td>
              <td>${r.mean_carbs || '—'}</td>
              <td>${r.mean_fiber || '—'}</td>
            </tr>`;
          }).join('')}
        </tbody>
      </table>
    </div>
    <p class="anlt-footnote">RENI benchmarks (Philippine RENI 2015): WRA 2000 kcal / 55 g protein; Children 0–5 ~1000 kcal / 15 g protein.</p>
  `;
  wrap.appendChild(sec);
}

// ── 5. Energy Adequacy ────────────────────────────────────
function renderEnergyAdequacy(wrap, rows) {
  if (!rows || !rows.length) {
    appendEmpty(wrap, 'Energy Adequacy (% vs RENI)', 'No completed recalls yet.');
    return;
  }
  const sec = mkSection('Energy Adequacy (% vs RENI)');
  const grid = document.createElement('div');
  grid.style.cssText = 'display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;';

  rows.forEach(r => {
    const label = GROUP_LABEL[r.type] || r.type;
    const n = +r.n;
    const adequate  = +r.adequate  || 0;
    const below     = +r.below     || 0;
    const deficient = +r.deficient || 0;
    const card = document.createElement('div');
    card.className = 'anlt-card';
    const cid = `adq_${r.type}`;
    card.innerHTML = `
      <h4 class="anlt-card-title">${label} <span style="font-weight:400;color:var(--text-muted)">(n=${n})</span></h4>
      <canvas id="${cid}" height="200"></canvas>
      <div class="anlt-legend">
        <span class="anlt-leg-dot" style="background:#2d6a4f"></span>Adequate ≥90% RENI
        <span class="anlt-leg-dot" style="background:#f4a261;margin-left:.75rem"></span>Below 66–89%
        <span class="anlt-leg-dot" style="background:#e63946;margin-left:.75rem"></span>Deficient &lt;66%
      </div>
    `;
    grid.appendChild(card);
    requestAnimationFrame(() => {
      new Chart(document.getElementById(cid), {
        type: 'doughnut',
        data: {
          labels: ['Adequate', 'Below', 'Deficient'],
          datasets: [{ data: [adequate, below, deficient], backgroundColor: ['#2d6a4f','#f4a261','#e63946'], borderWidth: 0 }],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} (${n > 0 ? Math.round(ctx.raw/n*100) : 0}%)` } },
          },
        },
      });
    });
  });

  sec.appendChild(grid);
  wrap.appendChild(sec);
}

// ── 6. Macronutrient Distribution ────────────────────────
function renderMacroDist(wrap, rows) {
  if (!rows || !rows.length) return;
  const sec = mkSection('Macronutrient Distribution (% of Total Energy)');
  const card = document.createElement('div');
  card.className = 'anlt-card';
  card.style.maxWidth = '640px';
  card.innerHTML = '<canvas id="macroChart" height="140"></canvas>';
  sec.appendChild(card);
  wrap.appendChild(sec);
  requestAnimationFrame(() => {
    new Chart(document.getElementById('macroChart'), {
      type: 'bar',
      data: {
        labels: rows.map(r => GROUP_LABEL[r.type] || r.type),
        datasets: [
          { label: '% Protein',      data: rows.map(r => r.pct_protein || 0), backgroundColor: '#457b9d', borderRadius: 4 },
          { label: '% Fat',          data: rows.map(r => r.pct_fat     || 0), backgroundColor: '#f4a261', borderRadius: 4 },
          { label: '% Carbohydrate', data: rows.map(r => r.pct_carbs   || 0), backgroundColor: '#52b788', borderRadius: 4 },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
        },
        plugins: { legend: { position: 'bottom' } },
      },
    });
  });
}

// ── 7. Food Group Frequency ───────────────────────────────
function renderFoodGroups(wrap, rows) {
  if (!rows || !rows.length) {
    appendEmpty(wrap, 'Food Group Consumption Frequency', 'No food items matched to FCT yet.');
    return;
  }
  const sec = mkSection('Food Group Consumption Frequency');
  const card = document.createElement('div');
  card.className = 'anlt-card';
  card.innerHTML = '<canvas id="fgChart" height="360"></canvas>';
  sec.appendChild(card);
  wrap.appendChild(sec);
  requestAnimationFrame(() => {
    new Chart(document.getElementById('fgChart'), {
      type: 'bar',
      data: {
        labels: rows.map(r => r.food_group),
        datasets: [{
          label: 'Times reported',
          data: rows.map(r => r.frequency),
          backgroundColor: '#52b788',
          borderRadius: 4,
        }],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } },
      },
    });
  });
}

// ── 8. Meal Occasion Distribution ────────────────────────
function renderMealOccasions(wrap, rows) {
  if (!rows || !rows.length) {
    appendEmpty(wrap, 'Meal Occasion Distribution', 'No meal data yet.');
    return;
  }
  const total = rows.reduce((s, r) => s + +r.count, 0);
  const sec   = mkSection('Meal Occasion Distribution');
  const grid  = document.createElement('div');
  grid.style.cssText = 'display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;';

  const chartCard = document.createElement('div');
  chartCard.className = 'anlt-card';
  chartCard.innerHTML = '<canvas id="mealChart" height="280"></canvas>';

  const tblCard = document.createElement('div');
  tblCard.className = 'anlt-card';
  tblCard.innerHTML = `
    <table style="width:100%;border-collapse:collapse;font-size:.875rem">
      <thead>
        <tr>
          <th style="text-align:left;padding:.4rem .75rem;border-bottom:1px solid var(--border)">Occasion</th>
          <th style="text-align:right;padding:.4rem .75rem;border-bottom:1px solid var(--border)">Count</th>
          <th style="text-align:right;padding:.4rem .75rem;border-bottom:1px solid var(--border)">%</th>
        </tr>
      </thead>
      <tbody>
        ${rows.map(r => {
          const pct = total > 0 ? Math.round(+r.count / total * 100) : 0;
          return `<tr>
            <td style="padding:.35rem .75rem">${mealLabel(r.meal_occasion)}</td>
            <td style="text-align:right;padding:.35rem .75rem">${r.count}</td>
            <td style="text-align:right;padding:.35rem .75rem;color:var(--text-muted)">${pct}%</td>
          </tr>`;
        }).join('')}
      </tbody>
    </table>
  `;

  grid.appendChild(chartCard);
  grid.appendChild(tblCard);
  sec.appendChild(grid);
  wrap.appendChild(sec);

  requestAnimationFrame(() => {
    new Chart(document.getElementById('mealChart'), {
      type: 'doughnut',
      data: {
        labels: rows.map(r => mealLabel(r.meal_occasion)),
        datasets: [{ data: rows.map(r => r.count), backgroundColor: PALETTE, borderWidth: 0 }],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } },
          tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw} (${total > 0 ? Math.round(ctx.raw/total*100) : 0}%)` } },
        },
      },
    });
  });
}

// ── 9. Place of Consumption ───────────────────────────────
function renderPlaces(wrap, rows) {
  if (!rows || !rows.length) return;
  const sec = mkSection('Place of Consumption');
  const card = document.createElement('div');
  card.className = 'anlt-card';
  card.style.maxWidth = '600px';
  card.innerHTML = '<canvas id="placesChart" height="220"></canvas>';
  sec.appendChild(card);
  wrap.appendChild(sec);
  requestAnimationFrame(() => {
    new Chart(document.getElementById('placesChart'), {
      type: 'bar',
      data: {
        labels: rows.map(r => r.place || 'Not specified'),
        datasets: [{
          label: 'Food items',
          data: rows.map(r => r.count),
          backgroundColor: PALETTE,
          borderRadius: 4,
        }],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } },
      },
    });
  });
}

// ── 10. Top Foods ─────────────────────────────────────────
function renderTopFoods(wrap, rows) {
  if (!rows || !rows.length) return;
  const sec = mkSection('Top 10 Most Consumed Foods');
  sec.innerHTML += `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th><th>Food Name</th><th>Food Group</th>
            <th>Times Reported</th><th>Avg Amount (g)</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map((r, i) => `
            <tr>
              <td style="color:var(--text-muted)">${i + 1}</td>
              <td><strong>${r.food_name || '—'}</strong></td>
              <td><small style="color:var(--text-muted)">${r.food_group || '—'}</small></td>
              <td>${r.frequency}</td>
              <td>${r.avg_grams || '—'}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
  wrap.appendChild(sec);
}

// ── Helpers ───────────────────────────────────────────────
function mkSection(title) {
  const sec = document.createElement('section');
  sec.className = 'sv-section';
  sec.innerHTML = `<div class="sv-section-header"><h3>${title}</h3></div>`;
  return sec;
}

function appendEmpty(wrap, title, msg) {
  const sec = mkSection(title);
  sec.innerHTML += `<p style="color:var(--text-muted);padding:.5rem 0">${msg}</p>`;
  wrap.appendChild(sec);
}

function adqClass(pct) {
  if (pct >= 90) return 'badge-reni-adequate';
  if (pct >= 66) return 'badge-reni-below';
  return 'badge-reni-deficient';
}

document.addEventListener('DOMContentLoaded', loadAnalytics);
