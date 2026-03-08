/* ============================================================
   interview.js — 5-Pass Interview Engine
   Philippine 24-Hour Food Recall | FNRI FCT | Intake24-style
   Specs: time/place, description/amount, preparation,
          added ingredients, RENI nutrient adequacy
   ============================================================ */

// ─── Constants ────────────────────────────────────────────

const MEAL_OCCASIONS = [
  { value: 'early_morning',   label: 'Early Morning'    },
  { value: 'breakfast',       label: 'Breakfast'        },
  { value: 'morning_snack',   label: 'Morning Snack'    },
  { value: 'lunch',           label: 'Lunch'            },
  { value: 'afternoon_snack', label: 'Afternoon Snack'  },
  { value: 'dinner',          label: 'Dinner'           },
  { value: 'evening_snack',   label: 'Evening Snack'    },
  { value: 'overnight',       label: 'Overnight'        },
  { value: 'other',           label: 'Other'            },
];

const PLACE_OPTIONS = [
  { value: 'home',          label: 'Home (Bahay)'             },
  { value: 'school',        label: 'School (Paaralan)'        },
  { value: 'work',          label: 'Work / Office'            },
  { value: 'carenderia',    label: 'Carenderia / Canteen'     },
  { value: 'restaurant',    label: 'Restaurant / Fast Food'   },
  { value: 'street_vendor', label: 'Street Vendor / Tindahan' },
  { value: 'neighbors',     label: "Neighbor / Relative's"    },
  { value: 'other',         label: 'Other'                    },
];

// Philippine-specific forgotten food probes (10 categories incl. breastmilk)
const FORGOTTEN_FOOD_PROBES = [
  {
    category: 'Drinks',
    question: 'Did the respondent have anything to drink — water, juice, softdrinks, coffee, or tea?',
    examples: 'e.g. tubig, kape 3-in-1, tsaa, juice, soda, buko juice, Milo, tsokolate',
  },
  {
    category: 'Milk and Milk Products',
    question: 'Did the respondent have any milk, formula, cheese, or yogurt?',
    examples: 'e.g. evap milk, powdered milk, infant formula (gatas ng sanggol), cheese, fresh milk',
  },
  {
    category: 'Alcoholic Beverages',
    question: 'Did the respondent drink any beer, wine, gin, tuba, or lambanog?',
    examples: 'e.g. beer, gin, rum, tuba, lambanog, basi, wine',
  },
  {
    category: 'Sweets and Desserts',
    question: 'Did the respondent eat any candy, cake, cookies, ice cream, or kakanin?',
    examples: 'e.g. kendi, keyk, ice cream, leche flan, bibingka, biko, puto, champorado, halo-halo, sorbetes',
  },
  {
    category: 'Fruits',
    question: 'Did the respondent eat any fresh fruits, dried fruits, or fruit salad?',
    examples: 'e.g. mangga, saging, papaya, pakwan, bayabas, dalandan, avocado, pinya, santol',
  },
  {
    category: 'Street Food and Fast Food',
    question: 'Did the respondent eat anything bought from a vendor, carenderia, or fast food?',
    examples: 'e.g. fishball, squid ball, kwek-kwek, banana cue, siomai, siopao, burger, fried chicken, french fries, balut',
  },
  {
    category: 'Snacks and Chips',
    question: 'Did the respondent eat any chips, crackers, nuts, junk food, or biscuits?',
    examples: 'e.g. chichirya, chips, mani, galletas, crackers, biscuits, popcorn',
  },
  {
    category: 'Vitamins and Supplements',
    question: 'Did the respondent take any vitamins, minerals, or food supplements?',
    examples: 'e.g. Vitamin C, iron tablet, multivitamins, Ferrous sulfate, Vitamin A capsule, calcium tablet',
  },
  {
    category: 'Condiments and Sauces',
    question: 'Were any condiments or sauces consumed — soy sauce, patis, vinegar, bagoong, or sugar?',
    examples: 'e.g. toyo, patis, suka, bagoong, banana catsup, asukal used as dip or added at the table',
  },
  {
    category: 'Breastmilk (Children 0–5)',
    question: 'Was the child breastfed at any time yesterday, including at night?',
    examples: 'Record number of breastfeeding episodes and approximate duration/side',
    childOnly: true,
  },
];

// ─── Philippine RENI 2015 Reference Values ────────────────
// Source: FNRI-DOST Recommended Energy and Nutrient Intakes
const RENI_REFERENCE = {
  wra_19_29: {
    label:    'WRA, 19–29 yrs (moderate activity)',
    energy:   2000, protein: 60,  fat: 55,  carbs: 275, fiber: 25,
    calcium:  750,  iron:    32,  vitA: 500,
  },
  wra_30_49: {
    label:    'WRA, 30–49 yrs (moderate activity)',
    energy:   1900, protein: 60,  fat: 52,  carbs: 260, fiber: 25,
    calcium:  750,  iron:    26,  vitA: 500,
  },
  child_0_6mo: {
    label:    'Infant, 0–6 months',
    energy:   555,  protein: 9.1, fat: 31,  carbs: 59,
    calcium:  200,  iron:    0.27,
  },
  child_7_12mo: {
    label:    'Infant, 7–12 months',
    energy:   750,  protein: 13.5,fat: 30,  carbs: 95,
    calcium:  260,  iron:    11,
  },
  child_1_3: {
    label:    'Child, 1–3 years',
    energy:   1000, protein: 20,  fat: 33,  carbs: 137, fiber: 14,
    calcium:  500,  iron:    11,
  },
  child_3_5: {
    label:    'Child, 3–5 years',
    energy:   1350, protein: 24,  fat: 45,  carbs: 186, fiber: 16,
    calcium:  500,  iron:    10,
  },
};

// ─── Quick-add Ingredients ────────────────────────────────
// Pre-configured common Filipino cooking additions
// Nutrient values per 100g (from FNRI FCT)
const QUICK_INGREDIENTS = [
  { key: 'veg_oil',     label: '+ Cooking Oil',      name: 'Vegetable cooking oil', grams: 5,  desc: '1 tsp',
    per100: { energy: 884,  protein: 0,   fat: 100,  carbs: 0,   fiber: 0 } },
  { key: 'coconut_oil', label: '+ Coconut Oil',       name: 'Coconut oil',           grams: 5,  desc: '1 tsp',
    per100: { energy: 862,  protein: 0,   fat: 100,  carbs: 0,   fiber: 0 } },
  { key: 'sugar',       label: '+ Sugar',             name: 'Sugar, white',          grams: 4,  desc: '1 tsp',
    per100: { energy: 387,  protein: 0,   fat: 0,    carbs: 100, fiber: 0 } },
  { key: 'toyo',        label: '+ Soy Sauce',         name: 'Soy sauce (toyo)',      grams: 16, desc: '1 tbsp',
    per100: { energy: 53,   protein: 8.1, fat: 0.1,  carbs: 4.9, fiber: 0.8 } },
  { key: 'patis',       label: '+ Fish Sauce',        name: 'Fish sauce (patis)',    grams: 18, desc: '1 tbsp',
    per100: { energy: 35,   protein: 5.1, fat: 0,    carbs: 3.6, fiber: 0 } },
  { key: 'margarine',   label: '+ Margarine/Butter',  name: 'Margarine',             grams: 5,  desc: '1 tsp',
    per100: { energy: 714,  protein: 0.9, fat: 80.7, carbs: 0.9, fiber: 0 } },
  { key: 'vinegar',     label: '+ Vinegar',           name: 'Vinegar (suka)',        grams: 10, desc: '1 tbsp',
    per100: { energy: 21,   protein: 0,   fat: 0,    carbs: 0.9, fiber: 0 } },
  { key: 'bagoong',     label: '+ Bagoong',           name: 'Shrimp paste (bagoong)',grams: 10, desc: '1 tsp',
    per100: { energy: 155,  protein: 20.5,fat: 5.5,  carbs: 6.5, fiber: 0 } },
];

// ─── App State ────────────────────────────────────────────
const State = {
  session:           null,
  foodItems:         [],
  household:         null,
  households:        [],
  // Added ingredients: keyed by food item ID
  addedIngredients:  {},   // { [itemId]: [{tempId, ingredient_name, amount_grams, ...nutrients}] }
  // FCT modal mode
  fctMode:           'food',  // 'food' | 'ingredient'
  fctItemId:         null,
  // Quick ingredient: which item has the pending quick-add form open
  activeQuickIng:    null,   // { itemId, quickIng }
};

// ─── App ──────────────────────────────────────────────────
window.App = {

  // ── Boot ──────────────────────────────────────────────
  async init() {
    await this.loadHouseholds();
    this.showView('viewHouseholds');
  },

  // ── Households ────────────────────────────────────────
  async loadHouseholds() {
    try {
      const data = await api.get('api/households.php');
      State.households = data.households || [];
      this.renderHouseholds(State.households);
    } catch {
      el('hhList').innerHTML = '<p style="color:var(--red);padding:.5rem">Failed to load households.</p>';
    }
  },

  renderHouseholds(list) {
    const container = el('hhList');
    if (!list.length) {
      container.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:2rem">No households assigned.</p>';
      return;
    }
    container.innerHTML = list.map(hh => `
      <div class="hh-card" onclick="App.openHousehold(${hh.id})">
        <div class="hh-card-left">
          <div class="hh-card-id">${hh.hh_id}</div>
          <div class="hh-card-addr">${hh.barangay}, ${hh.municipality} &bull; ${hh.address || ''}</div>
        </div>
        <div class="hh-card-right">
          ${statusPill(hh.status)}
          <div class="hh-progress">${hh.completed_respondents || 0}/${hh.respondent_count || 0} respondents done</div>
          ${hh.day2_eligible ? '<span class="badge badge-gold">Day 2</span>' : ''}
        </div>
      </div>
    `).join('');
  },

  filterHouseholds(q) {
    const lq = q.toLowerCase();
    this.renderHouseholds(
      State.households.filter(h =>
        h.hh_id.toLowerCase().includes(lq) ||
        (h.barangay || '').toLowerCase().includes(lq) ||
        (h.municipality || '').toLowerCase().includes(lq)
      )
    );
  },

  // ── Household Detail ──────────────────────────────────
  async openHousehold(id) {
    try {
      const data = await api.get(`api/households.php?id=${id}`);
      State.household = data.household;
      this.renderHousehold(data.household);
      this.showView('viewHousehold');
    } catch {
      notify('Failed to load household.', 'error');
    }
  },

  renderHousehold(hh) {
    el('hhDetailTitle').textContent = hh.hh_id;
    el('hhDetailAddr').textContent  = `${hh.address || ''} — ${hh.barangay}, ${hh.municipality}`;
    el('respondentList').innerHTML  = (hh.respondents || []).map(r => `
      <div class="respondent-card">
        <div class="respondent-info">
          <div class="respondent-name">${r.name}</div>
          <div class="respondent-meta">
            ${r.age ? r.age + ' yrs' : ''}
            ${r.sex ? (r.sex === 'male' ? '&bull; Male' : '&bull; Female') : ''}
            &bull; <strong>${respondentType(r.type)}</strong>
          </div>
        </div>
        <div class="respondent-actions">
          ${r.session_status === 'completed'
            ? '<span class="badge badge-green">Completed</span>'
            : `<button class="btn-primary" onclick="App.startInterview(${r.id},${hh.id},${r.session_id||'null'})">
                ${r.session_id ? 'Continue' : 'Start'} Recall
               </button>`
          }
        </div>
      </div>
    `).join('');
  },

  // ── Interview ─────────────────────────────────────────
  async startInterview(respondentId, householdId, existingSessionId) {
    try {
      let sessionId;
      if (existingSessionId) {
        sessionId = existingSessionId;
      } else {
        const data = await api.post('api/recall.php?action=start', {
          respondent_id: respondentId,
          household_id:  householdId,
          is_day2: 0,
        });
        sessionId = data.session_id;
      }
      await this.loadSession(sessionId);
    } catch {
      notify('Failed to start session.', 'error');
    }
  },

  async loadSession(sessionId) {
    const data         = await api.get(`api/recall.php?action=get&session_id=${sessionId}`);
    State.session      = data.session;
    State.foodItems    = data.session.food_items || [];
    // Restore added ingredients from server
    State.addedIngredients = {};
    State.foodItems.forEach(f => {
      if (f.added_ingredients?.length) {
        State.addedIngredients[f.id] = f.added_ingredients.map(i => ({
          ...i,
          tempId: i.id,
        }));
      }
    });
    this.renderInterviewBanner(data.session);
    this.goToPass(data.session.current_pass);
    this.showView('viewInterview');
  },

  // ── View Navigation ───────────────────────────────────
  showView(viewId) {
    ['viewHouseholds','viewHousehold','viewInterview'].forEach(v => {
      const e = el(v);
      if (e) e.hidden = (v !== viewId);
    });
    const backBtn = el('btnBack');
    const title   = el('navTitle');
    if (viewId === 'viewHouseholds') {
      backBtn.hidden    = true;
      title.textContent = 'My Households';
    } else if (viewId === 'viewHousehold') {
      backBtn.hidden    = false;
      title.textContent = State.household?.hh_id || 'Household';
    } else {
      backBtn.hidden    = false;
      title.textContent = 'Interview';
    }
  },

  goBack() {
    if (!el('viewInterview').hidden) {
      this.showView('viewHousehold');
    } else if (!el('viewHousehold').hidden) {
      this.loadHouseholds();
      this.showView('viewHouseholds');
    }
  },

  renderInterviewBanner(s) {
    el('intRespondentName').textContent = s.respondent_name;
    el('intRespondentMeta').textContent =
      `${s.age ? s.age + ' yrs' : ''} ${s.sex ? '· ' + s.sex : ''} · ${respondentType(s.respondent_type)}`;
    el('intHHId').textContent = s.hh_id;
  },

  updateStepper(currentPass) {
    for (let i = 1; i <= 5; i++) {
      const s = el(`step${i}`);
      if (!s) continue;
      s.className = 'step';
      if (i < currentPass)   s.classList.add('done');
      if (i === currentPass) s.classList.add('active');
    }
  },

  goToPass(pass) {
    this.updateStepper(pass);
    switch (pass) {
      case 1: this.renderPass1(); break;
      case 2: this.renderPass2(); break;
      case 3: this.renderPass3(); break;
      case 4: this.renderPass4(); break;
      case 5: this.renderPass5(); break;
      default: this.renderComplete();
    }
  },

  async advancePass() {
    const next = State.session.current_pass + 1;
    try {
      await api.post('api/recall.php?action=advance_pass', {
        session_id: State.session.id,
        next_pass:  next,
      });
      State.session.current_pass = next;
      if (next > 5) {
        State.session.status = 'completed';
        this.renderComplete();
        this.updateStepper(6);
      } else {
        await this.loadSession(State.session.id);
      }
    } catch {
      notify('Error advancing pass.', 'error');
    }
  },

  // ════════════════════════════════════════════════════
  // PASS 1 — Quick List
  // ════════════════════════════════════════════════════
  renderPass1() {
    el('passContent').innerHTML = `
      <div class="pass-title">Pass 1: Quick List</div>
      <div class="pass-description">
        Ask the respondent to recall <strong>all foods, drinks, and supplements</strong> consumed
        in the past 24 hours. Record names quickly — details will be captured in later passes.
      </div>
      <div class="quick-list-input">
        <input type="text" id="p1Input"
               placeholder="Type food/drink name and press Enter…"
               onkeydown="if(event.key==='Enter') App.p1AddFood()">
        <button class="btn-primary" onclick="App.p1AddFood()">Add</button>
      </div>
      <ul class="food-list" id="p1FoodList"></ul>
      <div class="pass-footer">
        <button class="btn-primary" onclick="App.advancePass()">
          Next: Forgotten Foods &rarr;
        </button>
      </div>
    `;
    this.p1RenderList();
    setTimeout(() => el('p1Input')?.focus(), 50);
  },

  p1RenderList() {
    const list  = el('p1FoodList');
    const items = State.foodItems.filter(f => !f.is_deleted);
    if (!list) return;
    if (!items.length) {
      list.innerHTML = '<li style="color:var(--text-muted);font-size:.85rem;padding:.4rem .5rem">No foods added yet.</li>';
      return;
    }
    list.innerHTML = items.map(f => `
      <li class="food-item">
        <span class="food-item-seq">${f.sequence_no}</span>
        <span class="food-item-name">${f.quick_list_name}</span>
        <div class="food-item-badges">
          ${f.source === 'forgotten_food_probe' ? '<span class="badge badge-gold">Probe</span>' : ''}
          <button class="btn-delete" onclick="App.deleteFood(${f.id})" title="Remove">&times;</button>
        </div>
      </li>
    `).join('');
  },

  async p1AddFood() {
    const inp  = el('p1Input');
    const name = inp.value.trim();
    if (!name) return;
    try {
      const res = await api.post('api/recall.php?action=add_food', {
        session_id: State.session.id,
        quick_list_name: name,
        source: 'quick_list',
        pass: 1,
      });
      State.foodItems.push({
        id: res.id, sequence_no: res.sequence_no,
        quick_list_name: name, source: 'quick_list', is_deleted: 0,
      });
      inp.value = '';
      this.p1RenderList();
    } catch {
      notify('Failed to add food item.', 'error');
    }
  },

  async deleteFood(itemId) {
    try {
      await api.delete(`api/recall.php?action=delete_food&item_id=${itemId}`);
      State.foodItems = State.foodItems.map(f =>
        f.id === itemId ? { ...f, is_deleted: 1 } : f
      );
      this.p1RenderList();
    } catch {
      notify('Failed to remove item.', 'error');
    }
  },

  // ════════════════════════════════════════════════════
  // PASS 2 — Forgotten Foods (Philippine-specific probes)
  // ════════════════════════════════════════════════════
  renderPass2() {
    const isChild = State.session.respondent_type === 'child_0_5';
    const probes  = FORGOTTEN_FOOD_PROBES.filter(p => !p.childOnly || isChild);

    el('passContent').innerHTML = `
      <div class="pass-title">Pass 2: Forgotten Foods</div>
      <div class="pass-description">
        Use these probes to uncover foods or drinks that may have been missed.
        Ask each category and add any items reported.
      </div>
      <div id="probeList">
        ${probes.map((p, i) => `
          <div class="probe-card" id="probe_${i}">
            <div class="probe-category">${p.category}</div>
            <div class="probe-question">${p.question}</div>
            <div class="probe-examples">${p.examples}</div>
            <div class="probe-btns">
              <button class="probe-btn" onclick="App.p2Answer(${i},'yes')">Yes</button>
              <button class="probe-btn" onclick="App.p2Answer(${i},'no')">No</button>
              <button class="probe-btn" onclick="App.p2Answer(${i},'unknown')">Not Sure</button>
            </div>
            <div class="probe-add-input" id="probeInput_${i}" style="display:none">
              <input type="text" placeholder="Enter food/drink name and press Enter…"
                     onkeydown="if(event.key==='Enter') App.p2AddFood(${i},this)">
              <button class="btn-sm" onclick="App.p2AddFood(${i},this.previousElementSibling)">Add</button>
            </div>
            <ul class="food-list" id="probeItems_${i}" style="margin-top:.4rem"></ul>
          </div>
        `).join('')}
      </div>
      <div class="pass-footer">
        <button class="btn-outline" onclick="App.goToPass(1)">&larr; Back</button>
        <button class="btn-primary" onclick="App.advancePass()">
          Next: Time &amp; Occasion &rarr;
        </button>
      </div>
    `;

    // Show probe-added items under first probe
    const probeItems = State.foodItems.filter(f => f.source === 'forgotten_food_probe' && !f.is_deleted);
    if (probeItems.length) {
      const list = el('probeItems_0');
      if (list) list.innerHTML = probeItems.map(f => `
        <li class="food-item">
          <span class="food-item-seq">${f.sequence_no}</span>
          <span class="food-item-name">${f.quick_list_name}</span>
        </li>
      `).join('');
    }
  },

  p2Answer(idx, answer) {
    const card    = el(`probe_${idx}`);
    const btns    = card.querySelectorAll('.probe-btn');
    const inputEl = el(`probeInput_${idx}`);
    btns.forEach(b => b.classList.remove('selected-yes','selected-no'));
    if (answer === 'yes') {
      btns[0].classList.add('selected-yes');
      inputEl.style.display = 'flex';
      inputEl.querySelector('input').focus();
    } else {
      btns[answer === 'no' ? 1 : 2].classList.add('selected-no');
      inputEl.style.display = 'none';
    }
  },

  async p2AddFood(idx, input) {
    const name = input.value.trim();
    if (!name) return;
    try {
      const res = await api.post('api/recall.php?action=add_food', {
        session_id: State.session.id,
        quick_list_name: name,
        source: 'forgotten_food_probe',
        pass: 2,
      });
      State.foodItems.push({
        id: res.id, sequence_no: res.sequence_no,
        quick_list_name: name, source: 'forgotten_food_probe', is_deleted: 0,
      });
      input.value = '';
      el(`probeItems_${idx}`).insertAdjacentHTML('beforeend', `
        <li class="food-item">
          <span class="food-item-seq">${res.sequence_no}</span>
          <span class="food-item-name">${name}</span>
          <span class="badge badge-gold">Probe</span>
        </li>
      `);
    } catch {
      notify('Failed to add food.', 'error');
    }
  },

  // ════════════════════════════════════════════════════
  // PASS 3 — Time, Occasion & Place
  // ════════════════════════════════════════════════════
  renderPass3() {
    const items           = State.foodItems.filter(f => !f.is_deleted);
    const occasionOptions = MEAL_OCCASIONS.map(o =>
      `<option value="${o.value}">${o.label}</option>`
    ).join('');
    const placeOptions = PLACE_OPTIONS.map(p =>
      `<option value="${p.value}">${p.label}</option>`
    ).join('');

    el('passContent').innerHTML = `
      <div class="pass-title">Pass 3: Time, Occasion &amp; Place</div>
      <div class="pass-description">
        For each food, record <strong>when</strong> (meal occasion and time) and
        <strong>where</strong> (place of eating) it was consumed.
      </div>

      <div class="p3-table-wrap">
        <table class="p3-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Food / Drink</th>
              <th>Meal Occasion</th>
              <th>Time</th>
              <th>Place of Eating</th>
            </tr>
          </thead>
          <tbody>
            ${items.map(f => `
              <tr id="p3row_${f.id}">
                <td class="p3-seq">${f.sequence_no}</td>
                <td class="p3-name">${f.quick_list_name}</td>
                <td>
                  <select id="occ_${f.id}" class="p3-select" onchange="App.p3Save(${f.id})">
                    <option value="">— Select —</option>
                    ${occasionOptions}
                  </select>
                </td>
                <td>
                  <input type="time" id="time_${f.id}" class="p3-time" step="900"
                         onchange="App.p3Save(${f.id})">
                </td>
                <td>
                  <select id="place_${f.id}" class="p3-select" onchange="App.p3Save(${f.id})">
                    <option value="">— Select —</option>
                    ${placeOptions}
                  </select>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>

      <div class="pass-footer">
        <button class="btn-outline" onclick="App.goToPass(2)">&larr; Back</button>
        <button class="btn-primary" onclick="App.advancePass()">
          Next: Detail Cycle &rarr;
        </button>
      </div>
    `;

    // Pre-fill saved values
    items.forEach(f => {
      if (f.meal_occasion) {
        const s = el(`occ_${f.id}`);
        if (s) s.value = f.meal_occasion;
      }
      if (f.meal_time) {
        const t = el(`time_${f.id}`);
        if (t) t.value = f.meal_time.slice(0,5);
      }
      if (f.place_of_consumption) {
        const p = el(`place_${f.id}`);
        if (p) p.value = f.place_of_consumption;
      }
    });
  },

  async p3Save(itemId) {
    const occ   = el(`occ_${itemId}`)?.value   || null;
    const time  = el(`time_${itemId}`)?.value  || null;
    const place = el(`place_${itemId}`)?.value || null;
    try {
      await api.put('api/recall.php?action=update_food', {
        id: itemId,
        meal_occasion:         occ,
        meal_time:             time || null,
        place_of_consumption:  place,
      });
      State.foodItems = State.foodItems.map(f =>
        f.id === itemId
          ? { ...f, meal_occasion: occ, meal_time: time, place_of_consumption: place }
          : f
      );
    } catch { /* silent, will sync on next save */ }
  },

  // ════════════════════════════════════════════════════
  // PASS 4 — Detail Cycle
  // (FCT identification, brand, amount, preparation, added ingredients)
  // ════════════════════════════════════════════════════
  renderPass4() {
    const items = State.foodItems.filter(f => !f.is_deleted);
    el('passContent').innerHTML = `
      <div class="pass-title">Pass 4: Detail Cycle</div>
      <div class="pass-description">
        For each food, identify it in the <strong>FNRI Food Composition Table</strong>,
        specify the <strong>amount</strong> in household measures, record the
        <strong>preparation method</strong>, and add any <strong>ingredients
        used in cooking or at the table</strong> (oil, sugar, condiments).
      </div>
      <div id="p4List">
        ${items.map(f => this.p4ItemHTML(f)).join('')}
      </div>
      <div class="pass-footer">
        <button class="btn-outline" onclick="App.goToPass(3)">&larr; Back</button>
        <button class="btn-primary" onclick="App.advancePass()">
          Next: Review &rarr;
        </button>
      </div>
    `;
  },

  p4ItemHTML(f) {
    const isDone   = f.fct_food_id && f.amount_grams;
    const mealLbl  = f.meal_occasion ? mealLabel(f.meal_occasion) : 'No occasion';
    const placeLbl = f.place_of_consumption
      ? (PLACE_OPTIONS.find(p => p.value === f.place_of_consumption)?.label || f.place_of_consumption)
      : '';
    const kcal     = f.energy_kcal ? ` · ${fmt(f.energy_kcal, 0)} kcal` : '';

    return `
      <div class="detail-item" id="detailItem_${f.id}">
        <div class="detail-item-header" onclick="App.p4Toggle(${f.id})">
          <div>
            <div class="detail-item-title">${f.food_name || f.quick_list_name}</div>
            <div class="detail-item-meta">
              ${mealLbl}${f.meal_time ? ' · ' + f.meal_time.slice(0,5) : ''}
              ${placeLbl ? ' &bull; ' + placeLbl : ''}
            </div>
          </div>
          <div class="detail-item-status">
            ${isDone
              ? `<span class="badge badge-green">&#10003;${kcal}</span>`
              : '<span class="badge badge-gray">Needs detail</span>'}
          </div>
        </div>
        <div class="detail-item-body" id="detailBody_${f.id}" style="display:none">
          ${this.p4BodyHTML(f)}
        </div>
      </div>
    `;
  },

  p4BodyHTML(f) {
    const measures = [];
    if (f.measure_1_desc) measures.push(`<option value="${f.measure_1_grams}">${f.measure_1_desc} (~${f.measure_1_grams}g)</option>`);
    if (f.measure_2_desc) measures.push(`<option value="${f.measure_2_grams}">${f.measure_2_desc} (~${f.measure_2_grams}g)</option>`);
    if (f.measure_3_desc) measures.push(`<option value="${f.measure_3_grams}">${f.measure_3_desc} (~${f.measure_3_grams}g)</option>`);

    const hasFCT   = !!f.fct_food_id;
    const hasGrams = hasFCT && f.amount_grams;
    const ings     = State.addedIngredients[f.id] || [];

    return `
      <!-- Step 1: FCT Identification -->
      <div class="detail-step">
        <div class="detail-step-label">1. Identify Food (FNRI FCT)</div>
        ${hasFCT ? `
          <div class="fct-selected">
            <div>
              <div class="fct-selected-name">${f.fct_food_name || f.food_name}</div>
              <div class="fct-selected-group">${f.food_group || ''}</div>
            </div>
            <button class="btn-sm" onclick="App.openFCT(${f.id},'food')">Change</button>
          </div>
        ` : `
          <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
            <button class="btn-outline" onclick="App.openFCT(${f.id},'food')">
              Search FNRI FCT for &ldquo;${f.quick_list_name}&rdquo;
            </button>
            <span style="font-size:.8rem;color:var(--text-muted)">or enter manually below</span>
          </div>
          <div class="field" style="margin-top:.5rem">
            <input type="text" id="foodNameManual_${f.id}"
                   value="${f.food_name || f.quick_list_name}"
                   placeholder="Food name if not in FCT">
          </div>
        `}
      </div>

      <!-- Step 2: Brand name (optional) -->
      ${hasFCT ? `
        <div class="detail-step">
          <div class="detail-step-label">2. Brand Name <span class="optional">(if applicable)</span></div>
          <input type="text" id="brandName_${f.id}" class="field-input"
                 value="${f.brand_name || ''}"
                 placeholder="e.g. Lucky Me, Nestlé, San Miguel, UFC — leave blank if none">
        </div>
      ` : ''}

      <!-- Step 3: Amount and Preparation -->
      ${hasFCT ? `
        <div class="detail-step">
          <div class="detail-step-label">3. Amount &amp; Preparation</div>
          <div class="portion-row">
            ${measures.length ? `
              <div class="field">
                <label>Household Measure</label>
                <select id="hmSel_${f.id}" onchange="App.p4HMChange(${f.id},this)">
                  <option value="">— Select measure —</option>
                  ${measures.join('')}
                  <option value="custom">Enter grams manually</option>
                </select>
              </div>
            ` : ''}
            <div class="field">
              <label>Amount (grams)</label>
              <input type="number" id="gramsInput_${f.id}" min="1" max="2000" step="0.5"
                     value="${f.amount_grams || ''}" placeholder="e.g. 240"
                     oninput="App.p4UpdateNutrients(${f.id})">
            </div>
            <div class="field">
              <label>Cooking / Preparation Method</label>
              <input type="text" id="cookMethod_${f.id}" class="field-input"
                     value="${f.cooking_method || ''}"
                     placeholder="e.g. boiled, fried in oil, raw, steamed, sautéed">
            </div>
          </div>
        </div>
      ` : ''}

      <!-- Step 4: Added Ingredients -->
      ${hasFCT ? `
        <div class="detail-step">
          <div class="detail-step-label">4. Added Ingredients
            <span class="optional">cooking oil, sugar, condiments used in cooking or at table</span>
          </div>

          <div class="quick-ing-bar">
            ${QUICK_INGREDIENTS.map(qi => `
              <button class="quick-ing-btn"
                      onclick="App.p4ShowQuickIng(${f.id},'${qi.key}')">
                ${qi.label}
              </button>
            `).join('')}
            <button class="quick-ing-btn quick-ing-fct"
                    onclick="App.openFCT(${f.id},'ingredient')">
              + Other (search FCT)
            </button>
          </div>

          <!-- Quick ingredient amount form -->
          <div id="quickIngForm_${f.id}" class="quick-ing-form" style="display:none"></div>

          <!-- Added ingredients list -->
          <ul class="added-ing-list" id="addedIngList_${f.id}">
            ${this.p4IngListHTML(f.id)}
          </ul>
        </div>
      ` : ''}

      <!-- Step 5: Calculated Nutrients (base + added) -->
      ${hasFCT ? `
        <div class="detail-step">
          <div class="detail-step-label">5. Calculated Nutrients (per amount consumed)</div>
          <div id="nutrientStrip_${f.id}" class="nutrient-strip" style="${f.amount_grams ? '' : 'display:none'}">
            <div class="nutrient-chip">Energy: <strong id="nc_kcal_${f.id}">${fmt(f.energy_kcal)} kcal</strong></div>
            <div class="nutrient-chip">Protein: <strong id="nc_prot_${f.id}">${fmt(f.protein_g)} g</strong></div>
            <div class="nutrient-chip">Fat: <strong id="nc_fat_${f.id}">${fmt(f.fat_g)} g</strong></div>
            <div class="nutrient-chip">Carbs: <strong id="nc_carb_${f.id}">${fmt(f.carbs_g)} g</strong></div>
            <div class="nutrient-chip">Fiber: <strong id="nc_fib_${f.id}">${fmt(f.fiber_g)} g</strong></div>
            <div class="nutrient-chip-note">Includes added ingredients</div>
          </div>
          <div style="margin-top:.6rem;text-align:right">
            <button class="btn-sm" onclick="App.p4Save(${f.id})">Save Details &#10003;</button>
          </div>
        </div>
      ` : `
        <div style="text-align:right;margin-top:.75rem">
          <button class="btn-sm" onclick="App.p4SaveManual(${f.id})">Save &#10003;</button>
        </div>
      `}
    `;
  },

  p4Toggle(id) {
    const body = el(`detailBody_${id}`);
    if (body) body.style.display = body.style.display === 'none' ? 'block' : 'none';
  },

  p4HMChange(itemId, sel) {
    if (!sel.value || sel.value === 'custom') return;
    const grams = el(`gramsInput_${itemId}`);
    if (grams) { grams.value = sel.value; this.p4UpdateNutrients(itemId); }
  },

  // ── Nutrient calculation: base + added ingredients ───
  p4CalcTotals(itemId) {
    const item  = State.foodItems.find(f => f.id === itemId);
    const grams = parseFloat(el(`gramsInput_${itemId}`)?.value || 0);
    const r     = item?.fct_energy_per100 && grams ? grams / 100 : 0;

    const base = {
      energy:  (item?.fct_energy_per100  || 0) * r,
      protein: (item?.fct_protein_per100 || 0) * r,
      fat:     (item?.fct_fat_per100     || 0) * r,
      carbs:   (item?.fct_carbs_per100   || 0) * r,
      fiber:   (item?.fct_fiber_per100   || 0) * r,
    };

    const ings  = State.addedIngredients[itemId] || [];
    const added = ings.reduce((acc, ing) => ({
      energy:  acc.energy  + (+ing.energy_kcal || 0),
      protein: acc.protein + (+ing.protein_g   || 0),
      fat:     acc.fat     + (+ing.fat_g       || 0),
      carbs:   acc.carbs   + (+ing.carbs_g     || 0),
      fiber:   acc.fiber   + (+ing.fiber_g     || 0),
    }), { energy:0, protein:0, fat:0, carbs:0, fiber:0 });

    return {
      energy:  base.energy  + added.energy,
      protein: base.protein + added.protein,
      fat:     base.fat     + added.fat,
      carbs:   base.carbs   + added.carbs,
      fiber:   base.fiber   + added.fiber,
    };
  },

  p4UpdateNutrients(itemId) {
    const t     = this.p4CalcTotals(itemId);
    const strip = el(`nutrientStrip_${itemId}`);
    const grams = parseFloat(el(`gramsInput_${itemId}`)?.value || 0);
    if (!strip) return;

    if (grams > 0) {
      strip.style.display = '';
      el(`nc_kcal_${itemId}`).textContent = fmt(t.energy) + ' kcal';
      el(`nc_prot_${itemId}`).textContent  = fmt(t.protein) + ' g';
      el(`nc_fat_${itemId}`).textContent   = fmt(t.fat) + ' g';
      el(`nc_carb_${itemId}`).textContent  = fmt(t.carbs) + ' g';
      el(`nc_fib_${itemId}`).textContent   = fmt(t.fiber) + ' g';
    }
  },

  // ── Added Ingredients ────────────────────────────────
  p4IngListHTML(itemId) {
    const ings = State.addedIngredients[itemId] || [];
    if (!ings.length) return '';
    return ings.map((ing, idx) => `
      <li class="added-ing-item">
        <div class="added-ing-name">${ing.ingredient_name}</div>
        <div class="added-ing-detail">
          ${ing.amount_desc || ''} (${fmt(ing.amount_grams, 1)}g) &bull;
          ${fmt(ing.energy_kcal, 1)} kcal &bull;
          P:${fmt(ing.protein_g,1)}g F:${fmt(ing.fat_g,1)}g C:${fmt(ing.carbs_g,1)}g
        </div>
        <button class="btn-delete" onclick="App.p4RemoveIng(${itemId}, ${idx})">&times;</button>
      </li>
    `).join('');
  },

  p4RefreshIngList(itemId) {
    const list = el(`addedIngList_${itemId}`);
    if (list) list.innerHTML = this.p4IngListHTML(itemId);
    this.p4UpdateNutrients(itemId);
  },

  p4ShowQuickIng(itemId, key) {
    const qi   = QUICK_INGREDIENTS.find(q => q.key === key);
    if (!qi) return;
    const form = el(`quickIngForm_${itemId}`);
    if (!form) return;

    State.activeQuickIng = { itemId, qi };
    form.style.display = 'flex';
    form.innerHTML = `
      <span class="quick-ing-form-name">${qi.name}</span>
      <input type="number" id="quickIngAmt_${itemId}" class="quick-ing-amt"
             value="${qi.grams}" min="0.5" max="500" step="0.5">
      <span class="quick-ing-unit">g <small>(${qi.desc} = ${qi.grams}g)</small></span>
      <button class="btn-sm" onclick="App.p4ConfirmQuickIng(${itemId})">Add</button>
      <button class="btn-delete" onclick="App.p4CancelQuickIng(${itemId})">Cancel</button>
    `;
    form.querySelector('input')?.focus();
  },

  p4ConfirmQuickIng(itemId) {
    const { qi } = State.activeQuickIng || {};
    if (!qi) return;

    const grams = parseFloat(el(`quickIngAmt_${itemId}`)?.value || qi.grams);
    if (!grams || grams <= 0) return;

    const r   = grams / 100;
    const ing = {
      tempId:          Date.now(),
      ingredient_name: qi.name,
      fct_food_id:     null,
      amount_desc:     `${grams}g`,
      amount_grams:    grams,
      energy_kcal:     qi.per100.energy  * r,
      protein_g:       qi.per100.protein * r,
      fat_g:           qi.per100.fat     * r,
      carbs_g:         qi.per100.carbs   * r,
      fiber_g:         qi.per100.fiber   * r,
    };

    if (!State.addedIngredients[itemId]) State.addedIngredients[itemId] = [];
    State.addedIngredients[itemId].push(ing);

    el(`quickIngForm_${itemId}`).style.display = 'none';
    State.activeQuickIng = null;
    this.p4RefreshIngList(itemId);
  },

  p4CancelQuickIng(itemId) {
    const form = el(`quickIngForm_${itemId}`);
    if (form) form.style.display = 'none';
    State.activeQuickIng = null;
  },

  p4RemoveIng(itemId, idx) {
    if (!State.addedIngredients[itemId]) return;
    State.addedIngredients[itemId].splice(idx, 1);
    this.p4RefreshIngList(itemId);
  },

  // ── Save food item detail + added ingredients ────────
  async p4Save(itemId) {
    const item   = State.foodItems.find(f => f.id === itemId);
    if (!item) return;
    const grams  = parseFloat(el(`gramsInput_${itemId}`)?.value || 0);
    if (!grams) { notify('Please enter an amount in grams.', 'error'); return; }

    const totals = this.p4CalcTotals(itemId);
    const brand  = el(`brandName_${itemId}`)?.value.trim() || null;
    const method = el(`cookMethod_${itemId}`)?.value.trim() || null;

    try {
      // Save food item with total nutrients
      await api.put('api/recall.php?action=update_food', {
        id:              itemId,
        food_name:       item.food_name,
        brand_name:      brand,
        cooking_method:  method,
        amount_grams:    grams,
        energy_kcal:     totals.energy,
        protein_g:       totals.protein,
        fat_g:           totals.fat,
        carbs_g:         totals.carbs,
        fiber_g:         totals.fiber,
      });

      // Save added ingredients
      const ings = State.addedIngredients[itemId] || [];
      await api.post('api/ingredients.php', {
        food_item_id: itemId,
        ingredients:  ings.map(ing => ({
          ingredient_name: ing.ingredient_name,
          fct_food_id:     ing.fct_food_id || null,
          amount_desc:     ing.amount_desc,
          amount_grams:    ing.amount_grams,
          energy_kcal:     ing.energy_kcal,
          protein_g:       ing.protein_g,
          fat_g:           ing.fat_g,
          carbs_g:         ing.carbs_g,
          fiber_g:         ing.fiber_g,
        })),
      });

      // Update local state
      Object.assign(item, {
        brand_name:     brand,
        cooking_method: method,
        amount_grams:   grams,
        energy_kcal:    totals.energy,
        protein_g:      totals.protein,
        fat_g:          totals.fat,
        carbs_g:        totals.carbs,
        fiber_g:        totals.fiber,
      });

      // Update header badge
      const header = document.querySelector(`#detailItem_${itemId} .detail-item-status`);
      if (header) header.innerHTML =
        `<span class="badge badge-green">&#10003; ${fmt(totals.energy, 0)} kcal</span>`;

      notify('Saved.');
    } catch {
      notify('Failed to save details.', 'error');
    }
  },

  async p4SaveManual(itemId) {
    const name = el(`foodNameManual_${itemId}`)?.value.trim();
    if (!name) return;
    try {
      await api.put('api/recall.php?action=update_food', { id: itemId, food_name: name });
      State.foodItems = State.foodItems.map(f =>
        f.id === itemId ? { ...f, food_name: name } : f
      );
      notify('Saved.');
    } catch {
      notify('Failed to save.', 'error');
    }
  },

  // ════════════════════════════════════════════════════
  // FCT MODAL (dual mode: food identification / ingredient)
  // ════════════════════════════════════════════════════
  async openFCT(itemId, mode = 'food') {
    State.fctItemId = itemId;
    State.fctMode   = mode;

    const item = State.foodItems.find(f => f.id === itemId);
    const titleEl = document.querySelector('#fctModal .modal-header h3');
    if (titleEl) {
      titleEl.textContent = mode === 'ingredient'
        ? 'Add Ingredient (FNRI FCT)'
        : 'Identify Food (FNRI FCT)';
    }

    show('fctModal');
    el('fctSearch').value = (mode === 'food' && item?.quick_list_name) ? item.quick_list_name : '';

    try {
      const data = await api.get('api/fct.php?groups=1');
      el('fctGroups').innerHTML = data.groups.map(g =>
        `<button class="fct-group-btn" onclick="App.fctFilterGroup('${encodeURIComponent(g)}')">${g}</button>`
      ).join('');
    } catch {}

    this.searchFCT(el('fctSearch').value);
    setTimeout(() => el('fctSearch')?.focus(), 50);
  },

  closeFCT() {
    hide('fctModal');
    el('fctSearch').value = '';
    el('fctResults').innerHTML = '';
    el('fctGroups').querySelectorAll('.fct-group-btn').forEach(b => b.classList.remove('active'));
  },

  async searchFCT(q) {
    const url = q ? `api/fct.php?q=${encodeURIComponent(q)}` : `api/fct.php`;
    try {
      const data = await api.get(url);
      this.renderFCTResults(data.foods || []);
    } catch {}
  },

  async fctFilterGroup(group) {
    el('fctGroups').querySelectorAll('.fct-group-btn').forEach(b => b.classList.remove('active'));
    event?.target?.classList?.add('active');
    el('fctSearch').value = '';
    try {
      const data = await api.get(`api/fct.php?group=${group}`);
      this.renderFCTResults(data.foods || []);
    } catch {}
  },

  renderFCTResults(foods) {
    const container = el('fctResults');
    if (!foods.length) {
      container.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:1rem;font-size:.85rem">No results found.</p>';
      return;
    }
    container.innerHTML = foods.map(f => `
      <div class="fct-result-item" onclick="App.selectFCT(${f.id})">
        <div class="fct-result-name">${f.food_name}</div>
        ${f.local_name ? `<div class="fct-result-local">${f.local_name}</div>` : ''}
        <div class="fct-result-kcal">
          ${f.food_group} &bull;
          <strong>${fmt(f.energy_kcal)} kcal</strong>/100g &bull;
          P:${fmt(f.protein_g)}g &bull; F:${fmt(f.fat_g)}g &bull; C:${fmt(f.carbs_g)}g
        </div>
      </div>
    `).join('');
  },

  async selectFCT(fctId) {
    const itemId = State.fctItemId;
    try {
      const data = await api.get(`api/fct.php?id=${fctId}`);
      const food = data.food;
      this.closeFCT();

      if (State.fctMode === 'food') {
        // Update food item with FCT data
        State.foodItems = State.foodItems.map(f => {
          if (f.id !== itemId) return f;
          return {
            ...f,
            fct_food_id:        food.id,
            food_name:          food.food_name,
            food_group:         food.food_group,
            fct_food_name:      food.food_name,
            fct_energy_per100:  +food.energy_kcal,
            fct_protein_per100: +food.protein_g,
            fct_fat_per100:     +food.fat_g,
            fct_carbs_per100:   +food.carbs_g,
            fct_fiber_per100:   +food.fiber_g,
            measure_1_desc:     food.measure_1_desc,
            measure_1_grams:    food.measure_1_grams,
            measure_2_desc:     food.measure_2_desc,
            measure_2_grams:    food.measure_2_grams,
            measure_3_desc:     food.measure_3_desc,
            measure_3_grams:    food.measure_3_grams,
          };
        });
        await api.put('api/recall.php?action=update_food', {
          id: itemId, fct_food_id: fctId, food_name: food.food_name,
        });
        // Refresh body
        const body = el(`detailBody_${itemId}`);
        const item = State.foodItems.find(f => f.id === itemId);
        if (body && item) {
          body.innerHTML = this.p4BodyHTML(item);
          body.style.display = 'block';
        }

      } else {
        // Ingredient mode: add as added ingredient
        const grams = food.measure_1_grams || 15;
        const r     = grams / 100;
        const ing   = {
          tempId:          Date.now(),
          ingredient_name: food.food_name,
          fct_food_id:     food.id,
          amount_desc:     food.measure_1_desc || `${grams}g`,
          amount_grams:    grams,
          energy_kcal:     +food.energy_kcal  * r,
          protein_g:       +food.protein_g    * r,
          fat_g:           +food.fat_g        * r,
          carbs_g:         +food.carbs_g      * r,
          fiber_g:         +food.fiber_g      * r,
        };
        if (!State.addedIngredients[itemId]) State.addedIngredients[itemId] = [];
        State.addedIngredients[itemId].push(ing);
        this.p4RefreshIngList(itemId);
      }
    } catch {
      notify('Failed to select food.', 'error');
    }
  },

  // ════════════════════════════════════════════════════
  // PASS 5 — Review + Nutrient Adequacy (RENI)
  // ════════════════════════════════════════════════════
  renderPass5() {
    const items = State.foodItems.filter(f => !f.is_deleted);
    const reni  = this.getRENI(State.session);

    // Group by meal occasion
    const ORDER   = ['early_morning','breakfast','morning_snack','lunch','afternoon_snack','dinner','evening_snack','overnight','other'];
    const grouped = {};
    items.forEach(f => {
      const key = f.meal_occasion || 'other';
      if (!grouped[key]) grouped[key] = [];
      grouped[key].push(f);
    });
    const sortedKeys = ORDER.filter(k => grouped[k]);

    // Sum totals
    let totals = { energy:0, protein:0, fat:0, carbs:0, fiber:0, calcium:0, iron:0 };
    items.forEach(f => {
      totals.energy  += +f.energy_kcal || 0;
      totals.protein += +f.protein_g   || 0;
      totals.fat     += +f.fat_g       || 0;
      totals.carbs   += +f.carbs_g     || 0;
      totals.fiber   += +f.fiber_g     || 0;
    });

    el('passContent').innerHTML = `
      <div class="pass-title">Pass 5: Review</div>
      <div class="pass-description">
        Review the complete food recall with the respondent. Make any final corrections,
        additions, or deletions before submitting.
      </div>

      ${sortedKeys.map(key => `
        <div class="meal-group">
          <div class="meal-group-header">${mealLabel(key)}</div>
          ${grouped[key].map(f => {
            const ings = State.addedIngredients[f.id] || f.added_ingredients || [];
            const place = f.place_of_consumption
              ? (PLACE_OPTIONS.find(p => p.value === f.place_of_consumption)?.label || f.place_of_consumption)
              : '';
            return `
              <div class="review-item">
                <div style="flex:1">
                  <div class="review-item-name">${f.food_name || f.quick_list_name}</div>
                  <div class="review-item-detail">
                    ${f.amount_grams ? f.amount_grams + 'g' : '<em>Amount not specified</em>'}
                    ${f.cooking_method ? ' · ' + f.cooking_method : ''}
                    ${f.brand_name ? ' · <em>' + f.brand_name + '</em>' : ''}
                    ${place ? ' &bull; ' + place : ''}
                    ${f.meal_time ? ' &bull; ' + f.meal_time.slice(0,5) : ''}
                    ${f.source === 'forgotten_food_probe' ? ' &bull; <span class="badge badge-gold" style="font-size:.65rem">Probe</span>' : ''}
                  </div>
                  ${ings.length ? `
                    <div class="review-item-ings">
                      <span class="review-ings-label">Added:</span>
                      ${ings.map(i =>
                        `<span class="review-ing-tag">${i.ingredient_name} ${i.amount_desc || ''}</span>`
                      ).join('')}
                    </div>
                  ` : ''}
                </div>
                <div class="review-item-kcal">
                  ${f.energy_kcal ? fmt(f.energy_kcal, 0) + ' kcal' : '—'}
                </div>
              </div>
            `;
          }).join('')}
        </div>
      `).join('')}

      <!-- Daily Totals -->
      <div class="nutrient-summary">
        <div class="nutrient-summary-item">
          <div class="nutrient-summary-value">${fmt(totals.energy, 0)}</div>
          <div class="nutrient-summary-label">Total Energy (kcal)</div>
        </div>
        <div class="nutrient-summary-item">
          <div class="nutrient-summary-value">${fmt(totals.protein)}</div>
          <div class="nutrient-summary-label">Protein (g)</div>
        </div>
        <div class="nutrient-summary-item">
          <div class="nutrient-summary-value">${fmt(totals.fat)}</div>
          <div class="nutrient-summary-label">Fat (g)</div>
        </div>
        <div class="nutrient-summary-item">
          <div class="nutrient-summary-value">${fmt(totals.carbs)}</div>
          <div class="nutrient-summary-label">Carbohydrates (g)</div>
        </div>
        <div class="nutrient-summary-item">
          <div class="nutrient-summary-value">${items.length}</div>
          <div class="nutrient-summary-label">Food Items</div>
        </div>
      </div>

      <!-- RENI Nutrient Adequacy Table -->
      ${this.renderRENITable(reni, totals)}

      <div class="pass-footer" style="flex-direction:column;gap:.5rem">
        <button class="btn-outline" onclick="App.goToPass(4)">&larr; Back to Details</button>
        <button class="btn-primary btn-full" onclick="App.submitRecall()">
          &#10003; Complete &amp; Submit Recall
        </button>
      </div>
    `;
  },

  getRENI(session) {
    const age  = +session.age;
    const type = session.respondent_type;
    if (type === 'child_0_5') {
      if (!age || age < 1)  return RENI_REFERENCE.child_0_6mo;
      if (age < 2)          return RENI_REFERENCE.child_1_3;
      return RENI_REFERENCE.child_3_5;
    }
    if (age && age >= 30)   return RENI_REFERENCE.wra_30_49;
    return RENI_REFERENCE.wra_19_29;
  },

  renderRENITable(reni, totals) {
    const rows = [
      { label: 'Energy',        unit: 'kcal', value: totals.energy,  ref: reni.energy  },
      { label: 'Protein',       unit: 'g',    value: totals.protein, ref: reni.protein },
      { label: 'Total Fat',     unit: 'g',    value: totals.fat,     ref: reni.fat     },
      { label: 'Carbohydrates', unit: 'g',    value: totals.carbs,   ref: reni.carbs   },
      { label: 'Dietary Fiber', unit: 'g',    value: totals.fiber,   ref: reni.fiber   },
    ].filter(r => r.ref);

    const badge = (pct) => {
      if (pct > 120) return ['excess',    'Excess'];
      if (pct >= 90) return ['adequate',  'Adequate'];
      if (pct >= 66) return ['below',     'Below'];
      return             ['deficient', 'Deficient'];
    };

    return `
      <div class="reni-wrap">
        <div class="reni-title">
          Nutrient Adequacy — Philippine RENI
          <span class="reni-subtitle">${reni.label}</span>
        </div>
        <table class="reni-table">
          <thead>
            <tr>
              <th>Nutrient</th>
              <th>Consumed</th>
              <th>RENI</th>
              <th colspan="2">% Adequacy</th>
            </tr>
          </thead>
          <tbody>
            ${rows.map(row => {
              const pct         = row.ref > 0 ? Math.round((row.value / row.ref) * 100) : 0;
              const [cls, lbl]  = badge(pct);
              const barW        = Math.min(100, pct);
              return `
                <tr>
                  <td class="reni-nutrient">${row.label}</td>
                  <td class="reni-value">${fmt(row.value, 1)} ${row.unit}</td>
                  <td class="reni-ref">${row.ref} ${row.unit}</td>
                  <td class="reni-bar-cell">
                    <div class="reni-bar-track">
                      <div class="reni-bar-fill reni-${cls}" style="width:${barW}%"></div>
                      <div class="reni-bar-100"></div>
                    </div>
                    <span class="reni-pct">${pct}%</span>
                  </td>
                  <td><span class="badge-reni badge-reni-${cls}">${lbl}</span></td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
        <p class="reni-note">
          * Adequacy: &ge;90% Adequate, 66–89% Below, &lt;66% Deficient, &gt;120% Excess
        </p>
      </div>
    `;
  },

  async submitRecall() {
    if (!confirm('Submit this recall? This action cannot be undone.')) return;
    try {
      await api.post('api/recall.php?action=advance_pass', {
        session_id: State.session.id,
        next_pass:  6,
      });
      State.session.status = 'completed';
      this.renderComplete();
      this.updateStepper(6);
    } catch {
      notify('Failed to submit.', 'error');
    }
  },

  renderComplete() {
    el('passContent').innerHTML = `
      <div class="pass-complete-banner">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="12" cy="12" r="10"/>
          <path d="M8 12l3 3 5-5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <h3>Recall Completed</h3>
        <p>The 24-hour food recall for <strong>${State.session.respondent_name}</strong>
           has been successfully recorded and submitted.</p>
        <div style="margin-top:1.5rem;display:flex;gap:.75rem;justify-content:center">
          <button class="btn-primary"
                  onclick="App.showView('viewHousehold');App.openHousehold(${State.session.household_id})">
            Back to Household
          </button>
        </div>
      </div>
    `;
  },
};

document.addEventListener('DOMContentLoaded', () => App.init());
