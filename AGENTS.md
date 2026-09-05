# AGENTS.md - OrgSuite

## Projekt

Nextcloud-App `orgsuite` für die gemeinsame Navigation der fachlich getrennten AD- und BR-Apps sowie den administrativen Einstieg für app-übergreifende Suite-Einstellungen.

Lokale Einstiegspunkte:

    https://nextcloud-dev.ddev.site/apps/orgsuite/ad
    https://nextcloud-dev.ddev.site/apps/orgsuite/br

Nextcloud-App-ID:

    orgsuite

Die priorisierte Produktplanung und offene Entscheidungen stehen in `ROADMAP.md`; verbindliche Fach-, Sicherheits- und Architekturregeln bleiben in dieser Datei.

## Zielsetzung

OrgSuite stellt genau zwei Haupteinstiege im Nextcloud-Appmenue bereit:

- `AD` fuer AD Kalender, Assistenzplanung, AD Urlaub, AD Raumplaner, AD
  Recruitment und den BQ-Planer. Der BQ-Planer bleibt bis zur Release-Reife
  durch seine Katalogflags aus Auslieferungsbundles ausgeschlossen.
- `BR` fuer BRTop und BR-Stunden. Die eigenständige Berechtigungsmatrix
  gehört zum Portfolio IKT/Datenschutz und ist kein OrgSuite-Ziel.

Die Fachapps bleiben eigenständige Repositories, Datenmodelle und Berechtigungsräume. OrgSuite besitzt keine Fachdaten und erweitert keine fachlichen Rechte. Zielapps erzwingen ihre Berechtigungen weiterhin serverseitig. OrgSuite stellt ab zwei AD-Fachprodukten ausschließlich Navigation, gemeinsame Assets und den Nextcloud-Adminadapter für in LocalBase persistierte Organisations- und Freigabeverträge bereit. Einstellungen, die nur eine Fachapp betreffen, erhalten einen eigenen Adminabschnitt in dieser Fachapp.

## Navigationsvertrag

- Die Haupteinstiege werden dynamisch registriert und nur angezeigt, wenn mindestens eine Zielapp fuer die angemeldete Person aktiviert ist.
- `AD` leitet bevorzugt zum AD Kalender weiter, `BR` bevorzugt zu BRTop. Ist das bevorzugte Ziel nicht aktiviert, wird die erste aktivierte Fachapp der Suite verwendet. AD-Ziele und ihre Reihenfolge stammen aus dem versionierten LocalBase-Produktkatalog.
- OrgSuite lädt `js/suite-navigation.js` und `css/suite-navigation.css` zentral über `BeforeTemplateRenderedEvent`. Fachapps stellen nur einen wirkungslosen Host mit `data-orgsuite`, `data-suite` und `data-current-app` bereit und besitzen dadurch keine harte Asset-Abhängigkeit.
- Die Menuestruktur wird ausschliesslich hier gepflegt. Fachapps duplizieren keine Linklisten oder Menuelogik.
- OrgSuite führt weder die historische noch die aktuelle App-ID der
  Berechtigungsmatrix als BR-Ziel, Weiterleitungsziel oder Quermenüeintrag.
- Nextcloud-Admins verwalten zusätzliche externe Links für AD und BR in der
  OrgSuite-Administration. OrgSuite speichert stabile ID, Suite,
  Bezeichnung, HTTPS-URL, Aktivstatus und Reihenfolge in der eigenen
  AppConfig. Aktive Links gelten für alle angemeldeten Personen, öffnen im
  selben Tab und erteilen keine Rechte im Zielsystem. Unsichere URL-Schemata,
  Zugangsdaten in URLs, ungültige Suites und doppelte IDs werden abgelehnt,
  ohne den bisherigen Stand zu verändern.
- Ein sichtbarer Link ist keine Berechtigung. Jeder Zielcontroller und jede API prueft Zugriffe selbst.
- Der Produktinstaller aktiviert OrgSuite erst ab zwei aktivierten AD-Fachprodukten. Bei einer Einzelinstallation registriert das Fachprodukt stattdessen seinen eigenen Nextcloud-Einstieg.

## Repository und gemeinsamer Arbeitsablauf

- Dieses Verzeichnis ist ein eigenstaendiges Git-Repository.
- Diese Datei und lokal referenzierte Skills bilden bei einem direkten Start in diesem Repository die vollständige Repository-Steuerung.
- Fuer Git-, Sandbox-, DDEV-/`occ`-Sicherheit, Verifikation und Learning Candidates gilt der lokal mitgefuehrte Skill `work-in-nextcloud-app`; die folgenden OrgSuite-Regeln und Pruefungen ergaenzen ihn.
- Keine Fachdaten in diese App verschieben. Organisationsdefinitionen und Freigaben werden hier administriert, ihre Persistenz und ihr gemeinsamer Vertrag bleiben jedoch in LocalBase; die Fachapps lesen und erzwingen ihre Rechte weiterhin selbst.

## Architektur und UI

- Controller bleiben duenn und leiten nur zu aktivierten Zielapps weiter.
- Nextcloud-Navigation, Suite-Menue und Definitionen bleiben getrennt von den Fachapps.
- Das Suite-Menue verwendet semantisches `nav`, eine sichtbare Fokusmarkierung und `aria-current="page"`.
- Das Menue bleibt kompakt, darf umbrechen und darf den Scrollvertrag der einbettenden App nicht veraendern.
- Das Quermenü bleibt innerhalb des jeweiligen App-Scrollcontainers am oberen Rand sticky sichtbar und besitzt dafür einen deckenden Nextcloud-Hintergrund. Es verändert keine globalen Nextcloud-Container.
- Keine globalen Nextcloud- oder `body`-Selektoren ueberschreiben.
- App-übergreifende Organisations- und Freigabeeinstellungen werden über einen LocalBase-`ISettings`-Adapter im OrgSuite-Adminabschnitt angezeigt. Controller, Assets und Persistenz bleiben in LocalBase. App-spezifische Administration bleibt im Adminabschnitt der Fachapp; normale App-Einstellungen sind persönliche Einstellungen des eingeloggten Kontos.
- Die im LocalBase-Organigramm global gespeicherte Links-rechts-Anordnung ist ausschließlich visuell. OrgSuite darf daraus weder fachliche Rollen-/Bereichsreihenfolgen noch Kalender- oder Berechtigungswirkung ableiten.
- Administrative API-Endpunkte verzichten auf `NoAdminRequired`, prüfen die aktive Sitzung zusätzlich explizit auf Nextcloud-Adminrechte und behalten den CSRF-Schutz für Schreibzugriffe bei.
- Ausschließlich lokal erzeugte Testkonten erhalten initial ihr
  Benutzerkürzel als Passwort. Diese Testvorgabe gilt nie für Staging,
  Produktion, echte Konten oder externe Benutzer-Backends.

## Tests

Schnelle Pruefungen:

    php tests/run.php
    node tests/run-js.mjs
    ORGS_ADMIN_USER=... ORGS_ADMIN_PASSWORD=... tests/http-smoke.sh

Bei Aenderungen an Routen, Dependency Injection oder Navigationsregistrierung zusaetzlich in DDEV pruefen:

    ddev exec -d /var/www/html/html php occ status
    ddev exec -d /var/www/html/html php occ app:list | grep -i orgsuite

## Dokumentenverantwortung

- `README.md` beschreibt ausschließlich den aktuellen nutzbaren Stand,
  Installation, Betrieb, Tests und den Dokumentationsindex.
- `ROADMAP.md` enthält ausschließlich offene, zurückgestellte oder
  freigabepflichtige Arbeit und Entscheidungen.
- `CHANGELOG.md` dokumentiert erledigte Änderungen releasebezogen; erledigte
  Checklisten verbleiben nicht in der Roadmap.
- `docs/architecture.md` ist die ausführliche Quelle für geltende fachliche
  und technische Architekturverträge.
- `docs/manual-acceptance.md` enthält wiederholbare manuelle Prüfungen und
  keine Produktplanung.
- `AGENTS.md` enthält ausschließlich verbindliche Arbeits-, Sicherheits-,
  Architektur- und Prüfregeln. Zusätzliche Dokumente werden in `README.md`
  mit eindeutiger Zuständigkeit eingeordnet.

## Parent-Governance-Vertrag: 2

- Die für dieses Subrepository anwendbaren Regeln des Parent-Workspaces sind
  verbindlich. Dazu gehören insbesondere app-übergreifende ADRs und
  öffentliche Verträge, Repositorygrenzen sowie Workspace-, Delivery- und
  Release-Gates.
- Diese lokale `AGENTS.md` und die lokalen Skills bleiben die vollständige,
  ohne Parent-Checkout arbeitsfähige Repository-Steuerung. Die anwendbaren
  Parent-Regeln werden dafür hier oder in den lokalen Skills mitgeführt.
- Repository-lokale Regeln dürfen Parent-Verträge konkretisieren und verschärfen,
  aber nicht abschwächen oder umgehen.
- Bei einem Widerspruch gilt bis zur Klärung die strengere Regel. Die Arbeit
  stoppt, bis die kanonische Quelle bestimmt, die Regelprojektionen
  synchronisiert und eine erforderliche Entscheidung dokumentiert ist.
- Ist der Parent-Workspace nicht verfügbar, bleibt die lokale Steuerung
  wirksam. Vor Cross-App-, Release- oder Delivery-Arbeit muss ein vermuteter
  neuerer Parent-Stand oder eine Regelungslücke zuerst gegen den Parent
  geprüft werden.

### Entwicklungsphase und Kompatibilitätsbedarf

Entscheidung vom 5. September 2026: Das Gesamtprojekt befindet sich vollständig
in der Entwicklung. Es gibt kein PROD, keinen produktiven Datenbestand und
keinen bereits betriebenen Bestand mit zu erhaltendem Upgradepfad. STAGING
ist eine wegwerfbare Entwicklungs- und Integrationsumgebung und darf im
konkret beauftragten Reinstall vollständig neu aufgebaut werden. Wenige
externe Testnutzer ändern diese Einordnung nicht.

Vor einer Datenmigration, Legacy-Unterstützung, Compatibility Layer,
Deprecated API, Dual-Read/Dual-Write, einem Altschema-Fallback, Übergangsformat
oder der Unterstützung historischer Entwicklungsstände wird geprüft:

1. Wurde der betroffene Zustand jemals produktiv eingesetzt?
2. Benötigen reale Daten oder Nutzer seine Erhaltung?
3. Gibt es einen anderen konkreten technischen Erhaltungsgrund, insbesondere
   einen geltenden Plattform- oder externen API-Vertrag?

Sind alle relevanten Antworten nein, ist die saubere Breaking-Change-/
Reinstall-Lösung der Standard. Frühere rein interne Entwicklungsstände
begründen weder Abwärtskompatibilität noch eine Deprecationfrist.
Entwicklungsschemata dürfen durch ein kanonisches Installationsschema ersetzt,
alte interne APIs und Konfigurationsformate samt ausschließlich dafür
benötigten Adaptern und Tests entfernt werden. Architekturqualität und der
saubere Zielzustand haben Vorrang. Nextclouds nötige Installationsmigrationen
bleiben erhalten; ein Verzeichnisname `Migration` beweist keine Altlast.

Breaking Changes werden im selben Änderungskontext vollständig durchgezogen:
betroffene Provider, Consumer, standardisierte APIs, Vertragsversionen,
Metadaten, Tests und Dokumentation müssen zusammenpassen. Unterstützte
Nextcloud-/openDesk-Plattformverträge, externe Standards, Autorisierung und
Datenschutz gelten unverändert. Fehlende oder inkompatible optionale Provider
bleiben kontrolliert sichtbar. Ein Reinstall erlaubt keine privaten
Fremdtabellenzugriffe oder parallel erfundenen Plattformmechanismen.

Vor destruktiver Arbeit werden die tatsächlich benötigten externen
Testidentitäten, Gruppen, Rollen und nicht reproduzierbaren Testdaten gezielt
gesichert oder über bestehende native Setup-Strukturen reproduzierbar gemacht.
Echte Personen- und Zugangsdaten bleiben außerhalb von Git. Diese begrenzte
Sicherung begründet keine allgemeine Legacy-Unterstützung. Ein Reinstall
bleibt ein normaler unterstützter Entwicklungsweg; der vorhandene
Compatibility-Workflow besitzt den Fresh-Install-Nachweis, dessen aktueller
Belegstatus in `docs/workspace.md` beschrieben ist.

Diese Phase endet ausschließlich durch einen ausdrücklich dokumentierten,
von Simon freigegebenen **Production-Readiness-/Production-Freeze-Entscheid**.
Ein Release Candidate, eine Versionsnummer, ein Staging-Deployment oder ein
externer Testzugang lösen den Wechsel nicht aus. Der Entscheid wird in dieser
kanonischen Lifecycle-Quelle mit Datum, Geltungsbereich und betroffenem
Versions-/Datenstand festgehalten und in die lokale Steuerung projiziert.
Dann werden Upgradepfade, Datenbankmigrationen, Persistenz, Backup/Restore,
Rollback, Release-/API-Kompatibilitätszusagen, Deployment-/Freigabeprozess und
PROD→STAGING/COPY-Strategie neu bewertet. Eine vollständige PROD-Governance
wird jetzt nicht vorweggenommen.

Diese Regel entscheidet den Kompatibilitätsbedarf, erweitert aber keinen
Repository-Schreibauftrag und ersetzt keine Freigabe für eine konkrete
destruktive Aktion. Lokale Regelprojektionen folgen dem bestehenden
`docs/parent-governance-contract.md`; ein unsynchronisierter Einzel-Checkout
darf keinen abweichenden Phasenstand stillschweigend annehmen.

### Prüfaufwand

- Vor einem neuen Test, Scan, Linter, Architektur- oder Systemcheck wird
  geprüft, welcher bestehende Check dieselbe Eigenschaft bereits nachweist.
  Diesen erweitern oder sein nachweislich passendes Ergebnis wiederverwenden;
  ein zusätzlicher Check braucht eine benannte zusätzliche Fehlerklasse oder
  Vertrauensgrenze. Gleicher Input, gleiche Prüfung, gleiche Fehlerklasse und
  gleiche Phase begründen keinen zweiten Lauf. Gestaffelte Unit-, Contract-
  und Runtime-Nachweise bleiben erhalten. Die dokumentierten lokalen
  Prüfeinstiege bestimmen Umfang und Ergebnisgültigkeit.
