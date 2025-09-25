<?php

/** @var rex_addon $this */

// Tabelle anlegen
rex_sql_table::get(rex::getTable('dev_modules'))
    ->ensurePrimaryIdColumn() // id INT AUTO_INCREMENT
    ->ensureColumn(new rex_sql_column('module_id', 'int(10) unsigned', false))
    ->ensureColumn(new rex_sql_column('dev_module_id', 'int(10) unsigned', false))
    ->ensureGlobalColumns()
    ->setPrimaryKey(['id'])
    ->ensure();

// Default-Config initialisieren
if (!$this->hasConfig()) {
    $this->setConfig('status', 'active');
}
