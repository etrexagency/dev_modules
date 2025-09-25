/*
dev_modules
Diese JavaScript-Datei könnte in der `boot.php` des AddOns `dev_modules` im Backend eingebunden werden (rex_view::addJsFile)
https://redaxo.org/doku/master/addon-assets
*/

// jQuery closure (»Funktionsabschluss«)
// Erzeugt einen Scope, also einen privaten Bereich
// http://molily.de/javascript-core/#closures
(function ($) {

    // rex:ready
    // Führt Code aus, sobald der DOM vollständig geladen wurde
    // https://redaxo.org/doku/master/addon-assets#rexready
    $(document).on('rex:ready', function (event, container) {
   

        // Message anzeigen
        function showMessage(type, msg) {
            const el = type === 'success' ?
                document.getElementById('responseSuccess') :
                document.getElementById('responseWarning');
            if (!el) return;
            el.textContent = msg; // keine innerHTML -> sicherer
            el.hidden = false; // hidden-Attribut entfernen

            // Message wieder ausblenden
            setTimeout(() => {
                el.hidden = true;
            }, 4500);
        }

        // Vor dem Neuladen die Message in die Session schreiben
        function messageAfterReload(type, msg) {
            sessionStorage.setItem('devmode_flash', JSON.stringify({
                type,
                msg,
                ts: Date.now()
            }));
            location.reload();
        }

            // Message anzeigen wenn vorhanden
            const raw = sessionStorage.getItem('devmode_flash');
            if (raw) {
                try {
                    const f = JSON.parse(raw);
                    if (f && f.msg) showMessage(f.type, f.msg);
                } catch (_) {}
                sessionStorage.removeItem('devmode_flash');
            }

            // Button Klick auf erstellen
            document.querySelectorAll('.js-create').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    let id = this.dataset.id;
                    fetch(currentBasePath + '&func=createDevModule&module_id=' + id, {
                            credentials: 'same-origin'
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                messageAfterReload('success', data.message);
                            } else {
                                showMessage('warning', data.message);
                            }
                        });
                });
            });

            // Button Klick auf löschen
            document.querySelectorAll('.js-clear').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    let id = this.dataset.id;
                    let dev_id = this.dataset.dev;
                    if (window.confirm("DEV Modul ("+dev_id+") wirklich löschen?")) {
                        fetch(currentBasePath + '&func=deleteDevModule&module_id=' + id + '&dev_module_id=' + dev_id, {
                            credentials: 'same-origin'
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                messageAfterReload('success', data.message);
                            } else {
                                showMessage('warning', data.message);
                            }
                        });
                    }
                });
            });

            // Button Klick auf überschreiben
            document.querySelectorAll('.js-overwrite').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    let id = this.dataset.id;
                    let dev_id = this.dataset.dev;
                    if (window.confirm("LIVE Modul ("+id+") wirklich mit DEV Modul ("+dev_id+") überschreiben?")) {
                        fetch(currentBasePath + '&func=overwriteModule&module_id=' + id + '&dev_module_id=' + dev_id, {
                            credentials: 'same-origin'
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                messageAfterReload('success', data.message);
                            } else {
                                showMessage('warning', data.message);
                            }
                        });
                    }
                });
            });
    
    });
    
})(jQuery);
