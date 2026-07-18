# Roadmap – OrgSuite

Diese Datei bündelt geplante Erweiterungen und offene Produktentscheidungen. Verbindliche Fach-, Sicherheits- und Architekturregeln stehen in `AGENTS.md`.

## Aktueller Fokus

- Gemeinsame AD-/BR-Navigation und den administrativen Einstieg für Organisations- und Freigabeverträge auf einem realitätsnahen Staging abnehmen.
- Standalone- und Mehrproduktzustände einschließlich deaktivierter Zielapps zuverlässig prüfen.

## Geplante Erweiterungen

- Neue Navigationsziele werden nur gemeinsam mit einer tatsächlich vorhandenen Fachapp aufgenommen.
- Der Adminbereich wächst nur mit freigegebenen app-übergreifenden LocalBase-Verträgen; app-spezifische Einstellungen bleiben in der Fachapp.
- OrgSuite bleibt frei von Fachdaten und fachlichen Berechtigungserweiterungen.

## Vor der Umsetzung zu klären

- Betroffene Fachapps, bevorzugtes Fallback-Ziel und Standalone-Verhalten.
- Serverseitige Zielberechtigungen, Tastaturbedienung und Contract-Tests jeder neuen Navigation oder Adminintegration.
