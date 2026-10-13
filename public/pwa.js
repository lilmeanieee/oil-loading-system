/* Offline support for PA Fuel 118.
   1. Registers the service worker and saves the pages this user can open.
   2. Shows an offline / waiting-to-sync indicator.
   3. Forms marked data-offline="queue" are saved on this device when there is no connection
      and sent automatically when it returns. data-offline="online" forms need a connection. */
(function () {
  'use strict';

  var DB_NAME = 'oil-loading-pwa';
  var STORE = 'queue';
  var syncing = false;
  var toastTimer = null;
  var installEvent = null;

  function $(id) { return document.getElementById(id); }
  function uid() { return String(Date.now()) + '-' + Math.random().toString(36).slice(2, 8); }

  /* ---------- queue stored in IndexedDB ---------- */
  function openDb() {
    return new Promise(function (resolve, reject) {
      var req = indexedDB.open(DB_NAME, 1);
      req.onupgradeneeded = function () { req.result.createObjectStore(STORE, { keyPath: 'id' }); };
      req.onsuccess = function () { resolve(req.result); };
      req.onerror = function () { reject(req.error); };
    });
  }
  function run(mode, make) {
    return openDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var t = db.transaction(STORE, mode);
        var r = make(t.objectStore(STORE));
        t.oncomplete = function () { resolve(r ? r.result : undefined); };
        t.onerror = t.onabort = function () { reject(t.error); };
      });
    });
  }
  function allItems() { return run('readonly', function (s) { return s.getAll(); }).then(function (r) { return (r || []).sort(function (a, b) { return a.createdAt - b.createdAt; }); }); }
  function saveItem(item) { return run('readwrite', function (s) { return s.put(item); }); }
  function removeItem(id) { return run('readwrite', function (s) { return s.delete(id); }); }

  /* ---------- helpers ---------- */
  function toast(message) {
    var el = $('pwa-toast');
    if (!el) return;
    el.textContent = message;
    el.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.hidden = true; }, 6000);
  }

  // navigator.onLine can say "online" with no internet, so ask the server.
  function reachable() {
    if (!navigator.onLine) return Promise.resolve(false);
    var ctrl = new AbortController();
    var timer = setTimeout(function () { ctrl.abort(); }, 3000);
    return fetch('/pwa/ping', { cache: 'no-store', signal: ctrl.signal })
      .then(function (r) { clearTimeout(timer); return r.ok; })
      .catch(function () { clearTimeout(timer); return false; });
  }

  function csrf() {
    return fetch('/pwa/csrf', { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } })
      .then(function (r) { if (!r.ok || r.redirected) return null; return r.json(); })
      .then(function (j) { return j && j.token ? j.token : null; })
      .catch(function () { return null; });
  }

  function worker() {
    if (!('serviceWorker' in navigator)) return Promise.resolve(null);
    return navigator.serviceWorker.ready.then(function (reg) { return reg.active; }).catch(function () { return null; });
  }

  function clearPages() { worker().then(function (sw) { if (sw) sw.postMessage({ type: 'CLEAR_PAGES' }); }); }

  /* ---------- service worker and page saving ---------- */
  function precache() {
    if (!navigator.onLine || document.body.dataset.pwaAuth !== '1') return;
    fetch('/pwa/urls', { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } })
      .then(function (r) { if (!r.ok || r.redirected) return null; return r.json(); })
      .then(function (data) {
        if (!data) return;
        var assets = [].slice.call(document.querySelectorAll('link[rel="stylesheet"][href], script[src]'))
          .map(function (n) { return n.href || n.src; })
          .filter(function (u) { return u.indexOf('/pwa/') === -1; });
        return worker().then(function (sw) { if (sw) sw.postMessage({ type: 'PRECACHE', pages: data.pages || [], assets: assets }); });
      })
      .catch(function () {});
  }

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').then(function () { return navigator.serviceWorker.ready; }).then(precache).catch(function () {});
    });
  }

  /* ---------- on-screen status ---------- */
  function render() {
    return allItems().then(function (items) {
      var pill = $('pwa-pill');
      if (!pill) return;
      var offline = !navigator.onLine;
      var text = $('pwa-pill-text');
      pill.hidden = !(offline || items.length);
      if (offline) text.textContent = items.length ? 'Offline: ' + items.length + ' waiting to sync' : 'Offline: your work is saved on this device';
      else text.textContent = items.length + ' waiting to sync';

      var list = $('pwa-list');
      var tpl = $('pwa-item-tpl');
      list.innerHTML = '';
      items.forEach(function (it) {
        var li = tpl.content.firstElementChild.cloneNode(true);
        li.querySelector('[data-label]').textContent = it.label;
        li.querySelector('[data-meta]').textContent = 'Saved ' + new Date(it.createdAt).toLocaleString();
        var err = li.querySelector('[data-error]');
        var retry = li.querySelector('[data-retry]');
        if (it.state === 'failed') {
          err.textContent = it.message || 'This could not be sent.';
          err.hidden = false;
          retry.hidden = false;
          retry.addEventListener('click', function () { it.state = 'pending'; it.message = ''; saveItem(it).then(render).then(sync); });
        }
        li.querySelector('[data-remove]').addEventListener('click', function () {
          if (confirm('Remove this saved item? It will not be sent.')) removeItem(it.id).then(render);
        });
        list.appendChild(li);
      });
      [].slice.call(document.querySelectorAll('[data-pwa-queue-count]')).forEach(function (n) { n.textContent = items.length; });
      var last = null;
      try { last = localStorage.getItem('pwaLastSync'); } catch (e) {}
      [].slice.call(document.querySelectorAll('[data-pwa-last-sync]')).forEach(function (n) { n.textContent = last ? new Date(Number(last)).toLocaleString() : 'not yet'; });
      $('pwa-panel-note').textContent = items.length
        ? 'These were saved on this device and will be sent when you are online.'
        : 'Nothing is waiting.';
    });
  }

  /* ---------- saving forms while offline ---------- */
  function enqueue(form, submitter) {
    var fields = [];
    new FormData(form).forEach(function (value, key) { if (key !== '_token') fields.push([key, value]); });
    if (submitter && submitter.name) fields.push([submitter.name, submitter.value]);
    var refField = form.dataset.offlineRef ? form.elements[form.dataset.offlineRef] : null;
    var ref = refField && refField.value ? ' ' + refField.value : '';
    var kind = submitter && submitter.value ? ' (' + submitter.value + ')' : '';
    var item = {
      id: uid(),
      url: form.getAttribute('action') || location.href,
      fields: fields,
      label: (form.dataset.offlineLabel || document.title) + ref + kind,
      createdAt: Date.now(),
      state: 'pending',
      message: ''
    };
    return saveItem(item).then(function () {
      try { sessionStorage.setItem('pwaToast', 'Saved on this device. It will be sent when the connection returns.'); } catch (e) {}
      location.href = document.body.dataset.pwaHome || '/dashboard';
    });
  }

  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    if (!form || !form.dataset || !form.dataset.offline) return;
    if (form.dataset.pwaBypass) { delete form.dataset.pwaBypass; return; }
    var submitter = ev.submitter || null;
    ev.preventDefault();
    reachable().then(function (ok) {
      if (ok) {
        if (form.dataset.pwaClear !== undefined) clearPages();
        form.dataset.pwaBypass = '1';
        if (form.requestSubmit) form.requestSubmit(submitter || undefined); else form.submit();
        return;
      }
      if (form.dataset.offline === 'queue') return enqueue(form, submitter);
      toast('This needs an internet connection. Try again when you are back online.');
    });
  }, true);

  /* ---------- sending saved items when the connection returns ---------- */
  function send(item, token) {
    var body = new FormData();
    item.fields.forEach(function (pair) { body.append(pair[0], pair[1]); });
    body.append('_token', token);
    return fetch(item.url, {
      method: 'POST', body: body, credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token }
    });
  }

  function firstError(json) {
    if (json && json.errors) { var k = Object.keys(json.errors)[0]; if (k) return json.errors[k][0]; }
    return (json && json.message) || 'The server did not accept this.';
  }

  function sync() {
    if (syncing) return Promise.resolve();
    syncing = true;
    var sent = 0;
    return allItems().then(function (items) {
      var pending = items.filter(function (i) { return i.state !== 'failed'; });
      if (!pending.length) return;
      return reachable().then(function (ok) {
        if (!ok) return;
        return csrf().then(function (token) {
          if (!token) { toast('Sign in again to finish sending your saved work.'); return; }
          var chain = Promise.resolve(true);
          pending.forEach(function (it) {
            chain = chain.then(function (go) {
              if (!go) return false;
              return send(it, token).then(function (res) {
                if (res.status === 419) return csrf().then(function (t2) { token = t2; return t2 ? send(it, t2) : res; });
                return res;
              }).then(function (res) {
                if (res.redirected && /\/login/.test(res.url)) { toast('Sign in again to finish sending your saved work.'); return false; }
                if (res.ok) { sent++; return removeItem(it.id).then(function () { return true; }); }
                if (res.status === 422 || res.status === 403 || res.status === 404) {
                  return res.json().catch(function () { return {}; }).then(function (j) {
                    it.state = 'failed';
                    it.message = res.status === 403 ? 'Your role is not allowed to do this.' : firstError(j);
                    return saveItem(it).then(function () { return true; });
                  });
                }
                return false; // server problem: try again later
              }).catch(function () { return false; });
            });
          });
          return chain;
        });
      });
    }).then(function () {
      syncing = false;
      if (sent) { try { localStorage.setItem('pwaLastSync', String(Date.now())); } catch (e) {} toast('Sent ' + sent + ' saved item' + (sent > 1 ? 's' : '') + '. Reload the page to see the latest records.'); precache(); }
      return render();
    }).catch(function () { syncing = false; });
  }

  /* ---------- start up ---------- */
  document.addEventListener('DOMContentLoaded', function () {
    var pill = $('pwa-pill');
    var panel = $('pwa-panel');
    if (pill) pill.addEventListener('click', function () {
      panel.hidden = !panel.hidden;
      pill.setAttribute('aria-expanded', String(!panel.hidden));
    });
    if ($('pwa-panel-close')) $('pwa-panel-close').addEventListener('click', function () { panel.hidden = true; pill.setAttribute('aria-expanded', 'false'); });
    if ($('pwa-sync-now')) $('pwa-sync-now').addEventListener('click', function () { sync(); });

    try {
      var saved = sessionStorage.getItem('pwaToast');
      if (saved) { sessionStorage.removeItem('pwaToast'); toast(saved); }
    } catch (e) {}

    render().then(sync);
  });

  window.addEventListener('online', function () { render(); sync(); });
  window.addEventListener('offline', function () { render(); });
  setInterval(function () { if (navigator.onLine) sync(); }, 30000);

  /* ---------- install button ---------- */
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    installEvent = e;
    var btn = $('pwa-install');
    if (btn) btn.hidden = false;
  });
  document.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('#pwa-install') : null;
    if (!btn || !installEvent) return;
    installEvent.prompt();
    installEvent.userChoice.then(function () { installEvent = null; btn.hidden = true; });
  });
  window.addEventListener('appinstalled', function () { var b = $('pwa-install'); if (b) b.hidden = true; });
})();
