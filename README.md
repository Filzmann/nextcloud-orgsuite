# OrgSuite

Gemeinsame AD-/BR-Navigation und Nextcloud-Adminoberfläche für organisationsweite Gruppen-, Hierarchie- und Freigabeeinstellungen.

## Staging-Kompatibilität

- Nextcloud 34
- PHP 8.3 oder neuer innerhalb des von Nextcloud 34 unterstützten Bereichs
- Abhängigkeit: `localbase`
- App-ID und Installationsordner: `orgsuite`

## Installation

```bash
sudo -u www-data php occ app:enable localbase
sudo -u www-data php occ app:enable orgsuite
```

Nach der Aktivierung werden Organisationsdefinition und Freigaben ausschließlich im Nextcloud-Adminbereich der OrgSuite gepflegt.
