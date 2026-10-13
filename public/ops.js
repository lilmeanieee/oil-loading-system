/* PA Fuel 118 - Operations pages (Dashboard, ATL Approvals, Documents & Release, Hauler Management).
   DEMO ONLY: data comes from App\Support\OpsData and the working copy is kept in this browser
   (localStorage) so approvals, printouts and master-data edits carry over between pages.
   The backend developer replaces load() and save() with real requests. */
(function () {
  'use strict';

  var KEY = 'pafuel118.ops.v1';
  var NOW = new Date();
  var TODAY = new Date(NOW.getFullYear(), NOW.getMonth(), NOW.getDate());

  var PILL = {
    ok: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
    warn: 'bg-amber-50 text-amber-800 ring-amber-200',
    bad: 'bg-red-50 text-red-800 ring-red-200',
    info: 'bg-sky-50 text-sky-800 ring-sky-200',
    acc: 'bg-teal-50 text-teal-800 ring-teal-200',
    '': 'bg-slate-100 text-slate-700 ring-slate-200'
  };
  var STAGE = {
    lt: ['warn', 'LT to print'],
    'return': ['info', 'Awaiting driver return'],
    release: ['warn', 'DR/WC to print'],
    handover: ['acc', 'Ready for handover'],
    done: ['ok', 'Released']
  };
  var ASTAT = {
    pending: ['warn', 'Pending review'],
    resubmitted: ['info', 'Resubmitted'],
    returned: ['bad', 'Returned'],
    declined: ['bad', 'Declined'],
    approved: ['ok', 'Approved']
  };
  var CHK = [
    ['truck', 'Truck', 'Plate, registration and certificates'],
    ['driver', 'Driver', 'Licence and hauler match'],
    ['hauler', 'Hauler', 'Active and matches truck'],
    ['products', 'Products', 'Product named for every compartment'],
    ['liters', 'Liters', 'Total against truck capacity'],
    ['comps', 'Compartments', 'Each load fits its compartment']
  ];

  function sum(a) { return a.reduce(function (x, y) { return x + y; }, 0); }
  function two(n) { return String(n).padStart(2, '0'); }
  function nowHM() { var d = new Date(); return two(d.getHours()) + ':' + two(d.getMinutes()); }
  function fmtL(n) { return Number(n).toLocaleString('en-US') + ' L'; }
  function parseD(s) { var p = s.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); }
  function fmtD(s) { return parseD(s).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); }
  function daysTo(s) { return Math.round((parseD(s) - TODAY) / 864e5); }
  function certState(s) {
    var n = daysTo(s);
    return n < 0 ? ['bad', 'Expired ' + fmtD(s)] : n <= 30 ? ['warn', 'Expires in ' + n + ' d'] : ['ok', 'Valid to ' + fmtD(s)];
  }
  function parseComps(t) {
    return String(t || '').split(',').map(function (x) { return parseInt(x.replace(/\D/g, ''), 10); }).filter(function (x) { return x > 0; });
  }

  function load(seed) {
    try {
      var s = JSON.parse(localStorage.getItem(KEY));
      if (s && s.ver === seed.ver) { s.user = seed.user; if (s.staff.indexOf(seed.user) < 0) s.staff.unshift(seed.user); return s; }
    } catch (e) { /* ignore */ }
    return JSON.parse(JSON.stringify(seed));
  }
  function save(d) { try { localStorage.setItem(KEY, JSON.stringify(d)); } catch (e) { /* ignore */ } }

  function OpsApp(seed, opts) {
    opts = opts || {};
    var q = {};
    try { q = Object.fromEntries(new URLSearchParams(location.search)); } catch (e) { /* ignore */ }

    return {
      d: JSON.parse(JSON.stringify(seed)),
      allowed: opts.tabs || ['haulers', 'trucks', 'drivers'],
      canManage: !!opts.canManage,
      u: opts.urls || { approvals: '/approvals', documents: '/documents', hauler: '/hauler-management' },
      today: TODAY.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' }),
      CHK: CHK,
      /* approvals */
      tab: q.tab || 'pending', selId: q.id || null, mode: null, reason: '', reasonErr: '', checks: {},
      /* documents */
      docF: q.f || 'all', search: q.q || '', docId: q.id || null, modal: null, rpReason: 'Paper jam or misprint',
      hStaff: '', hTime: '',
      /* hauler management */
      hv: q.tab || 'haulers', hq: '', hf: null, hErr: '',

      init: function () {
        this.d = load(seed);
        this.hStaff = this.d.user; this.hTime = nowHM();
        if (this.allowed.indexOf(this.hv) < 0) this.hv = this.allowed[0];
        var self = this;
        window.addEventListener('keydown', function (e) { if (e.key === 'Escape') { self.modal = null; self.hf = null; } });
      },
      persist: function () { save(this.d); },

      /* ---- lookups ---- */
      pill: function (k) { return 'inline-flex items-center whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ' + (PILL[k] || PILL['']); },
      fmtL: fmtL, fmtD: fmtD, daysTo: daysTo, certState: certState,
      hauler: function (id) { return this.d.haulers.find(function (x) { return x.id === id; }) || { name: 'Unknown hauler' }; },
      driver: function (id) { return this.d.drivers.find(function (x) { return x.id === id; }) || { name: 'Unknown driver' }; },
      truck: function (p) { return this.d.trucks.find(function (x) { return x.plate === p; }); },
      cap: function (t) { return t ? sum(t.comps) : 0; },
      total: function (a) { return sum(a.lines.map(function (l) { return l.l; })); },
      short: function (id) { return id.replace('ATL-2026-', 'ATL '); },
      stage: function (a) {
        var x = a.d;
        if (x.ho) return 'done';
        if (x.lt.st === 'awaiting') return 'lt';
        if (!x.ret) return 'return';
        if (x.dr.st === 'awaiting' || x.wc.st === 'awaiting') return 'release';
        return 'handover';
      },
      stageP: function (a) { return STAGE[this.stage(a)]; },
      astat: function (s) { return ASTAT[s]; },
      cert: function (label, date) { var c = certState(date); return { k: c[0], t: c[1], label: label + ': ' + fmtD(date).slice(0, 6) }; },
      truckCerts: function (t) { return [['Calibration', t.cal], ['LTO', t.lto], ['Safety', t.saf]].map(function (c) { return this.cert(c[0], c[1]); }, this); },

      /* ---- automatic checks ---- */
      checksFor: function (a) {
        var self = this, t = this.truck(a.truck), dr = this.d.drivers.find(function (x) { return x.id === a.driver; }),
          h = this.d.haulers.find(function (x) { return x.id === a.h; }), r = {};
        if (!t) r.truck = { s: 'bad', n: 'Truck is not registered.' };
        else {
          var list = [['Calibration certificate', t.cal], ['LTO registration', t.lto], ['Safety permit', t.saf]].map(function (x) { return [x[0], daysTo(x[1]), x[1]]; });
          var ex = list.filter(function (x) { return x[1] < 0; }), so = list.filter(function (x) { return x[1] >= 0 && x[1] <= 30; });
          r.truck = ex.length ? { s: 'bad', n: ex.map(function (x) { return x[0] + ' expired ' + fmtD(x[2]); }).join('; ') + '.' }
            : so.length ? { s: 'warn', n: so.map(function (x) { return x[0] + ' expires in ' + x[1] + ' days'; }).join('; ') + '.' }
            : { s: 'ok', n: 'Registered, all certificates valid.' };
          if (t.h !== a.h && r.truck.s === 'ok') r.truck = { s: 'bad', n: 'Truck is registered under ' + this.hauler(t.h).name + '.' };
        }
        if (!dr) r.driver = { s: 'bad', n: 'Driver is not on file.' };
        else {
          var n = daysTo(dr.exp);
          r.driver = n < 0 ? { s: 'bad', n: 'Licence expired ' + fmtD(dr.exp) + '.' }
            : dr.h !== a.h ? { s: 'bad', n: 'Driver belongs to ' + this.hauler(dr.h).name + '.' }
            : n <= 60 ? { s: 'warn', n: 'Licence ' + dr.lic + ' expires in ' + n + ' days.' }
            : { s: 'ok', n: 'Licence ' + dr.lic + ' valid to ' + fmtD(dr.exp) + '.' };
        }
        r.hauler = !h ? { s: 'bad', n: 'Hauler is not on file.' } : !h.active ? { s: 'bad', n: 'Hauler is marked inactive.' } : { s: 'ok', n: 'Active hauler.' };
        var empty = a.lines.filter(function (l) { return !l.p || !(l.l > 0); });
        r.products = empty.length ? { s: 'bad', n: 'Compartment ' + empty.map(function (l) { return l.c; }).join(', ') + ' has no product or liters.' }
          : { s: 'ok', n: Array.from(new Set(a.lines.map(function (l) { return l.p; }))).join(', ') + '.' };
        var tot = this.total(a), c = this.cap(t);
        r.liters = !t ? { s: 'bad', n: 'Cannot compare without a truck.' } : tot > c ? { s: 'bad', n: fmtL(tot) + ' is over the truck capacity of ' + fmtL(c) + '.' } : { s: 'ok', n: fmtL(tot) + ' of ' + fmtL(c) + ' capacity.' };
        var over = t ? a.lines.filter(function (l) { return l.c > t.comps.length || l.l > t.comps[l.c - 1]; }) : [];
        r.comps = over.length ? { s: 'bad', n: over.map(function (l) { return l.c > t.comps.length ? 'Compartment ' + l.c + ' does not exist' : 'Compartment ' + l.c + ' holds ' + fmtL(l.l) + ', limit ' + fmtL(t.comps[l.c - 1]); }).join('; ') + '.' }
          : { s: 'ok', n: a.lines.length + ' of ' + (t ? t.comps.length : 0) + ' compartments used, none over its limit.' };
        void self;
        return r;
      },
      flagged: function (a) { var c = this.checksFor(a); return Object.keys(c).some(function (k) { return c[k].s === 'bad'; }); },
      lineView: function (a) {
        var t = this.truck(a.truck);
        return a.lines.map(function (l) {
          var cp = t ? t.comps[l.c - 1] : 0;
          return { c: l.c, p: l.p, l: l.l, cp: cp || 0, pct: cp ? Math.min(100, Math.round(l.l / cp * 100)) : 100, over: !cp || l.l > cp };
        });
      },

      /* ---- dashboard ---- */
      get c() {
        var A = this.d.atl, self = this,
          pend = A.filter(function (a) { return a.st === 'pending' || a.st === 'resubmitted'; }),
          ap = A.filter(function (a) { return a.st === 'approved'; }),
          by = function (s) { return ap.filter(function (a) { return self.stage(a) === s; }); };
        return { pend: pend, ap: ap, lt: by('lt'), ret: by('return'), rel: by('release'), ho: by('handover'), done: by('done') };
      },
      get alerts() {
        var al = [], self = this;
        this.d.atl.filter(function (a) { return a.st === 'pending' || a.st === 'resubmitted'; }).forEach(function (a) {
          var ac = self.checksFor(a), f = CHK.filter(function (x) { return ac[x[0]].s === 'bad'; });
          if (f.length) al.push({ k: 'bad', x: self.short(a.id) + ' has ' + f.length + ' flagged check' + (f.length > 1 ? 's' : '') + ': ' + f.map(function (x) { return x[1].toLowerCase(); }).join(', '), href: self.u.approvals + '?tab=' + a.st + '&id=' + a.id });
        });
        this.d.trucks.forEach(function (t) {
          [['Calibration certificate', t.cal], ['LTO registration', t.lto], ['Safety permit', t.saf]].forEach(function (c) {
            var x = daysTo(c[1]);
            if (x <= 30) al.push({ k: x < 0 ? 'bad' : 'warn', x: t.plate + ': ' + c[0] + ' ' + (x < 0 ? 'expired ' + fmtD(c[1]) : 'expires in ' + x + ' days'), href: self.u.hauler + '?tab=trucks' });
          });
        });
        this.d.drivers.forEach(function (dr) {
          var x = daysTo(dr.exp);
          if (x <= 60) al.push({ k: x < 0 ? 'bad' : 'warn', x: dr.name + ': licence ' + (x < 0 ? 'expired' : 'expires in ' + x + ' days'), href: self.u.hauler + '?tab=drivers' });
        });
        return al;
      },
      qrow: function (a, kind) {
        var sub = kind === 'review' ? fmtL(this.total(a)) + (this.flagged(a) ? ' · flagged' : '')
          : kind === 'lt' ? 'approved' : (this.stage(a) === 'handover' ? 'printed, hand over' : 'driver is back');
        return this.truck(a.truck) ? a.truck + ' · ' + this.driver(a.driver).name + ' · ' + sub : sub;
      },

      /* ---- ATL approvals ---- */
      tabs: [['pending', 'Pending'], ['resubmitted', 'Resubmitted'], ['returned', 'Returned'], ['approved', 'Approved'], ['declined', 'Declined']],
      tabCount: function (k) { return this.d.atl.filter(function (a) { return a.st === k; }).length; },
      get alist() { var t = this.tab; return this.d.atl.filter(function (a) { return a.st === t; }); },
      get cur() { var l = this.alist, id = this.selId; return l.find(function (a) { return a.id === id; }) || l[0] || null; },
      setTab: function (k) { this.tab = k; this.selId = null; this.mode = null; this.reason = ''; this.reasonErr = ''; },
      pick: function (id) { this.selId = id; this.mode = null; this.reason = ''; this.reasonErr = ''; },
      get ck() { return (this.cur && this.checks[this.cur.id]) || {}; },
      tick: function (k, v) { var id = this.cur.id; this.checks[id] = Object.assign({}, this.checks[id] || {}, (function () { var o = {}; o[k] = v; return o; })()); },
      get canApprove() {
        if (!this.cur) return false;
        var ac = this.checksFor(this.cur), ck = this.ck;
        return CHK.every(function (x) { return ck[x[0]]; }) && !Object.keys(ac).some(function (k) { return ac[k].s === 'bad'; });
      },
      log: function (k, x) { this.d.act.unshift({ t: nowHM(), k: k, x: x }); },
      approve: function () {
        var a = this.cur; if (!a || !this.canApprove) return;
        a.st = 'approved'; a.hist.push({ t: nowHM(), text: 'Approved by ' + this.d.user });
        var s = this.d.seq, mk = function (k) { s[k]++; return { no: k.toUpperCase() + '-2026-0' + s[k], st: 'awaiting', n: 0, at: '' }; };
        a.d = { lt: mk('lt'), ret: false, dr: mk('dr'), wc: mk('wc'), ho: null, log: [] };
        this.log('ok', a.id + ' approved. LT ' + a.d.lt.no + ' is ready to print');
        this.persist(); this.toast('ATL approved. The Loading Ticket is ready to print');
        this.selId = null;
      },
      confirmReason: function () {
        var r = (this.reason || '').trim(), a = this.cur;
        if (r.length < 5) { this.reasonErr = 'Enter a reason of at least a few words. The hauler will see it.'; return; }
        var rt = this.mode === 'return';
        a.st = rt ? 'returned' : 'declined'; a.reason = r;
        a.hist.push({ t: nowHM(), text: (rt ? 'Returned for correction' : 'Declined') + ' by ' + this.d.user + ': ' + r });
        this.log('bad', a.id + (rt ? ' returned for correction: ' : ' declined: ') + r);
        this.persist(); this.toast(rt ? 'ATL returned to the hauler' : 'ATL declined');
        this.mode = null; this.reason = ''; this.reasonErr = ''; this.tab = a.st; this.selId = a.id;
      },

      /* ---- documents and release ---- */
      docFilters: [['all', 'All'], ['lt', 'LT to print'], ['return', 'Awaiting driver'], ['release', 'DR/WC to print'], ['handover', 'Ready for handover'], ['done', 'Released']],
      docCount: function (k) { var self = this, ap = this.d.atl.filter(function (a) { return a.st === 'approved'; }); return k === 'all' ? ap.length : ap.filter(function (a) { return self.stage(a) === k; }).length; },
      get dlist() {
        var self = this, term = this.search.trim().toLowerCase();
        return this.d.atl.filter(function (a) {
          if (a.st !== 'approved') return false;
          if (self.docF !== 'all' && self.stage(a) !== self.docF) return false;
          if (!term) return true;
          return [a.id, a.truck, self.driver(a.driver).name, self.hauler(a.h).name, a.to, a.d.lt.no, a.d.dr.no, a.d.wc.no].join(' ').toLowerCase().indexOf(term) >= 0;
        });
      },
      get dcur() { var l = this.dlist, id = this.docId; return l.find(function (a) { return a.id === id; }) || l[0] || null; },
      setDocF: function (k) { this.docF = k; this.docId = null; },
      pickDoc: function (id) { this.docId = id; },
      get steps() {
        var a = this.dcur; if (!a) return [];
        var x = a.d, s = [['Step 1', 'Print LT', x.lt.st === 'printed'], ['Step 2', 'Driver returns', !!x.ret], ['Step 3', 'Print DR and WC', x.dr.st === 'printed' && x.wc.st === 'printed'], ['Step 4', 'Hand over', !!x.ho]];
        var first = s.findIndex(function (z) { return !z[2]; });
        return s.map(function (z, i) { return { a: z[0], b: z[1], done: z[2], now: i === first }; });
      },
      docName: { lt: 'Loading Ticket', dr: 'Delivery Receipt', wc: 'Withdrawal Certificate' },
      docLocked: function (a, k) { return k !== 'lt' && !a.d.ret; },
      printDoc: function (a, k, reason) {
        var x = a.d[k]; x.st = 'printed'; x.n++; x.at = nowHM();
        var nm = k.toUpperCase();
        a.d.log.push({ t: x.at, text: x.n > 1 ? nm + ' reprinted by ' + this.d.user + ' (' + reason + ')' : nm + ' printed by ' + this.d.user });
        this.log('ok', (x.n > 1 ? nm + ' reprinted' : nm + ' printed') + ' for ' + a.id);
        this.persist(); this.toast(nm + ' ' + x.no + ' sent to the printer and logged');
      },
      openPreview: function (k) { this.modal = { kind: 'preview', k: k }; },
      doPrint: function () { var m = this.modal, a = this.dcur; this.printDoc(a, m.k); this.modal = null; },
      openReprint: function (k) { this.rpReason = 'Paper jam or misprint'; this.modal = { kind: 'reprint', k: k }; },
      doReprint: function () { var m = this.modal; this.printDoc(this.dcur, m.k, this.rpReason); this.modal = null; },
      markReturned: function () {
        var a = this.dcur; a.d.ret = true;
        a.d.log.push({ t: nowHM(), text: 'Driver returned to Operations' });
        this.log('info', 'Driver ' + this.driver(a.driver).name + ' returned to Operations (' + a.id + ')');
        this.persist(); this.toast('Driver marked as returned. DR and WC are unlocked');
      },
      handover: function () {
        var a = this.dcur, tm = this.hTime || nowHM();
        a.d.ho = { staff: this.hStaff, time: tm };
        a.d.log.push({ t: tm, text: 'DR and WC handed over to ' + this.driver(a.driver).name + '. Released by ' + this.hStaff });
        this.log('ok', 'DR and WC for ' + a.id + ' handed to driver and released by ' + this.hStaff);
        this.persist(); this.toast('Handover recorded');
      },
      get paper() {
        var a = this.dcur, m = this.modal; if (!a || !m || m.kind !== 'preview') return null;
        var sig = { lt: ['Issued by (Operations)', 'Truck driver', 'Loading supervisor'], dr: ['Released by (Operations)', 'Received by driver', 'Received by consignee'], wc: ['Authorized by (Operations)', 'Withdrawn by driver', 'Security check'] }[m.k];
        return { title: this.docName[m.k], no: a.d[m.k].no, atl: a.id.replace('ATL-2026-', ''), date: this.today, sig: sig, awaiting: a.d[m.k].st === 'awaiting' && (m.k === 'lt' || a.d.ret) };
      },

      /* ---- hauler management ---- */
      hTabs: function () { var self = this; return [['haulers', 'Haulers', this.d.haulers.length], ['trucks', 'Trucks', this.d.trucks.length], ['drivers', 'Drivers', this.d.drivers.length]].filter(function (t) { return self.allowed.indexOf(t[0]) >= 0; }); },
      match: function (parts) { var t = this.hq.trim().toLowerCase(); return !t || parts.join(' ').toLowerCase().indexOf(t) >= 0; },
      get fHaulers() { var self = this; return this.d.haulers.filter(function (h) { return self.match([h.name, h.contact, h.phone, h.addr]); }); },
      get fTrucks() { var self = this; return this.d.trucks.filter(function (t) { return self.match([t.plate, self.hauler(t.h).name, self.driver(t.d).name]); }); },
      get fDrivers() { var self = this; return this.d.drivers.filter(function (x) { return self.match([x.name, x.lic, x.phone, self.hauler(x.h).name]); }); },
      truckCount: function (hid) { return this.d.trucks.filter(function (t) { return t.h === hid; }).length; },
      setHv: function (k) { this.hv = k; this.hq = ''; },
      driverOpts: function (h) { return this.d.drivers.filter(function (x) { return x.h === h; }); },
      openForm: function (kind, id) {
        var d = this.d, f = { kind: kind, id: id || '' };
        if (kind === 'hauler') { var h = d.haulers.find(function (x) { return x.id === id; }) || { name: '', contact: '', phone: '', addr: '', active: true }; Object.assign(f, { name: h.name, contact: h.contact, phone: h.phone, addr: h.addr, active: h.active ? '1' : '0' }); }
        if (kind === 'driver') { var x = d.drivers.find(function (y) { return y.id === id; }) || { name: '', lic: '', exp: '', phone: '', h: d.haulers[0].id }; Object.assign(f, { name: x.name, lic: x.lic, exp: x.exp, phone: x.phone, h: x.h }); }
        if (kind === 'truck') { var t = d.trucks.find(function (y) { return y.plate === id; }) || { plate: '', h: d.haulers[0].id, d: '', comps: [4000, 4000, 4000, 4000], cal: '', lto: '', saf: '' }; Object.assign(f, { plate: t.plate, h: t.h, dr: t.d, comps: t.comps.join(', '), cal: t.cal, lto: t.lto, saf: t.saf, reassign: false }); }
        this.hErr = ''; this.hf = f;
      },
      get capNote() { var c = parseComps(this.hf && this.hf.comps); return c.length + ' compartments, capacity ' + fmtL(sum(c)); },
      get assignedElsewhere() {
        var f = this.hf; if (!f || f.kind !== 'truck' || !f.dr) return null;
        return this.d.trucks.find(function (t) { return t.d === f.dr && t.plate !== f.id; }) || null;
      },
      truckHaulerChanged: function () { var f = this.hf; if (!this.driverOpts(f.h).some(function (x) { return x.id === f.dr; })) f.dr = ''; },
      saveForm: function () {
        this.hErr = '';
        var f = this.hf, d = this.d, v = function (s) { return String(s == null ? '' : s).trim(); }, err = function (m) { this.hErr = m; }.bind(this);
        if (f.kind === 'hauler') {
          if (!v(f.name)) return err('Enter the company name.');
          var o = { name: v(f.name), contact: v(f.contact), phone: v(f.phone), addr: v(f.addr), active: f.active === '1' };
          if (f.id) Object.assign(d.haulers.find(function (x) { return x.id === f.id; }), o);
          else { var n = 1; while (d.haulers.some(function (x) { return x.id === 'H' + n; })) n++; d.haulers.push(Object.assign({ id: 'H' + n }, o)); }
        }
        if (f.kind === 'driver') {
          if (!v(f.name)) return err("Enter the driver's name.");
          if (!v(f.lic)) return err('Enter the licence number.');
          if (!v(f.exp)) return err('Enter the licence expiry date.');
          var od = { name: v(f.name), h: f.h, lic: v(f.lic), exp: f.exp, phone: v(f.phone) };
          if (f.id) Object.assign(d.drivers.find(function (x) { return x.id === f.id; }), od);
          else { var m = 1; while (d.drivers.some(function (x) { return x.id === 'D' + m; })) m++; d.drivers.push(Object.assign({ id: 'D' + m }, od)); }
        }
        if (f.kind === 'truck') {
          var plate = v(f.plate).toUpperCase(), comps = parseComps(f.comps);
          if (!plate) return err('Enter the plate number.');
          if (!f.id && this.truck(plate)) return err('A truck with this plate is already registered.');
          if (!comps.length) return err('Enter at least one compartment in liters, for example 4000, 4000, 4000.');
          if (!f.cal || !f.lto || !f.saf) return err('Enter all three certificate expiry dates.');
          var other = this.assignedElsewhere;
          if (other && !f.reassign) return err(this.driver(f.dr).name + ' is already the default driver of ' + other.plate + '. Tick the box to move the driver to this truck.');
          if (other) other.d = '';
          var ot = { plate: plate, h: f.h, d: f.dr, comps: comps, cal: f.cal, lto: f.lto, saf: f.saf };
          if (f.id) Object.assign(this.truck(f.id), ot); else d.trucks.push(ot);
        }
        this.persist(); this.hf = null; this.toast('Saved');
      },

      /* ---- toast ---- */
      msg: '', _t: null,
      toast: function (m) { var self = this; this.msg = m; clearTimeout(this._t); this._t = setTimeout(function () { self.msg = ''; }, 3200); }
    };
  }

  window.OpsApp = OpsApp;
  document.addEventListener('alpine:init', function () { window.Alpine.data('opsApp', OpsApp); });
})();
