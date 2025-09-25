
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
      if (leftAt && awaySec >= THRESHOLD) {
        location.reload();
      }
      leftAt = 0;
    }
  });

  // Zurück aus bfcache -> reload
  window.addEventListener('pageshow', (e) => {
    if (e.persisted) {
      location.reload();
    }
  });
}

