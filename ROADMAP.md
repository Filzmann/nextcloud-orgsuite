# Roadmap – OrgSuite

Diese Datei enthält ausschließlich offene Arbeit, zurückgestellte Vorhaben
und Freigabegates. Der aktuelle Funktionsumfang steht in `README.md`,
erledigte Änderungen in `CHANGELOG.md` und geltende Architektur in
`docs/architecture.md`.

## Aktueller Fokus

- Die manuellen Prüfungen werden im ausfüllbaren
  [`docs/manual-acceptance.md`](docs/manual-acceptance.md) dokumentiert.
- Gemeinsame AD-/BR-Navigation und den administrativen Einstieg für Organisations- und Freigabeverträge auf einem realitätsnahen Staging abnehmen.
- Dabei auch die globale, rein visuelle Links-rechts-Anordnung der LocalBase-Organigrammkarten prüfen; die fachliche Gruppenreihenfolge bleibt davon getrennt.
- Standalone- und Mehrproduktzustände einschließlich deaktivierter Zielapps zuverlässig prüfen.
- Den katalogisierten BQ-Planer in Standalone- und Mehrproduktzuständen
  prüfen; seine Bundle-Freigabe bleibt ein getrenntes Release-Gate.
- Die Verwaltung zusätzlicher externer AD-/BR-Menülinks auf
  einem realitätsnahen Staging visuell und fachlich abnehmen. Der aktuelle
  Vertrag gilt für alle angemeldeten Personen, verwendet ausschließlich
  HTTPS und öffnet Ziele im selben Tab; Gruppenfilter sind nicht Bestandteil
  dieses freigegebenen Umfangs.

## Geplante Erweiterungen

### ORGS-EXTERNAL-LINK-NAVIGATION – Zielöffnung und gemeinsame Reihenfolge

Produktentscheidung aus der manuellen Abnahme vom 4. Oktober 2026:

- Externe AD-/BR-Menülinks öffnen standardmäßig in einem neuen Tab.
- Der Öffnungsmodus ist pro Link administrativ zwischen neuem und demselben
  Tab konfigurierbar; sichere `rel`-Attribute und HTTPS-Validierung bleiben
  verbindlich.
- Interne Produktziele und externe Links erhalten je Suite eine gemeinsame,
  per Drag-and-drop und Tastatur veränderbare Menüreihenfolge.
- Die Umsetzung benötigt Validierungs-, CSRF-, Nichtadmin-Deny-,
  Tastatur-/Fokus- und Persistenztests. Bis zur Umsetzung beschreibt
  `README.md` weiterhin den aktuellen Stand.

- Neue Navigationsziele werden nur gemeinsam mit einer tatsächlich vorhandenen Fachapp aufgenommen.
- Der Adminbereich wächst nur mit freigegebenen app-übergreifenden LocalBase-Verträgen; app-spezifische Einstellungen bleiben in der Fachapp.
- OrgSuite bleibt frei von Fachdaten und fachlichen Berechtigungserweiterungen.

## Vor der Umsetzung zu klären

- Betroffene Fachapps, bevorzugtes Fallback-Ziel und Standalone-Verhalten.
- Serverseitige Zielberechtigungen, Tastaturbedienung und Contract-Tests jeder neuen Navigation oder Adminintegration.

## Bewusst zurückgestellt – niedrigste Priorität

### ORGS-L10N – Navigation und Adminadapter lokalisieren

Status seit 17. September 2026: Die Umsetzung beginnt erst nach allen höher
priorisierten Roadmap-Aufgaben und einer erneuten ausdrücklichen Freigabe des
Root-Vorhabens `ZM-06`. Neue Funktionen und Codeänderungen berücksichtigen
die spätere Lokalisierbarkeit an den jeweils berührten Stellen, lösen aber
keine flächige Umstellung oder Übersetzungsimplementierung aus.

Bei der späteren Umsetzung wechseln sichtbare Navigation, Status- und
Fehlermeldungen auf Nextcloud-l10n; Produkt-IDs, Routen, Suite-Schlüssel und
Capability-Verträge bleiben sprachneutral. PHP-/JavaScript-Übergabe,
Fallback, Platzhalter und Escaping werden app-lokal geprüft.
