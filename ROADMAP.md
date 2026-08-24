# Roadmap – OrgSuite

Diese Datei bündelt geplante Erweiterungen und offene Produktentscheidungen. Verbindliche Fach-, Sicherheits- und Architekturregeln stehen in `AGENTS.md`.

## Zukunftsplanung – nicht freigegeben

### ORGS-L10N – OrgSuite vollständig lokalisieren

Status: später, nicht freigegeben; Pilot-App, Reihenfolge und Rohtext-Gate
werden vor jeder Umsetzung appübergreifend separat freigegeben

- Navigation, Adminadapter, Status- und Fehlermeldungen vertikal auf
  Nextcloud-l10n umstellen.
- Produkt-IDs, Routen, Menü-Suite-Schlüssel und Capability-Verträge
  sprachneutral lassen.
- Deutsche Ausgabe, eine weitere Locale, Fallback, Platzhalter,
  Pluralformen, Escaping und JavaScript/PHP-Übergabe testen.
- Erst nach vollständiger Migration einen Rohtext-Check für OrgSuite
  verbindlich schalten.

## Aktueller Fokus

- Die manuellen Prüfungen werden im ausfüllbaren
  [`docs/manual-acceptance.md`](docs/manual-acceptance.md) dokumentiert.
- Gemeinsame AD-/BR-Navigation und den administrativen Einstieg für Organisations- und Freigabeverträge auf einem realitätsnahen Staging abnehmen.
- Dabei auch die globale, rein visuelle Links-rechts-Anordnung der LocalBase-Organigrammkarten prüfen; die fachliche Gruppenreihenfolge bleibt davon getrennt.
- Standalone- und Mehrproduktzustände einschließlich deaktivierter Zielapps zuverlässig prüfen.
- Den katalogisierten BQ-Planer in Standalone- und Mehrproduktzuständen
  prüfen; seine Bundle-Freigabe bleibt ein getrenntes Release-Gate.
- Die implementierte Verwaltung zusätzlicher externer AD-/BR-Menülinks auf
  einem realitätsnahen Staging visuell und fachlich abnehmen. Der aktuelle
  Vertrag gilt für alle angemeldeten Personen, verwendet ausschließlich
  HTTPS und öffnet Ziele im selben Tab; Gruppenfilter sind nicht Bestandteil
  dieses freigegebenen Umfangs.

## Geplante Erweiterungen

- Neue Navigationsziele werden nur gemeinsam mit einer tatsächlich vorhandenen Fachapp aufgenommen.
- Der Adminbereich wächst nur mit freigegebenen app-übergreifenden LocalBase-Verträgen; app-spezifische Einstellungen bleiben in der Fachapp.
- OrgSuite bleibt frei von Fachdaten und fachlichen Berechtigungserweiterungen.

## Vor der Umsetzung zu klären

- Betroffene Fachapps, bevorzugtes Fallback-Ziel und Standalone-Verhalten.
- Serverseitige Zielberechtigungen, Tastaturbedienung und Contract-Tests jeder neuen Navigation oder Adminintegration.
