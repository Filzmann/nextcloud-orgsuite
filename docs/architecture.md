# Architektur – OrgSuite

## Verantwortung

OrgSuite stellt die gemeinsamen AD- und BR-Einstiege, das Quermenü sowie den
Administrationsadapter für app-übergreifende Suite-Einstellungen bereit. Sie
besitzt keine Fachdaten und erweitert keine Rechte der Zielapps.

## Navigation

- AD-Ziele und Reihenfolge stammen aus dem versionierten
  LocalBase-Produktkatalog; BR-Ziele verbleiben im festgelegten BR-Vertrag.
- Fachapps stellen nur wirkungslose Menühosts bereit und laden keine
  OrgSuite-Assets direkt.
- Bei genau einem AD-Fachprodukt bleibt OrgSuite deaktiviert; ab zwei
  Produkten wird sie durch den geprüften Installer aktiviert.
- Zusätzliche externe Links sind HTTPS-basiert, zentral konfiguriert und
  erteilen keine Rechte im Zielsystem.

## Administration

Gemeinsame Organisations- und Freigabeverträge werden in LocalBase
persistiert und über einen Adapter in OrgSuite angezeigt. App-spezifische
Administration bleibt in der jeweiligen Fachapp. Administrative Requests
prüfen Nextcloud-Adminstatus und CSRF serverseitig.
