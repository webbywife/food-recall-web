/* ============================================================
   app.js — shared utilities
   ============================================================ */

function getCsrfToken() {
  const m = document.querySelector('meta[name="csrf-token"]');
  return m ? m.content : '';
}

const api = {
  async get(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  },
  async post(url, body) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
      body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  },
  async put(url, body) {
    const res = await fetch(url, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
      body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  },
  async delete(url) {
    const res = await fetch(url, {
      method: 'DELETE',
      headers: { 'X-CSRF-Token': getCsrfToken() },
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  },
};

function el(id) { return document.getElementById(id); }

function show(id) { const e = el(id); if (e) e.hidden = false; }
function hide(id) { const e = el(id); if (e) e.hidden = true; }

function fmt(n, dec = 1) {
  const v = parseFloat(n);
  return isNaN(v) ? '—' : v.toFixed(dec);
}

function statusPill(status) {
  const labels = {
    pending:     'Pending',
    in_progress: 'In Progress',
    completed:   'Done',
  };
  return `<span class="status-pill status-${status}">${labels[status] || status}</span>`;
}

function mealLabel(m) {
  const map = {
    early_morning:    'Early Morning',
    breakfast:        'Breakfast',
    morning_snack:    'Morning Snack',
    lunch:            'Lunch',
    afternoon_snack:  'Afternoon Snack',
    dinner:           'Dinner',
    evening_snack:    'Evening Snack',
    overnight:        'Overnight',
    other:            'Other',
  };
  return map[m] || m || '';
}

function respondentType(t) {
  return t === 'wra' ? 'WRA' : t === 'child_0_5' ? 'Child 0–5 yrs' : 'Other';
}

function notify(msg, type = 'success') {
  const n = document.createElement('div');
  n.textContent = msg;
  n.style.cssText = `
    position:fixed; bottom:1.25rem; right:1.25rem; z-index:9999;
    padding:.65rem 1.1rem; border-radius:8px; font-size:.88rem; font-weight:600;
    background:${type === 'success' ? 'var(--green-800)' : 'var(--red)'};
    color:#fff; box-shadow:0 4px 16px rgba(0,0,0,.18);
    animation:slideIn .25s ease;
  `;
  const style = document.createElement('style');
  style.textContent = `@keyframes slideIn{from{transform:translateY(12px);opacity:0}to{transform:none;opacity:1}}`;
  document.head.appendChild(style);
  document.body.appendChild(n);
  setTimeout(() => n.remove(), 3000);
}
