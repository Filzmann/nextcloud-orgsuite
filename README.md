# OrgSuite

Gemeinsame AD-/BR-Navigation und Nextcloud-Adminoberfläche für organisationsweite Gruppen-, Hierarchie- und Freigabeeinstellungen. OrgSuite enthält keine Fachdaten.

## Staging-Kompatibilität

- Nextcloud 34
- PHP 8.3 oder neuer innerhalb des von Nextcloud 34 unterstützten Bereichs
- Laufzeitbasis: `localbase`
- App-ID und Installationsordner: `orgsuite`

## Installation

OrgSuite ist mitgelieferte Infrastruktur und kein separates AD-Fachprodukt. Der Produktinstaller aktiviert sie automatisch ab zwei aktiven AD-Fachprodukten. Bei einer Einzelinstallation bleibt sie deaktiviert; die Fachapp besitzt dann ihren eigenen Einstieg und Adminabschnitt.

Nach der Aktivierung werden Organisationsdefinition und Freigaben im Nextcloud-Adminbereich der OrgSuite gepflegt. Persistenz, geschützte Admin-API und Organisationseditor liegen in LocalBase.

## Roadmap

Geplante Erweiterungen und offene Produktentscheidungen stehen in der [Roadmap](ROADMAP.md).

Installations-, Betriebs- und Abnahmeunterlagen stehen im öffentlichen [AD-Suite-Projekt](https://github.com/Filzmann/ad-suite).
