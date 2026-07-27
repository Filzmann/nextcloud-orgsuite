# Roadmap – OrgSuite

Diese Datei bündelt geplante Erweiterungen und offene Produktentscheidungen. Verbindliche Fach-, Sicherheits- und Architekturregeln stehen in `AGENTS.md`.

## Aktueller Fokus

- Gemeinsame AD-/BR-Navigation und den administrativen Einstieg für Organisations- und Freigabeverträge auf einem realitätsnahen Staging abnehmen.
- Dabei auch die globale, rein visuelle Links-rechts-Anordnung der LocalBase-Organigrammkarten prüfen; die fachliche Gruppenreihenfolge bleibt davon getrennt.
- Standalone- und Mehrproduktzustände einschließlich deaktivierter Zielapps zuverlässig prüfen.

## Geplante Erweiterungen

- **Externe Links im AD-Menü:** Nextcloud-Admins können im OrgSuite-Adminbereich externe Navigationsziele mit Anzeigename, HTTPS-URL, Reihenfolge und Aktivstatus anlegen, bearbeiten und entfernen. Jeder Link kann auf eine oder mehrere bestehende Nextcloud-Gruppen eingeschränkt werden; die Mitgliedschaft wird serverseitig ausgewertet und das Menü liefert angemeldeten Personen ausschließlich die für sie sichtbaren Links. Ein sichtbarer externer Link erteilt keine Rechte im Zielsystem. Eingaben werden validiert, Ausgaben sicher escaped und unsichere URL-Schemata abgelehnt. Die Umsetzung erhält Allow-/Deny-Tests für Administration und Gruppensichtbarkeit sowie Tastatur-, Fokus- und Menü-Smokes.
- Neue Navigationsziele werden nur gemeinsam mit einer tatsächlich vorhandenen Fachapp aufgenommen.
- Der Adminbereich wächst nur mit freigegebenen app-übergreifenden LocalBase-Verträgen; app-spezifische Einstellungen bleiben in der Fachapp.
- OrgSuite bleibt frei von Fachdaten und fachlichen Berechtigungserweiterungen.

## Vor der Umsetzung zu klären

- Betroffene Fachapps, bevorzugtes Fallback-Ziel und Standalone-Verhalten.
- Serverseitige Zielberechtigungen, Tastaturbedienung und Contract-Tests jeder neuen Navigation oder Adminintegration.
- Für externe Links: Bedeutet eine leere Gruppenauswahl „für alle angemeldeten Personen“ oder soll mindestens eine Gruppe verpflichtend sein?
- Für externe Links: Öffnen sie standardmäßig im selben Tab oder in einem neuen Tab, und wird diese Wahl je Link konfigurierbar?
- Für externe Links: Bleibt die Funktion zunächst ausschließlich im AD-Menü oder soll derselbe Vertrag später auch das BR-Menü unterstützen?
