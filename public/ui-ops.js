/* PA Fuel 118 - Operations pages: Dashboard, ATL Approvals, Compartment allocation, Documents & Release.
   DEMO ONLY: reads and writes the browser copy of the sample data (see ui-core.js). */
(function () {
  'use strict';
  var P = window.PX, esc = P.esc, ic = P.ic, pill = P.pill, fmtL = P.fmtL, fmtD = P.fmtD, nowHM = P.nowHM, sum = P.sum;
  var PRODUCTS = ['R95', 'R91', 'DIESEL'];
  var REMARKS = ['Standard load', 'Partial load', 'Segregate from adjacent compartment', 'Top-up only', 'Seal required', 'Priority discharge'];
  var STAGE = { alloc: ['warn', 'Needs allocation'], lt: ['warn', 'LT to print'], 'return': ['info', 'Awaiting driver return'], release: ['acc', 'DR, WC and handover'], done: ['ok', 'Released'] };
  var ASTAT = { pending: ['warn', 'Pending review'], declined: ['bad', 'Declined'], approved: ['ok', 'Approved'] };
  var TABS = [['pending', 'Pending'], ['approved', 'Approved'], ['declined', 'Declined']];
  var DFILT = [['all', 'All'], ['alloc', 'Needs allocation'], ['lt', 'LT to print'], ['return', 'Awaiting driver'], ['release', 'DR, WC and handover'], ['done', 'Released']];
  var DOCN = { lt: 'Loading Ticket', dr: 'Delivery Receipt', wc: 'Withdrawal Certificate' };
  var CERTS = [['or', 'Official Receipt (OR)'], ['cr', 'Certificate of Registration (CR)'], ['cal', 'Calibration certificate']];

  var D, L, root, U, S, USER;

  /* ---------- derived ---------- */
  function vis(a) { return a.st !== 'draft' && a.sync === 'synced'; }
  function stage(a) {
    var d = a.d;
    if (!d) return 'alloc';
    if (d.ho) return 'done';
    if (d.lt.st === 'awaiting') return 'lt';
    if (!d.ret) return 'return';
    return 'release';
  }
  function counts() {
    var A = D.atl.filter(vis), pend = A.filter(function (a) { return a.st === 'pending'; }), ap = A.filter(function (a) { return a.st === 'approved'; });
    var by = function (s) { return ap.filter(function (a) { return stage(a) === s; }); };
    return { pend: pend, ap: ap, al: by('alloc'), lt: by('lt'), ret: by('return'), rel: by('release'), done: by('done') };
  }
  function rowPill(a) { return a.st === 'approved' ? (a.d ? pill('ok', 'Allocated') : pill('warn', 'Needs allocation')) : pill.apply(null, ASTAT[a.st]); }
  function logAct(k, x) { D.act.unshift({ t: nowHM(), k: k, x: x }); }
  function note(k, x, id) { D.notes.unshift({ t: nowHM(), k: k, x: x, id: id, u: true }); }
  function nums(a) {
    var mk = function (k) { D.seq[k]++; return { no: k.toUpperCase() + '-2026-0' + D.seq[k], st: 'awaiting', n: 0, at: '' }; };
    a.d = { lt: mk('lt'), ret: false, dr: mk('dr'), wc: mk('wc'), ho: null, log: [] };
  }
  function allocUrl(id) { return U.allocate.replace('__ID__', encodeURIComponent(id)); }
  function docUrl(f, id) { return U.documents + '?' + (f ? 'f=' + f + '&' : '') + (id ? 'id=' + encodeURIComponent(id) : ''); }
  function atlUrl(tab, id) { return U.approvals + '?tab=' + tab + (id ? '&id=' + encodeURIComponent(id) : ''); }

  /* ---------- allocation logic ---------- */
  function reqOf(a) { var r = {}; a.lines.forEach(function (l) { if (l.p && +l.l > 0) r[l.p] = (r[l.p] || 0) + (+l.l); }); return r; }
  function reconRows(a, rows) {
    var req = reqOf(a), al = {};
    rows.forEach(function (x) { if (x.p) al[x.p] = (al[x.p] || 0) + (+x.l || 0); });
    return PRODUCTS.filter(function (p) { return req[p] || al[p]; }).map(function (p) { return { p: p, req: req[p] || 0, al: al[p] || 0 }; });
  }
  function autoRows(a) {
    var t = L.truck(a.plate), rows = t.comps.map(function () { return { p: '', l: 0, rem: 'Standard load' }; }), req = reqOf(a), ci = 0, left = 0;
    Object.keys(req).sort(function (x, y) { return PRODUCTS.indexOf(x) - PRODUCTS.indexOf(y); }).forEach(function (p) {
      var r = req[p];
      while (r > 0 && ci < rows.length) { var c = t.comps[ci], take = Math.min(r, c); rows[ci] = { p: p, l: take, rem: take === c ? 'Standard load' : 'Partial load' }; r -= take; ci++; }
      left += r;
    });
    return { rows: rows, left: left };
  }
  function allocCheck(a, rows) {
    var t = L.truck(a.plate), req = reqOf(a), rowErr = {}, over = [];
    rows.forEach(function (r, i) {
      var c = t.comps[i], l = +r.l || 0;
      if (l > c) { rowErr[i] = 'Over capacity by ' + (l - c).toLocaleString('en-US') + ' L'; over.push(i); }
      else if (l > 0 && !r.p) rowErr[i] = 'Choose a product';
      else if (r.p && !(l > 0)) rowErr[i] = 'Enter liters or clear the product';
      else if (r.p && !req[r.p]) rowErr[i] = r.p + ' is not requested on this ATL';
    });
    var rec = reconRows(a, rows), mis = rec.filter(function (x) { return x.req !== x.al; });
    var other = Object.keys(rowErr).filter(function (i) { return over.indexOf(+i) < 0; });
    var checks = [
      { k: 'Compartment capacity', s: over.length ? 'bad' : 'ok', n: over.length ? over.map(function (i) { return 'C' + (i + 1) + ' is over by ' + ((+rows[i].l) - t.comps[i]).toLocaleString('en-US') + ' L'; }).join(', ') + '.' : 'Every compartment is within its registered capacity.' },
      { k: 'Product assignments', s: other.length ? 'bad' : 'ok', n: other.length ? other.map(function (i) { return 'C' + (+i + 1) + ': ' + rowErr[i]; }).join('; ') + '.' : 'Each compartment holds one requested product with liters.' },
      { k: 'Allocated matches requested', s: mis.length ? 'bad' : 'ok', n: mis.length ? mis.map(function (x) { return x.p + ': ' + fmtL(x.al) + ' of ' + fmtL(x.req); }).join('; ') + '.' : 'Allocated liters equal requested liters for every product.' }
    ];
    return { rowErr: rowErr, rec: rec, checks: checks, ok: checks.every(function (c) { return c.s === 'ok'; }) && rec.length > 0 };
  }

  /* ---------- dashboard ---------- */
  function vDash() {
    var c = counts();
    var tile = function (lab, n, sub, cls, href) { return '<a class="tile ' + cls + '" href="' + href + '"><span class="lab">' + lab + '</span><span class="big num">' + n + '</span><span class="sub">' + sub + '</span></a>'; };
    var printed = c.rel.filter(function (a) { return a.d.dr.st === 'printed' && a.d.wc.st === 'printed'; });
    var flow = [['Step 1', 'Allocate compartments', c.al.length, atlUrl('approved')], ['Step 2', 'Print LT', c.lt.length, docUrl('lt')], ['Step 3', 'Driver returns', c.ret.length, docUrl('return')], ['Step 4', 'DR, WC and handover', c.rel.length, docUrl('release')]];
    var qrow = function (a, sub, act, href) { return '<div class="qi"><b>' + a.id.replace('ATL-2026-', 'ATL ') + '</b><span class="t">' + esc(a.plate) + ' · ' + esc(L.driverName(a)) + '<span class="muted"> · ' + sub + '</span></span><a class="btn sm" href="' + href + '">' + act + '</a></div>'; };
    var grp = function (title, list, fn) { return '<div class="qgroup"><h3>' + title + '<span class="pill">' + list.length + '</span></h3>' + (list.length ? list.slice(0, 3).map(fn).join('') + (list.length > 3 ? '<div class="small muted">and ' + (list.length - 3) + ' more</div>' : '') : '<div class="small muted">Nothing waiting.</div>') + '</div>'; };
    var al = [];
    D.trucks.forEach(function (t) { CERTS.forEach(function (c) { var d = t.docs[c[0]].exp, x = P.daysTo(d); if (d && x <= 30) al.push({ k: x < 0 ? 'bad' : 'warn', x: t.plate + ': ' + c[1] + ' ' + (x < 0 ? 'expired ' + fmtD(d) : 'expires in ' + x + ' days') }); }); });
    D.drivers.forEach(function (d) { var x = P.daysTo(d.exp); if (d.exp && x <= 60) al.push({ k: x < 0 ? 'bad' : 'warn', x: d.name + ': licence ' + (x < 0 ? 'expired' : 'expires in ' + x + ' days') }); });
    return P.head('Dashboard', 'What needs a decision, an allocation or a printout right now.') + '\
  <div class="grid tiles">' +
      tile('Pending ATL reviews', c.pend.length, 'Waiting for your decision', c.pend.length ? 'warn' : '', atlUrl('pending')) +
      tile('Approved ATLs', c.ap.length, c.al.length + ' awaiting compartment allocation', 'ok', atlUrl('approved')) +
      tile('Loading Tickets awaiting printing', c.lt.length, 'Allocated and ready to print', c.lt.length ? 'warn' : '', docUrl('lt')) +
      tile('DR and WC awaiting release', c.rel.length, printed.length + ' printed, not yet handed over', c.rel.length ? 'info' : '', docUrl('release')) + '</div>\
  <section class="card" style="margin-top:16px"><div class="card-h"><h2>Release workflow</h2><span class="small muted sp">Approved trucks at each step</span></div>\
    <ol class="flow">' + flow.map(function (f) { return '<li data-href="' + f[3] + '" tabindex="0" role="link"><div class="st">' + f[0] + '</div><div class="nm">' + f[1] + '</div><div class="ct num">' + f[2] + '</div></li>'; }).join('') + '</ol></section>\
  <div class="grid cols2" style="margin-top:16px">\
    <section class="card"><div class="card-h"><h2>Needs action</h2></div>' +
      grp('ATLs to review', c.pend, function (a) { return qrow(a, fmtL(L.total(a)), 'Review', atlUrl('pending', a.id)); }) +
      grp('Approved ATLs to allocate', c.al, function (a) { return qrow(a, fmtL(L.total(a)), 'Allocate', allocUrl(a.id)); }) +
      grp('Loading Tickets to print', c.lt, function (a) { return qrow(a, 'allocated', 'Open', docUrl('lt', a.id)); }) +
      grp('Drivers to mark as returned', c.ret, function (a) { return qrow(a, 'LT printed', 'Open', docUrl('return', a.id)); }) +
      grp('DR, WC and handover', c.rel, function (a) { return qrow(a, a.d.dr.st === 'printed' && a.d.wc.st === 'printed' ? 'printed, hand over' : 'driver is back', 'Open', docUrl('release', a.id)); }) + '</section>\
    <div class="grid" style="align-content:start;min-width:0">\
      <section class="card"><div class="card-h"><h2>Alerts</h2><span class="pill ' + (al.length ? 'warn' : 'ok') + ' sp">' + al.length + '</span></div>' +
      (al.length ? al.map(function (x) { return '<div class="alert">' + pill(x.k, x.k === 'bad' ? 'Action' : 'Watch') + '<span style="min-width:0;overflow-wrap:anywhere">' + esc(x.x) + '</span></div>'; }).join('') : '<div class="empty">No alerts.</div>') + '</section>\
      <section class="card"><div class="card-h"><h2>Recent activity</h2></div><ul class="feed">' + D.act.slice(0, 8).map(function (x) { return '<li><time>' + esc(x.t) + '</time><i class="' + x.k + '"></i><span style="min-width:0;overflow-wrap:anywhere">' + esc(x.x) + '</span></li>'; }).join('') + '</ul></section>\
    </div></div>';
  }

  /* ---------- ATL approvals ---------- */
  function fileBtn(f, id, i) { return P.fileBtn(f, 'data-src="atl" data-id="' + id + '" data-i="' + i + '"'); }
  function vAtl() {
    var list = D.atl.filter(function (a) { return vis(a) && a.st === S.atlTab; });
    var cur = S.atl && L.atl(S.atl);
    if (!cur || !vis(cur) || (cur.st !== S.atlTab && list.length)) S.atl = list[0] ? list[0].id : null;
    var a = S.atl && L.atl(S.atl) && L.atl(S.atl).st === S.atlTab ? L.atl(S.atl) : null;
    var tabs = TABS.map(function (t) { return '<button class="tab" role="tab" data-a="atltab" data-v="' + t[0] + '" aria-selected="' + (S.atlTab === t[0]) + '">' + t[1] + '<span class="n">' + D.atl.filter(function (x) { return vis(x) && x.st === t[0]; }).length + '</span></button>'; }).join('');
    var rows = list.length ? list.map(function (x) { return '<button class="row" data-a="atlsel" data-id="' + x.id + '" aria-current="' + (!!a && a.id === x.id) + '"><span class="r1"><b>' + x.id + '</b>' + rowPill(x) + '</span><span class="r2">' + esc(x.plate) + ' · ' + esc(L.driverName(x)) + '</span><span class="r2">' + esc(L.haulerName(x)) + ' · ' + fmtL(L.total(x)) + ' · ' + esc(x.at) + '</span></button>'; }).join('') : '<div class="empty">No ATLs in this tab.</div>';
    return P.head('ATL Approvals', 'Review each submitted ATL, then approve or decline it. Approved ATLs move on to compartment allocation.') + '<div class="tabs" role="tablist">' + tabs + '</div><div class="split"><section class="card list">' + rows + '</section>' + (a ? atlDetail(a) : '<section class="card"><div class="empty">Select an ATL to review.</div></section>') + '</div>';
  }
  function decisionHTML(a) {
    if (a.st === 'pending') {
      if (S.mode) return '<div class="reason"><div class="fld"><label for="reason">Why is this ATL declined? <span class="req">*</span></label><textarea id="reason" placeholder="State the reason the ATL user will see."></textarea></div><div class="err" id="rerr"></div><div class="actions"><button class="btn danger" data-a="confirmreason">Decline ATL</button><button class="btn" data-a="cancelmode">Cancel</button></div></div>';
      return '<div class="actions"><button class="btn primary" data-a="approve">Approve ATL</button><button class="btn danger" data-a="mode">Decline</button></div><div class="small muted">Approved ATLs move on to compartment allocation. A declined ATL needs a recorded reason and stays in the history for audit.</div>';
    }
    if (a.st === 'approved') return '<div class="actions">' + pill('ok', 'Approved') + '<span class="muted small">Compartment allocation is the next step.</span></div>';
    return '<div>' + pill.apply(null, ASTAT[a.st]) + '<p style="margin:8px 0 0"><b>Reason:</b> ' + esc(a.reason) + '</p><p class="muted small" style="margin:4px 0 0">This ATL and its history are kept for audit.</p></div>';
  }
  function allocSummary(a) {
    if (!a.d) return '<div class="callout warn"><b>Next step: compartment allocation</b><span>This ATL is approved. Assign the requested products to the truck\'s compartments. The Loading Ticket can be printed after that.</span></div><div class="actions"><a class="btn primary" href="' + allocUrl(a.id) + '">' + ic('wand') + 'Open compartment allocation</a></div>';
    var locked = a.d.lt.st === 'printed';
    return '<div class="actions">' + pill('ok', 'Allocated') + '<span class="small muted">' + a.alloc.length + ' compartments assigned</span></div><div class="scroll"><table><thead><tr><th>Comp.</th><th>Product</th><th class="r">Liters</th><th>Remark</th></tr></thead><tbody>' + a.alloc.map(function (x) { return '<tr><td class="mono">C' + x.c + '</td><td><span class="pdot ' + x.p + '"></span>' + esc(x.p) + '</td><td class="num r">' + fmtL(x.l) + '</td><td>' + esc(x.rem) + '</td></tr>'; }).join('') + '</tbody></table></div><div class="actions"><a class="btn sm" href="' + allocUrl(a.id) + '">' + (locked ? 'View allocation' : 'Edit allocation') + '</a>' + (locked ? '<span class="small muted">The Loading Ticket is printed, so the allocation is locked.</span>' : '') + '</div>';
  }
  function atlDetail(a) {
    var t = L.truck(a.plate), open = a.st === 'pending';
    var req = a.lines.map(function (l) { return '<tr><td><span class="pdot ' + l.p + '"></span><b>' + esc(l.p) + '</b></td><td class="num r">' + fmtL(l.l) + '</td></tr>'; }).join('');
    var kv = function (l, x, mono) { return '<div><dt>' + l + '</dt><dd class="' + (mono ? 'mono' : '') + '">' + (x || '-') + '</dd></div>'; };
    return '<section class="card"><div class="card-h"><h2 class="mono" style="font-size:17px">' + a.id + '</h2>' + rowPill(a) + '<span class="muted small sp">Submitted ' + esc(a.at) + '</span></div>\
  <div class="sec"><h3>ATL information</h3><dl class="kv">' + kv('ATL number', esc(a.id), 1) + kv('Control number', esc(a.ctl), 1) + kv('Sales Order number', esc(a.so), 1) + kv('Date', fmtD(a.date)) + '</dl></div>\
  <div class="sec"><h3>Customer and destination</h3><dl class="kv">' + kv('Attention', esc(a.attn)) + kv('Depot address (Attention)', esc(a.attnAddr)) + kv('Customer', esc(a.to)) + kv('Customer address', esc(a.addr)) + kv('Depot address', esc(a.depot)) + kv('Thru', esc(a.thru)) + kv('Destination', esc(a.dest)) + '</dl></div>\
  <div class="sec"><h3>Truck and driver</h3><dl class="kv">' + kv('Truck', esc(a.plate), 1) + kv('Driver', esc(L.driverName(a)) + (a.mode === 'sub' ? ' (substitute)' : '')) + kv('Hauler', esc(L.haulerName(a))) + kv('Total volume', fmtL(L.total(a)) + ' of ' + fmtL(L.cap(t))) + '</dl></div>\
  <div class="sec"><h3>Requested products</h3><div class="scroll"><table><thead><tr><th>Product</th><th class="r">Liters</th></tr></thead><tbody>' + req + '</tbody></table></div></div>\
  <div class="sec"><h3>Attachments</h3>' + (a.files.length ? '<div class="files">' + a.files.map(function (f, i) { return fileBtn(f, a.id, i); }).join('') + '</div>' : '<div class="small muted">No supporting documents were attached. They are optional.</div>') + '</div>\
  <div class="sec"><h3>' + (open ? 'Decision' : 'Outcome') + '</h3><div id="decision">' + decisionHTML(a) + '</div></div>' +
      (a.st === 'approved' ? '<div class="sec"><h3>Compartment allocation</h3>' + allocSummary(a) + '</div>' : '') +
      '<div class="sec"><h3>History</h3><ul class="hist">' + a.hist.map(function (h) { return '<li><time>' + esc(h.t) + '</time><span>' + esc(h.text) + '</span></li>'; }).join('') + '</ul></div></section>';
  }

  /* ---------- compartment allocation ---------- */
  function ensureDraft(a, locked) {
    if (S.ad && S.ad.id === a.id) return;
    var t = L.truck(a.plate);
    S.ad = { id: a.id, rows: t.comps.map(function (c, i) { var x = a.alloc.find(function (y) { return y.c === i + 1; }); return x ? { p: x.p, l: x.l, rem: x.rem } : { p: '', l: 0, rem: 'Standard load' }; }), note: '' };
    if (!a.alloc.length && !locked) { var r = autoRows(a); S.ad.rows = r.rows; S.ad.note = r.left ? 'auto-short:' + r.left : 'auto'; }
  }
  function diagHTML(a, rows) {
    var t = L.truck(a.plate);
    return '<div class="tanks">' + t.comps.map(function (c, i) { var r = rows[i], l = +r.l || 0, pct = Math.min(100, Math.round(l / c * 100)); return '<div class="tank ' + (r.p ? 'pr-' + r.p : '') + ' ' + (l > c ? 'over' : '') + '"><div class="th"><b>C' + (i + 1) + '</b><span>' + (r.p || 'Empty') + '</span></div><div class="tfill"><i style="height:' + pct + '%"></i></div><div class="tl num">' + l.toLocaleString('en-US') + ' / ' + c.toLocaleString('en-US') + ' L</div></div>'; }).join('') + '</div>';
  }
  function reconHTML(a, ck) {
    return '<div class="scroll"><table class="recon"><thead><tr><th>Product</th><th class="r">Requested</th><th class="r">Allocated</th><th class="r">Difference</th><th></th></tr></thead><tbody>' + ck.rec.map(function (x) { return '<tr><td><span class="pdot ' + x.p + '"></span><b>' + x.p + '</b></td><td class="r num">' + fmtL(x.req) + '</td><td class="r num">' + fmtL(x.al) + '</td><td class="r num">' + (x.al - x.req > 0 ? '+' : '') + (x.al - x.req).toLocaleString('en-US') + '</td><td>' + (x.req === x.al ? pill('ok', 'Matches') : pill('bad', 'Mismatch')) + '</td></tr>'; }).join('') + '</tbody></table></div>';
  }
  function checkHTML(ck) { return ck.checks.map(function (c) { return '<div class="chk2"><div><b>' + c.k + '</b><span class="nt ' + (c.s === 'ok' ? '' : 'bad') + '">' + esc(c.n) + '</span></div>' + (c.s === 'ok' ? pill('ok', 'Passed') : pill('bad', 'Fix needed')) + '</div>'; }).join(''); }
  function barHTML(a, ck, locked) {
    if (locked) return '<span class="st">The Loading Ticket is printed, so this allocation is locked.</span><a class="btn" href="' + atlUrl('approved', a.id) + '">Back to ATL Approvals</a>';
    return '<span class="st">' + (ck.ok ? '<b>Valid.</b> Capacities respected and allocated liters match the request.' : 'Resolve the items above to save.') + '</span><a class="btn" href="' + atlUrl('approved', a.id) + '">Cancel</a><button class="btn primary" data-a="savealloc" ' + (ck.ok ? '' : 'disabled') + '>' + ic('save') + 'Save allocation</button>';
  }
  function vAlloc() {
    var a = L.atl(S.allocId);
    if (!a || a.st !== 'approved' || !vis(a)) return P.head('Compartment allocation', 'This ATL is not approved, so it cannot be allocated.') + '<a class="btn" href="' + U.approvals + '">Back to ATL Approvals</a>';
    var t = L.truck(a.plate), locked = !!(a.d && a.d.lt.st === 'printed');
    ensureDraft(a, locked);
    var rows = S.ad.rows, ck = allocCheck(a, rows), n = S.ad.note;
    var nt = n === 'auto' ? '<div class="callout ok"><b>Automatic allocation applied</b><span>Products and liters were assigned from the requested volumes and the registered compartment capacities. Review them and adjust if needed.</span></div>' : n.indexOf('auto-short') === 0 ? '<div class="callout warn"><b>Automatic allocation could not place everything</b><span>' + fmtL(n.split(':')[1]) + ' does not fit because each compartment holds one product. Adjust manually, or decline the ATL with a reason.</span></div>' : '';
    var rowsHTML = t.comps.map(function (c, i) {
      var r = rows[i];
      return '<tr><td class="mono"><b>C' + (i + 1) + '</b></td><td class="num">' + c.toLocaleString('en-US') + ' L</td>\
    <td><select id="ar_p_' + i + '" data-ar="' + i + '" data-k="p" aria-label="Product for compartment ' + (i + 1) + '" ' + (locked ? 'disabled' : '') + '><option value="">Empty</option>' + PRODUCTS.map(function (p) { return '<option ' + (p === r.p ? 'selected' : '') + '>' + p + '</option>'; }).join('') + '</select></td>\
    <td style="min-width:130px"><input type="number" id="ar_l_' + i + '" data-ar="' + i + '" data-k="l" min="0" step="100" inputmode="numeric" value="' + (r.l || '') + '" aria-label="Liters for compartment ' + (i + 1) + '" ' + (locked ? 'disabled' : '') + '><div class="rerr" id="rowerr_' + i + '">' + esc(ck.rowErr[i] || '') + '</div></td>\
    <td><select id="ar_r_' + i + '" data-ar="' + i + '" data-k="rem" aria-label="Remark for compartment ' + (i + 1) + '" ' + (locked ? 'disabled' : '') + '>' + REMARKS.map(function (x) { return '<option ' + (x === r.rem ? 'selected' : '') + '>' + x + '</option>'; }).join('') + '</select></td></tr>';
    }).join('');
    return '<div class="crumb"><a class="lnk" href="' + atlUrl('approved', a.id) + '">ATL Approvals</a><span>/</span><span class="mono">' + a.id + '</span><span>/</span><span>Compartment allocation</span></div>' +
      P.head('Compartment allocation', 'Products are assigned automatically from the requested volumes and the registered compartments. Adjust where needed, then save.') + '\
  <div class="grid" style="gap:16px">' + nt + '\
   <section class="card"><div class="card-b"><dl class="kv"><div><dt>ATL</dt><dd class="mono">' + a.id + '</dd></div><div><dt>Truck</dt><dd class="mono">' + esc(a.plate) + '</dd></div><div><dt>Driver</dt><dd>' + esc(L.driverName(a)) + '</dd></div><div><dt>Customer</dt><dd>' + esc(a.to) + '</dd></div><div><dt>Requested</dt><dd class="num">' + fmtL(L.total(a)) + '</dd></div><div><dt>Truck capacity</dt><dd class="num">' + fmtL(L.cap(t)) + ' in ' + t.comps.length + ' compartments</dd></div></dl></div></section>\
   <section class="card"><div class="card-h"><h2>Truck compartments</h2><div class="actions sp">' + (locked ? '' : '<button class="btn sm" data-a="autoagain">' + ic('wand') + 'Run automatic allocation</button><button class="btn sm" data-a="clearalloc">Clear all</button>') + '</div></div><div class="card-b" id="allocdiag">' + diagHTML(a, rows) + '</div></section>\
   <section class="card"><div class="card-h"><h2>Assignments</h2><span class="small muted sp">One product per compartment. Liters cannot exceed the limit.</span></div><div class="scroll"><table><thead><tr><th>Comp.</th><th>Limit</th><th>Product</th><th>Liters</th><th>Remark</th></tr></thead><tbody>' + rowsHTML + '</tbody></table></div></section>\
   <div class="grid cols2">\
    <section class="card"><div class="card-h"><h2>Requested vs allocated</h2></div><div id="allocrecon">' + reconHTML(a, ck) + '</div></section>\
    <section class="card"><div class="card-h"><h2>Validation</h2></div><div class="card-b" id="alloccheck" style="gap:0">' + checkHTML(ck) + '</div></section>\
   </div></div><div class="actionbar" id="allocbar">' + barHTML(a, ck, locked) + '</div>';
  }
  function patchAlloc() {
    var a = L.atl(S.allocId); if (!a || !S.ad) return;
    var rows = S.ad.rows, ck = allocCheck(a, rows), t = L.truck(a.plate);
    var set = function (id, h) { var e = document.getElementById(id); if (e) e.innerHTML = h; };
    set('allocdiag', diagHTML(a, rows)); set('allocrecon', reconHTML(a, ck)); set('alloccheck', checkHTML(ck)); set('allocbar', barHTML(a, ck, false));
    t.comps.forEach(function (c, i) { set('rowerr_' + i, esc(ck.rowErr[i] || '')); });
  }

  /* ---------- documents & release ---------- */
  function vDocs() {
    var ap = D.atl.filter(function (a) { return vis(a) && a.st === 'approved'; }), q = S.q.trim().toLowerCase();
    var m = function (a) { return !q || [a.id, a.plate, L.driverName(a), L.haulerName(a), a.to, a.dest, a.d && a.d.lt.no, a.d && a.d.dr.no, a.d && a.d.wc.no].join(' ').toLowerCase().indexOf(q) >= 0; };
    var list = ap.filter(function (a) { return m(a) && (S.docF === 'all' || stage(a) === S.docF); });
    if (!S.doc || !list.find(function (x) { return x.id === S.doc; })) S.doc = list[0] ? list[0].id : null;
    var a = S.doc ? L.atl(S.doc) : null;
    var ft = DFILT.map(function (f) { return '<button class="tab" role="tab" data-a="docf" data-v="' + f[0] + '" aria-selected="' + (S.docF === f[0]) + '">' + f[1] + '<span class="n">' + (f[0] === 'all' ? ap.length : ap.filter(function (x) { return stage(x) === f[0]; }).length) + '</span></button>'; }).join('');
    var rows = list.length ? list.map(function (x) { return '<button class="row" data-a="docsel" data-id="' + x.id + '" aria-current="' + (!!a && a.id === x.id) + '"><span class="r1"><b>' + x.id + '</b>' + pill.apply(null, STAGE[stage(x)]) + '</span><span class="r2">' + esc(x.plate) + ' · ' + esc(L.driverName(x)) + '</span><span class="r2">' + esc(x.to) + '</span></button>'; }).join('') : '<div class="empty">No approved ATLs match.</div>';
    return P.head('Documents & Release', 'Print the LT first. When the driver returns, prepare and hand over the DR and WC before the driver leaves.') + '\
  <div class="tabs" role="tablist">' + ft + '</div>\
  <div class="split"><div style="min-width:0"><div class="search"><div class="fld"><label for="q">Search approved ATLs</label><input type="search" id="q" placeholder="ATL, plate, driver, hauler or document no." value="' + esc(S.q) + '"></div></div><section class="card list">' + rows + '</section></div>' + (a ? docDetail(a) : '<section class="card"><div class="empty">Select an approved ATL to open its documents.</div></section>') + '</div>';
  }
  function stepsOf(arr) {
    var f = arr.findIndex(function (s) { return !s[2]; });
    return '<ol class="steps">' + arr.map(function (s, i) { return '<li class="' + (s[2] ? 'done' : i === f ? 'now' : '') + '"><b>' + s[0] + (s[2] ? ' · done' : '') + '</b>' + s[1] + '</li>'; }).join('') + '</ol>';
  }
  function docDetail(a) {
    var d = a.d, st = stage(a), dn = esc(L.driverName(a));
    var head_ = '<div class="card-h"><h2 class="mono" style="font-size:17px">' + a.id + '</h2>' + pill.apply(null, STAGE[st]) + '<span class="sp small muted">' + esc(a.plate) + ' · ' + dn + ' · ' + fmtL(L.total(a)) + '</span></div>';
    if (!d) return '<section class="card">' + head_ + '<div class="sec"><div class="next"><div><b>Next: allocate the compartments</b><span>The LT, DR and WC list the compartments, so they unlock once the allocation is saved.</span></div><a class="btn primary" href="' + allocUrl(a.id) + '">' + ic('wand') + 'Open compartment allocation</a></div>' + stepsOf([['Step 1', 'Allocate compartments', false], ['Step 2', 'Print LT', false], ['Step 3', 'Driver returns', false], ['Step 4', 'DR, WC and handover', false]]) + '</div></section>';
    var printedBoth = d.dr.st === 'printed' && d.wc.st === 'printed';
    var steps = [['Step 1', 'Allocated', true], ['Step 2', 'Print LT', d.lt.st === 'printed'], ['Step 3', 'Driver returns', d.ret], ['Step 4', 'DR, WC and handover', !!d.ho]];
    var next;
    if (st === 'lt') next = '<div class="next"><div><b>Next: print the Loading Ticket</b><span>Print the LT first. The driver can then load.</span></div><button class="btn primary" data-a="print" data-k="lt" data-id="' + a.id + '">' + ic('print') + 'Print Loading Ticket</button></div>';
    else if (st === 'return') next = '<div class="next"><div><b>Is the driver back in Operations?</b><span>' + dn + ' has the LT. Mark the driver as returned to unlock the DR and WC.</span></div><button class="btn primary" data-a="returned" data-id="' + a.id + '">Mark driver returned to Operations</button></div>';
    else if (st === 'release') next = '<div class="next"><div><b>' + (printedBoth ? 'Next: record the handover' : 'Next: print the DR and WC, then hand them over') + '</b><span>' + (printedBoth ? 'DR and WC are printed. Record who handed them to the driver and when.' : 'Print both documents, hand them to the driver, then record the handover. All in the section below.') + '</span></div>' + (printedBoth ? '' : '<button class="btn primary" data-a="printboth" data-id="' + a.id + '">' + ic('print') + 'Print DR and WC</button>') + '</div>';
    else next = '<div class="next done"><div><b>Released</b><span>DR and WC were handed to ' + dn + ' by ' + esc(d.ho.staff) + ' at ' + d.ho.time + '.</span></div></div>';
    var docCard = function (k, name, locked, nt) {
      var x = d[k];
      return '<div class="doc ' + (locked ? 'locked' : '') + '"><h4>' + name + '<span class="mono">' + x.no + '</span></h4><div>' + (locked ? pill('', 'Locked') : x.st === 'printed' ? pill('ok', 'Printed' + (x.n > 1 ? ' (' + x.n + 'x)' : '')) : pill('warn', 'Awaiting print')) + '</div><div class="small muted">' + (locked ? nt : x.st === 'printed' ? 'Last printed ' + x.at : 'Not printed yet') + '</div><div class="actions"><button class="btn sm" data-a="preview" data-k="' + k + '" data-id="' + a.id + '">' + ic('eye') + 'Preview</button>' + (x.st === 'awaiting' ? '<button class="btn sm primary" data-a="print" data-k="' + k + '" data-id="' + a.id + '" ' + (locked ? 'disabled' : '') + '>' + ic('print') + 'Print</button>' : '<button class="btn sm" data-a="reprint" data-k="' + k + '" data-id="' + a.id + '">' + ic('print') + 'Reprint</button>') + '</div></div>';
    };
    var ho;
    if (d.ho) ho = '<div class="actions">' + pill('ok', 'Released') + '<span>DR and WC handed to <b>' + dn + '</b>. Released by <b>' + esc(d.ho.staff) + '</b> at <b class="mono">' + d.ho.time + '</b>.</span></div>';
    else ho = '<div class="fgrid"><div class="fld"><label for="hstaff">Released by</label><select id="hstaff" ' + (printedBoth ? '' : 'disabled') + '>' + D.staff.map(function (s) { return '<option ' + (s === USER ? 'selected' : '') + '>' + esc(s) + '</option>'; }).join('') + '</select></div><div class="fld"><label for="htime">Time of release</label><input type="time" id="htime" value="' + nowHM() + '" ' + (printedBoth ? '' : 'disabled') + '></div></div><div class="actions"><button class="btn primary" data-a="handover" data-id="' + a.id + '" ' + (printedBoth ? '' : 'disabled') + '>Record handover to ' + dn + '</button></div><div class="small muted">' + (printedBoth ? 'Printing does not mark the documents as handed over. Record the handover once the driver has them.' : d.ret ? 'Print the DR and WC to enable the handover.' : 'Unlocks after the driver returns and both documents are printed.') + '</div>';
    return '<section class="card">' + head_ + '\
  <div class="sec">' + next + stepsOf(steps) + '</div>\
  <div class="sec"><h3>Loading Ticket</h3><div class="docs">' + docCard('lt', 'Loading Ticket', false) + '</div>\
    <div class="actions">' + (d.ret ? pill('ok', 'Driver is back in Operations') : '<span class="small muted">' + (d.lt.st === 'printed' ? 'Waiting for the driver to return. Use the button at the top once they are at the desk.' : 'Print the LT first.') + '</span>') + '</div>' +
      (d.lt.st === 'printed' ? '<div class="small muted">Compartment allocation is locked after the LT is printed. <a class="lnk" href="' + allocUrl(a.id) + '">View allocation</a></div>' : '<div class="small muted"><a class="lnk" href="' + allocUrl(a.id) + '">Edit compartment allocation</a> before printing if anything changed.</div>') + '</div>\
  <div class="sec"><h3>Delivery Receipt, Withdrawal Certificate and handover</h3><div class="docs">' + docCard('dr', 'Delivery Receipt', !d.ret, 'Unlocks when the driver returns.') + docCard('wc', 'Withdrawal Certificate', !d.ret, 'Unlocks when the driver returns.') + '</div>' +
      (d.ret && !printedBoth ? '<div class="actions"><button class="btn primary" data-a="printboth" data-id="' + a.id + '">' + ic('print') + 'Print DR and WC</button><span class="small muted">Prints both at once.</span></div>' : '') + ho + '</div>\
  <div class="sec"><h3>Print and release history</h3>' + (d.log.length ? '<ul class="hist">' + d.log.slice().reverse().map(function (h) { return '<li><time>' + esc(h.t) + '</time><span>' + esc(h.text) + '</span></li>'; }).join('') + '</ul>' : '<div class="small muted">No activity yet.</div>') + '</div></section>';
  }
  function paperDoc(a, k) {
    var d = a.d, T = DOCN[k];
    var sig = { lt: ['Issued by (Operations)', 'Truck driver', 'Loading supervisor'], dr: ['Released by (Operations)', 'Received by driver', 'Received by customer'], wc: ['Authorized by (Operations)', 'Withdrawn by driver', 'Security check'] }[k];
    return '<div class="paper"><div class="ph"><h3>' + T + '</h3><div class="pm">' + d[k].no + '<br>ATL ' + a.id.replace('ATL-2026-', '') + '<br>' + fmtD(a.date) + '</div></div>\
  <dl><div><dt>Truck</dt><dd>' + esc(a.plate) + '</dd></div><div><dt>Driver</dt><dd>' + esc(L.driverName(a)) + '</dd></div><div><dt>Hauler</dt><dd>' + esc(L.haulerName(a)) + '</dd></div><div><dt>Customer</dt><dd>' + esc(a.to) + '</dd></div><div><dt>Destination</dt><dd>' + esc(a.dest) + '</dd></div></dl>\
  <div class="scroll"><table><thead><tr><th>Comp.</th><th>Product</th><th class="r">Liters</th></tr></thead><tbody>' + a.alloc.map(function (l) { return '<tr><td>C' + l.c + '</td><td>' + esc(l.p) + '</td><td class="r num">' + (+l.l).toLocaleString('en-US') + '</td></tr>'; }).join('') + '<tr><td></td><td><b>Total</b></td><td class="r num"><b>' + L.total(a).toLocaleString('en-US') + '</b></td></tr></tbody></table></div>\
  <div class="sig">' + sig.map(function (s) { return '<div>' + s + '</div>'; }).join('') + '</div></div>';
  }
  function renderModal() {
    var m = S.modal, el = P.$('#px-modal', root);
    if (!m) { el.innerHTML = ''; return; }
    var a = L.atl(m.id), title = '', body = '', foot = '', wide = '';
    if (m.kind === 'preview') { wide = 'wide'; title = 'Preview'; body = '<div class="modal-b">' + paperDoc(a, m.k) + '</div>'; foot = '<button class="btn" data-a="mclose">Close</button>' + (a.d[m.k].st === 'awaiting' && (m.k === 'lt' || a.d.ret) ? '<button class="btn primary" data-a="print" data-k="' + m.k + '" data-id="' + a.id + '">' + ic('print') + 'Print</button>' : ''); }
    if (m.kind === 'reprint') { title = 'Reprint ' + a.d[m.k].no; body = '<div class="modal-b"><p style="margin:0">This copy is logged as reprint ' + a.d[m.k].n + ' for ' + a.id + '.</p><div class="fld"><label for="rp">Reason for reprint</label><select id="rp"><option>Paper jam or misprint</option><option>Copy damaged or lost</option><option>Correction to details</option><option>Extra copy requested</option></select></div></div>'; foot = '<button class="btn" data-a="mclose">Cancel</button><button class="btn primary" data-a="doreprint" data-k="' + m.k + '" data-id="' + a.id + '">' + ic('print') + 'Reprint</button>'; }
    el.innerHTML = '<div class="modal-bg" data-a="mbg"><div class="modal ' + wide + '" role="dialog" aria-modal="true" aria-label="' + esc(title) + '"><div class="modal-h"><h2>' + esc(title) + '</h2><button class="btn sm" data-a="mclose" aria-label="Close">Close</button></div>' + body + '<div class="modal-f">' + foot + '</div></div></div>';
  }

  /* ---------- actions ---------- */
  function printDoc(id, k, reason) {
    var a = L.atl(id), x = a.d[k]; x.st = 'printed'; x.n++; x.at = nowHM();
    var nm = k.toUpperCase();
    a.d.log.push({ t: x.at, text: x.n > 1 ? nm + ' reprinted by ' + USER + ' (' + reason + ')' : nm + ' printed by ' + USER });
    logAct('ok', (x.n > 1 ? nm + ' reprinted' : nm + ' printed') + ' for ' + id);
    if (k === 'lt' && x.n === 1) note('ok', id + ' Loading Ticket printed. The driver can load.', id);
    P.toast(nm + ' ' + x.no + ' sent to the printer and logged. This does not record a handover', root);
  }
  var ACTIONS = {
    atltab: function (e) { S.atlTab = e.v; S.atl = null; S.mode = null; },
    atlsel: function (e) { S.atl = e.id; S.mode = null; if (innerWidth <= 900) setTimeout(function () { var c = document.querySelector('.px .split > section:last-child'); if (c) c.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 30); },
    mode: function () { S.mode = 'decline'; }, cancelmode: function () { S.mode = null; },
    confirmreason: function () {
      var r = (P.$('#reason', root).value || '').trim();
      if (r.length < 5) { P.$('#rerr', root).textContent = 'Enter a reason of at least a few words. The ATL user will see it.'; return 'skip'; }
      var a = L.atl(S.atl); a.st = 'declined'; a.reason = r; a.by = USER; a.rt = nowHM();
      a.hist.push({ t: nowHM(), text: 'Declined by ' + USER + ': ' + r }); S.mode = null;
      logAct('bad', a.id + ' declined: ' + r); note('bad', a.id + ' declined: ' + r, a.id);
      P.toast('ATL declined. Its history is kept for audit', root); S.atlTab = 'declined'; S.atl = a.id;
    },
    approve: function () {
      var a = L.atl(S.atl); a.st = 'approved'; a.d = null; a.alloc = []; a.hist.push({ t: nowHM(), text: 'Approved by ' + USER });
      logAct('ok', a.id + ' approved. Ready for compartment allocation'); note('ok', a.id + ' approved. Operations will allocate the compartments next.', a.id);
      P.toast('ATL approved. Next, allocate the compartments', root); S.atlTab = 'approved'; S.atl = a.id;
    },
    autoagain: function () { var a = L.atl(S.allocId), r = autoRows(a); S.ad.rows = r.rows; S.ad.note = r.left ? 'auto-short:' + r.left : 'auto'; },
    clearalloc: function () { S.ad.rows = S.ad.rows.map(function () { return { p: '', l: 0, rem: 'Standard load' }; }); S.ad.note = ''; },
    savealloc: function () {
      var a = L.atl(S.allocId), ck = allocCheck(a, S.ad.rows); if (!ck.ok) return 'skip';
      var first = !a.d;
      a.alloc = S.ad.rows.map(function (r, i) { return { c: i + 1, p: r.p, l: +r.l || 0, rem: r.rem, note: '' }; }).filter(function (x) { return x.l > 0 && x.p; });
      if (first) nums(a);
      a.hist.push({ t: nowHM(), text: (first ? 'Compartments allocated by ' : 'Compartment allocation updated by ') + USER });
      logAct('ok', a.id + (first ? ' compartments allocated. LT ' + a.d.lt.no + ' is ready to print' : ' allocation updated'));
      if (first) note('info', a.id + ' compartments allocated. Documents are being prepared.', a.id);
      D.__flash = 'Allocation saved. Continue in Documents & Release';
      P.store.save(D); location.href = docUrl('', a.id); return 'skip';
    },
    docf: function (e) { S.docF = e.v; S.doc = null; },
    docsel: function (e) { S.doc = e.id; if (innerWidth <= 900) setTimeout(function () { var c = document.querySelector('.px .split > section:last-child'); if (c) c.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 30); },
    preview: function (e) { S.modal = { kind: 'preview', id: e.id, k: e.k }; },
    print: function (e) { printDoc(e.id, e.k); S.modal = null; },
    reprint: function (e) { S.modal = { kind: 'reprint', id: e.id, k: e.k }; },
    doreprint: function (e) { printDoc(e.id, e.k, P.$('#rp', root).value); S.modal = null; },
    returned: function (e) { var a = L.atl(e.id); a.d.ret = true; a.d.log.push({ t: nowHM(), text: 'Driver returned to Operations' }); logAct('info', 'Driver ' + L.driverName(a) + ' returned to Operations (' + a.id + ')'); P.toast('Driver marked as returned. DR and WC are unlocked', root); },
    printboth: function (e) { var a = L.atl(e.id); ['dr', 'wc'].forEach(function (k) { if (a.d[k].st === 'awaiting') printDoc(e.id, k); }); P.toast('DR and WC sent to the printer and logged. This does not record a handover', root); },
    handover: function (e) {
      var a = L.atl(e.id), s = P.$('#hstaff', root).value, tm = P.$('#htime', root).value || nowHM();
      a.d.ho = { staff: s, time: tm }; a.d.log.push({ t: tm, text: 'DR and WC handed over to ' + L.driverName(a) + '. Released by ' + s });
      logAct('ok', 'DR and WC for ' + a.id + ' handed to driver and released by ' + s); note('ok', 'DR and WC for ' + a.id + ' released to the driver.', a.id);
      P.toast('Handover recorded', root);
    },
    mclose: function () { S.modal = null; },
    mbg: function (e, t, ev) { if (ev.target !== t) return 'skip'; S.modal = null; },
    pvfile: function (e) { var f = L.atl(e.id) && L.atl(e.id).files[+e.i]; if (f) P.preview(f, root); return 'skip'; },
    pvclose: function () { P.preview(null, root); return 'skip'; },
    pvbg: function (e, t, ev) { if (ev.target === t) P.preview(null, root); return 'skip'; }
  };

  function render() {
    P.keepFocus(function () {
      var v = { dash: vDash, atl: vAtl, alloc: vAlloc, docs: vDocs }[S.page]();
      P.$('#px-main', root).innerHTML = v;
    });
    renderModal();
  }
  function run(name, ds, t, ev) {
    var f = ACTIONS[name]; if (!f) return;
    var r = f(ds, t, ev); if (r === 'skip') return;
    P.store.save(D); render();
  }

  window.PXOps = {
    mount: function (cfg) {
      root = document.getElementById('px');
      U = cfg.urls; D = P.store.load(cfg.seed); L = P.lookups(D); USER = D.user;
      var q = P.query();
      S = { page: cfg.page, atlTab: ['pending', 'approved', 'declined'].indexOf(q.tab) >= 0 ? q.tab : 'pending', atl: q.id || null, mode: null, docF: DFILT.some(function (f) { return f[0] === q.f; }) ? q.f : 'all', doc: q.id || null, q: '', modal: null, ad: null, allocId: cfg.id || null };
      if (S.page === 'atl' && S.atl) { var x = L.atl(S.atl); if (x && vis(x) && ['pending', 'approved', 'declined'].indexOf(x.st) >= 0 && !q.tab) S.atlTab = x.st; }
      root.innerHTML = P.frame();
      P.wire(root, {
        run: run,
        escape: function () { if (P.previewOpen()) P.preview(null, root); else if (S.modal) { S.modal = null; renderModal(); } },
        change: function (ev) {
          var t = ev.target, d = t.dataset || {};
          if (d.ar !== undefined && S.ad) { S.ad.rows[+d.ar][d.k] = t.value; S.ad.note = ''; render(); }
        },
        input: function (ev) {
          var t = ev.target, d = t.dataset || {};
          if (t.id === 'q') { S.q = t.value; S.doc = null; render(); return; }
          if (d.ar !== undefined && d.k === 'l' && S.ad) { S.ad.rows[+d.ar].l = t.value === '' ? 0 : +t.value; S.ad.note = ''; patchAlloc(); }
        }
      });
      render();
      if (D.__flash) { P.toast(D.__flash, root); delete D.__flash; P.store.save(D); }
    }
  };
})();
