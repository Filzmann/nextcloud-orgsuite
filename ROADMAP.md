# Roadmap – OrgSuite

Diese Datei bündelt geplante Erweiterungen und offene Produktentscheidungen. Verbindliche Fach-, Sicherheits- und Architekturregeln stehen in `AGENTS.md`.

## Nextcloud-Kompatibilitätsgate

### ORGS-NC-COMPAT – OpenDesk-Boden 33 und Navigationsmatrix nachweisen

Status: `info.xml` bleibt bei 34/34; NC 33.0.7 ist nur statisch geprüft. Vor
`min-version="33"` müssen Fresh Install/Upgrade, DI, Einprodukt- und
Mehrproduktzustände, deaktivierte Ziele, AD-/BR-Navigation, Template-Event,
zentrale Assets und LocalBase-Adminadapter auf NC 33 grün sein. Jede weitere
Major wird lückenlos über `verify-nextcloud-future-compatibility` geprüft;
eine neue Obergrenze gilt erst, wenn alle unterstützten Kombinationen
kontrolliert funktionieren.

## Systemweit gegatete app-lokale Aufgabe

### ORGS-L10N – Navigation und Adminadapter lokalisieren

Aktivierung ausschließlich nach Freigabe des Root-Vorhabens `ZM-06`.
Sichtbare Navigation, Status- und Fehlermeldungen wechseln auf
Nextcloud-l10n; Produkt-IDs, Routen, Suite-Schlüssel und Capability-Verträge
bleiben sprachneutral. PHP-/JavaScript-Übergabe, Fallback, Platzhalter und
Escaping werden app-lokal geprüft.

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
