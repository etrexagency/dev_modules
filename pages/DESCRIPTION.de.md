# ETX: DEV Modules

## Beschreibung

DEV Modules erleichtert das sichere Entwickeln und Testen von REDAXO-Modulen im laufenden Betrieb. Du kannst zu jedem LIVE-Modul eine DEV-Kopie anlegen, diese im Frontend gezielt verwenden und bei Bedarf das LIVE-Modul damit überschreiben – ohne die öffentliche Seite zu gefährden. Zusätzlich bietet das AddOn eine visuelle Modulverwaltung an, welche durch Aufrufe ans Backend gelöst wird. Diese Modulverwaltung erlaubt es vom Frontend aus, Module zu verwalten und an beliebigen Stellen hinzuzufügen. Zudem gibt es einen Button für das Anzeigen der DEV-Kopie anstelle der LIVE Modul, falls eine Kopie besteht. Auf der Seite befinden sich zusätzlich ein Button, über den man direkt zum Editiermodus gelangt.

## Funktionen

### Modul-Management

- DEV-Kopie erstellen: legt zu einem LIVE-Modul ein zweites Modul mit dem Suffix "[DEV]" an.
- LIVE mit DEV überschreiben: übernimmt den aktuellen DEV-Stand in das LIVE-Modul.
- DEV-Kopie löschen: entfernt die DEV-Variante samt Zuordnung.

<img width="983" height="742" alt="image" src="https://github.com/user-attachments/assets/5e90afe5-9003-4164-bc88-aeebfe00d05b" />

### Frontend-Optionen (WYSIWYG-Werkzeuge)

Im Frontend können – nur für eingeloggte REDAXO-Nutzer – hilfreiche Bedienelemente eingeblendet werden:

- Seite bearbeiten: Direktlink in den Editiermodus der aktuellen Seite.
- Options Mode: zeigt Verwaltungs-Buttons pro Slice/Modul:
- Öffnen, Bearbeiten, Löschen, Status ändern (online/offline)
- Kopieren, Ausschneiden (bloecks)
- Nach oben / unten verschieben
- Block hinzufügen zwischen bestehenden Modulen
- DEV Mode: rendert im Frontend die DEV-Kopien anstelle der LIVE-Module. Ideal zum Prüfen neuer Ausgaben, ohne die Live-Besucher zu beeinflussen.

<img width="975" height="879" alt="image" src="https://github.com/user-attachments/assets/561d0a5b-586d-4f79-bfad-d2dca8620a2e" />

#### Frontend Optionen aktiv (zeigt im angemeldeten Modus die Buttons unten an: Editiermodus öffnen, Options Mode und DEV Mode)

<img width="1260" height="1187" alt="image" src="https://github.com/user-attachments/assets/80c3e09f-268b-480a-aab6-d26e7fafdd66" />

#### Frontend Optionen: Options Mode aktiv (zeigt Optionen anhand AddOn-Einstellungen an)

<img width="1259" height="1198" alt="image" src="https://github.com/user-attachments/assets/91f432d6-f70a-4c66-b3b8-e519d283b1cd" />

#### Frontend Optionen: Options Mode - Block hinzufügen

<img width="1263" height="1198" alt="image" src="https://github.com/user-attachments/assets/b3965102-00a2-4916-8963-e2472e6353b8" />

#### Frontend Optionen: DEV Mode aktiv (zeigt verknüpfte DEV anstatt LIVE Module aus den AddOn Einstellungen)

<img width="1267" height="1195" alt="image" src="https://github.com/user-attachments/assets/5e47ee6c-e7e9-43c9-b765-0d159fd3420e" />


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
- [blOecks](https://github.com/FriendsOfREDAXO/bloecks) >= 4.0.0 für ausschneiden & einfügen
