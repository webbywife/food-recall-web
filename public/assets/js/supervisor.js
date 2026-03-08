/* ============================================================
   supervisor.js — Supervisor Dashboard
   ============================================================ */

const Supervisor = {
  data: { interviewers: [], day2_pending: [] },

  async init() {
    await this.reload();
    await this.loadHouseholds();
  },

  async reload() {
    try {
      const data = await api.get('api/quota.php');
      this.data  = data;
      this.renderStats();
      this.renderQuotaTable();
      this.renderDay2Table();
    } catch (e) {
      console.error(e);
      notify('Failed to load dashboard.', 'error');
    }
  },

  renderStats() {
    const { interviewers, day2_pending } = this.data;

    const totalHH     = interviewers.reduce((s, i) => s + (+i.total_hh || 0), 0);
    const completedHH = interviewers.reduce((s, i) => s + (+i.completed_hh || 0), 0);

    el('statTotalHH').textContent       = totalHH;
    el('statCompletedHH').textContent   = completedHH;
    el('statDay2').textContent          = day2_pending.length;
    el('statInterviewers').textContent  = interviewers.length;
  },

  renderQuotaTable() {
    const body = el('quotaBody');
    if (!this.data.interviewers.length) {
      body.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:1.5rem">No interviewers found.</td></tr>';
      return;
    }

    body.innerHTML = this.data.interviewers.map(i => {
      const hhPct  = i.target_hh > 0 ? Math.min(100, Math.round((i.completed_hh / i.target_hh) * 100)) : 0;
      const wraPct = i.target_wra > 0 ? Math.min(100, Math.round((i.completed_wra / i.target_wra) * 100)) : 0;
      const chPct  = i.target_children > 0 ? Math.min(100, Math.round((i.completed_children / i.target_children) * 100)) : 0;

      return `
        <tr>
          <td>
            <strong>${i.full_name}</strong><br>
            <small style="color:var(--text-muted)">${i.username}</small>
          </td>
          <td>${i.assignment_area || '—'}</td>
          <td>
            ${i.completed_hh}/${i.target_hh || '?'}
            ${progressBar(hhPct)}
          </td>
          <td>
            ${i.completed_wra || 0}/${i.target_wra || '?'}
            ${progressBar(wraPct)}
          </td>
          <td>
            ${i.completed_children || 0}/${i.target_children || '?'}
            ${progressBar(chPct)}
          </td>
          <td>
            ${i.day2_done || 0}/${i.day2_eligible || 0}
          </td>
          <td>
            <button class="btn-sm" onclick="Supervisor.openQuotaModal(${i.id}, '${i.full_name.replace(/'/g,"\\'")}', ${i.target_hh || 0}, ${i.target_wra || 0}, ${i.target_children || 0})">
              Set Quota
            </button>
          </td>
        </tr>
      `;
    }).join('');
  },

  renderDay2Table() {
    const body = el('day2Body');
    const rows = this.data.day2_pending || [];

    if (!rows.length) {
      body.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:1.5rem">No Day 2 recalls pending.</td></tr>';
      return;
    }

    body.innerHTML = rows.map(h => `
      <tr>
        <td><strong>${h.hh_id}</strong></td>
        <td>${h.municipality}</td>
        <td>${h.barangay}</td>
        <td>${h.interviewer_name}</td>
        <td>
          ${h.day2_scheduled_date
            ? `<span class="${isOverdue(h.day2_scheduled_date) ? 'badge badge-red' : 'badge badge-green'}">${h.day2_scheduled_date}</span>`
            : '—'}
        </td>
        <td>
          <button class="btn-sm" onclick="Supervisor.markDay2Done(${h.id})">Mark Done</button>
        </td>
      </tr>
    `).join('');
  },

  // ── Quota Modal ──────────────────────────────────────────
  openQuotaModal(id, name, targetHH, targetWRA, targetChildren) {
    el('quotaInterviewerId').value = id;
    el('quotaModalName').textContent = name;
    el('qTargetHH').value = targetHH;
    el('qTargetWRA').value = targetWRA;
    el('qTargetChildren').value = targetChildren;
    show('quotaModal');
  },

  closeQuotaModal() {
    hide('quotaModal');
  },

  async saveQuota() {
    const id = +el('quotaInterviewerId').value;
    try {
      await api.put('api/quota.php', {
        interviewer_id: id,
        target_hh:       +el('qTargetHH').value,
        target_wra:      +el('qTargetWRA').value,
        target_children: +el('qTargetChildren').value,
        period_start:    el('qPeriodStart').value || null,
        period_end:      el('qPeriodEnd').value   || null,
      });
      this.closeQuotaModal();
      notify('Quota saved.');
      await this.reload();
    } catch {
      notify('Failed to save quota.', 'error');
    }
  },

  // ── Day 2 ────────────────────────────────────────────────
  async markDay2Done(householdId) {
    if (!confirm('Mark this household Day 2 recall as completed?')) return;
    try {
      await api.post('api/quota.php', { household_id: householdId });
      notify('Day 2 marked as done.');
      await this.reload();
    } catch {
      notify('Failed to update.', 'error');
    }
  },
};

// ── Helpers ──────────────────────────────────────────────
function progressBar(pct) {
  return `<div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:${pct}%"></div></div>`;
}

function isOverdue(dateStr) {
  return new Date(dateStr) < new Date();
}

document.addEventListener('DOMContentLoaded', () => Supervisor.init());

document.addEventListener('keydown', e => {
  if (e.key !== 'Escape') return;
  if (!el('quotaModal').hidden)  { Supervisor.closeQuotaModal();  return; }
  if (!el('addHHModal').hidden)  { Supervisor.closeAddHHModal();  return; }
  if (!el('csvModal').hidden)    { Supervisor.closeCSVModal();    return; }
});

// ── Household Management ──────────────────────────────────

Supervisor.loadHouseholds = async function () {
  try {
    const data = await api.get('api/households.php');
    this.renderHouseholds(data.households || []);
  } catch (e) {
    console.error('Failed to load households', e);
  }
};

Supervisor.renderHouseholds = function (rows) {
  const body = el('hhTableBody');
  if (!rows.length) {
    body.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:1.5rem">No households yet. Add one or upload a CSV.</td></tr>';
    return;
  }
  const statusColor = { completed: 'green', in_progress: 'blue', pending: 'gray' };
  body.innerHTML = rows.map(h => `
    <tr>
      <td><strong>${h.hh_id}</strong></td>
      <td>${h.municipality || '—'}</td>
      <td>${h.barangay || '—'}</td>
      <td>${h.interviewer_name || '—'}</td>
      <td>${+h.completed_respondents || 0}/${+h.respondent_count || 0}</td>
      <td><span class="badge badge-${statusColor[h.status] || 'gray'}">${h.status}</span></td>
    </tr>
  `).join('');
};

// ── Add Household Modal ───────────────────────────────────

Supervisor._respRowIdx = 0;

Supervisor.openAddHHModal = function () {
  el('hhID').value = '';
  el('hhRegion').value = '';
  el('hhProvince').value = '';
  el('hhMunicipality').value = '';
  el('hhBarangay').value = '';
  el('hhAddress').value = '';
  el('respondentRows').innerHTML = '';
  this._respRowIdx = 0;

  const sel = el('hhInterviewer');
  sel.innerHTML = '<option value="">— None —</option>';
  (this.data.interviewers || []).forEach(i => {
    sel.innerHTML += `<option value="${i.id}">${i.full_name} (${i.username})</option>`;
  });

  show('addHHModal');
};

Supervisor.closeAddHHModal = function () { hide('addHHModal'); };

Supervisor.addRespondentRow = function () {
  const idx = this._respRowIdx++;
  const row = document.createElement('div');
  row.className = 'respondent-row';
  row.id = `respRow_${idx}`;
  row.innerHTML = `
    <input type="text"   class="resp-name" placeholder="Full name *" style="flex:2">
    <input type="number" class="resp-age"  placeholder="Age" min="0" max="120" style="flex:1;min-width:60px">
    <select class="resp-sex" style="flex:1">
      <option value="">Sex</option>
      <option value="female">Female</option>
      <option value="male">Male</option>
    </select>
    <select class="resp-type" style="flex:1.5">
      <option value="wra">WRA</option>
      <option value="child_0_5">Child 0–5</option>
      <option value="other">Other</option>
    </select>
    <button type="button" class="btn-sm btn-danger-sm" onclick="document.getElementById('respRow_${idx}').remove()">✕</button>
  `;
  el('respondentRows').appendChild(row);
};

Supervisor.saveHousehold = async function () {
  const hhID = el('hhID').value.trim();
  if (!hhID) { notify('HH ID is required.', 'error'); return; }

  try {
    const hhRes = await api.post('api/households.php', {
      hh_id:                   hhID,
      region:                  el('hhRegion').value.trim()       || null,
      province:                el('hhProvince').value.trim()     || null,
      municipality:            el('hhMunicipality').value.trim() || null,
      barangay:                el('hhBarangay').value.trim()     || null,
      address:                 el('hhAddress').value.trim()      || null,
      assigned_interviewer_id: el('hhInterviewer').value ? +el('hhInterviewer').value : null,
    });

    const hhDbId = hhRes.id;
    const rows   = document.querySelectorAll('#respondentRows .respondent-row');
    let added = 0;
    for (const row of rows) {
      const name = row.querySelector('.resp-name').value.trim();
      if (!name) continue;
      await api.post('api/respondents.php', {
        household_id: hhDbId,
        name,
        age:  row.querySelector('.resp-age').value  || null,
        sex:  row.querySelector('.resp-sex').value  || null,
        type: row.querySelector('.resp-type').value || 'other',
      });
      added++;
    }

    this.closeAddHHModal();
    notify(`Household ${hhID} added with ${added} respondent(s).`);
    await this.reload();
    await this.loadHouseholds();
  } catch (e) {
    notify('Failed to save household.', 'error');
    console.error(e);
  }
};

// ── CSV Upload Modal ──────────────────────────────────────

Supervisor._csvFile = null;

Supervisor.openCSVModal = function () {
  el('csvFileInput').value = '';
  el('csvFileName').textContent = 'No file selected';
  el('csvPreview').style.display = 'none';
  el('csvResult').style.display  = 'none';
  el('csvImportBtn').disabled = true;
  this._csvFile = null;
  show('csvModal');
};

Supervisor.closeCSVModal = function () { hide('csvModal'); };

Supervisor.previewCSV = function (input) {
  const file = input.files[0];
  if (!file) return;
  this._csvFile = file;
  el('csvFileName').textContent = file.name;

  const reader = new FileReader();
  reader.onload = (e) => {
    const lines   = e.target.result.split('\n').filter(Boolean);
    const preview = lines.slice(0, 6);
    const previewEl = el('csvPreview');
    previewEl.innerHTML = `
      <div style="font-size:.8rem;font-weight:600;color:var(--text-muted);margin-bottom:.4rem">Preview (first 5 data rows):</div>
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:.75rem">
          ${preview.map((line, i) => `
            <tr style="background:${i === 0 ? 'var(--surface-hover,#f5f5f5)' : 'transparent'}">
              ${line.split(',').map(c => `<td style="border:1px solid var(--border);padding:.2rem .45rem">${c.trim()}</td>`).join('')}
            </tr>
          `).join('')}
        </table>
      </div>
    `;
    previewEl.style.display  = 'block';
    el('csvImportBtn').disabled = false;
    el('csvResult').style.display = 'none';
  };
  reader.readAsText(file);
};

Supervisor.importCSV = async function () {
  if (!this._csvFile) return;
  const btn = el('csvImportBtn');
  btn.disabled = true;
  btn.textContent = 'Importing…';

  const formData = new FormData();
  formData.append('file', this._csvFile);

  try {
    const res    = await fetch('api/import.php', { method: 'POST', body: formData });
    const result = await res.json();

    const resultEl = el('csvResult');
    resultEl.style.display = 'block';

    if (result.success) {
      resultEl.innerHTML = `
        <div class="csv-result-ok">
          Imported: <strong>${result.created_households}</strong> households,
          <strong>${result.created_respondents}</strong> respondents.
          ${result.skipped > 0 ? `<br><span style="opacity:.75">${result.skipped} rows skipped.</span>` : ''}
          ${result.errors?.length ? `<br><span style="color:#c62828">${result.errors.join('; ')}</span>` : ''}
        </div>
      `;
      notify(`Imported ${result.created_households} HH, ${result.created_respondents} respondents.`);
      await this.reload();
      await this.loadHouseholds();
    } else {
      resultEl.innerHTML = `<div class="csv-result-err">${result.error || 'Import failed.'}</div>`;
      notify(result.error || 'Import failed.', 'error');
    }
  } catch (e) {
    notify('Import failed.', 'error');
    console.error(e);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Import';
  }
};
