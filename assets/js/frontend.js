
function addModuleToCurrentPosition (slice_add_pos, sliceId, clang, articleId) {
    const dropdown = document.getElementById("moduleDropdown");
    const ctype = 1;

    dropdown.addEventListener("change", function() {
        console.log(this.value);
        window.open("/redaxo/index.php?page=content/edit&article_id=" + articleId + "&clang=" + clang + "&ctype=" + ctype + "&slice_id=" + sliceId + "&function=add&module_id=" + this.value + "#slice-add-pos-" + slice_add_pos + "", "_self")
    });
 }

// nur aktivieren, wenn <html data-dev> vorhanden ist
if (document.documentElement.hasAttribute('data-dev') && !window.__visReloadBound) {
  window.__visReloadBound = true;

  let leftAt = 0;              // Zeit merken, wann Tab verlassen wurde
  const THRESHOLD = 0;         // Sek.; 0 = immer reloaden

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      leftAt = Date.now();     // Tab verlassen
    } else {
      const awaySec = (Date.now() - leftAt) / 1000;
      if (leftAt && awaySec >= THRESHOLD && document.documentElement.hasAttribute('data-options-mode')) {
        location.reload();
      }
      leftAt = 0;
    }
  });

  // Zurück aus bfcache -> reload
  window.addEventListener('pageshow', (e) => {
    if (e.persisted && document.documentElement.hasAttribute('data-options-mode')) {
      location.reload();
    }
  });
}

(() => {
  const API_URL = '/redaxo/index.php';

  if (window.__devModulesBloecksBound) return;
  window.__devModulesBloecksBound = true;

  const post = (params) => {
    const body = new URLSearchParams(params).toString();
    return fetch(API_URL, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
      body
    }).then(r => r.json()); // Bloecks API liefert JSON
  };

  document.addEventListener('click', (e) => {
    const a = e.target.closest('.btn-bloecks-copy, .btn-bloecks-cut, .btn-bloecks-paste');
    if (!a) return;

    e.preventDefault();
    e.stopPropagation();

    const action =
      a.classList.contains('btn-bloecks-copy') ? 'copy' :
      a.classList.contains('btn-bloecks-cut')  ? 'cut'  :
      'paste';

    const params = {
      'rex-api-call': 'bloecks',
      'function': action,
      slice_id:       a.dataset.sliceId   || '',
      article_id:     a.dataset.articleId || '',
      clang:          a.dataset.clangId   || '',
      ctype:          a.dataset.ctypeId   || '1',
      bloecks_target: a.dataset.targetSlice || '',
      paste_position: a.dataset.pastePosition || 'after'
    };

    // Neues Tab für CUT vorbereiten (Popup-Blocker umgehen)
    const link = a.dataset.link || '';
    let pendingWin = null;
  if (action && link) {
      pendingWin = window.open('about:blank', '_blank');
      if (pendingWin) pendingWin.opener = null;
    }

    const oldHTML = a.innerHTML;
    a.innerHTML = '<span class="rex-loader"></span>';
    a.classList.add('is-loading');

    post(params).then(res => {
      a.classList.remove('is-loading');
      a.innerHTML = oldHTML;

      if (!res || res.success === false) {
        if (pendingWin && !pendingWin.closed) pendingWin.close();
        alert(res?.message || 'Aktion fehlgeschlagen.');
        return;
      }

      if (res.new_slice_id) {
        sessionStorage.setItem('bloecks_scroll_target', String(res.new_slice_id));
      } else if (action) {
        sessionStorage.setItem('bloecks_scroll_position', String(window.pageYOffset || 0));
      }

      // Nach erfolgreichem CUT: neues Tab navigieren
      if (action && link) {
        if (pendingWin) pendingWin.location.href = link;
        else window.open(link, '_blank'); // Fallback
      }

      // Reload damit der Backend-State sichtbar wird
      location.reload();
    }).catch(() => {
      a.classList.remove('is-loading');
      a.innerHTML = oldHTML;
      if (pendingWin && !pendingWin.closed) pendingWin.close();
      alert('Netzwerkfehler bei der Aktion.');
    });
  });
})();

(() => {
  if (window.__devMoveStatusBound) return;
  window.__devMoveStatusBound = true;

  const API = '/redaxo/index.php?rex-api-call=dev_modules_csrf';
  let TOKENS = null;

  function getTokens() {
    if (TOKENS) return Promise.resolve(TOKENS);
    return fetch(API, { credentials: 'same-origin' })
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(j => {
        if (!j || !j.success) throw new Error('csrf fetch failed');
        TOKENS = j.tokens;
        return TOKENS;
      });
  }

  document.addEventListener('click', async (e) => {
    const a = e.target.closest('a[data-dev-action]');
    if (!a) return;

    // Wir übernehmen das Öffnen des Tabs
    e.preventDefault();
    e.stopPropagation();

    try {
      const t = await getTokens();
      const url = new URL(a.getAttribute('href'), location.origin);
      const action = a.dataset.devAction;

      if (action === 'move') {
        url.searchParams.set('_csrf_token', t.content_move_slice);
      } else if (action === 'status') {
        url.searchParams.set('_csrf_token', t.content_slice_status);
      }

      // optional: kleiner Toast vor dem Öffnen
      // showToast(`${action === 'move' ? 'Verschiebe' : 'Schalte Status um'}…`);

      window.open(url.toString(), '_blank', 'noopener'); // neuer Tab
    } catch (err) {
      alert('CSRF-Token konnte nicht geladen werden (eingeloggt?).');
    }
  });
})();

