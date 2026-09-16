# Manuelles Abnahmeformular – OrgSuite

Dieses Formular dokumentiert die fachliche, visuelle und sicherheitsbezogene
Abnahme der gemeinsamen AD-/BR-Navigation und des administrativen
OrgSuite-Einstiegs auf einem realitätsnahen Staging-System. OrgSuite enthält
keine Fachdaten und erteilt keine Rechte in Zielapps.

Pro Prüffall wird genau ein Ergebnis markiert. Keine Passwörter, Tokens,
personenbezogenen Echtdaten, vollständigen Mitgliederlisten oder internen
Kennungen eintragen. Ausschließlich neutrale Testkonten und synthetische
Organisationsdaten verwenden.

## Kopfdaten

| Feld | Eintrag |
|---|---|
| Datum und Uhrzeit | |
| Prüfer*in | |
| Umgebung und URL | |
| OrgSuite-Version | |
| LocalBase-Version | |
| Nextcloud-Version | |
| Browser und Version | |
| Fenstergröße / Zoom | |
| Neutrale Testkonten und Zielapp-Rechte | |
| Aktivierte AD- und BR-Apps | |

Ergebniskennzeichnung: `[ ] erfolgreich` / `[ ] nicht erfolgreich` /
`[ ] nicht geprüft`. Bei „nicht erfolgreich“ oder „nicht geprüft“ ist eine
Begründung verpflichtend.

## A. Haupteinstiege und Weiterleitung

| ID | Was wird geprüft? | Auszuführende Schritte | Erwartetes Ergebnis | Ergebnis | Warum/Beleg/Abweichung |
|---|---|---|---|---|---|
| A1 | AD-Einstieg | Mindestens eine aktuelle AD-Zielapp aktivieren und den Nextcloud-Appbereich mit einem berechtigten Testkonto öffnen. | Genau ein Haupteinstieg `AD` erscheint und führt zu einer aktivierten, für das Konto nutzbaren Zielapp. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| A2 | BR-Einstieg | Mindestens eine BR-Zielapp aktivieren und denselben Weg prüfen. | Genau ein Haupteinstieg `BR` erscheint und führt zu einer aktivierten, nutzbaren BR-App. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| A3 | Bevorzugtes AD-Ziel | AD Kalender zusammen mit einer weiteren AD-App aktivieren und `AD` öffnen. | AD Kalender wird als bevorzugtes Ziel verwendet. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| A4 | AD-Fallback | Das bevorzugte AD-Ziel deaktivieren und den Einstieg erneut öffnen. | Die erste noch aktivierte Zielapp wird verwendet; es entsteht keine Schleife oder Fehlerseite. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| A5 | Bevorzugtes BR-Ziel und Fallback | BRTop mit weiterer BR-App prüfen, danach BRTop deaktivieren und wiederholen. | Zuerst wird BRTop verwendet, danach eine aktive BR-Fallback-App. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| A6 | Keine aktive Zielapp | Für eine Suite alle Zielapps deaktivieren und Navigation sowie direkte Suite-Route prüfen. | Der betreffende Haupteinstieg wird nicht angeboten; die Route leitet nicht auf eine deaktivierte App. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| A7 | Einzelproduktzustand | Eine AD-Installation mit genau einem Fachprodukt gemäß Installervertrag prüfen. | OrgSuite bleibt deaktiviert; das Fachprodukt besitzt seinen eigenen Standalone-Einstieg. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |

## B. Quermenü in Fachapps

| ID | Was wird geprüft? | Auszuführende Schritte | Erwartetes Ergebnis | Ergebnis | Warum/Beleg/Abweichung |
|---|---|---|---|---|---|
| B1 | Zentrale Menüliste | In mehreren AD- und BR-Fachapps die angebotenen Quermenüs vergleichen. | Links und Reihenfolge stammen erkennbar aus OrgSuite; Fachapps zeigen keine abweichenden duplizierten Linklisten. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| B2 | Aktuelle App | Jede aktivierte Zielapp nacheinander öffnen. | Genau der aktuelle Link trägt `aria-current="page"` und eine verständliche sichtbare Markierung. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| B3 | Deaktivierte Zielapp | Eine Zielapp deaktivieren und die verbleibenden Fachapps neu laden. | Der nicht nutzbare Link verschwindet beziehungsweise wird nicht als aktives Ziel angeboten. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| B4 | Sticky und Scrollvertrag | Eine lange Fachansicht vertikal scrollen und zusätzlich eine breite Tabelle horizontal bewegen. | Das Quermenü bleibt innerhalb des App-Scrollcontainers oben sichtbar und erzeugt keinen globalen zweiten Scrollbereich. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| B5 | Schmales Fenster | Menü bei kleinem Viewport und vergrößertem Browserzoom prüfen. | Das Menü darf kompakt umbrechen; Links bleiben lesbar und bedienbar, ohne Fachinhalte zu überdecken. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| B6 | Tastatur und Fokus | Alle Menüpunkte nur mit Tastatur durchlaufen und aktivieren. | Fokus ist sichtbar, Reihenfolge ist nachvollziehbar und jedes Ziel lässt sich ohne Zeiger bedienen. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |

## C. Fachrechte bleiben in den Zielapps

| ID | Was wird geprüft? | Auszuführende Schritte | Erwartetes Ergebnis | Ergebnis | Warum/Beleg/Abweichung |
|---|---|---|---|---|---|
| C1 | Sichtbarer Link ohne Fachrecht | Mit einem Testkonto einen sichtbaren Zielapp-Link aktivieren, für dessen Fachfunktion das Konto keine Berechtigung besitzt. | Die Zielapp verweigert den Zugriff serverseitig; OrgSuite erweitert das Fachrecht nicht. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| C2 | Direkter Zielaufruf | Dieselbe nicht erlaubte Zielroute und einen Ziel-API-Endpunkt direkt aufrufen. | Beide Aufrufe werden von der Zielapp abgewiesen; Navigation ist keine Zugriffskontrolle. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| C3 | OrgSuite ohne Fachdaten | OrgSuite-Routen, Oberfläche und administrativen Bereich auf Sitzungs-, Urlaubs-, Bewerbungs- oder andere Fachdaten prüfen. | OrgSuite hält und zeigt keine Fachdaten der Zielapps. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| C4 | Fehler einer Zielapp | Eine Zielapp in einer isolierten Testumgebung gezielt nicht erreichbar machen und eine andere Zielapp öffnen. | Die unabhängige Zielapp bleibt erreichbar; der Fehler erteilt keine Rechte und verändert keine Fachdaten. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |

## D. Administrativer Einstieg

| ID | Was wird geprüft? | Auszuführende Schritte | Erwartetes Ergebnis | Ergebnis | Warum/Beleg/Abweichung |
|---|---|---|---|---|---|
| D1 | Adminadapter | Als Nextcloud-Admin mit mehreren AD-Produkten den OrgSuite-Adminabschnitt öffnen. | Die von LocalBase bereitgestellte Organisations- und Freigabeoberfläche erscheint einmal im OrgSuite-Kontext. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D2 | Nichtadmin-Deny | Als Nichtadmin Adminabschnitt und direkte administrative Lese- sowie Schreibaufrufe versuchen. | Der Zugriff wird serverseitig verweigert; keine LocalBase-Konfiguration ändert sich. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D3 | CSRF-Schutz | Einen schreibenden Adminaufruf mit Sitzung, aber ohne gültiges Requesttoken wiederholen. | Der Request wird abgewiesen und die bestehende Konfiguration bleibt unverändert. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D4 | App-spezifische Einstellungen | Adminbereich auf Kalenderprovider-, Raum- oder andere nur eine Fachapp betreffende Einstellungen prüfen. | App-spezifische Administration bleibt im eigenen Fachapp-Abschnitt und wird nicht in OrgSuite dupliziert. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D5 | Visuelle Diagrammordnung | Organigrammkarten in LocalBase über den OrgSuite-Adminadapter horizontal umordnen und danach fachliche Rollenreihenfolge sowie Rechte prüfen. | Nur die Darstellung ändert sich; fachliche Reihenfolge, Kalender und Berechtigungen bleiben unverändert. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D6 | Datensparsame Abnahme | Formular und Screenshots prüfen. | Es wurden ausschließlich synthetische Organisationsdaten dokumentiert; keine Secrets oder realen Mitgliederlisten sind enthalten. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D7 | Externe AD-/BR-Links | Je einen synthetischen HTTPS-Link für AD und BR anlegen, sortieren, deaktivieren, wieder aktivieren und speichern. | Der gespeicherte Stand bleibt nach Neuladen erhalten; nur aktive Links erscheinen in richtiger Suite und Reihenfolge. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D8 | Linkvalidierung und Bestandsschutz | HTTP-, JavaScript-, URL-mit-Zugangsdaten-, doppelte-ID- und ungültige Suite-Eingaben versuchen. | Die Eingaben werden verständlich abgewiesen und die zuvor gültige Liste bleibt vollständig unverändert. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D9 | Linkadministration schützen | Externe Links als Nichtadmin lesen und speichern sowie als Admin ohne CSRF-Token schreiben. | Alle unzulässigen Requests werden serverseitig abgewiesen; die Linkliste bleibt unverändert. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |
| D10 | Tastatur und Ausgabe | Linkverwaltung und die resultierenden AD-/BR-Menüs nur per Tastatur bedienen. | Felder und Sortier-/Entfernaktionen besitzen verständliche Beschriftungen und sichtbaren Fokus; benutzerdefinierte Bezeichnungen werden sicher als Text ausgegeben und Links öffnen im selben Tab. | [ ] erfolgreich [ ] nicht erfolgreich [ ] nicht geprüft | |

## Automatisierter Nachweis vom 22.08.2026

Service-, Controller-, Listener- und JavaScript-Smokes belegen atomare
AppConfig-Persistenz, HTTPS-Validierung, Deny/CSRF-Grenzen, aktive AD- und
BR-Menüausgabe, sichere Textausgabe sowie semantische und fokussierbare
Adminbedienelemente. Ein selbstbereinigender Nextcloud-34-HTTP-Smoke hat zwei
synthetische Links gespeichert, in beiden ausgelieferten Menüs nachgewiesen,
Nichtadmin und fehlenden CSRF-Token abgewiesen und anschließend den vorherigen
AppConfig-Stand wiederhergestellt. Das temporäre lokale Standardkonto wurde
entfernt. Die visuelle manuelle Staging-Abnahme bleibt getrennt offen.

## Automatisierter lokaler Nachweis vom 11.09.2026

Im Rahmen der risikoarmen Luna-Prüfung wurden aus dem Repository-Root nur die
lokalen, nicht mutierenden Prüfungen ausgeführt:

| Prüfung | Ergebnis | Aussagegrenze |
|---|---|---|
| `php tests/run.php` | erfolgreich | PHP-Syntax sowie OrgSuite-Service-, Controller-, Navigation-, Asset- und Entkopplungsverträge sind grün. |
| `node tests/run-js.mjs` | erfolgreich | JavaScript- und externe-Link-Smokes sind grün. |
| `git diff --check` | erfolgreich | Keine Whitespace-Fehler im aktuellen Arbeitsbaum. |

DDEV, `occ`, Installation, App-Aktivierung und fachliche Staging-Daten wurden
nicht verändert. Dieser Nachweis ersetzt weder die visuelle Staging-Abnahme
noch eine fachliche, sicherheitsbezogene oder releasebezogene
Gesamtentscheidung.

## Abschlussentscheidung

| Feld | Eintrag |
|---|---|
| Anzahl erfolgreich | |
| Anzahl nicht erfolgreich | |
| Anzahl nicht geprüft | |
| Kritische Abweichungen / Ticketreferenzen | |
| Erneute Prüfung erforderlich bis | |
| Gesamtentscheidung | [ ] abgenommen [ ] mit Auflagen abgenommen [ ] nicht abgenommen |
| Begründung der Gesamtentscheidung | |
| Name / Datum | |
