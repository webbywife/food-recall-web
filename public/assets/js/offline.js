/* ============================================================
   offline.js — Offline-first API layer + sync manager
   Intercepts app.js api.* calls; falls back to IndexedDB.
   ============================================================ */
const Offline = (() => {

  // ── Helpers ──────────────────────────────────────────────────
  const isLid  = id => typeof id === 'string' && /^L\d+$/.test(id);
  const toLid  = id => parseInt(id.slice(1));
  const fromLid = n => `L${n}`;
  const csrf   = () => document.querySelector('meta[name=csrf-token]')?.content ?? '';
  const hasLid = obj => obj && Object.values(obj).some(v => isLid(String(v)));

  async function safeFetch(url, opts = {}) {
    const ctrl  = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), 8000);
    try {
      const res = await fetch(url, { ...opts, signal: ctrl.signal });
      clearTimeout(timer);
      return res;
    } catch(e) { clearTimeout(timer); throw e; }
  }

  // ── Capture original api methods ─────────────────────────────
  const _get    = api.get.bind(api);
  const _post   = api.post.bind(api);
  const _put    = api.put.bind(api);
  const _delete = api.delete.bind(api);

  // ── Override api.get ─────────────────────────────────────────
  api.get = async function(url) {
    const u = new URL(url, location.origin);
    // Local-ID in query → always IDB
    for (const v of u.searchParams.values()) if (isLid(v)) return readIDB(url);
    if (state.online) {
      try {
        const res = await safeFetch(url);
        if (res.ok) { const d = await res.json(); cacheRead(url, d); return d; }
      } catch {}
    }
    return readIDB(url);
  };

  // ── Override api.post ─────────────────────────────────────────
  api.post = async function(url, body) {
    if (hasLid(body)) return writeIDB(url, body, 'POST');
    if (state.online) {
      try { return await _post(url, body); } catch {}
    }
    return writeIDB(url, body, 'POST');
  };

  // ── Override api.put ──────────────────────────────────────────
  api.put = async function(url, body) {
    if (hasLid(body)) return writeIDB(url, body, 'PUT');
    if (state.online) {
      try { return await _put(url, body); } catch {}
    }
    return writeIDB(url, body, 'PUT');
  };

  // ── Override api.delete ───────────────────────────────────────
  api.delete = async function(url) {
    const u = new URL(url, location.origin);
    for (const v of u.searchParams.values()) if (isLid(v)) return writeIDB(url, null, 'DELETE');
    if (state.online) {
      try { return await _delete(url); } catch {}
    }
    return writeIDB(url, null, 'DELETE');
  };

  // ── Cache reads into IDB ──────────────────────────────────────
  async function cacheRead(url, data) {
    const u = new URL(url, location.origin);
    const p = u.pathname.replace(/.*\//, '');
    const a = u.searchParams.get('action');
    const id = u.searchParams.get('id');

    if (p === 'households.php' && !id && data.households) {
      IDB.putMany('households', data.households);
    }
    if (p === 'households.php' && id && data.household) {
      const { respondents = [], ...hh } = data.household;
      IDB.put('households', hh);
      if (respondents.length) IDB.putMany('respondents', respondents);
    }
    if (p === 'fct.php' && data.foods) {
      IDB.putMany('fct_foods', data.foods);
    }
    if (p === 'recall.php' && a === 'get' && data.session) {
      const { food_items = [], ...s } = data.session;
      const rows = await IDB.getByIndex('sessions', 'by_sid', s.id);
      const base = rows[0] || {};
      const lid  = await IDB.put('sessions', { ...base, ...s, server_id: s.id, synced: 1 });
      for (const fi of food_items) {
        const fiRows = await IDB.getByIndex('food_items', 'by_sid', fi.id);
        const fb     = fiRows[0] || {};
        await IDB.put('food_items', {
          ...fb, ...fi, server_id: fi.id, _sess_lid: base._lid || lid, synced: 1,
        });
      }
    }
  }

  // ── IDB reads ────────────────────────────────────────────────
  async function readIDB(url) {
    const u  = new URL(url, location.origin);
    const p  = u.pathname.replace(/.*\//, '');
    const a  = u.searchParams.get('action');
    const id = u.searchParams.get('id');
    const sid = u.searchParams.get('session_id');

    if (p === 'households.php' && !id) {
      const households = await IDB.getAll('households');
      return { households };
    }
    if (p === 'households.php' && id) {
      const hh = await IDB.get('households', parseInt(id));
      if (!hh) return { error: 'Not found (offline)' };
      const respondents = await IDB.getByIndex('respondents', 'by_hh', parseInt(id));
      return { household: { ...hh, respondents } };
    }
    if (p === 'fct.php') {
      const q  = (u.searchParams.get('q') || '').toLowerCase();
      const gs = (u.searchParams.get('groups') || '').split(',').map(Number).filter(Boolean);
      let all  = await IDB.getAll('fct_foods');
      if (q) all = all.filter(f =>
        (f.food_name  || '').toLowerCase().includes(q) ||
        (f.local_name || '').toLowerCase().includes(q)
      );
      if (gs.length) all = all.filter(f => gs.includes(f.food_group_id));
      const foods = all.slice(0, 20);
      return { foods, total: all.length };
    }
    if (p === 'recall.php' && a === 'get' && sid) {
      return getSessionIDB(sid);
    }
    return { error: 'Offline: data unavailable', offline: true };
  }

  async function getSessionIDB(sid) {
    let sess;
    if (isLid(sid)) {
      sess = await IDB.get('sessions', toLid(sid));
    } else {
      const rows = await IDB.getByIndex('sessions', 'by_sid', parseInt(sid));
      sess = rows[0];
    }
    if (!sess) return { error: 'Session not found (offline)' };

    const items = (await IDB.getByIndex('food_items', 'by_sess', sess._lid))
      .filter(f => !f.is_deleted)
      .map(f => ({
        ...f,
        id:         f.server_id || fromLid(f._lid),
        session_id: sid,
        added_ingredients: f.added_ingredients || [],
      }));

    return {
      session: {
        id:              sess.server_id || fromLid(sess._lid),
        respondent_id:   sess.respondent_id,
        household_id:    sess.household_id,
        respondent_name: sess.respondent_name,
        respondent_type: sess.respondent_type,
        age:             sess.age,
        sex:             sess.sex,
        hh_id:           sess.hh_id,
        barangay:        sess.barangay,
        municipality:    sess.municipality,
        current_pass:    sess.current_pass || 1,
        status:          sess.status || 'in_progress',
        is_day2:         sess.is_day2 || 0,
        notes:           sess.notes || '',
        food_items:      items,
      }
    };
  }

  // ── IDB writes ───────────────────────────────────────────────
  async function writeIDB(url, body, method) {
    const u = new URL(url, location.origin);
    const p = u.pathname.replace(/.*\//, '');
    const a = u.searchParams.get('action');

    if (p === 'recall.php') {
      if (a === 'start'        && method === 'POST')   return startSessionIDB(body);
      if (a === 'add_food'     && method === 'POST')   return addFoodIDB(body);
      if (a === 'update_food'  && method === 'PUT')    return updateFoodIDB(body);
      if (a === 'delete_food'  && method === 'DELETE') return deleteFoodIDB(u.searchParams.get('item_id'));
      if (a === 'advance_pass' && method === 'POST')   return advancePassIDB(body);
      if (a === 'save_notes'   && method === 'POST')   return saveNotesIDB(body);
    }
    if (p === 'ingredients.php' && method === 'POST') return saveIngredientsIDB(body);

    return { error: 'Offline: write queued', offline: true };
  }

  async function startSessionIDB({ respondent_id, household_id, is_day2 }) {
    const existing = await IDB.getByIndex('sessions', 'by_resp', respondent_id);
    const active   = existing.find(s => s.status !== 'completed');
    if (active) {
      return { session_id: active.server_id ? String(active.server_id) : fromLid(active._lid), resumed: true };
    }
    const resp = await IDB.get('respondents', respondent_id);
    const hh   = await IDB.get('households',  household_id);
    const lid  = await IDB.put('sessions', {
      respondent_id, household_id, is_day2: is_day2 || 0,
      status: 'in_progress', current_pass: 1,
      recall_date:     new Date().toISOString().slice(0, 10),
      respondent_name: resp?.name, respondent_type: resp?.type,
      age: resp?.age, sex: resp?.sex,
      hh_id: hh?.hh_id, barangay: hh?.barangay, municipality: hh?.municipality,
      server_id: null, synced: 0,
    });
    if (hh)   IDB.put('households',  { ...hh,   status:        'in_progress' });
    if (resp) IDB.put('respondents', { ...resp,  recall_status: 'in_progress' });
    updateBadge();
    return { session_id: fromLid(lid), resumed: false };
  }

  async function addFoodIDB({ session_id, quick_list_name, source, pass }) {
    const sessLid = isLid(session_id) ? toLid(session_id)
      : (await IDB.getByIndex('sessions', 'by_sid', parseInt(session_id)))[0]?._lid;
    const existing = await IDB.getByIndex('food_items', 'by_sess', sessLid);
    const seq = existing.filter(f => !f.is_deleted).length + 1;
    const lid = await IDB.put('food_items', {
      _sess_lid: sessLid, session_id,
      sequence_no: seq, quick_list_name,
      source: source || 'quick_list', added_pass: pass || 1,
      is_deleted: 0, server_id: null, synced: 0, added_ingredients: [],
    });
    updateBadge();
    return { success: true, id: fromLid(lid), sequence_no: seq };
  }

  async function updateFoodIDB({ id, ...fields }) {
    const item = await resolveFood(id);
    if (!item) return { error: 'Not found (offline)' };
    await IDB.put('food_items', { ...item, ...fields, synced: 0 });
    updateBadge();
    return { success: true };
  }

  async function deleteFoodIDB(item_id) {
    const item = await resolveFood(item_id);
    if (!item) return { success: true };
    await IDB.put('food_items', { ...item, is_deleted: 1, synced: 0 });
    updateBadge();
    return { success: true };
  }

  async function advancePassIDB({ session_id, next_pass }) {
    const sess = await resolveSess(session_id);
    if (!sess) return { error: 'Session not found (offline)' };
    const statusMap = { 2:'forgotten_foods',3:'time_occasion',4:'detail_cycle',5:'review',6:'completed' };
    const status    = statusMap[next_pass] || 'completed';
    await IDB.put('sessions', { ...sess, current_pass: next_pass, status, synced: 0,
      ...(status === 'completed' ? { completed_at: new Date().toISOString() } : {}) });
    if (status === 'completed') {
      const resp = await IDB.get('respondents', sess.respondent_id);
      if (resp) IDB.put('respondents', { ...resp, recall_status: 'completed' });
    }
    updateBadge();
    return { success: true, status };
  }

  async function saveNotesIDB({ session_id, notes }) {
    const sess = await resolveSess(session_id);
    if (sess) await IDB.put('sessions', { ...sess, notes, synced: 0 });
    return { success: true };
  }

  async function saveIngredientsIDB({ food_item_id, ingredients }) {
    const item = await resolveFood(food_item_id);
    if (!item) return { error: 'Not found (offline)' };
    await IDB.put('food_items', { ...item, added_ingredients: ingredients, synced: 0 });
    updateBadge();
    return { success: true };
  }

  // ── ID resolvers ─────────────────────────────────────────────
  async function resolveSess(sid) {
    if (isLid(sid)) return IDB.get('sessions', toLid(sid));
    const rows = await IDB.getByIndex('sessions', 'by_sid', parseInt(sid));
    return rows[0];
  }
  async function resolveFood(fid) {
    if (isLid(fid)) return IDB.get('food_items', toLid(fid));
    const rows = await IDB.getByIndex('food_items', 'by_sid', parseInt(fid));
    return rows[0];
  }

  // ── Sync ─────────────────────────────────────────────────────
  let syncing = false;

  async function sync() {
    if (syncing || !state.online) return;
    syncing = true;
    updateSyncBtn(true);

    try {
      await pullData();
      await syncSessions();
      await syncFoodItems();
      const n = await unsyncedCount();
      notify(n === 0 ? 'All data synced.' : `Sync partial — ${n} items pending.`, n === 0 ? 'success' : 'error');
    } catch(e) {
      console.error('Sync error', e);
      notify('Sync failed. Will retry when reconnected.', 'error');
    } finally {
      syncing = false;
      updateSyncBtn(false);
      updateBadge();
    }
  }

  async function pullData() {
    // Pull FCT foods once per install
    const fctDone = await IDB.getMeta('fct_synced');
    if (!fctDone) {
      const d = await fetch('api/sync.php?type=fct').then(r => r.json());
      if (d.fct_foods) { await IDB.putMany('fct_foods', d.fct_foods); await IDB.setMeta('fct_synced', 1); }
    }
    // Pull households + respondents
    const hd = await fetch('api/households.php').then(r => r.json());
    if (hd.households) {
      await IDB.putMany('households', hd.households);
      for (const hh of hd.households) {
        const d = await fetch(`api/households.php?id=${hh.id}`).then(r => r.json());
        if (d.household?.respondents) await IDB.putMany('respondents', d.household.respondents);
      }
    }
  }

  async function syncSessions() {
    const all      = await IDB.getAll('sessions');
    const newSess  = all.filter(s => !s.server_id);
    const modified = all.filter(s =>  s.server_id && !s.synced);

    for (const s of newSess) {
      try {
        const res = await fetch('api/recall.php?action=start', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
          body: JSON.stringify({ respondent_id: s.respondent_id, household_id: s.household_id, is_day2: s.is_day2 }),
        });
        if (!res.ok) continue;
        const { session_id } = await res.json();
        await IDB.put('sessions', { ...s, server_id: session_id, synced: 1 });
        // Advance pass if already progressed
        if (s.current_pass > 1) {
          for (let p = 2; p <= Math.min(s.current_pass, 6); p++) {
            await fetch('api/recall.php?action=advance_pass', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
              body: JSON.stringify({ session_id, next_pass: p }),
            });
          }
        }
      } catch(e) { console.warn('Failed to sync session', s._lid, e); }
    }

    for (const s of modified) {
      try {
        for (let p = 2; p <= Math.min(s.current_pass, 6); p++) {
          await fetch('api/recall.php?action=advance_pass', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
            body: JSON.stringify({ session_id: s.server_id, next_pass: p }),
          });
        }
        await IDB.put('sessions', { ...s, synced: 1 });
      } catch {}
    }
  }

  async function syncFoodItems() {
    const all = await IDB.getAll('food_items');

    // Sync new items (no server_id yet)
    for (const fi of all.filter(f => !f.server_id)) {
      try {
        const sess = await IDB.get('sessions', fi._sess_lid);
        if (!sess?.server_id) continue; // session not yet synced

        const res = await fetch('api/recall.php?action=add_food', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
          body: JSON.stringify({
            session_id:      sess.server_id,
            quick_list_name: fi.quick_list_name,
            source:          fi.source || 'quick_list',
            pass:            fi.added_pass || 1,
          }),
        });
        if (!res.ok) continue;
        const { id: server_id } = await res.json();
        await IDB.put('food_items', { ...fi, server_id, synced: 1 });

        // Push detail fields
        const detailFields = ['meal_occasion','meal_time','place_of_consumption','fct_food_id',
          'food_name','brand_name','cooking_method','amount_grams','household_measure_desc',
          'household_measure_amount','energy_kcal','protein_g','fat_g','carbs_g','fiber_g'];
        const detail = {};
        detailFields.forEach(k => { if (fi[k] != null) detail[k] = fi[k]; });
        if (Object.keys(detail).length) {
          await fetch('api/recall.php?action=update_food', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
            body: JSON.stringify({ id: server_id, ...detail }),
          });
        }

        if (fi.is_deleted) {
          await fetch(`api/recall.php?action=delete_food&item_id=${server_id}`, {
            method: 'DELETE', headers: { 'X-CSRF-Token': csrf() },
          });
        }

        if (fi.added_ingredients?.length) {
          await fetch('api/ingredients.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
            body: JSON.stringify({ food_item_id: server_id, ingredients: fi.added_ingredients }),
          });
        }
      } catch(e) { console.warn('Failed to sync food_item', fi._lid, e); }
    }

    // Sync updates to existing server items
    for (const fi of all.filter(f => f.server_id && !f.synced)) {
      try {
        if (fi.is_deleted) {
          await fetch(`api/recall.php?action=delete_food&item_id=${fi.server_id}`, {
            method: 'DELETE', headers: { 'X-CSRF-Token': csrf() },
          });
        } else {
          const detail = {};
          ['meal_occasion','meal_time','place_of_consumption','fct_food_id','food_name','brand_name',
           'cooking_method','amount_grams','household_measure_desc','household_measure_amount',
           'energy_kcal','protein_g','fat_g','carbs_g','fiber_g'
          ].forEach(k => { if (fi[k] != null) detail[k] = fi[k]; });
          if (Object.keys(detail).length) {
            await fetch('api/recall.php?action=update_food', {
              method: 'PUT',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
              body: JSON.stringify({ id: fi.server_id, ...detail }),
            });
          }
          if (fi.added_ingredients?.length) {
            await fetch('api/ingredients.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
              body: JSON.stringify({ food_item_id: fi.server_id, ingredients: fi.added_ingredients }),
            });
          }
        }
        await IDB.put('food_items', { ...fi, synced: 1 });
      } catch {}
    }
  }

  // ── UI helpers ───────────────────────────────────────────────
  async function unsyncedCount() {
    const [s, f] = await Promise.all([IDB.getAll('sessions'), IDB.getAll('food_items')]);
    return s.filter(x => !x.synced).length + f.filter(x => !x.synced).length;
  }

  async function updateBadge() {
    const n    = await unsyncedCount();
    const el   = document.getElementById('offlineBadge');
    const btn  = document.getElementById('syncBtn');
    if (el)  el.textContent  = n > 0 ? `${n} unsynced` : (state.online ? '' : 'Offline');
    if (btn) { btn.hidden = (n === 0 && state.online); btn.textContent = `↑ Sync (${n})`; }
  }

  function updateSyncBtn(busy) {
    const btn = document.getElementById('syncBtn');
    if (!btn) return;
    btn.disabled    = busy;
    btn.textContent = busy ? 'Syncing…' : `↑ Sync`;
  }

  function updateBar() {
    const bar = document.getElementById('offlineBar');
    if (bar) bar.hidden = state.online;
  }

  // ── Connectivity ─────────────────────────────────────────────
  const state = { online: navigator.onLine };

  function onOnline()  { state.online = true;  updateBar(); updateBadge(); notify('Back online — syncing…', 'success'); sync(); }
  function onOffline() { state.online = false; updateBar(); updateBadge(); notify('Working offline — data saved locally.', 'error'); }

  // ── Initial data pull ─────────────────────────────────────────
  async function initialPull() {
    if (!state.online) { updateBar(); updateBadge(); return; }
    try { await pullData(); } catch(e) { console.warn('Initial pull error', e); }
    updateBadge();
  }

  return {
    init() {
      window.addEventListener('online',  onOnline);
      window.addEventListener('offline', onOffline);
      updateBar();
      initialPull();
    },
    sync,
    get online() { return state.online; },
  };
})();
