# Changelog

## Versionen

### 1.0.2

-  Realisiert, dass das Developer AddOn gar nicht benötigt wird.
-  Package.yml angepasst
-  DESCRIPTION und README Markdown Dateien mit Bildern ergänzt.

### 1.0.1

Workflow Bugfix

### 1.0.0

#### Erstveröffentlichung von ETX: DEV Modules.

Bietet eine vollständige Entwicklungs-/Live-Trennung für REDAXO-Module sowie Frontend-Bedienung für eingeloggte Nutzer.

**Enthaltene Features**:

- DEV-Kopie pro LIVE-Modul erstellen, LIVE mit DEV überschreiben oder DEV-Kopie löschen.
- Frontend-Optionen (nur mit Backend-Login):
- Seite bearbeiten (Direktlink in den Editiermodus).
- Options Mode: Öffnen, Bearbeiten, Löschen, Status (online/offline), Kopieren, Ausschneiden (blÖcks), Nach oben/unten verschieben, sowie Block hinzufügen zwischen Modulen.
- DEV Mode: Rendert DEV-Kopien im Frontend anstelle der LIVE-Module zum gefahrlosen Testen.
- Rechteverwaltung via dev_modules[] (optional nur Admins).
- Saubere Ausgabe der Frontend-Assets nur für eingeloggte Nutzer.

**Integration**:
Um das AddOn nutzen zu können, muss es installiert und im Template `<?php echo getArticleContent(); ?>` hinterlegt werden, welches basierend auf dem aktiven Modus prüft, wie die Artikel-Slices der Module verarbeitet werden.

---

### Semantische Versionisierung (MAJOR.MINOR.PATCH)

- MAJOR wird erhöht, wenn grundlegende API-Änderungen vorgenommen werden.
- MINOR wird erhöht, wenn eine neue Funktionen hinzugefügt wird, ohne die bestehende API (exportierte Funktionen) oder Funktionen zu beeinträchtigen, d. h. wenn es sich um eine nicht bahnbrechende Änderung handelt.
- PATCH wird erhöht, wenn eine rückwärtskompatible Fehlerbehebungen vorgenommen wird. Eine Patch-Änderung sollte keine Änderungen an der API beinhalten.
