
<?php
session_start();


// Frontend Konfiguration auslesen

/**
 * Function to delete entry in mapping table and the DEV module
 */
if (!function_exists('deleteEntryInMappingTable')) {

    function deleteEntryInMappingTable($moduleId, $devModuleId)
    {
        // Eintrag in rex_dev_modules löschen
        $sql = rex_sql::factory();
        $sql->setTable(rex::getTable('dev_modules'));
        $sql->setWhere(['module_id' => $moduleId]);
        $sql->delete();

        // DEV-Modul in rex_module löschen
        $sql2 = rex_sql::factory();
        $sql2->setTable(rex::getTable('module'));
        $sql2->setWhere(['id' => $devModuleId]);
        $sql2->delete();

        return true;
    }
}

/**
 * Checks if module somewhere on a page.
 */
if (!function_exists('checkIfModuleInUsage')) {
    function checkIfModuleInUsage(int $id): bool
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT 1 FROM ' . rex::getTable('article_slice') . ' WHERE module_id = ? LIMIT 1',
            [$id]
        );
        return $sql->getRows() > 0;
    }
}

/**
 * Returns path to module editor based on ID.
 */
if (!function_exists('moduleEditUrl')) {
    function moduleEditUrl(int $id): string
    {
        return rex_url::backendPage('modules/modules', [
            'start'     => 0,
            'function'  => 'edit',
            'module_id' => $id,
        ]);
    }
}


/**
 * Simple REX_VAR replacer for common tokens in module OUTPUT.
 * Very minimal: add more cases if needed (e.g. output=html, escapes, etc.).
 */

if (!function_exists('apply_simple_rex_vars')) {
    function apply_simple_rex_vars(string $code, rex_article_slice $slice): string
    {
        // Optional: kommentierte REX-Tags freilegen:  /* REX_XYZ[...] */ -> REX_XYZ[...]
        $code = preg_replace('/\/\*\s*(REX_(?:VALUE|MEDIA|MEDIALIST|LINK|LINKLIST|URL)\s*\[[^\]]+\])\s*\*\//i', '$1', $code);

        // Helper zum Holen eines Wertes + optionaler Output-Variante
        $get = function (string $tag, int $i, ?string $out = null): string {
            switch (strtoupper($tag)) {
                case 'REX_VALUE':
                    return (string) $GLOBALS['__slice__']->getValue($i);
                case 'REX_MEDIA':
                    if ($out === 'url') return (string) $GLOBALS['__slice__']->getMediaUrl($i) ?? '';
                    return (string) $GLOBALS['__slice__']->getMedia($i) ?? '';
                case 'REX_MEDIALIST':
                    return (string) $GLOBALS['__slice__']->getMediaList($i) ?? '';
                case 'REX_LINK':
                    if ($out === 'url') return (string) $GLOBALS['__slice__']->getLinkUrl($i) ?? '';
                    $id = $GLOBALS['__slice__']->getLink($i);
                    return null === $id ? '' : (string) $id;
                case 'REX_LINKLIST':
                    return (string) $GLOBALS['__slice__']->getLinkList($i) ?? '';
                case 'REX_URL':
                    $id = $GLOBALS['__slice__']->getLink($i);
                    return null === $id ? '' : (string) rex_getUrl($id);
            }
            return '';
        };

        // Gequotet: 'REX_XYZ[id=1 output=url]' | "REX_XYZ[1]"
        $code = preg_replace_callback(
            '/([\'"])\s*(REX_(?:VALUE|MEDIA|MEDIALIST|LINK|LINKLIST|URL))\s*\[\s*(?:id\s*=)?\s*(\d+)\s*(?:\s+output\s*=\s*(url))?\s*\]\s*\1/i',
            function ($m) use ($get) {
                [$all, $q, $tag, $idx, $out] = $m + [null, null, null, null, null];
                $val = $get($tag, (int)$idx, $out ?: null);
                $escaped = str_replace($q, '\\' . $q, $val);
                return $q . $escaped . $q;
            },
            $code
        );

        // Ungequotet: REX_XYZ[id=1 output=url] | REX_XYZ[1]
        $code = preg_replace_callback(
            '/\b(REX_(?:VALUE|MEDIA|MEDIALIST|LINK|LINKLIST|URL))\s*\[\s*(?:id\s*=)?\s*(\d+)\s*(?:\s+output\s*=\s*(url))?\s*\]/i',
            function ($m) use ($get) {
                $tag = $m[1];
                $idx = (int)$m[2];
                $out = $m[3] ?? null;
                $val = $get($tag, $idx, $out);
                $escaped = str_replace(['\\', '\''], ['\\\\', '\\\''], $val);
                return '\'' . $escaped . '\'';
            },
            $code
        );

        return $code;
    }
}

/**
 * Render a slice with a specific module id (can be the original or a replacement).
 * NOTE: This fetches module OUTPUT code and evals it. You lose article caching.
 */


if (!function_exists('render_slice_with_module')) {
    function render_slice_with_module(rex_article_slice $slice, int $moduleId, bool $useSimpleParser = true): string
    {

        // Load module row
        $sql = rex_sql::factory();
        $row = $sql->setQuery('SELECT id, output FROM ' . rex::getTable('module') . ' WHERE id = ' . $moduleId . ' LIMIT 1')->getRow();

        if ($sql->getRows() !== 1) {
            return '// module ' . $moduleId . ' not found';
        }

        // Use getValue() instead of array access on getRow()
        $outputCode = (string) $sql->getValue('output');

        if ($outputCode === '') {
            return '// module ' . $moduleId . ' has empty output';
        }

        // Optionally replace a few common REX_VARs so typical modules still work
        if ($useSimpleParser) {
            $GLOBALS['__slice__'] = $slice;
            $outputCode = apply_simple_rex_vars($outputCode, $slice);
            unset($GLOBALS['__slice__']);
        }

        // Make a few useful things available to the module code scope
        // $slice -> the current rex_article_slice
        // $articleId, $clangId -> context ids
        $articleId = $slice->getArticleId();
        $clangId   = $slice->getClangId();

        // If modules expect helpers, add/prepare them here.

        ob_start();
        try {
            // Evaluate as PHP template
            eval('?>' . $outputCode);
        } catch (Throwable $e) {
            // Don’t break the page; show a comment instead
            ob_end_clean();
            return '// render error in module ' . $moduleId . ': ' . htmlspecialchars($e->getMessage()) . '';
        }
        return (string) ob_get_clean();
    }
}

/**
 * Decide which module id to render for a given slice based on the mapping.
 */

if (!function_exists('resolve_target_module_id')) {
    function resolve_target_module_id(rex_article_slice $slice, array $replacementMap): int
    {

        $orig = (int) $slice->getModuleId();
        return isset($replacementMap[$orig]) ? (int) $replacementMap[$orig] : $orig;
    }
}

/**
 * Returns content of article with replaced dev modules.
 */
if (!function_exists('getChangedArticleContent')) {
    function getChangedArticleContent($articleId)
    {
        $clangId = rex_clang::getCurrentId();
        $devModules = rex_sql::factory()->getArray(
            'SELECT module_id, dev_module_id FROM ' . rex::getTable('dev_modules')
        );
        $replacementMap = array_column($devModules, 'dev_module_id', 'module_id'); // [live => dev]

        /**
         * Configure your module replacement map here (or inject it).
         * Key = original module_id, Value = replacement module_id.
         * Example: module 2 should be rendered with module 3.
         */

        // ----------------------
        // Collect slices & render
        // ----------------------

        /** @var rex_article_slice[] $slices */
        $slices = rex_article_slice::getSlicesForArticle($articleId, $clangId);


        // Debug output (optional): raw slice objects
        // echo '<pre>'; print_r($slices); echo '</pre><br><br>';


        // Render each slice using the resolved (possibly replaced) module id
        $html = '';

        foreach ($slices as $slice) {
            $targetModuleId = resolve_target_module_id($slice, $replacementMap);
            $html .= render_slice_with_module($slice, $targetModuleId, true);
        }

        return $html;
    }
}

/**
 * Returns content of article with Frontend options for modules.
 */
if (!function_exists('getArticleContentWithFrontendOptions')) {
    function getArticleContentWithFrontendOptions($articleId)
    {
        // bring global frontend config into function scope

        $clang     = rex_clang::getCurrentId();
        $ctype     = 1;


        // CSRF-Tokens
        $csrfMove   = rex_csrf_token::factory(rex_api_content_move_slice::class)->getValue();
        $csrfStatus = rex_csrf_token::factory(rex_api_content_slice_status::class)->getValue();
        // Module für "Block hinzufügen"
        $modules = [];
        if (!empty($GLOBALS["frontend_config"]['addingModules'])) {
            $modules = rex_sql::factory()->getArray('SELECT id, name FROM ' . rex::getTable('module') . ' ORDER BY name ASC');
        }

        // Modul-Options (select)
        $optionsHtml = '';
        foreach ($modules as $m) {
            $id   = (int) $m['id'];
            $name = htmlspecialchars($m['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $optionsHtml .= '<option value="' . $id . '">' . $name . '</option>';
        }

        /** @var rex_article_slice[] $slices */
        $slices = rex_article_slice::getSlicesForArticle($articleId, $clang);

        // Render each slice using the resolved (possibly replaced) module id
        $html = '';
        $index = 1;
        foreach ($slices as $slice) {
            $sliceId  = $slice->getId();
            $moduleId = $slice->getModuleId();
            $status   = (int) $slice->getValue("status");
            $statusCl = $status === 1 ? 'rex-online' : 'rex-offline';

            if (!empty($GLOBALS["frontend_config"]['addingModules'])) {
                $html .= "<button class='rex-add-module'
                popovertarget='rex-add-module-popup' onclick='addModuleToCurrentPosition($index, $sliceId, $clang, $articleId)'>
                <i class='fa fa-light fa-plus'></i> Block hinzufügen
            </button>";
            }

            $html .= "<div class='rex-slice-wrapper $statusCl' data-id='$sliceId'>";

            $html .= "<div class='rex-slice-actions'>";

            // open
            $html .= "<a target='_blank'
                    href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '#slice' . $sliceId) . "'
                    class='btn btn-open'><i class='fa fa-light fa-arrow-up-right-from-square'></i></a>";

            if (!empty($GLOBALS["frontend_config"]['edit'])) {
                $html .= "<a target='_blank' title='Editieren'
                        href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '&slice_id=' . $sliceId . '&clang=' . $clang . '&ctype=' . $ctype . '&function=edit#slice' . $sliceId) . "'
                        class='btn btn-edit'><i class='fa fa-light fa-pencil'></i></a>";
            }

            if (!empty($GLOBALS["frontend_config"]['delete'])) {
                $html .= "<a target='_blank' title='Löschen' onclick='return confirm(\"Block löschen?\");'
                        href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '&slice_id=' . $sliceId . '&clang=' . $clang . '&ctype=' . $ctype . '&function=delete&save=1#slice' . $sliceId) . "'
                        class='btn btn-delete'>
                        <i class='fa fa-light fa-trash'></i></a>";
            }

            // status
            if (!empty($GLOBALS["frontend_config"]['delete'])) {
                $html .= "<a target='_blank' title='Status'
                    href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '&slice_id=' . $sliceId . '&clang=' . $clang . '&ctype=' . $ctype . '&status=0&rex-api-call=content_slice_status&_csrf_token=' . $csrfStatus) . "'
                    class='btn btn-default $statusCl'>";

                if ($status === 1) {
                    $html .= "<div class='status online'></div>";
                } else {
                    $html .= "<div class='status offline'></div>";
                }
                $html .= "</a>";
            }
            if (!empty($GLOBALS["frontend_config"]['copy'])) {
                $html .= "<a target='_blank' title='Kopieren' href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '&bloecks=cutncopy&module_id=' . $moduleId . '&slice_id=' . $sliceId . '&clang=' . $clang . '&ctype=' . $ctype . '&revision=0&cuc_action=copy') . "'
                        class='btn btn-copy'><i class='fa fa-light fa-copy'></i></a>";
            }

            if (!empty($GLOBALS["frontend_config"]['cut'])) {
                $html .= "<a target='_blank' title='Ausschneiden' href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '&bloecks=cutncopy&module_id=' . $moduleId . '&slice_id=' . $sliceId . '&clang=' . $clang . '&ctype=' . $ctype . '&revision=0&cuc_action=cut') . "'
                        class='btn btn-cut'><i class='fa fa-light fa-cut'></i></a>";
            }

            if (!empty($GLOBALS["frontend_config"]['moveUp'])) {
                $html .= "<a target='_blank' title='Nach oben schieben' href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '&slice_id=' . $sliceId . '&clang=' . $clang . '&ctype=' . $ctype . '&upd=' . time() . '&direction=moveup&rex-api-call=content_move_slice&_csrf_token=' . $csrfMove . '#slice' . $sliceId) . "'
                        class='btn btn-move'><i class='fa fa-light fa-arrow-up'></i></a>";
            }

            if (!empty($GLOBALS["frontend_config"]['moveDown'])) {
                $html .= "<a target='_blank' title='Nach unten schieben' href='" . rex_escape('/redaxo/index.php?page=content/edit&article_id=' . $articleId . '&slice_id=' . $sliceId . '&clang=' . $clang . '&ctype=' . $ctype . '&upd=' . time() . '&direction=movedown&rex-api-call=content_move_slice&_csrf_token=' . $csrfMove . '#slice' . $sliceId) . "'
                        class='btn btn-move'><i class='fa fa-light fa-arrow-down'></i></a>";
            }
            $html .= "</div>";

            $html .= render_slice_with_module($slice, $moduleId, true);
            $html .= "</div>";
        }


        // Letzter "Block hinzufügen"-Button
        if (!empty($GLOBALS["frontend_config"]['addingModules'])) {
            $pos = count($slices);
            $html .= "<button class='rex-add-module' popovertarget='rex-add-module-popup' onclick='addModuleToCurrentPosition($pos, -1, $clang,$articleId)'><i class='fa fa-light fa-plus'></i> Block hinzufügen</button>";
        }

        $html .= "<div popover id='rex-add-module-popup'>
                        <select name='module_id' id='moduleDropdown'>
                            <option value=''>Modul auswählen...</option>";
        foreach ($modules as $module) {
            $html .= '<option value="' . (int) $module['id'] . '">' . htmlspecialchars($module['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</option>';
        }
        $html .= "</select></div>";

        return $html;
    }
}
function showDevModulesButtons($articleId, $html = '')
{
    // bring global frontend config into function scope

    $html .= '<div class="rex-page-actions">';

    // "Seite bearbeiten" Button im Frontend
    if ($GLOBALS["frontend_config"]['editPage']) {
        $editUrl   = rex_url::backendPage('content/edit', ['article_id' => $articleId]);
        $btn       = '<a title="Editiermodus öffnen" href="' . $editUrl . '" target="_blank"><i class="fa fa-light fa-file-pen"></i></a>';
        $html .= $btn;
    }
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' .  $_SERVER['HTTP_HOST'];

    $html .= '<div>';
    $btn = '<a class="optionsMode ' . ($_SESSION['optionsMode'] ? 'active' : '') . '" title="Modul Optionen umschalten" href="' . $base_url . '?options_mode=' . ($_SESSION['optionsMode'] ? 0 : 1) . '"><i class="fa fa-light fa-brush"></i></a>';
    $html .= $btn;

    if (rex::getUser()->isAdmin()) {
        $btn       = '<a class="devMode ' . ($_SESSION['devMode'] ? 'active' : '') . '"  title="DEV Modus umschalten" href="' . $base_url . '?dev_mode=' . ($_SESSION['devMode'] ? 0 : 1) . '"><i class="fa fa-light fa-wrench"></i></a>';
        $html .= $btn;
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}
if (!function_exists('getArticleContent')) {
    function getArticleContent()
    {
        $articleId    = rex_article::getCurrentId();
        $ctype = 1;

        // DEV-Mode aktiv -> Module mit DEV Versionen ausgeben (falls vorhanden)
        if (rex::isFrontend() and rex_backend_login::hasSession() and $_SESSION["devMode"]) {

            return getChangedArticleContent($articleId);
        }

        // Option Mode aktiv -> Module mit Optionen ausgeben
        if ($GLOBALS["frontend_config"]['activateAddOn'] and rex::isFrontend() and rex_backend_login::hasSession() and $_SESSION["optionsMode"]) {

            return getArticleContentWithFrontendOptions($articleId);
        }


        return rex_tmpl::getArticle($ctype, $articleId);
    }
}
