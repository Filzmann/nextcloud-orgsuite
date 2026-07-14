# AGENTS.md - OrgSuite

## Projekt

Nextcloud-App `orgsuite` fuer die gemeinsame Navigation der fachlich getrennten AD- und BR-Apps sowie den administrativen Einstieg für organisationsweite Suite-Einstellungen.

Lokale Einstiegspunkte:

    https://nextcloud-dev.ddev.site/apps/orgsuite/ad
    https://nextcloud-dev.ddev.site/apps/orgsuite/br

Nextcloud-App-ID:

    orgsuite

## Zielsetzung

OrgSuite stellt genau zwei Haupteinstiege im Nextcloud-Appmenue bereit:

- `AD` fuer AD Kalender, Assistenzplanung, AD Urlaub und AD Raumplaner.
- `BR` fuer BRTop, BR-Stunden und Berechtigungsmatrix.

Die Fachapps bleiben eigenstaendige Repositories, Datenmodelle und Berechtigungsraeume. OrgSuite besitzt keine Fachdaten und erweitert keine fachlichen Rechte. Zielapps erzwingen ihre Berechtigungen weiterhin serverseitig. OrgSuite stellt ausschließlich den Nextcloud-Adminadapter für gemeinsame, in LocalBase persistierte Organisations- und Freigabeverträge bereit.

## Navigationsvertrag

- Die Haupteinstiege werden dynamisch registriert und nur angezeigt, wenn mindestens eine Zielapp fuer die angemeldete Person aktiviert ist.
- `AD` leitet bevorzugt zum AD Kalender weiter, `BR` bevorzugt zu BRTop. Ist das bevorzugte Ziel nicht aktiviert, wird die erste aktivierte Fachapp der Suite verwendet.
- Jede Fachapp bindet `js/suite-navigation.js` und `css/suite-navigation.css` ein und stellt genau einen Host mit `data-orgsuite`, `data-suite` und `data-current-app` bereit.
- Die Menuestruktur wird ausschliesslich hier gepflegt. Fachapps duplizieren keine Linklisten oder Menuelogik.
- Ein sichtbarer Link ist keine Berechtigung. Jeder Zielcontroller und jede API prueft Zugriffe selbst.

## Git- und Arbeitsregeln

- Dieses Verzeichnis ist ein eigenstaendiges Git-Repository.
- Vor Commits `git status --short`, `git diff --stat` und `git diff --name-only` pruefen.
- Dateien gezielt stagen; niemals `git add .`.
- Keine Fachdaten in diese App verschieben. Organisationsdefinitionen und Freigaben werden hier administriert, ihre Persistenz und ihr gemeinsamer Vertrag bleiben jedoch in LocalBase; die Fachapps lesen und erzwingen ihre Rechte weiterhin selbst.

## Architektur und UI

- Controller bleiben duenn und leiten nur zu aktivierten Zielapps weiter.
- Nextcloud-Navigation, Suite-Menue und Definitionen bleiben getrennt von den Fachapps.
- Das Suite-Menue verwendet semantisches `nav`, eine sichtbare Fokusmarkierung und `aria-current="page"`.
- Das Menue bleibt kompakt, darf umbrechen und darf den Scrollvertrag der einbettenden App nicht veraendern.
- Das Quermenü bleibt innerhalb des jeweiligen App-Scrollcontainers am oberen Rand sticky sichtbar und besitzt dafür einen deckenden Nextcloud-Hintergrund. Es verändert keine globalen Nextcloud-Container.
- Keine globalen Nextcloud- oder `body`-Selektoren ueberschreiben.
- Organisationsweite Einstellungen werden ausschließlich über `OCP\Settings\ISettings` im Nextcloud-Adminbereich angezeigt. Normale App-Einstellungen sind persönliche Einstellungen des eingeloggten Kontos.
- Administrative API-Endpunkte verzichten auf `NoAdminRequired`, prüfen die aktive Sitzung zusätzlich explizit auf Nextcloud-Adminrechte und behalten den CSRF-Schutz für Schreibzugriffe bei.

## Tests

Schnelle Pruefungen:

    php tests/run.php
    node tests/run-js.mjs
    ORGS_ADMIN_USER=... ORGS_ADMIN_PASSWORD=... tests/http-smoke.sh

Bei Aenderungen an Routen, Dependency Injection oder Navigationsregistrierung zusaetzlich in DDEV pruefen:

    ddev exec -d /var/www/html/html php occ status
    ddev exec -d /var/www/html/html php occ app:list | grep -i orgsuite
