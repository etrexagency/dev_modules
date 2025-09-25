<?php

// Diese Datei ist keine Pflichtdatei mehr.

// AddOn-Objekt bereitstellen und Data-Verzeichnis löschen (redaxo/data/addons/dev_modules/)
// Im AddOn-Kontext wäre auch $this->getDataPath() möglich

$addon = rex_addon::get('dev_modules');

rex_dir::delete($addon->getDataPath());

// SQL-Anweisungen können auch weiterhin über die `uninstall.sql` ausgeführt werden.
// Empfohlen wird aber die SQL-Anweisungen in der `uninstall.php` auszuführen
// Siehe auch https://redaxo.org/doku/master/datenbank-tabellen
// Die Tabelle des Demo-AddOns wird hier gelöscht
rex_sql_table::get(rex::getTable('dev_modules'))->drop();
