# OrgSuite

Gemeinsame AD-/BR-Navigation und Nextcloud-Adminoberfläche für organisationsweite Gruppen-, Hierarchie- und Freigabeeinstellungen. OrgSuite enthält keine Fachdaten.

## Staging-Kompatibilität

- Nextcloud 34
- PHP 8.3 oder neuer innerhalb des von Nextcloud 34 unterstützten Bereichs
- Laufzeitbasis: `localbase`
- App-ID und Installationsordner: `orgsuite`

## Installation

OrgSuite ist mitgelieferte Infrastruktur und kein separates AD-Fachprodukt. Der Produktinstaller aktiviert sie automatisch ab zwei aktiven AD-Fachprodukten. Bei einer Einzelinstallation bleibt sie deaktiviert; die Fachapp besitzt dann ihren eigenen Einstieg und Adminabschnitt.

Der AD-Produktkatalog enthält außerdem den BQ-Planer als navigierbares
Entwicklungsprodukt. Seine derzeit deaktivierten Bundle-Flags nehmen ihn noch
nicht in die aktuellen Release-Artefakte auf.

Nach der Aktivierung werden Organisationsdefinition und Freigaben im Nextcloud-Adminbereich der OrgSuite gepflegt. Persistenz, geschützte Admin-API und Organisationseditor liegen in LocalBase.

Im selben Adminabschnitt können Nextcloud-Admins zusätzliche externe
HTTPS-Links für das AD- und BR-Menü anlegen, sortieren, aktivieren und
entfernen. Diese Linkkonfiguration liegt in OrgSuite; sie erweitert keine
Rechte in Nextcloud oder im externen Zielsystem.

## Roadmap

Geplante Erweiterungen und offene Produktentscheidungen stehen in der [Roadmap](ROADMAP.md).

Für die manuelle Staging-Prüfung von Haupteinstiegen, Quermenüs und
Adminadapter steht ein ausfüllbares
[Abnahmeformular](docs/manual-acceptance.md) bereit. Es prüft ausdrücklich,
dass OrgSuite keine Fachrechte erteilt und keine Fachdaten hält.

Installations-, Betriebs- und Abnahmeunterlagen stehen im öffentlichen [AD-Suite-Projekt](https://github.com/Filzmann/ad-suite).

## Dokumentation

- [Architektur](docs/architecture.md)
- [Manuelle Abnahme](docs/manual-acceptance.md)
- [Roadmap](ROADMAP.md)
- [Changelog](CHANGELOG.md)
- [Arbeitsregeln](AGENTS.md)
