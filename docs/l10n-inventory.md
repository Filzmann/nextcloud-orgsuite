# L10N-Inventar – OrgSuite (orgsuite)

Stand: migriert (ZM-06). Quellsprache ist Deutsch (Suite-Konvention); sichtbare
Texte sind mit `IL10N::t()` bzw. `window.t('orgsuite', …)` markiert; die
Kataloge `l10n/en.json`/`en.js` und `l10n/en_GB.json`/`en_GB.js` liefern
Englisch. Ohne Katalogeintrag greift der deutsche Quelltext; die Locale kommt
aus Nextcloud, fachlicher Fallback `de-DE`.

## Unveränderte Maschinenverträge

- App-IDs, Routen, interne Schlüssel (`app:…`, `external:…`), DOM-IDs,
  CSS-Klassen, externe Link-IDs/-URLs und die gespeicherte Menüreihenfolge
  bleiben sprachunabhängig.
- Admin-konfigurierte Linkbezeichnungen und die FLZ-Menübezeichnung sind
  Nutzerdaten und werden nicht übersetzt.

## Sichtbare Texte

- `js/suite-navigation.js` (bereits `window.t`), `js/suite-label-admin.js`,
  `js/external-links-admin.js`: alle UI-Texte via `translate('orgsuite', …)`.
- `lib/Listener/SuiteAssetsListener.php` (`BR-Anwendungen`),
  `lib/Service/SuiteTargetCatalog.php` (eigene Konstant-Labels `Sitzungen`,
  `Stunden`, `Kapazität`), `lib/Settings/AdminSection.php` (`Filzmann Hub`),
  `lib/Controller/*AdminController.php` (Fehlermeldungen inkl.
  Service-Validierungsmeldungen via `t($error->getMessage())`),
  `appinfo/info.xml` (`name`/`summary`/`description`).

`tests/Ui/L10nCatalogContractTest.php` erzwingt, dass jeder Quelltext
(inkl. `InvalidArgumentException`-Meldungen) in jedem Katalog vorhanden ist.
