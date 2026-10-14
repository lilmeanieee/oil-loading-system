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
    ['hauler', 'Hauler', 'On file and matches truck'],
    ['products', 'Products', 'Product named for every compartment'],
    ['liters', 'Liters', 'Total against truck capacity'],
    ['comps', 'Compartments', 'Each load fits its compartment']
  ];

  function sum(a) { return a.reduce(function (x, y) { return x + y; }, 0); }
  function two(n) { return String(n).padStart(2, '0'); }
  function nowHM() { var d = new Date(); return two(d.getHours()) + ':' + two(d.getMinutes()); }
  function fmtL(n) { return Number(n).toLocaleString('en-US') + ' L'; }
  function parseD(s) { var p = s.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); }
  function fmtD(s) { return !s ? 'not set' : parseD(s).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); }
  function daysTo(s) { return !s ? -99999 : Math.round((parseD(s) - TODAY) / 864e5); }
  function certState(s) {
    if (!s) return ['bad', 'Not set'];
    var n = daysTo(s);
    return n < 0 ? ['bad', 'Expired ' + fmtD(s)] : n <= 30 ? ['warn', 'Expires in ' + n + ' d'] : ['ok', 'Valid to ' + fmtD(s)];
  }
  function parseComps(t) {
    return String(t || '').split(',').map(function (x) { return parseInt(x.replace(/\D/g, ''), 10); }).filter(function (x) { return x > 0; });
  }

  var DOCS = [['or', 'Official Receipt (OR)', 'OR'], ['cr', 'Certificate of Registration (CR)', 'CR'], ['cal', 'Calibration certificate', 'Calibration']];
  function normalize(d) {
    d.haulers.forEach(function (h) { delete h.active; });
    d.drivers.forEach(function (x) { if (x.exp == null) x.exp = ''; if (x.lic == null) x.lic = ''; if (x.phone == null) x.phone = ''; });
    d.trucks.forEach(function (t) {
      if (!t.docs) {
        t.docs = {
          or: { exp: t.saf || '', file: t.saf ? 'OR ' + t.plate + '.pdf' : '' },
          cr: { exp: t.lto || '', file: t.lto ? 'CR ' + t.plate + '.pdf' : '' },
          cal: { exp: t.cal || '', file: t.cal ? 'Calibration ' + t.plate + '.pdf' : '' }
        };
        delete t.saf; delete t.lto; delete t.cal;
      }
      if (!t.cap) t.cap = sum(t.comps);
      if (t.s == null) t.s = '';
    });
    return d;
  }

  function load(seed) {
    try {
      var s = JSON.parse(localStorage.getItem(KEY));
      if (s && s.ver === seed.ver) { s.user = seed.user; if (s.staff.indexOf(seed.user) < 0) s.staff.unshift(seed.user); return normalize(s); }
    } catch (e) { /* ignore */ }
    return normalize(JSON.parse(JSON.stringify(seed)));
  }
  function save(d) { try { localStorage.setItem(KEY, JSON.stringify(d)); } catch (e) { /* ignore */ } }

  function OpsApp(seed, opts) {
    opts = opts || {};
    var q = {};
    try { q = Object.fromEntries(new URLSearchParams(location.search)); } catch (e) { /* ignore */ }

    return {
      d: normalize(JSON.parse(JSON.stringify(seed))),
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
        window.addEventListener('keydown', function (e) { if (e.key === 'Escape') { self.modal = null; if (self.hc) self.hc = null; else self.hf = null; } });
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
      truckCerts: function (t) { return DOCS.map(function (c) { return this.cert(c[2], t.docs[c[0]].exp); }, this); },

      /* ---- automatic checks ---- */
      checksFor: function (a) {
        var self = this, t = this.truck(a.truck), dr = this.d.drivers.find(function (x) { return x.id === a.driver; }),
          h = this.d.haulers.find(function (x) { return x.id === a.h; }), r = {};
        if (!t) r.truck = { s: 'bad', n: 'Truck is not registered.' };
        else {
          var list = DOCS.map(function (c) { return [c[1], daysTo(t.docs[c[0]].exp), t.docs[c[0]].exp]; });
          var ex = list.filter(function (x) { return x[1] < 0; }), so = list.filter(function (x) { return x[1] >= 0 && x[1] <= 30; });
          r.truck = ex.length ? { s: 'bad', n: ex.map(function (x) { return x[0] + ' expired ' + fmtD(x[2]); }).join('; ') + '.' }
            : so.length ? { s: 'warn', n: so.map(function (x) { return x[0] + ' expires in ' + x[1] + ' days'; }).join('; ') + '.' }
            : { s: 'ok', n: 'Registered, all certificates valid.' };
          if (t.h !== a.h && r.truck.s === 'ok') r.truck = { s: 'bad', n: 'Truck is registered under ' + this.hauler(t.h).name + '.' };
        }
        if (!dr) r.driver = { s: 'bad', n: 'Driver is not on file.' };
        else {
          var n = daysTo(dr.exp);
          r.driver = !dr.exp ? { s: 'bad', n: 'Licence details have not been entered yet.' } : n < 0 ? { s: 'bad', n: 'Licence expired ' + fmtD(dr.exp) + '.' }
            : dr.h !== a.h ? { s: 'bad', n: 'Driver belongs to ' + this.hauler(dr.h).name + '.' }
            : n <= 60 ? { s: 'warn', n: 'Licence ' + dr.lic + ' expires in ' + n + ' days.' }
            : { s: 'ok', n: 'Licence ' + dr.lic + ' valid to ' + fmtD(dr.exp) + '.' };
        }
        r.hauler = !h ? { s: 'bad', n: 'Hauler is not on file.' } : { s: 'ok', n: h.name + '.' };
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
          DOCS.map(function (c) { return [c[1], t.docs[c[0]].exp]; }).forEach(function (c) {
            var x = daysTo(c[1]);
            if (x <= 30) al.push({ k: x < 0 ? 'bad' : 'warn', x: t.plate + ': ' + c[0] + ' ' + (x < 0 ? 'expired ' + fmtD(c[1]) : 'expires in ' + x + ' days'), href: self.u.hauler + '?tab=trucks' });
          });
        });
        this.d.drivers.forEach(function (dr) {
          var x = daysTo(dr.exp);
          if (dr.exp && x <= 60) al.push({ k: x < 0 ? 'bad' : 'warn', x: dr.name + ': licence ' + (x < 0 ? 'expired' : 'expires in ' + x + ' days'), href: self.u.hauler + '?tab=drivers' });
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
      get blockers() {
        if (!this.cur) return [];
        var ac = this.checksFor(this.cur);
        return Object.keys(ac).filter(function (k) { return ac[k].s === 'bad'; }).map(function (k) { return ac[k].n; });
      },
      get canApprove() { return !!this.cur && this.blockers.length === 0; },
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
      get fTrucks() { var self = this; return this.d.trucks.filter(function (t) { return self.match([t.plate, self.hauler(t.h).name, self.driver(t.d).name, self.driver(t.s).name]); }); },
      get fDrivers() { var self = this; return this.d.drivers.filter(function (x) { return self.match([x.name, x.lic, x.phone, self.hauler(x.h).name, self.driverRole(x)]); }); },
      truckCount: function (hid) { return this.d.trucks.filter(function (t) { return t.h === hid; }).length; },
      setHv: function (k) { this.hv = k; this.hq = ''; },
      DOCS: DOCS,
      hc: null, cErr: '',
      low: function (x) { return String(x == null ? '' : x).trim().toLowerCase(); },
      haulerByName: function (n) { var k = this.low(n); return k ? this.d.haulers.find(function (h) { return this.low(h.name) === k; }, this) || null : null; },
      driverTruck: function (id) { return this.d.trucks.find(function (t) { return t.d === id; }) || null; },
      subTrucks: function (id) { return this.d.trucks.filter(function (t) { return t.s === id; }); },
      driverRole: function (x) {
        var t = this.driverTruck(x.id), st = this.subTrucks(x.id).map(function (y) { return y.plate; });
        return (t ? t.plate + ' (driver)' : '') + (t && st.length ? ', ' : '') + (st.length ? st.join(', ') + ' (substitute)' : '') || 'No truck';
      },
      get isNewHauler() { var f = this.hf; return !!f && f.kind === 'truck' && !!f.hname.trim() && !this.haulerByName(f.hname); },
      openForm: function (kind, id, ro) {
        var d = this.d, f = { kind: kind, id: id || '', ro: !!ro };
        if (kind === 'hauler') { var h = d.haulers.find(function (x) { return x.id === id; }) || { name: '', contact: '', phone: '', addr: '' }; Object.assign(f, { name: h.name, contact: h.contact, phone: h.phone, addr: h.addr }); }
        if (kind === 'driver') { var x = d.drivers.find(function (y) { return y.id === id; }); Object.assign(f, { name: x.name, lic: x.lic, exp: x.exp, phone: x.phone, h: x.h }); }
        if (kind === 'truck') {
          var t = d.trucks.find(function (y) { return y.plate === id; });
          var docs = {}; DOCS.forEach(function (c) { var o = t ? t.docs[c[0]] : { exp: '', file: '' }; docs[c[0]] = { exp: o.exp, file: o.file }; });
          var sd = t && t.s ? d.drivers.find(function (y) { return y.id === t.s; }) : null;
          Object.assign(f, {
            plate: t ? t.plate : '', hname: t ? this.hauler(t.h).name : '',
            dname: t && t.d ? this.driver(t.d).name : '', sname: sd ? sd.name : '', showSub: !!sd,
            cap: t ? String(t.cap) : '', cn: t ? String(t.comps.length) : '', cl: t ? t.comps.map(String) : [], docs: docs
          });
        }
        this.hErr = ''; this.hf = f;
      },
      closeForm: function () { this.hf = null; this.hc = null; },
      /* truck form: capacity and compartments */
      setCount: function () {
        var f = this.hf, n = parseInt(f.cn, 10);
        if (!(n >= 0)) n = 0; if (n > 12) n = 12;
        while (f.cl.length < n) f.cl.push('');
        f.cl.length = n;
      },
      num: function (x) { var n = Number(String(x == null ? '' : x).replace(/,/g, '')); return isFinite(n) ? n : 0; },
      get assigned() { var self = this; return sum((this.hf && this.hf.cl || []).map(function (v) { return self.num(v) > 0 ? self.num(v) : 0; })); },
      get remaining() { return this.num(this.hf && this.hf.cap) - this.assigned; },
      compErr: function (i) {
        var f = this.hf, v = f.cl[i], self = this;
        if (String(v).trim() === '') return '';
        var n = self.num(v); if (!(n > 0) || n % 1) return 'Enter a whole number above 0.';
        var cap = self.num(f.cap), others = sum(f.cl.map(function (x, j) { return j === i ? 0 : (self.num(x) > 0 ? self.num(x) : 0); }));
        if (cap > 0 && n > cap) return 'More than the truck capacity of ' + fmtL(cap) + '.';
        if (cap > 0 && others + n > cap) return 'Only ' + fmtL(Math.max(0, cap - others)) + ' left to assign.';
        return '';
      },
      pickFile: function (k, ev) { var fl = ev.target.files && ev.target.files[0]; if (fl) this.hf.docs[k].file = fl.name; },
      /* resolve a driver typed by name: reuse, rename or create (applied only after everything validates) */
      planDriver: function (name, hid, plate, role, curId) {
        var self = this, k = this.low(name), d = this.d;
        var ex = d.drivers.find(function (x) { return x.h === hid && self.low(x.name) === k; });
        if (ex) {
          var tk = d.trucks.find(function (t) { return t.d === ex.id && t.plate !== plate; });
          if (role === 'driver' && tk) return { err: ex.name + ' is already the driver of ' + tk.plate + '. Each driver has one truck.' };
          return { id: ex.id };
        }
        if (curId) {
          var cd = d.drivers.find(function (x) { return x.id === curId; });
          var shared = role === 'sub' && d.trucks.some(function (t) { return t.s === curId && t.plate !== plate; });
          if (cd && !shared) return { id: curId, rename: name.trim() };
        }
        return { create: name.trim() };
      },
      applyDriver: function (pl, hid) {
        var d = this.d;
        if (pl.id) { var x = d.drivers.find(function (y) { return y.id === pl.id; }); x.h = hid; if (pl.rename) x.name = pl.rename; return pl.id; }
        var m = 1; while (d.drivers.some(function (y) { return y.id === 'D' + m; })) m++;
        d.drivers.push({ id: 'D' + m, name: pl.create, h: hid, lic: '', exp: '', phone: '' });
        return 'D' + m;
      },
      saveForm: function () {
        this.hErr = '';
        var f = this.hf, d = this.d, v = function (s) { return String(s == null ? '' : s).trim(); }, err = function (m) { this.hErr = m; }.bind(this), self = this;
        if (f.kind === 'hauler') {
          if (!v(f.name)) return err('Enter the company name.');
          if (!v(f.addr)) return err('Enter the company address.');
          var dup = d.haulers.find(function (x) { return self.low(x.name) === self.low(f.name) && x.id !== f.id; });
          if (dup) return err('A hauler with this company name already exists.');
          var o = { name: v(f.name), contact: v(f.contact), phone: v(f.phone), addr: v(f.addr) };
          if (f.id) Object.assign(d.haulers.find(function (x) { return x.id === f.id; }), o);
          else { var n = 1; while (d.haulers.some(function (x) { return x.id === 'H' + n; })) n++; d.haulers.push(Object.assign({ id: 'H' + n }, o)); }
        }
        if (f.kind === 'driver') {
          if (!v(f.name)) return err("Enter the driver's name.");
          if (!v(f.lic)) return err('Enter the licence number.');
          if (!v(f.exp)) return err('Enter the licence expiry date.');
          Object.assign(d.drivers.find(function (x) { return x.id === f.id; }), { name: v(f.name), lic: v(f.lic), exp: f.exp, phone: v(f.phone) });
        }
        if (f.kind === 'truck') {
          var plate = v(f.plate).toUpperCase();
          if (!plate) return err('Enter the plate number.');
          if (!f.id && this.truck(plate)) return err('A truck with this plate is already registered.');
          if (!v(f.hname)) return err('Select an existing hauler or type a new company name.');
          if (!v(f.dname)) return err("Enter the driver's full name.");
          var cap = this.num(f.cap), cn = parseInt(f.cn, 10);
          if (!(cap > 0)) return err('Enter the total capacity of the truck in liters.');
          if (!(cn >= 1)) return err('Enter the number of compartments.');
          for (var i = 0; i < f.cl.length; i++) {
            if (v(f.cl[i]) === '') return err('Enter the liters for compartment ' + (i + 1) + '.');
            if (this.compErr(i)) return err('Compartment ' + (i + 1) + ': ' + this.compErr(i));
          }
          if (this.remaining !== 0) return err('The compartments add up to ' + fmtL(this.assigned) + ' but the truck capacity is ' + fmtL(cap) + '. ' + (this.remaining > 0 ? fmtL(this.remaining) + ' still to assign.' : ''));
          for (var j = 0; j < DOCS.length; j++) {
            var c = DOCS[j], dc = f.docs[c[0]];
            if (!dc.exp) return err('Enter the expiry date of the ' + c[1] + '.');
            if (!dc.file) return err('Upload the ' + c[1] + '.');
          }
          if (f.showSub && v(f.sname) && this.low(f.sname) === this.low(f.dname)) return err('The substitute driver must be a different person from the driver.');
          if (this.isNewHauler && !this.hc) { this.cErr = ''; this.hc = { name: v(f.hname), contact: '', phone: '', addr: '' }; return; }
          this.finishTruck(this.haulerByName(f.hname) ? this.haulerByName(f.hname).id : null);
          return;
        }
        this.persist(); this.hf = null; this.toast('Saved');
      },
      confirmNewHauler: function () {
        var c = this.hc, v = function (s) { return String(s == null ? '' : s).trim(); }, self = this;
        this.cErr = '';
        if (!v(c.name)) { this.cErr = 'Enter the company name.'; return; }
        if (!v(c.addr)) { this.cErr = 'Enter the company address.'; return; }
        if (this.haulerByName(c.name)) { this.cErr = 'A hauler with this company name already exists. Go back and pick it from the list.'; return; }
        var d = this.d, n = 1; while (d.haulers.some(function (x) { return x.id === 'H' + n; })) n++;
        var nh = { id: 'H' + n, name: v(c.name), contact: v(c.contact), phone: v(c.phone), addr: v(c.addr) };
        var f = this.hf, plan = this.planDriver(f.dname, nh.id, f.plate.trim().toUpperCase(), 'driver', f.id ? (this.truck(f.id) || {}).d : '');
        if (plan.err) { this.cErr = plan.err; return; }
        d.haulers.push(nh); this.hc = null; f.hname = nh.name;
        this.finishTruck(nh.id, true);
        void self;
      },
      finishTruck: function (hid) {
        var f = this.hf, d = this.d, v = function (s) { return String(s == null ? '' : s).trim(); };
        var plate = v(f.plate).toUpperCase(), old = f.id ? this.truck(f.id) : null;
        var p1 = this.planDriver(f.dname, hid, plate, 'driver', old ? old.d : '');
        if (p1.err) { this.hErr = p1.err; return; }
        var p2 = null;
        if (f.showSub && v(f.sname)) { p2 = this.planDriver(f.sname, hid, plate, 'sub', old ? old.s : ''); if (p2.err) { this.hErr = p2.err; return; } if (p2.id && p2.id === p1.id) { this.hErr = 'The substitute driver must be a different person from the driver.'; return; } }
        var did = this.applyDriver(p1, hid), sid = p2 ? this.applyDriver(p2, hid) : '';
        var docs = {}; DOCS.forEach(function (c) { docs[c[0]] = { exp: f.docs[c[0]].exp, file: f.docs[c[0]].file }; });
        var ot = { plate: plate, h: hid, d: did, s: sid, cap: this.num(f.cap), comps: f.cl.map(function (x) { return Number(String(x).replace(/,/g, '')); }), docs: docs };
        if (old) Object.assign(old, ot); else d.trucks.push(ot);
        this.persist(); this.hf = null; this.hc = null; this.toast(old ? 'Truck saved' : 'Truck registered');
      },

      /* ---- toast ---- */
      msg: '', _t: null,
      toast: function (m) { var self = this; this.msg = m; clearTimeout(this._t); this._t = setTimeout(function () { self.msg = ''; }, 3200); }
    };
  }

  window.OpsApp = OpsApp;
  document.addEventListener('alpine:init', function () { window.Alpine.data('opsApp', OpsApp); });
})();
