/* PA Fuel 118 - shared helpers for the Operations and ATL User pages.
   DEMO ONLY: the working copy of the sample data lives in this browser (localStorage), the same
   store the Hauler Management page uses. The backend developer replaces PX.store with real requests. */
(function () {
  'use strict';
  var PX = window.PX = {};
  var KEY = 'pafuel118.ops.v1';
  var NOW = new Date();
  var TODAY = new Date(NOW.getFullYear(), NOW.getMonth(), NOW.getDate());
  var two = function (n) { return String(n).padStart(2, '0'); };

  PX.$ = function (s, r) { return (r || document).querySelector(s); };
  PX.esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  PX.sum = function (a) { return a.reduce(function (x, y) { return x + (+y || 0); }, 0); };
  PX.nowHM = function () { var d = new Date(); return two(d.getHours()) + ':' + two(d.getMinutes()); };
  PX.fmtL = function (n) { return Number(n || 0).toLocaleString('en-US') + ' L'; };
  PX.todayS = TODAY.getFullYear() + '-' + two(TODAY.getMonth() + 1) + '-' + two(TODAY.getDate());
  PX.parseD = function (s) { var p = s.split('-').map(Number); return new Date(p[0], p[1] - 1, p[2]); };
  PX.fmtD = function (s) { return !s ? 'not set' : PX.parseD(s).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }); };
  PX.daysTo = function (s) { return !s ? -99999 : Math.round((PX.parseD(s) - TODAY) / 864e5); };
  PX.certState = function (s) {
    if (!s) return ['bad', 'Not set'];
    var n = PX.daysTo(s);
    return n < 0 ? ['bad', 'Expired ' + PX.fmtD(s)] : n <= 30 ? ['warn', 'Expires in ' + n + ' days'] : ['ok', 'Valid to ' + PX.fmtD(s)];
  };
  PX.pill = function (k, t) { return '<span class="pill ' + k + '">' + PX.esc(t) + '</span>'; };
  PX.dateLong = TODAY.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' });

  var IC = {
    dash: '<path d="M3 13h7V3H3zM14 21h7V11h-7zM14 3v5h7V3zM3 21h7v-5H3z"/>',
    doc: '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
    clip: '<path d="M21 12l-8.5 8.5a5 5 0 0 1-7-7L14 5a3.3 3.3 0 0 1 5 5l-8.5 8.5a1.7 1.7 0 0 1-2.4-2.4L15 9"/>',
    print: '<path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2"/><path d="M6 14h12v7H6z"/>',
    eye: '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    edit: '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
    wand: '<path d="M15 4V2M15 16v-2M8 9h2M20 9h2M17.8 11.8L19 13M17.8 6.2L19 5M3 21l9-9M12.2 6.2L11 5"/>',
    save: '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/>',
    send: '<path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/>',
    sync: '<path d="M21 12a9 9 0 0 1-15.5 6.2L3 16M3 12A9 9 0 0 1 18.5 5.8L21 8"/><path d="M21 3v5h-5M3 21v-5h5"/>'
  };
  PX.ic = function (k) { return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + IC[k] + '</svg>'; };

  /* ---------- files ---------- */
  PX.fileKind = function (n, t) { t = t || ''; return /\.pdf$/i.test(n) || t === 'application/pdf' ? 'pdf' : /\.(png|jpe?g|gif|webp)$/i.test(n) || /^image\//.test(t) ? 'img' : 'other'; };
  PX.mkFile = function (file) {
    return new Promise(function (res) {
      var o = { name: file.name, size: file.size > 1048576 ? (file.size / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(file.size / 1024)) + ' KB', kind: PX.fileKind(file.name, file.type) };
      try { o.url = URL.createObjectURL(file); } catch (e) { /* ignore */ }
      if (o.kind === 'pdf') { var r = new FileReader(); r.onload = function () { o.buf = r.result; res(o); }; r.onerror = function () { res(o); }; r.readAsArrayBuffer(file); } else res(o);
    });
  };
  PX.fileBtn = function (f, attrs) { return '<button type="button" class="file-btn" data-a="pvfile" ' + attrs + ' title="Preview ' + PX.esc(f.name) + '">' + PX.ic('clip') + '<span>' + PX.esc(f.name) + '</span></button>'; };
  PX.samplePage = function (f) { return '<div class="paper"><div class="ph"><h3 style="font-size:18px;overflow-wrap:anywhere">' + PX.esc(f.name) + '</h3><span class="wm">Sample file</span></div><div class="ln" style="width:90%"></div><div class="ln" style="width:75%"></div><div class="ln" style="width:84%"></div><div class="ln" style="width:60%"></div><div class="ln" style="width:88%"></div><div class="ln" style="width:42%"></div></div><div class="small muted" style="text-align:center">This is demo data, so no real file content is stored. Files you upload open here with their actual content.</div>'; };
  var prev = null;
  PX.preview = function (f, host) {
    prev = f; var el = PX.$('#px-prev', host);
    if (!f) { el.innerHTML = ''; return; }
    var body;
    if (f.kind === 'img' && f.url) body = '<div class="pvbody"><img src="' + f.url + '" alt="' + PX.esc(f.name) + '"></div>';
    else if (f.kind === 'pdf' && f.url) body = '<div class="pvbody"><iframe class="pdfframe" src="' + f.url + '" title="' + PX.esc(f.name) + '"></iframe></div>';
    else if (!f.url && !f.buf) body = '<div class="pvbody">' + PX.samplePage(f) + '</div>';
    else body = '<div class="pvbody"><div class="empty">Preview is not available for this file type.</div></div>';
    el.innerHTML = '<div class="modal-bg pv" data-a="pvbg"><div class="modal" role="dialog" aria-modal="true" aria-label="Preview ' + PX.esc(f.name) + '"><div class="modal-h"><h2 style="overflow-wrap:anywhere">' + PX.esc(f.name) + ' <span class="small muted">' + PX.esc(f.size || '') + '</span></h2><button class="btn sm" data-a="pvclose">Close</button></div>' + body + '</div></div>';
  };
  PX.previewOpen = function () { return !!prev; };

  /* ---------- toast ---------- */
  var tt;
  PX.toast = function (m, host) { var e = PX.$('#px-toast', host); e.innerHTML = '<div class="toast" role="status">' + PX.esc(m) + '</div>'; clearTimeout(tt); tt = setTimeout(function () { e.innerHTML = ''; }, 3400); };

  /* ---------- store (shared with Hauler Management) ---------- */
  var DOCS = ['or', 'cr', 'cal'];
  function normalize(d) {
    d.haulers.forEach(function (h) { delete h.active; });
    d.drivers.forEach(function (x) { if (x.exp == null) x.exp = ''; if (x.lic == null) x.lic = ''; if (x.phone == null) x.phone = ''; });
    d.trucks.forEach(function (t) {
      if (!t.docs) {
        t.docs = { or: { exp: t.saf || '', file: t.saf ? 'OR ' + t.plate + '.pdf' : '' }, cr: { exp: t.lto || '', file: t.lto ? 'CR ' + t.plate + '.pdf' : '' }, cal: { exp: t.cal || '', file: t.cal ? 'Calibration ' + t.plate + '.pdf' : '' } };
        delete t.saf; delete t.lto; delete t.cal;
      }
      DOCS.forEach(function (k) { if (!t.docs[k]) t.docs[k] = { exp: '', file: '' }; });
      if (!t.cap) t.cap = PX.sum(t.comps);
      if (t.s == null) t.s = '';
    });
    d.atl = d.atl || []; d.act = d.act || []; d.notes = d.notes || [];
    return d;
  }
  PX.store = {
    load: function (seed) {
      try {
        var s = JSON.parse(localStorage.getItem(KEY));
        if (s && s.ver === seed.ver) { s.user = seed.user; s.atlUser = seed.atlUser; if (s.staff.indexOf(seed.user) < 0) s.staff.unshift(seed.user); return normalize(s); }
      } catch (e) { /* ignore */ }
      return normalize(JSON.parse(JSON.stringify(seed)));
    },
    save: function (d) {
      try {
        var c = JSON.parse(JSON.stringify(d, function (k, v) { return (k === 'url' || k === 'buf') ? undefined : v; }));
        localStorage.setItem(KEY, JSON.stringify(c));
      } catch (e) { /* ignore */ }
    }
  };

  /* ---------- shared lookups ---------- */
  PX.lookups = function (d) {
    var L = {
      hauler: function (id) { return d.haulers.find(function (x) { return x.id === id; }) || { name: 'Unknown hauler' }; },
      driver: function (id) { return d.drivers.find(function (x) { return x.id === id; }) || { name: 'Unknown driver' }; },
      truck: function (p) { return d.trucks.find(function (x) { return x.plate === p; }); },
      cap: function (t) { return t ? PX.sum(t.comps) : 0; },
      total: function (a) { return PX.sum(a.lines.map(function (l) { return l.l; })); },
      atl: function (id) { return d.atl.find(function (a) { return a.id === id; }); },
      driverId: function (a) { var t = L.truck(a.plate); if (!t) return ''; return a.mode === 'sub' && a.sub ? a.sub : t.d; },
      driverName: function (a) { var id = L.driverId(a); return id ? L.driver(id).name : ''; },
      haulerName: function (a) { var t = L.truck(a.plate); return t ? L.hauler(t.h).name : ''; },
      short: function (id) { return id.replace('ATL-2026-', 'ATL '); },
      products: function (a) { return Array.from(new Set(a.lines.map(function (l) { return l.p; }).filter(Boolean))); }
    };
    return L;
  };

  /* generic click wiring. handlers: {actions:{name:function(ds,el,ev)}, input:fn, change:fn, after:fn} */
  PX.wire = function (root, H) {
    root.addEventListener('click', function (ev) {
      var h = ev.target.closest('[data-href]');
      var t = ev.target.closest('[data-a]');
      if (t && !['INPUT', 'SELECT', 'TEXTAREA'].includes(t.tagName)) { H.run(t.dataset.a, t.dataset, t, ev); return; }
      if (h && !ev.target.closest('a,button')) location.href = h.dataset.href;
    });
    root.addEventListener('keydown', function (ev) {
      if ((ev.key === 'Enter' || ev.key === ' ') && ev.target.matches('[data-href]')) { ev.preventDefault(); location.href = ev.target.dataset.href; }
    });
    if (H.input) root.addEventListener('input', H.input);
    if (H.change) root.addEventListener('change', H.change);
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape' && H.escape) H.escape(); });
  };
  PX.keepFocus = function (fn) {
    var a = document.activeElement, id = a && a.id, p = a && a.selectionStart;
    fn();
    if (id) { var n = document.getElementById(id); if (n) { n.focus(); try { n.setSelectionRange(p, p); } catch (e) { /* ignore */ } } }
  };
  PX.query = function () { try { return Object.fromEntries(new URLSearchParams(location.search)); } catch (e) { return {}; } };
  PX.head = function (t, p) { return '<div class="top"><h1>' + t + '</h1>' + (p ? '<p>' + p + '</p>' : '') + '</div>'; };
  PX.frame = function () { return '<div id="px-main"></div><div id="px-modal"></div><div id="px-prev"></div><div id="px-toast" aria-live="polite"></div>'; };
})();
