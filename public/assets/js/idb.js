/* ============================================================
   idb.js — IndexedDB wrapper for offline storage
   ============================================================ */
const IDB = (() => {
  const NAME = 'fr24_offline';
  const VER  = 1;
  let _db;

  const open = () => _db ? Promise.resolve(_db) : new Promise((ok, fail) => {
    const req = indexedDB.open(NAME, VER);
    req.onsuccess = () => { _db = req.result; ok(_db); };
    req.onerror   = () => fail(req.error);
    req.onupgradeneeded = ({ target: { result: db } }) => {
      const mk = (name, opts, idxs = []) => {
        if (db.objectStoreNames.contains(name)) return;
        const s = db.createObjectStore(name, opts);
        idxs.forEach(([n, p, o]) => s.createIndex(n, p, o || {}));
      };
      mk('households',  { keyPath: 'id' },                          [['by_ivr', 'assigned_interviewer_id']]);
      mk('respondents', { keyPath: 'id' },                          [['by_hh',  'household_id']]);
      mk('fct_foods',   { keyPath: 'id' },                          [['by_name','food_name']]);
      mk('sessions',    { keyPath: '_lid', autoIncrement: true },   [['by_sid', 'server_id'], ['by_resp','respondent_id']]);
      mk('food_items',  { keyPath: '_lid', autoIncrement: true },   [['by_sess','_sess_lid'], ['by_sid', 'server_id']]);
      mk('meta',        { keyPath: 'k' });
    };
  });

  const w = req => new Promise((ok, fail) => {
    req.onsuccess = () => ok(req.result);
    req.onerror   = () => fail(req.error);
  });

  const ro = store => open().then(db => db.transaction(store, 'readonly').objectStore(store));
  const rw = store => open().then(db => db.transaction(store, 'readwrite').objectStore(store));

  return {
    get:    (s, k) => ro(s).then(st => w(st.get(k))),
    put:    (s, v) => rw(s).then(st => w(st.put(v))),
    del:    (s, k) => rw(s).then(st => w(st.delete(k))),
    getAll: (s)    => ro(s).then(st => w(st.getAll())),

    getByIndex(s, idx, val) {
      return open().then(db =>
        w(db.transaction(s, 'readonly').objectStore(s).index(idx).getAll(val))
      );
    },

    putMany(s, items) {
      return open().then(db => new Promise((ok, fail) => {
        const t  = db.transaction(s, 'readwrite');
        const st = t.objectStore(s);
        items.forEach(i => st.put(i));
        t.oncomplete = ok;
        t.onerror    = () => fail(t.error);
      }));
    },

    getMeta: k      => open().then(db => w(db.transaction('meta').objectStore('meta').get(k))).then(r => r?.v),
    setMeta: (k, v) => open().then(db => w(db.transaction('meta', 'readwrite').objectStore('meta').put({ k, v }))),
  };
})();
