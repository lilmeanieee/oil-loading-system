/* PA Fuel 118 - ATL User pages: Dashboard, ATL Records and Create ATL.
   DEMO ONLY: reads and writes the browser copy of the sample data (see ui-core.js), shared with Operations. */
(function () {
  'use strict';
  var P = window.PX, esc = P.esc, ic = P.ic, pill = P.pill, fmtL = P.fmtL, fmtD = P.fmtD, nowHM = P.nowHM, sum = P.sum;
  var PRODUCTS = ['R95', 'R91', 'DIESEL'];
  var CERTS = [['or', 'OR'], ['cr', 'CR'], ['cal', 'Calibration']];
  var ST = { draft: ['', 'Draft'], pending: ['warn', 'Pending review'], approved: ['ok', 'Approved'], declined: ['bad', 'Declined'] };
  var TABS = [['draft', 'Drafts'], ['pending', 'Pending'], ['approved', 'Approved'], ['declined', 'Declined'], ['all', 'All Records']];
  var DOCN = { lt: 'Loading Ticket', dr: 'Delivery Receipt', wc: 'Withdrawal Certificate' };
  var DSTAT = { locked: ['', 'Waiting for allocation'], awaiting: ['', 'Not yet printed'], printed: ['acc', 'Printed'], released: ['ok', 'Released to driver'] };
  var SECS = [['info', 'ATL information'], ['cust', 'Customer and destination'], ['truck', 'Truck and driver'], ['prod', 'Products and volume'], ['attach', 'Attachments (optional)'], ['act', 'Actions']];

  var D, L, root, U, S, ME;
  var online = function () { return navigator.onLine !== false; };

  /* ---------- records ---------- */
  var mine = function () { return D.atl.filter(function (a) { return a.creator === ME; }); };
  var total = function (a) { return L.total(a); };
  var blank = function () { return { id: '', ctl: '', orig: '', date: P.todayS, st: 'draft', sync: 'synced', creator: ME, so: '', attn: 'PA Fuel 118', attnAddr: 'Sigeul, General Santos City', to: '', addr: '', depot: '', thru: '', dest: '', plate: '', mode: 'default', sub: '', lines: [{ p: '', l: '' }], files: [], reason: '', by: '', rt: '', hist: [], alloc: [], d: null, origin: 'new', tried: false }; };
  var clone = function (o) { return JSON.parse(JSON.stringify(o, function (k, v) { return (k === 'url' || k === 'buf') ? v : v; })); };
  var pillOf = function (k, t) { return pill(k, t); };
  var statusPill = function (a) { return a.sync === 'queued' ? pillOf('info', 'Queued, not sent') : pill.apply(null, ST[a.st]); };
  var syncPill = function (a) { return a.sync === 'local' ? pillOf('info', 'On this device only') : a.sync === 'queued' ? pillOf('info', 'Not synced') : ''; };
  var unsynced = function () { return mine().filter(function (a) { return a.sync !== 'synced'; }); };
  var subsFor = function (t) { return t && t.s && D.drivers.find(function (x) { return x.id === t.s; }) ? [[t.s, D.drivers.find(function (x) { return x.id === t.s; }).name]] : []; };
  var drvName = function (f) { return L.driverName(f); };
  var certPill = function (x) { var n = P.daysTo(x); return n < 0 ? pill('bad', 'Expired') : n <= 30 ? pill('warn', 'Expiring in ' + n + ' days') : pill('ok', 'Valid'); };
  var myNotes = function () { var ids = mine().map(function (a) { return a.id; }); return D.notes.filter(function (n) { return !n.id || ids.indexOf(n.id) >= 0; }); };
  var docOf = function (a, k) {
    if (!a.d) return ['locked', '-'];
    var x = a.d[k];
    if (k !== 'lt' && a.d.ho) return ['released', x.no];
    return [x.st === 'printed' ? 'printed' : 'awaiting', x.no];
  };
  var canView = function (a, k) { if (!a.d) return false; return k === 'lt' ? a.d.lt.st === 'printed' : !!a.d.ho; };
  var fileBtnA = function (f, attrs) { return P.fileBtn(f, attrs); };

  /* ---------- validation ---------- */
  function validate(f) {
    var e = { info: [], cust: [], truck: [], prod: [], attach: [] }, t = L.truck(f.plate);
    if (!f.id.trim()) e.info.push('Enter the ATL number.');
    else if (D.atl.some(function (a) { return a.id.trim().toLowerCase() === f.id.trim().toLowerCase() && a.id !== f.orig; })) e.info.push('ATL number ' + f.id.trim() + ' is already used.');
    if (!f.ctl.trim()) e.info.push('Enter the control number.');
    if (!f.so.trim()) e.info.push('Enter the Sales Order number.');
    if (!f.attn.trim()) e.cust.push('Enter the Attention.');
    if (!f.attnAddr.trim()) e.cust.push('Enter the depot address of the Attention.');
    if (!f.to.trim()) e.cust.push('Enter the Customer (the company buying the product).');
    if (!f.addr.trim()) e.cust.push('Enter the Customer address.');
    if (!f.depot.trim()) e.cust.push('Enter the depot address.');
    if (!f.dest.trim()) e.cust.push('Enter the destination.');
    if (!t) e.truck.push('Select a truck plate number.');
    else if (f.mode === 'sub' && !f.sub) e.truck.push('Select the authorized substitute driver.');
    if (!f.lines.length) e.prod.push('Add at least one product.');
    f.lines.forEach(function (l, i) { var n = 'Product line ' + (i + 1); if (!l.p) e.prod.push(n + ': choose R95, R91 or DIESEL.'); if (!(l.l > 0)) e.prod.push(n + ': enter the requested liters.'); });
    var req = sum(f.lines.map(function (l) { return l.l; }));
    if (t && req > L.cap(t)) e.prod.push('Requested ' + fmtL(req) + ' is over the truck capacity of ' + fmtL(L.cap(t)) + '.');
    return e;
  }
  var nIssues = function (e) { return Object.keys(e).reduce(function (n, k) { return n + e[k].length; }, 0); };

  /* ---------- save / submit ---------- */
  function stash(f, status) {
    var i = D.atl.findIndex(function (a) { return a.id === f.orig; });
    var rec = JSON.parse(JSON.stringify(f));
    delete rec.origin; delete rec.tried; delete rec.orig;
    rec.id = (f.id || '').trim() || ('DRAFT-' + nowHM().replace(':', '')); rec.ctl = (f.ctl || '').trim(); rec.creator = ME;
    rec.st = status; rec.saved = nowHM();
    if (status === 'draft') { rec.sync = online() ? 'synced' : 'local'; rec.hist = i >= 0 ? D.atl[i].hist || [] : []; }
    else {
      rec.sync = online() ? 'synced' : 'queued'; rec.submitted = rec.sync === 'synced' ? nowHM() : ''; rec.review = 'queue';
      var prev = i >= 0 ? D.atl[i].hist || [] : [];
      rec.hist = prev.concat([{ t: nowHM(), text: online() ? 'Submitted' : 'Submitted offline. Waiting to sync.' }]);
      if (rec.sync === 'synced') D.act.unshift({ t: nowHM(), k: 'info', x: rec.id + ' submitted by ' + ME });
    }
    rec.at = rec.submitted || rec.saved;
    if (i >= 0) D.atl[i] = rec; else D.atl.push(rec);
    return rec;
  }
  function goRecords(tab, msg) { D.__flash = msg; P.store.save(D); location.href = U.records + '?tab=' + tab; }
  function doSave() { var r = stash(S.f, 'draft'); goRecords('draft', online() ? 'Draft ' + r.id + ' saved' : 'Draft saved on this device. It will sync when you are online'); }
  function doSubmit(f) {
    f = f || S.f; var e = validate(f);
    if (nIssues(e)) { f.tried = true; P.toast('Complete the highlighted items before submitting', root); render(); var el = document.getElementById('errsum'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' }); return false; }
    var r = stash(f, 'pending');
    if (online()) D.notes.unshift({ t: nowHM(), k: 'info', x: r.id + ' submitted. Waiting for Operations review.', id: r.id, u: true });
    goRecords('pending', online() ? r.id + ' submitted for approval' : 'Offline. Submission queued and will send when you sync');
    return true;
  }
  function doSync() {
    var items = unsynced();
    items.forEach(function (a) {
      var wasQ = a.sync === 'queued'; a.sync = 'synced';
      if (wasQ) { a.submitted = nowHM(); a.at = a.submitted; a.review = 'queue'; a.hist.push({ t: nowHM(), text: 'Synced and submitted' }); D.notes.unshift({ t: nowHM(), k: 'info', x: a.id + ' synced and submitted. Waiting for Operations review.', id: a.id, u: true }); D.act.unshift({ t: nowHM(), k: 'info', x: a.id + ' submitted by ' + ME }); }
    });
    P.toast(items.length + ' item' + (items.length > 1 ? 's' : '') + ' synchronized', root);
  }

  /* ---------- dashboard ---------- */
  function vDash() {
    var M = mine(), by = function (s) { return M.filter(function (a) { return a.st === s; }); };
    var dr = by('draft'), pe = by('pending'), pes = pe.filter(function (a) { return a.sync === 'synced'; }), ap = by('approved'), un = unsynced();
    var tile = function (lab, n, sub, cls, tab) { return '<a class="tile ' + cls + '" href="' + U.records + '?tab=' + tab + '"><span class="lab">' + lab + '</span><span class="big num">' + n + '</span><span class="sub">' + sub + '</span></a>'; };
    var recent = M.filter(function (a) { return a.st !== 'draft'; }).sort(function (x, y) { return (y.date + (y.submitted || y.saved || '')).localeCompare(x.date + (x.submitted || x.saved || '')); }).slice(0, 6);
    var notes = myNotes(), nUnread = notes.filter(function (n) { return n.u; }).length;
    var unDr = un.filter(function (a) { return a.st === 'draft'; }).length;
    return P.head('Dashboard', 'Your ATLs at a glance: what is waiting on you and what is waiting on Operations.') + '\
  <div class="grid tiles">' +
      tile('My drafts', dr.length, unDr ? unDr + ' saved on this device only' : 'Not yet submitted', dr.length ? '' : '', 'draft') +
      tile('Pending review', pes.length, pe.length > pes.length ? (pe.length - pes.length) + ' queued, not sent' : 'With Operations', pes.length ? 'warn' : '', 'pending') +
      tile('Approved', ap.length, ap.filter(function (a) { return !a.alloc.length; }).length + ' awaiting allocation by Operations', 'ok', 'approved') +
      tile('Declined', by('declined').length, 'Kept in your records', '', 'declined') + '</div>\
  <div class="grid cols2" style="margin-top:16px">\
    <div class="grid" style="align-content:start;min-width:0">\
      <section class="card"><div class="card-h"><h2>Recent submissions</h2><a class="btn sm sp" href="' + U.records + '?tab=all">View all records</a></div>\
        <div class="scroll"><table><thead><tr><th>ATL</th><th>Customer</th><th>Truck</th><th class="r">Liters</th><th>Status</th></tr></thead><tbody>' +
      (recent.length ? recent.map(function (a) { return '<tr><td><button class="idlink" data-a="detail" data-id="' + a.id + '">' + a.id.replace('ATL-2026-', 'ATL ') + '</button><div class="small muted">' + (a.date === P.todayS ? 'Today' : fmtD(a.date)) + ' ' + (a.submitted || a.saved || '') + '</div></td><td class="wrap">' + esc(a.to) + '<div class="small muted">' + esc(a.dest) + '</div></td><td class="mono">' + esc(a.plate) + '</td><td class="r num">' + fmtL(total(a)) + '</td><td>' + statusPill(a) + '</td></tr>'; }).join('') : '<tr><td colspan="5" class="empty">You have not submitted an ATL yet.</td></tr>') + '</tbody></table></div></section>\
      <section class="card"><div class="card-h"><h2>Offline drafts and unsynchronized submissions</h2><span class="pill ' + (online() ? 'ok' : 'warn') + ' sp">' + (online() ? 'Online' : 'Offline') + '</span></div>' +
      (un.length ? un.map(function (a) { return '<div class="sync-i"><div class="t"><b class="mono" style="font-weight:500">' + a.id.replace('ATL-2026-', 'ATL ') + '</b> <span class="muted">' + (a.sync === 'local' ? 'Draft saved on this device' : 'Submission queued, not sent') + ' at ' + esc(a.saved) + '</span></div>' + syncPill(a) + '</div>'; }).join('') + '<div class="sync-i"><span class="small muted t">' + (online() ? 'Sync now to send queued submissions and back up local drafts.' : 'You are offline. Items sync when the connection returns.') + '</span><button class="btn primary sm" data-a="sync" ' + (online() ? '' : 'disabled') + '>' + ic('sync') + 'Sync now</button></div>' : '<div class="empty">Everything is synchronized.</div>') + '</section>\
    </div>\
    <section class="card" style="align-self:start"><div class="card-h"><h2>Notifications and review remarks</h2>' + (nUnread ? '<button class="btn sm sp" data-a="readall">Mark ' + nUnread + ' as read</button>' : '') + '</div>\
      <ul class="feed">' + (notes.length ? notes.slice(0, 7).map(function (n) { return '<li class="' + (n.u ? 'unread' : '') + '"><time>' + esc(n.t) + '</time><i class="' + n.k + '"></i><span class="tx">' + esc(n.x) + ' ' + (n.id ? '<button class="idlink" data-a="detail" data-id="' + n.id + '">Open</button>' : '') + '</span></li>'; }).join('') : '<li><span></span><span></span><span class="tx muted">Nothing new.</span></li>') + '</ul></section>\
  </div>';
  }

  /* ---------- records ---------- */
  function rowActions(a) {
    var v = '<button class="btn sm" data-a="detail" data-id="' + a.id + '">' + ic('eye') + (a.st === 'declined' ? 'Read reason' : a.st === 'pending' ? 'Review status' : 'View details') + '</button>';
    if (a.st === 'draft') return '<a class="btn sm primary" href="' + U.create + '?edit=' + encodeURIComponent(a.id) + '">' + ic('edit') + 'Continue editing</a><button class="btn sm" data-a="previewrec" data-id="' + a.id + '">' + ic('eye') + 'Preview</button><button class="btn sm" data-a="submitrec" data-id="' + a.id + '">' + ic('send') + 'Submit</button>';
    return v;
  }
  function vRecords() {
    var M = mine(), q = S.q.trim().toLowerCase();
    var match = function (a) { return !q || [a.id, a.ctl, a.to, a.attn, a.dest, a.plate, a.so].concat(a.lines.map(function (l) { return l.p; })).join(' ').toLowerCase().indexOf(q) >= 0; };
    var list = M.filter(function (a) { return (S.tab === 'all' || a.st === S.tab) && match(a); }).sort(function (x, y) { return y.id.localeCompare(x.id); });
    var tabs = TABS.map(function (t) { return '<button class="tab" role="tab" data-a="tab" data-v="' + t[0] + '" aria-selected="' + (S.tab === t[0]) + '">' + t[1] + '<span class="n">' + (t[0] === 'all' ? M.length : M.filter(function (a) { return a.st === t[0]; }).length) + '</span></button>'; }).join('');
    var hint = { draft: 'Continue editing, preview or submit your drafts.', pending: 'Check where each submitted ATL is in the review.', approved: 'Open an approved ATL to see its allocation status and the documents you can view.', declined: 'Read why an ATL was declined. Declined ATLs stay in your records.', all: 'Search and review every ATL you created.' }[S.tab];
    return P.head('ATL Records', hint) + '<div class="tabs" role="tablist">' + tabs + '</div>\
  <div style="max-width:420px;margin-bottom:12px" class="fld"><label for="q">Search ' + (S.tab === 'all' ? 'all records' : 'this tab') + '</label><input type="search" id="q" placeholder="ATL no., customer, plate, product or Sales Order" value="' + esc(S.q) + '"></div>\
  <section class="card"><div class="scroll"><table><thead><tr><th>ATL</th><th>Customer and destination</th><th>Truck and driver</th><th>Products</th><th class="r">Liters</th><th>Status</th><th>Actions</th></tr></thead><tbody>' +
      (list.length ? list.map(function (a) {
        return '<tr><td><button class="idlink" data-a="detail" data-id="' + a.id + '">' + a.id.replace('ATL-2026-', 'ATL ') + '</button><div class="small muted mono">' + esc(a.ctl) + '</div><div class="small muted">' + fmtD(a.date) + '</div></td>\
  <td class="wrap">' + esc(a.to || 'Not set') + '<div class="small muted">' + esc(a.dest || '') + '</div></td>\
  <td>' + (a.plate ? '<span class="mono">' + esc(a.plate) + '</span><div class="small muted">' + esc(drvName(a) || 'No driver') + (a.mode === 'sub' ? ' (substitute)' : '') + '</div>' : '<span class="muted">Not set</span>') + '</td>\
  <td>' + (L.products(a).join(', ') || '<span class="muted">None</span>') + '</td><td class="r num">' + (total(a) ? fmtL(total(a)) : '-') + '</td>\
  <td><div style="display:flex;flex-direction:column;gap:4px;align-items:flex-start">' + statusPill(a) + (a.sync !== 'synced' && a.st === 'draft' ? syncPill(a) : '') + '</div></td><td><div class="acts">' + rowActions(a) + '</div></td></tr>';
      }).join('') : '<tr><td colspan="7" class="empty">' + (q ? 'No records match your search.' : 'No ATLs in this tab.') + '</td></tr>') + '</tbody></table></div></section>';
  }

  /* ---------- create form ---------- */
  var fi = function (id, label, val, b, o) { o = o || {}; return '<div class="fld ' + (o.wide ? 'wide' : '') + '"><label for="' + id + '">' + label + (o.req ? ' <span class="req">*</span>' : '') + '</label><input type="' + (o.type || 'text') + '" id="' + id + '" data-b="' + b + '" value="' + esc(val) + '" ' + (o.ph ? 'placeholder="' + esc(o.ph) + '"' : '') + '></div>'; };
  function secErr(f, k) { if (!f.tried) return ''; var e = validate(f)[k]; return e && e.length ? '<ul class="errs">' + e.map(function (x) { return '<li>' + esc(x) + '</li>'; }).join('') + '</ul>' : ''; }
  function reconHTML(f) {
    var t = L.truck(f.plate), req = sum(f.lines.map(function (l) { return l.l; })), c = L.cap(t), pct = c ? Math.min(100, Math.round(req / c * 100)) : 0;
    return '<div class="capbar"><div class="capline"><span>Requested <b class="num">' + fmtL(req) + '</b> of capacity <b class="num">' + (t ? fmtL(c) : '-') + '</b></span><span class="muted">' + (t && req > c ? 'Over capacity' : t ? fmtL(c - req) + ' free' : 'Select a truck') + '</span></div><div class="bar ' + (t && req > c ? 'over' : '') + '"><i style="width:' + pct + '%"></i></div></div>';
  }
  function navHTML(f) {
    var e = validate(f), done = ['info', 'cust', 'truck', 'prod'].filter(function (k) { return !e[k].length; }).length;
    return SECS.map(function (s, i) { var k = s[0], st = k === 'act' || k === 'attach' ? '' : (!e[k].length ? 'ok' : (f.tried ? 'bad' : '')); return '<button data-a="jump" data-v="' + k + '"><span class="dot ' + st + '">' + (st === 'ok' ? '&#10003;' : '') + '</span>' + (i + 1) + '. ' + s[1] + '</button>'; }).join('') + '<div class="prog">' + done + ' of 4 required sections ready</div>';
  }
  function statusLine(f) { var n = nIssues(validate(f)); return n ? '<b>' + n + '</b> item' + (n > 1 ? 's' : '') + ' to complete before you can submit' : '<b>Ready to submit</b>'; }
  function errsumHTML(f) {
    var e = validate(f), n = nIssues(e);
    return f.tried && n ? '<div class="errsum" id="errsum" role="alert"><h3>' + n + ' item' + (n > 1 ? 's' : '') + ' to fix before submitting</h3><ul>' + SECS.filter(function (s) { return e[s[0]] && e[s[0]].length; }).map(function (s) { return '<li><button data-a="jump" data-v="' + s[0] + '">' + s[1] + '</button>: ' + e[s[0]].length + ' item' + (e[s[0]].length > 1 ? 's' : '') + '</li>'; }).join('') + '</ul></div>' : '';
  }
  function vCreate() {
    var f = S.f; if (!f) return '';
    var t = L.truck(f.plate), subs = subsFor(t);
    var prodSel = function (sel, idAttr, extra) { return '<select id="' + idAttr + '" ' + extra + '><option value="">Select</option>' + PRODUCTS.map(function (p) { return '<option ' + (p === sel ? 'selected' : '') + '>' + p + '</option>'; }).join('') + '</select>'; };
    var lineRows = f.lines.map(function (l, i) { return '<div class="rowf prod"><div class="fld"><label for="ln_p_' + i + '">Product</label>' + prodSel(l.p, 'ln_p_' + i, 'data-ln="' + i + '" data-k="p" data-struct="1"') + '</div><div class="fld"><label for="ln_l_' + i + '">Requested liters</label><input type="number" id="ln_l_' + i + '" min="0" step="100" inputmode="numeric" data-ln="' + i + '" data-k="l" value="' + l.l + '"></div><button class="iconbtn" data-a="delline" data-i="' + i + '" aria-label="Remove product line ' + (i + 1) + '" ' + (f.lines.length < 2 ? 'disabled' : '') + '>Remove</button></div>'; }).join('');
    var slots = '<div class="small muted">Attachments are optional. Add anything that supports this request, such as a Sales Order copy or an authorization letter.</div>' + (f.files.length ? '<div class="attlist">' + f.files.map(function (v, i) { return '<div class="slot has"><div class="nm">' + fileBtnA(v, 'data-src="form" data-i="' + i + '"') + '</div><div class="acts"><span class="small muted">' + esc(v.size) + '</span><button class="iconbtn" data-a="rmfile" data-i="' + i + '" aria-label="Remove ' + esc(v.name) + '">Remove</button></div></div>'; }).join('') + '</div>' : '<div class="small muted">No files added yet.</div>') + '<div class="actions"><label class="btn sm fbtn">' + ic('plus') + 'Add files<input type="file" id="file_add" data-file="add" multiple accept=".pdf,.jpg,.jpeg,.png"></label><span class="small muted">PDF, JPG or PNG. Click a file name to preview it.</span></div>';
    var cb = 'style="padding:16px;display:flex;flex-direction:column;gap:12px"';
    return P.head(f.origin === 'new' ? 'Create ATL' : 'Edit ATL draft', 'Complete each section. Sections turn green when they are ready to submit.') + '\
  <div class="formgrid"><nav class="secnav" id="secnav" aria-label="Form sections">' + navHTML(f) + '</nav>\
  <div class="fcol"><div id="errsumwrap">' + errsumHTML(f) + '</div>\
   <section class="card fsec" id="sec_info"><div class="card-h"><span class="no">1</span><h2>ATL information</h2><span class="small muted sp">Only the date is filled in automatically</span></div><div class="card-b" ' + cb + '><div class="fgrid">\
     <div class="fld"><label for="f_date">Date</label><input type="text" id="f_date" readonly value="' + fmtD(f.date) + '"></div>' +
      fi('f_no', 'ATL number', f.id, 'id', { req: 1, ph: 'ATL-2026-0000' }) + fi('f_ctl', 'Control number', f.ctl, 'ctl', { req: 1, ph: 'CTL-000000-000' }) + fi('f_so', 'Sales Order number', f.so, 'so', { req: 1, ph: 'SO-26-0000' }) + '</div><div id="err_info">' + secErr(f, 'info') + '</div></div></section>\
   <section class="card fsec" id="sec_cust"><div class="card-h"><span class="no">2</span><h2>Customer and destination</h2></div><div class="card-b" ' + cb + '><div class="fgrid">' +
      fi('f_attn', 'Attention', f.attn, 'attn', { req: 1, ph: 'PA Fuel 118' }) + fi('f_attnaddr', 'Depot address (Attention)', f.attnAddr, 'attnAddr', { req: 1, ph: 'Depot address' }) +
      fi('f_to', 'Customer', f.to, 'to', { req: 1, ph: 'Company buying the product' }) + fi('f_addr', 'Customer address', f.addr, 'addr', { req: 1, ph: 'Street, barangay, city' }) +
      fi('f_depot', 'Depot address', f.depot, 'depot', { req: 1, ph: 'Customer depot address' }) + fi('f_dest', 'Destination', f.dest, 'dest', { req: 1, ph: 'Town or city, province' }) +
      fi('f_thru', 'Thru', f.thru, 'thru', { ph: 'Hauler or agent, if any' }) + '</div><div id="err_cust">' + secErr(f, 'cust') + '</div></div></section>\
   <section class="card fsec" id="sec_truck"><div class="card-h"><span class="no">3</span><h2>Truck and driver</h2></div><div class="card-b" ' + cb + '><div class="fgrid">\
     <div class="fld"><label for="f_plate">Plate number <span class="req">*</span></label><select id="f_plate" data-b="plate" data-struct="truck"><option value="">Select a registered truck</option>' + D.trucks.map(function (x) { return '<option ' + (x.plate === f.plate ? 'selected' : '') + '>' + esc(x.plate) + '</option>'; }).join('') + '</select></div>\
     <div class="fld"><label for="f_h">Hauler</label><input type="text" id="f_h" readonly value="' + (t ? esc(L.hauler(t.h).name) : '') + '" placeholder="Filled from the truck"></div>\
     <div class="fld"><label for="f_cap">Capacity</label><input type="text" id="f_cap" readonly value="' + (t ? fmtL(L.cap(t)) + ' in ' + t.comps.length + ' compartments' : '') + '" placeholder="Filled from the truck"></div></div>' +
      (t ? '<div class="certs"><h4>Certificates</h4><ul>' + CERTS.map(function (c) { var exp = t.docs[c[0]].exp; return '<li><b>' + c[1] + ':</b><span>' + (exp ? 'valid until ' + fmtD(exp) : 'not set') + '</span>' + (exp ? certPill(exp) : pill('bad', 'Missing')) + '</li>'; }).join('') + '</ul></div>' : '') + '\
     <div class="fgrid"><div class="fld"><label for="f_drv">Driver</label><input type="text" id="f_drv" readonly value="' + (t ? esc(L.driver(t.d).name) : '') + '" placeholder="Filled from the truck"></div>' +
      (t ? '<div class="fld"><span class="lbl">&nbsp;</span><div class="actions" style="margin:0"><button type="button" class="btn sm" data-a="togglesub" ' + (!subs.length ? 'disabled' : '') + '>' + (f.mode === 'sub' ? 'Use default driver' : 'Substitute driver') + '</button></div>' + (!subs.length ? '<span class="hint">No authorized substitute is registered for this truck. Operations can add one.</span>' : '') + '</div>' : '') + '</div>' +
      (f.mode === 'sub' && t ? '<div class="fld" style="max-width:420px"><label for="f_sub">Substitute driver <span class="req">*</span></label><select id="f_sub" data-b="sub" data-struct="sub"><option value="">Select</option>' + subs.map(function (s) { return '<option value="' + s[0] + '" ' + (s[0] === f.sub ? 'selected' : '') + '>' + esc(s[1]) + '</option>'; }).join('') + '</select></div>' : '') +
      '<div id="err_truck">' + secErr(f, 'truck') + '</div></div></section>\
   <section class="card fsec" id="sec_prod"><div class="card-h"><span class="no">4</span><h2>Products and volume</h2><button class="btn sm sp" data-a="addline">' + ic('plus') + 'Add product</button></div><div class="card-b" ' + cb + '><div class="rows">' + lineRows + '</div><div id="capwrap">' + reconHTML(f) + '</div><div class="small muted">Operations assigns the compartments during review. You only request the products and liters.</div><div id="err_prod">' + secErr(f, 'prod') + '</div></div></section>\
   <section class="card fsec" id="sec_attach"><div class="card-h"><span class="no">5</span><h2>Attachments</h2><span class="pill sp nodot">Optional</span></div><div class="card-b" style="padding:16px;display:flex;flex-direction:column;gap:10px">' + slots + '</div></section>\
   <div class="actionbar" id="sec_act"><span class="st" id="stline">' + statusLine(f) + (online() ? '' : ' · <b>Offline</b>: saves stay on this device') + '</span><button class="btn" data-a="savedraft">' + ic('save') + 'Save Draft</button><button class="btn" data-a="previewform">' + ic('eye') + 'Preview ATL</button><button class="btn primary" data-a="submitform">' + ic('send') + 'Submit for Approval</button></div>\
  </div></div>';
  }
  function liveRefresh() {
    var f = S.f; if (!f || S.page !== 'create') return;
    var set = function (id, h) { var e = document.getElementById(id); if (e) e.innerHTML = h; };
    set('secnav', navHTML(f)); set('capwrap', reconHTML(f)); set('stline', statusLine(f) + (online() ? '' : ' · <b>Offline</b>: saves stay on this device'));
    if (f.tried) { ['info', 'cust', 'truck', 'prod', 'attach'].forEach(function (k) { set('err_' + k, secErr(f, k)); }); set('errsumwrap', errsumHTML(f)); }
  }

  /* ---------- paper, detail ---------- */
  function atlPaper(a, tag) {
    var t = L.truck(a.plate);
    return '<div class="paper"><div class="ph"><div><h3>Authority to Load</h3><span class="wm">' + tag + '</span></div><div class="pm">' + esc(a.id) + '<br>' + esc(a.ctl) + '<br>' + fmtD(a.date) + '</div></div>\
  <dl><div><dt>Sales Order no.</dt><dd>' + esc(a.so || '-') + '</dd></div><div><dt>Attention</dt><dd>' + esc(a.attn) + '</dd></div><div><dt>Depot address (Attention)</dt><dd>' + esc(a.attnAddr) + '</dd></div><div><dt>Customer</dt><dd>' + esc(a.to) + '</dd></div><div><dt>Customer address</dt><dd>' + esc(a.addr) + '</dd></div><div><dt>Depot address</dt><dd>' + esc(a.depot) + '</dd></div><div><dt>Thru</dt><dd>' + esc(a.thru || '-') + '</dd></div><div><dt>Destination</dt><dd>' + esc(a.dest) + '</dd></div><div><dt>Truck</dt><dd>' + esc(a.plate) + '</dd></div><div><dt>Driver</dt><dd>' + esc(drvName(a) || '-') + (a.mode === 'sub' ? ' (substitute)' : '') + '</dd></div><div><dt>Hauler</dt><dd>' + esc(t ? L.hauler(t.h).name : '-') + '</dd></div></dl>\
  <div class="scroll"><table><thead><tr><th>Product</th><th class="r">Requested L</th></tr></thead><tbody>' + a.lines.map(function (l) { return '<tr><td>' + esc(l.p || '-') + '</td><td class="r num">' + (l.l ? (+l.l).toLocaleString('en-US') : '-') + '</td></tr>'; }).join('') + '<tr><td><b>Total</b></td><td class="r num"><b>' + total(a).toLocaleString('en-US') + '</b></td></tr></tbody></table></div>\
  <div class="small" style="color:#444">Compartment allocation is assigned by Operations on approval.</div>\
  <div class="sig"><div>Prepared by (' + esc(ME) + ')</div><div>Approved by (Operations)</div></div></div>';
  }
  function docPaper(a, k) {
    var sig = { lt: ['Issued by (Operations)', 'Truck driver'], dr: ['Released by (Operations)', 'Received by driver'], wc: ['Authorized by (Operations)', 'Withdrawn by driver'] }[k];
    return '<div class="paper"><div class="ph"><div><h3>' + DOCN[k] + '</h3><span class="wm">View only copy</span></div><div class="pm">' + docOf(a, k)[1] + '<br>' + esc(a.id) + '<br>' + fmtD(a.date) + '</div></div>\
  <dl><div><dt>Truck</dt><dd>' + esc(a.plate) + '</dd></div><div><dt>Driver</dt><dd>' + esc(drvName(a)) + '</dd></div><div><dt>Customer</dt><dd>' + esc(a.to) + '</dd></div><div><dt>Destination</dt><dd>' + esc(a.dest) + '</dd></div></dl>\
  <div class="scroll"><table><thead><tr><th>Comp.</th><th>Product</th><th class="r">Liters</th></tr></thead><tbody>' + a.alloc.map(function (x) { return '<tr><td>C' + x.c + '</td><td>' + x.p + '</td><td class="r num">' + (+x.l).toLocaleString('en-US') + '</td></tr>'; }).join('') + '<tr><td></td><td><b>Total</b></td><td class="r num"><b>' + total(a).toLocaleString('en-US') + '</b></td></tr></tbody></table></div>\
  <div class="sig">' + sig.map(function (s) { return '<div>' + s + '</div>'; }).join('') + '</div></div>';
  }
  function detailBody(a) {
    var top = '';
    if (a.st === 'declined') top = '<div class="sec"><div class="callout bad"><b>Declined' + (a.by ? ' by ' + esc(a.by) : '') + (a.rt ? ' at ' + esc(a.rt) : '') + '</b><span>' + esc(a.reason) + '</span></div><div class="small muted">This ATL and its history stay in your records. Create a new ATL if you still need the load.</div></div>';
    if (a.st === 'pending' && a.sync === 'synced') { var r = a.review || 'queue'; top = '<div class="sec"><h3>Review status</h3><ol class="track"><li class="done"><b>Step 1</b>Submitted ' + esc(a.submitted || '') + '</li><li class="now"><b>Step 2</b>' + (r === 'inreview' ? 'In review with Operations' : 'Waiting in the review queue') + '</li><li><b>Step 3</b>Decision: approve or decline</li></ol></div>'; }
    if (a.sync !== 'synced') top = '<div class="sec"><div class="callout info"><b>' + (a.sync === 'local' ? 'Saved on this device only' : 'Submission queued, not sent') + '</b><span>' + (online() ? 'Use Sync now on the dashboard to send it.' : 'It will be sent when you are back online and sync.') + '</span></div></div>';
    var docs = a.st === 'approved' ? '<div class="sec"><h3>Documents</h3><div class="scroll"><table><thead><tr><th>Document</th><th>Number</th><th>Status</th><th></th></tr></thead><tbody>' + ['lt', 'dr', 'wc'].map(function (k) { return '<tr><td>' + DOCN[k] + '</td><td class="mono">' + docOf(a, k)[1] + '</td><td>' + pill.apply(null, DSTAT[docOf(a, k)[0]]) + '</td><td>' + (canView(a, k) ? '<button class="btn sm" data-a="viewdoc" data-id="' + a.id + '" data-k="' + k + '">' + ic('eye') + 'View</button>' : '<span class="small muted">Not available yet</span>') + '</td></tr>'; }).join('') + '</tbody></table></div></div>' : '';
    var t = L.truck(a.plate);
    return top + '<div class="sec"><dl class="kv"><div><dt>Control no.</dt><dd class="mono">' + esc(a.ctl) + '</dd></div><div><dt>Date</dt><dd>' + fmtD(a.date) + '</dd></div><div><dt>Sales Order no.</dt><dd class="mono">' + esc(a.so || '-') + '</dd></div><div><dt>Attention</dt><dd>' + esc(a.attn || '-') + '</dd></div><div><dt>Depot address (Attention)</dt><dd>' + esc(a.attnAddr || '-') + '</dd></div><div><dt>Customer</dt><dd>' + esc(a.to || '-') + '</dd></div><div><dt>Customer address</dt><dd>' + esc(a.addr || '-') + '</dd></div><div><dt>Depot address</dt><dd>' + esc(a.depot || '-') + '</dd></div><div><dt>Thru</dt><dd>' + esc(a.thru || '-') + '</dd></div><div><dt>Destination</dt><dd>' + esc(a.dest || '-') + '</dd></div><div><dt>Truck</dt><dd class="mono">' + esc(a.plate || '-') + '</dd></div><div><dt>Driver</dt><dd>' + esc(drvName(a) || '-') + (a.mode === 'sub' && a.sub ? ' (substitute)' : '') + '</dd></div><div><dt>Total</dt><dd class="num">' + (total(a) ? fmtL(total(a)) + ' of ' + fmtL(L.cap(t)) : '-') + '</dd></div></dl></div>\
  <div class="sec"><h3>Requested products</h3>' + (a.lines.some(function (l) { return l.p; }) ? '<div class="scroll"><table><thead><tr><th>Product</th><th class="r">Requested</th></tr></thead><tbody>' + a.lines.map(function (l) { return '<tr><td>' + esc(l.p || '-') + '</td><td class="r num">' + (l.l ? fmtL(l.l) : '-') + '</td></tr>'; }).join('') + '</tbody></table></div>' : '<div class="small muted">No products yet.</div>') + '</div>' +
      docs + (a.st === 'approved' && !a.alloc.length ? '<div class="sec"><h3>Compartment allocation</h3><div class="callout warn"><b>Waiting for Operations</b><span>Your ATL is approved. Operations assigns the products to the truck compartments next. Documents are prepared after that.</span></div></div>' : '') +
      (a.alloc.length ? '<div class="sec"><h3>Compartment allocation</h3><div class="small muted">Assigned by Operations.</div><div class="scroll"><table><thead><tr><th>Comp.</th><th>Product</th><th class="r">Liters</th><th>Remark</th></tr></thead><tbody>' + a.alloc.map(function (x) { return '<tr><td class="mono">C' + x.c + '</td><td>' + x.p + '</td><td class="r num">' + fmtL(x.l) + '</td><td>' + esc(x.rem) + (x.note ? '<div class="small muted">' + esc(x.note) + '</div>' : '') + '</td></tr>'; }).join('') + '</tbody></table></div></div>' : '') +
      '<div class="sec"><h3>Attachments</h3>' + (a.files.length ? '<div class="files">' + a.files.map(function (f, i) { return fileBtnA(f, 'data-src="rec" data-id="' + a.id + '" data-i="' + i + '"'); }).join('') + '</div>' : '<div class="small muted">None attached. Attachments are optional.</div>') + '</div>\
  <div class="sec"><h3>History</h3>' + (a.hist.length ? '<ul class="hist">' + a.hist.slice().reverse().map(function (h) { return '<li><time>' + esc(h.t) + '</time><span>' + esc(h.text) + '</span></li>'; }).join('') + '</ul>' : '<div class="small muted">No history yet.</div>') + '</div>';
  }
  function renderModal() {
    var m = S.modal, el = P.$('#px-modal', root); if (!m) { el.innerHTML = ''; return; }
    var title = '', body = '', foot = '';
    if (m.kind === 'detail') { var a = L.atl(m.id); title = '<span class="mono" style="font-size:17px">' + esc(a.id) + '</span>' + statusPill(a); body = detailBody(a); foot = '<button class="btn" data-a="mclose">Close</button>' + (a.st === 'draft' ? '<button class="btn" data-a="previewrec" data-id="' + a.id + '">' + ic('eye') + 'Preview</button><a class="btn primary" href="' + U.create + '?edit=' + encodeURIComponent(a.id) + '">' + ic('edit') + 'Continue editing</a>' : ''); }
    if (m.kind === 'preview') { title = 'Preview ATL'; body = atlPaper(m.rec, 'Preview. Not submitted'); foot = '<button class="btn" data-a="mclose">Close</button>'; }
    if (m.kind === 'doc') { var b = L.atl(m.id); title = DOCN[m.k]; body = docPaper(b, m.k); foot = '<button class="btn" data-a="mclose">Close</button>'; }
    el.innerHTML = '<div class="modal-bg" data-a="mbg"><div class="modal" role="dialog" aria-modal="true" aria-label="' + esc(title.replace(/<[^>]+>/g, '')) + '"><div class="modal-h"><h2>' + title + '</h2><button class="btn sm" data-a="mclose" aria-label="Close">Close</button></div>' + body + '<div class="modal-f">' + foot + '</div></div></div>';
  }

  /* ---------- actions ---------- */
  var ACT = {
    tab: function (e) { S.tab = e.v; },
    sync: function () { doSync(); },
    readall: function () { D.notes.forEach(function (n) { n.u = false; }); },
    detail: function (e) { S.modal = { kind: 'detail', id: e.id }; },
    previewrec: function (e) { S.modal = { kind: 'preview', rec: L.atl(e.id) }; },
    submitrec: function (e) {
      var f = clone(L.atl(e.id)); f.orig = f.id; f.origin = 'draft'; f.tried = false;
      if (nIssues(validate(f))) { P.toast('Complete the highlighted items before submitting', root); setTimeout(function () { location.href = U.create + '?edit=' + encodeURIComponent(f.id) + '&tried=1'; }, 700); return 'skip'; }
      doSubmit(f); return 'skip';
    },
    viewdoc: function (e) { S.modal = { kind: 'doc', id: e.id, k: e.k }; },
    mclose: function () { S.modal = null; },
    mbg: function (e, t, ev) { if (ev.target !== t) return 'skip'; S.modal = null; },
    jump: function (e) { var el = document.getElementById(e.v === 'act' ? 'sec_act' : 'sec_' + e.v); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' }); return 'skip'; },
    addline: function () { S.f.lines.push({ p: '', l: '' }); },
    togglesub: function () { var f = S.f; f.mode = f.mode === 'sub' ? 'default' : 'sub'; f.sub = ''; },
    delline: function (e) { if (S.f.lines.length > 1) S.f.lines.splice(+e.i, 1); },
    rmfile: function (e) { S.f.files.splice(+e.i, 1); },
    pvfile: function (e) { var f = e.src === 'form' ? S.f.files[+e.i] : (L.atl(e.id) || { files: [] }).files[+e.i]; if (f) P.preview(f, root); return 'skip'; },
    pvclose: function () { P.preview(null, root); return 'skip'; },
    pvbg: function (e, t, ev) { if (ev.target === t) P.preview(null, root); return 'skip'; },
    savedraft: function () { doSave(); return 'skip'; },
    previewform: function () { S.modal = { kind: 'preview', rec: S.f }; },
    submitform: function () { doSubmit(); return 'skip'; }
  };
  function render() {
    P.keepFocus(function () {
      P.$('#px-main', root).innerHTML = { dash: vDash, records: vRecords, create: vCreate }[S.page]();
    });
    renderModal();
  }
  function run(name, ds, t, ev) {
    var f = ACT[name]; if (!f) return;
    var r = f(ds, t, ev); if (r === 'skip') return;
    P.store.save(D); render();
  }
  var bindVal = function (el) { return el.type === 'number' ? (el.value === '' ? '' : +el.value) : el.value; };
  function onField(ev) {
    var el = ev.target, f = S.f;
    if (el.id === 'q') { S.q = el.value; render(); return; }
    if (!f) return;
    if (el.dataset.file) { var list = Array.from(el.files || []); Promise.all(list.map(P.mkFile)).then(function (os) { os.forEach(function (o) { f.files.push(o); }); render(); }); el.value = ''; return; }
    var struct = false;
    if (el.dataset.b) {
      var k = el.dataset.b, v = bindVal(el);
      if (k === 'plate') { f.plate = v; f.mode = 'default'; f.sub = ''; struct = true; }
      else if (k === 'sub') { f.sub = v; }
      else f[k] = v;
    } else if (el.dataset.ln !== undefined) { var l = f.lines[+el.dataset.ln], kk = el.dataset.k; l[kk] = bindVal(el); struct = kk === 'p'; }
    else return;
    if (struct) render(); else liveRefresh();
  }

  window.PXAtl = {
    mount: function (cfg) {
      root = document.getElementById('px'); U = cfg.urls;
      D = P.store.load(cfg.seed); L = P.lookups(D); ME = D.atlUser || 'A. Mendoza';
      var q = P.query();
      S = { page: cfg.page, tab: TABS.some(function (t) { return t[0] === q.tab; }) ? q.tab : 'draft', q: '', modal: null, f: null };
      if (S.page === 'create') {
        var rec = q.edit ? L.atl(q.edit) : null;
        if (rec && rec.creator === ME && rec.st === 'draft') { S.f = clone(rec); S.f.orig = rec.id; S.f.origin = 'draft'; S.f.tried = q.tried === '1'; }
        else S.f = blank();
      }
      root.className += ' atl';
      root.innerHTML = P.frame();
      P.wire(root, {
        run: run,
        escape: function () { if (P.previewOpen()) P.preview(null, root); else if (S.modal) { S.modal = null; renderModal(); } },
        input: function (ev) { if (ev.target.matches('input[type=text],input[type=number],input[type=search],textarea')) onField(ev); },
        change: function (ev) { if (ev.target.matches('select,input[type=radio],input[type=file]')) onField(ev); }
      });
      window.addEventListener('online', function () { if (S.page === 'dash') render(); else liveRefresh(); });
      window.addEventListener('offline', function () { if (S.page === 'dash') render(); else liveRefresh(); });
      render();
      if (D.__flash) { P.toast(D.__flash, root); delete D.__flash; P.store.save(D); }
    }
  };
})();
