<?php

/** @var rex_addon $this */
$addon = rex_addon::get('dev_modules');

// Daten laden
$modules = rex_sql::factory()->getArray(
    'SELECT id, name FROM ' . rex::getTable('module') . ' ORDER BY name'
);
$moduleNames = array_column($modules, 'name', 'id');                // [id => name]
$allModuleIds = array_map('intval', array_keys($moduleNames));      // [id, id, …]

$devModules = rex_sql::factory()->getArray(
    'SELECT module_id, dev_module_id FROM ' . rex::getTable('dev_modules')
);
$mappedIds = array_column($devModules, 'dev_module_id', 'module_id'); // [live => dev]

// Inkonsistenz Cleanup der Mapping-Tabelle
$deleted = 0;
foreach ($devModules as $entry) {
    $liveId = (int) $entry['module_id'];
    $devId  = (int) $entry['dev_module_id'];

    $liveExists = array_key_exists($liveId, $moduleNames);
    $devExists  = array_key_exists($devId,  $moduleNames);

    // Live- oder DEV-Modul existiert nicht mehr -> Mapping löschen
    if (!$liveExists || !$devExists) {
        $del = rex_sql::factory();
        $del->setTable(rex::getTable('dev_modules'));
        $del->setWhere(['module_id' => $liveId]);
        $del->delete();
        unset($mappedIds[$liveId]);
        $deleted++;
        continue;
    }
}

// Ausgabe Tabelle
echo '<table id="DevModulesTable" class="table table-striped">';
echo '<tr>
        <th style="width:50px;">ID</th>
        <th>LIVE ' . $addon->i18n('general_module') . '</th>
        <th></th>
        <th></th>';

if (!empty($devModules)) {
    echo '<th style="width:50px;">ID</th>
        <th>DEV ' . $addon->i18n('general_module') . '</th>';
}

echo '</tr>';
echo '<tbody>';

// DEV Modul ID's
$devIdsOnly = array_map('intval', array_values($mappedIds));

foreach ($modules as $module) {
    $liveId   = (int) $module['id'];
    $liveName = htmlspecialchars($module['name']);

    // DEV Module nicht auflisten
    if (in_array($liveId, $devIdsOnly, true)) {
        continue;
    }

    $devId   = $mappedIds[$liveId] ?? null;
    $hasDev  = $devId !== null;

    echo '<tr>';
    echo '<td><a target="_blank" href="' . moduleEditUrl($liveId) . '">' . $liveId . '</a></td>';
    echo '<td>' . $liveName . '</td>';


    if ($hasDev) {
        echo '<td>';
        echo '<button class="btn btn-success btn-sm js-overwrite" data-id="' . $liveId . '" data-dev="' . $devId . '">' . $addon->i18n('config_overwrite_with_dev') . '</button>';
        echo '</td>';
    }

    if ($hasDev) {
        echo '<td>';
        echo '<button class="btn btn-danger btn-sm js-clear" data-id="' . $liveId . '" data-dev="' . $devId . '">' . $addon->i18n('config_delete_dev') . '</button>';
        echo '</td>';
    } else {
        echo '<td colspan="2">';
        echo '<button class="btn btn-primary btn-sm js-create" data-id="' . $liveId . '">' . $addon->i18n('config_create_dev') . '</button>';
        echo '</td>';
    }

    // Rechte Spalte
    if ($hasDev) {
        $devName = htmlspecialchars($moduleNames[$devId] ?? '[DEV nicht gefunden]');
        echo '<td><a target="_blank" href="' . moduleEditUrl($devId) . '">' . $devId . '</a></td>';
        echo '<td>' . $devName . '</td=>';
    } else if (!empty($devModules)) {
        echo '<td></td>';
        echo '<td></td>';
    }

    echo '</tr>';
}

echo '</tbody></table>';

// Info-Badge, wenn bereinigt wurde
if ($deleted > 0) {
    echo rex_view::info($deleted . ' ungültige DEV-Zuordnung(en) bereinigt.');
}
?>

<div id="responseWarning" class="alert alert-warning" hidden></div>
<div id="responseSuccess" class="alert alert-success" hidden></div>

<script>
    // Pfad für die JS Files mitgeben, damit dev_modules_controller aufgerufen werden kann
    let currentBasePath = "<?= rex_url::backendPage('dev_modules/controller') ?>";
</script>