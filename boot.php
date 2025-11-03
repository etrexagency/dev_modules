<?php

// Diese Datei ist keine Pflichdatei mehr.
// Die `boot.php` wird bei jeder Aktion in REDAXO ausgeführt (Frontend und Backend). Hier können beliebige Befehle ausgeführt werden.
// Dokumentation AddOn Aufbau und Struktur https://redaxo.org/doku/master/addon-struktur

rex_login::startSession();

$GLOBALS["frontend_config"] = [
    'activateAddOn'  => (bool) rex_config::get('dev_modules', 'activate'),
    'editPage'       => (bool) rex_config::get('dev_modules', 'edit-page'),
    'addingModules'  => (bool) rex_config::get('dev_modules', 'adding-modules'),
    'edit'           => (bool) rex_config::get('dev_modules', 'edit'),
    'status'         => (bool) rex_config::get('dev_modules', 'status'),
    'moveUp'         => (bool) rex_config::get('dev_modules', 'move-up'),
    'moveDown'       => (bool) rex_config::get('dev_modules', 'move-down'),
    'cut'            => (bool) rex_config::get('dev_modules', 'cut'),
    'copy'           => (bool) rex_config::get('dev_modules', 'copy'),
    'delete'         => (bool) rex_config::get('dev_modules', 'delete'),
];


// Zustände lesen (Default false)
$devKey = 'dev_modules.devMode';
$optKey = 'dev_modules.optionsMode';
$devMode     = rex_session($devKey, 'bool', false);
$optionsMode = rex_session($optKey, 'bool', false);

// GET-Parameter verarbeiten (0/1)
$devParam = rex_request('dev_mode', 'int', null);
$optParam = rex_request('options_mode', 'int', null);

// Exclusives Umschalten
if ($optParam !== null) {
    $optionsMode = (bool) $optParam;
    if ($optionsMode) $devMode = false;
    rex_set_session($optKey, $optionsMode);
    rex_set_session($devKey, $devMode);
}
if ($devParam !== null) {
    $devMode = (bool) $devParam;
    if ($devMode) $optionsMode = false;
    rex_set_session($devKey, $devMode);
    rex_set_session($optKey, $optionsMode);
}

$addon = rex_addon::get('dev_modules');

// Eigene PHP-Funktionen im Backend und Frontend einbinden
$addon->includeFile('functions/dev_modules_functions.php');
$addon->includeFile('functions/dev_modules_csrf.php');

// AddOn-Rechte (permissions) registieren
// Hinweis: In der `de_de.lang`-Datei sind Text-Einträge für das Backend vorhanden (z.B. perm_general_dev_modules[])
if (rex::isBackend() && is_object(rex::getUser())) {
    rex_perm::register('dev_modules[]');
    rex_perm::register('dev_modules[config]');
}

// Falls dev_modules=1 -> entsprechende Assets am richtigen Ort laden
if (rex::isBackend() && 'dev_modules' == rex_be_controller::getCurrentPagePart(1)) {
    rex_view::addCssFile($addon->getAssetsUrl('css/backend.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/backend.js'));
}

// Falls Frontend Options in den AddOn Einstellugnen aktiviert -> entsprechende Assets am richtigen Ort laden
if ($GLOBALS["frontend_config"]['activateAddOn'] && rex::isFrontend() && rex_backend_login::hasSession()) {
    rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) use ($addon) {
        $html = $ep->getSubject();

        // --- <head>-Assets injizieren (nur wenn <head> existiert)
        if (strpos($html, '</head>') !== false) {
            $injectHead = PHP_EOL
                . '<link rel="stylesheet" href="' . $addon->getAssetsUrl('css/frontend.css') . '">' . PHP_EOL
                . '<script src="' . $addon->getAssetsUrl('js/frontend.js') . '"></script>' . PHP_EOL;

            // optional: Deduplizieren, falls schon vorhanden
            if (strpos($html, $addon->getAssetsUrl('css/frontend.css')) === false) {
                $html = str_replace('</head>', $injectHead . '</head>', $html);
            }
        }

        // --- Buttons vor </body> einfügen (nur wenn </body> existiert)
        if (strpos($html, '</body>') !== false) {
            $articleId       = rex_article::getCurrentId();
            $buttonContainer = showDevModulesButtons($articleId);
            $html = str_replace('</body>', $buttonContainer . '</body>', $html);
        }

        // --- Buttons vor </body> einfügen (nur wenn </body> existiert)
        if (strpos($html, '<html') !== false) {
            $articleId       = rex_article::getCurrentId();
            $buttonContainer = showDevModulesButtons($articleId);
            $html = str_replace('<html', '<html data-dev', $html);
        }

        $ep->setSubject($html);
    });
}
