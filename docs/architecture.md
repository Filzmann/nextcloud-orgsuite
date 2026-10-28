# Architektur – Filzmann Hub (`orgsuite`)

## Verantwortung

Die technisch als `orgsuite` geführte Infrastruktur-App Filzmann Hub stellt die gemeinsamen FLZ- und BR-Einstiege, das Quermenü sowie den
Administrationsadapter für app-übergreifende Suite-Einstellungen bereit. Sie
besitzt keine Fachdaten und erweitert keine Rechte der Zielapps.

## Navigation

- FLZ-Ziele stammen aus dem versionierten LocalBase-Produktkatalog; BR-Ziele
  verbleiben im festgelegten BR-Vertrag. OrgSuite projiziert beide Quellen in
  einen lokalen Zielkatalog. Die administrierte Menüreihenfolge ordnet diese
  internen Ziele gemeinsam mit externen Links, ohne die bevorzugte
  Einstiegsweiterleitung oder Fachberechtigungen zu verändern.
- Fachapps stellen nur wirkungslose Menühosts bereit und laden keine
  OrgSuite-Assets direkt.
- Bei genau einem FLZ-Fachprodukt bleibt OrgSuite deaktiviert; ab zwei
  Produkten wird sie durch den geprüften Installer aktiviert.
- Zusätzliche externe Links sind HTTPS-basiert, zentral konfiguriert und
  erteilen keine Rechte im Zielsystem. Ihr Öffnungsmodus ist pro Link `new`
  oder `same`; `new` ist der Standard und wird mit `noopener noreferrer`
  ausgeliefert.

## Administration

Gemeinsame Organisations- und Freigabeverträge werden in LocalBase
persistiert und über einen Adapter in OrgSuite angezeigt. App-spezifische
Administration bleibt in der jeweiligen Fachapp. Administrative Requests
prüfen Nextcloud-Adminstatus und CSRF serverseitig.
Links und Reihenfolge werden erst nach vollständiger serverseitiger Prüfung
gemeinsam als ein versionierter Snapshot im bestehenden AppConfig-Wert
`external_links` persistiert. Dadurch kann kein gemischter Stand aus Linkliste
und Reihenfolge entstehen. Das frühere reine Listenformat bleibt lesbar und
wird beim nächsten administrativen Speichern in den Snapshot überführt.
Unbekannte, doppelte, fehlende oder suitefremde Sortierschlüssel werden
abgewiesen; die Oberfläche kann diese Schutzgrenze nicht ersetzen.
