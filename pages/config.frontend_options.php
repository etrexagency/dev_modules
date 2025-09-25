<?php

/*
AddOn-Einstellungen die in der Tabelle `rex_config` gespeichert werden.
Hier mit Verwendung der Klasse `rex_config_form`. Die Einstellungen werden automatisch
beim absenden des Formulars gespeichert.

Die beiden Dateien `config.rex_config_form.php` und `config.classic_form.php`
speichern die gleichen AddOn-Einstellungen.
Anhand der identischen Kommentare können die beiden Dateien "verglichen" werden.

https://redaxo.org/doku/master/konfiguration_form
*/

$addon = rex_addon::get('dev_modules');

// Instanzieren des Formulars
$form = rex_config_form::factory('dev_modules');

// Feldgruppe
$form->addFieldset($addon->i18n('general'));

// Aktivieren
$field = $form->addCheckboxField('activate');
$field->addOption($addon->i18n('config_activate'), 1);


// Seite bearbeiten Button
$field = $form->addCheckboxField(name: 'edit-page');
$field->addOption('"' . $addon->i18n('config_edit_page') . '" ' . $addon->i18n('general_show'), 1);

// Block hinzufügen sichtbar
$field = $form->addCheckboxField('adding-modules');
$field->addOption('"' . $addon->i18n('config_adding_modules') . '" ' . $addon->i18n('general_show'), 1);

// Feldgruppe
$form->addFieldset($addon->i18n('general_buttons'));

// --- Checkboxen für Module-Editor-Optionen ---

// Bearbeiten-Button
$field = $form->addCheckboxField('edit');
$field->addOption('"' . $addon->i18n('config_edit') . '" ' . $addon->i18n('general_show'), 1);

// Nach oben verschieben
$field = $form->addCheckboxField('move-up');
$field->addOption('"' . $addon->i18n('config_move_up') . '" ' . $addon->i18n('general_show'), 1);

// Nach unten verschieben
$field = $form->addCheckboxField('move-down');
$field->addOption('"' . $addon->i18n('config_move_down') . '" ' . $addon->i18n('general_show'), 1);

// Ausschneiden (bloecks)
$field = $form->addCheckboxField('cut');
$field->addOption('"' . $addon->i18n('config_cut') . '" ' . $addon->i18n('general_show'), 1);

// Kopieren (bloecks)
$field = $form->addCheckboxField('copy');
$field->addOption('"' . $addon->i18n('config_copy') . '" ' . $addon->i18n('general_show'), 1);

// Status
$field = $form->addCheckboxField('status');
$field->addOption('"' . $addon->i18n('config_status') . '" ' . $addon->i18n('general_show'), 1);

// Löschen
$field = $form->addCheckboxField('delete');
$field->addOption('"' . $addon->i18n('config_delete') . '" ' . $addon->i18n('general_show'), 1);

// Ausgabe
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('config'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
