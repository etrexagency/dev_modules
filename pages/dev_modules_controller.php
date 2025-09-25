<?php

// POST Aufruf für "createDevModule"
if (rex_request('func', 'string') === 'createDevModule') {
    // Dev Modul in Mapping Tabelle erstellen und Modul duplizieren mit [DEV] Suffix
    $moduleId = rex_request('module_id', 'int');

    // Schritt 1: Modul welches dupliziert wird holen
    $sql = rex_sql::factory();
    $origin_module = $sql->getArray('SELECT * FROM ' . rex::getTable('module') . ' WHERE id = ?', [$moduleId]);

    if (!empty($origin_module)) {
        $origin_module = $origin_module[0];

        // Schritt 2: Neue Werte vorbereiten (id rausnehmen!)
        unset($origin_module['id']);
        // evtl. auch "key" anpassen, falls der eindeutig sein muss
        $origin_module['name'] = $origin_module['name'] . ' [DEV]';

        // Schritt 3: Einfügen
        $sqlInsert = rex_sql::factory();
        $sqlInsert->setTable(rex::getTable('module'));
        $sqlInsert->setValues($origin_module);
        $sqlInsert->insert();

        // Schritt 4: Neue ID holen
        $newId = $sqlInsert->getLastId();
    }
    $user = rex::getUser();
    $data = [
        'module_id' => $moduleId,
        'dev_module_id' => $newId,
        'createdate' => date("Y-m-d H:i:s"),
        'createuser' => $user->getValue('login'),
        'updatedate' => date("Y-m-d H:i:s"),
        'updateuser' => $user->getValue('login'),
    ];

    $sql = rex_sql::factory();
    $sql->setTable(rex::getTable('dev_modules'));
    $sql->setValues($data);
    $sql->insertOrUpdate();


    rex_response::sendJson([
        'success' => true,
        'message' => "DEV-Kopie für Modul $moduleId erstellt!"
    ]);
    exit;
}

// POST Aufruf für "deleteDevModule"
if (rex_request('func', 'string') === 'deleteDevModule') {
    $moduleId    = rex_request('module_id', 'int');
    $devModuleId = rex_request('dev_module_id', 'int');

    $response = ['success' => false, 'message' => ''];

    if (checkIfModuleInUsage($devModuleId)) {
        $response['success'] = false;
        $response['message'] = "DEV-Modul {$devModuleId} wird noch verwendet und kann nicht gelöscht werden.";
        rex_response::sendJson($response);
        exit;
    }

    try {
        deleteEntryInMappingTable($moduleId, $devModuleId);
        $response['success'] = true;
        $response['message'] = "DEV-Kopie (ID $devModuleId) von Modul $moduleId gelöscht.";
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    rex_response::sendJson($response);
    exit;
}

// POST Aufruf für "overwriteModule"
if (rex_request('func', 'string') === 'overwriteModule') {
    // DEV Modul -> LIVE Modul und ID in allen slices aktualisieren
    $moduleId    = rex_request('module_id', 'int');     // Original Modul-ID
    $devModuleId = rex_request('dev_module_id', 'int'); // DEV Modul-ID

    $response = ['success' => false, 'message' => ''];

    try {
        $sql = rex_sql::factory();

        // DEV Modul holen
        $devModule = $sql->getArray(
            'SELECT input, output FROM ' . rex::getTable('module') . ' WHERE id = ?',
            [$devModuleId]
        );

        if (empty($devModule)) {
            $response['success'] = false;
            $response['message'] = "DEV-Modul mit ID $devModuleId nicht gefunden.";
            rex_response::sendJson($response);
            exit;
        }

        $devInput  = $devModule[0]['input'];
        $devOutput = $devModule[0]['output'];

        // Original-Modul überschreiben
        $sqlUpdate = rex_sql::factory();
        $sqlUpdate->setTable(rex::getTable('module'));
        $sqlUpdate->setWhere(['id' => $moduleId]);
        $sqlUpdate->setValue('input', $devInput);
        $sqlUpdate->setValue('output', $devOutput);
        $sqlUpdate->update();

        // Alle Slices, die auf das DEV-Modul zeigen, zurück auf das Original mappen
        $sqlSlice = rex_sql::factory();
        $sqlSlice->setTable(rex::getTable('article_slice'));
        $sqlSlice->setWhere(['module_id' => $devModuleId]);
        $sqlSlice->setValue('module_id', $moduleId);
        $sqlSlice->update();

        // DEV-Modul + Mapping löschen
        deleteEntryInMappingTable($moduleId, $devModuleId);

        $response['success'] = true;
        $response['message'] = "Modul $moduleId wurde mit DEV ($devModuleId) überschrieben. Alle Slices wurden aktualisiert und das DEV-Modul entfernt.";
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }

    rex_response::sendJson($response);
    exit;
}
