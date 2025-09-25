# ETX: DEV Modules

## Beschreibung

DEV Modules erleichtert das sichere Entwickeln und Testen von REDAXO-Modulen im laufenden Betrieb. Du kannst zu jedem LIVE-Modul eine DEV-Kopie anlegen, diese im Frontend gezielt verwenden und bei Bedarf das LIVE-Modul damit überschreiben – ohne die öffentliche Seite zu gefährden. Zusätzlich bietet das AddOn eine visuelle Modulverwaltung an, welche durch Aufrufe ans Backend gelöst wird. Diese Modulverwaltung erlaubt es vom Frontend aus, Module zu verwalten und an beliebigen Stellen hinzuzufügen. Zudem gibt es einen Button für das Anzeigen der DEV-Kopie anstelle der LIVE Modul, falls eine Kopie besteht. Auf der Seite befinden sich zusätzlich ein Button, über den man direkt zum Editiermodus gelangt.

## Funktionen

### Modul-Management

- DEV-Kopie erstellen: legt zu einem LIVE-Modul ein zweites Modul mit dem Suffix "[DEV]" an.
- LIVE mit DEV überschreiben: übernimmt den aktuellen DEV-Stand in das LIVE-Modul.
- DEV-Kopie löschen: entfernt die DEV-Variante samt Zuordnung.

### Frontend-Optionen (WYSIWYG-Werkzeuge)

Im Frontend können – nur für eingeloggte REDAXO-Nutzer – hilfreiche Bedienelemente eingeblendet werden:

- Seite bearbeiten: Direktlink in den Editiermodus der aktuellen Seite.
- Options Mode: zeigt Verwaltungs-Buttons pro Slice/Modul:
- Öffnen, Bearbeiten, Löschen, Status ändern (online/offline)
- Kopieren, Ausschneiden (bloecks)
- Nach oben / unten verschieben
- Block hinzufügen zwischen bestehenden Modulen
- DEV Mode: rendert im Frontend die DEV-Kopien anstelle der LIVE-Module. Ideal zum Prüfen neuer Ausgaben, ohne die Live-Besucher zu beeinflussen.

### Einstellungen

- LIVE / DEV Module: Liste aller Module mit Aktionen
  – DEV-Kopie erstellen, LIVE Modul überschreiben, DEV Kopie löschen.
- Frontend Optionen: Schalter für „Seite bearbeiten“, „Options Mode“, „DEV Mode“ und die einzelnen Buttons (Bearbeiten, Verschieben, Kopieren, Ausschneiden, Status, Löschen).

### Sicherheit & Berechtigungen

- Die Frontend-Werkzeuge erscheinen nur für eingeloggte REDAXO-User (Backend-Session).
- Optional lässt sich die Ausgabe auf Admins beschränken.
- AddOn-Permission: dev_modules[] (für Rollen steuerbar).

### Workflow-Beispiel

1. In LIVE / DEV Module bei einem Modul „DEV Kopie erstellen“ klicken.
2. DEV-Modul bearbeiten und testen.
3. Im Frontend den DEV Mode aktivieren → Seite zeigt die DEV-Module.
4. Passt alles? → „LIVE Modul überschreiben“.
5. LIVE Modul wird von DEV überschrieben
6. Die Module werden entsprechend bereinigt.

## Integration

Um das AddOn nutzen zu können, muss es installiert und im Template `<?php echo getArticleContent(); ?>` hinterlegt werden, welches basierend auf dem aktiven Modus prüft, wie die Artikel-Slices der Module verarbeitet werden.

## Voraussetzungen

- Redaxo version: 5.15.0
- [developer](https://github.com/FriendsOfREDAXO/developer) >= 3.6.0 for module input and output
- [blOecks](https://github.com/FriendsOfREDAXO/bloecks) >= 4.0.0 for module code-injection in editor mode
