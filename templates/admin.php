<?php
script('localbase', 'api/api-client');
script('localbase', 'ui/ui');
script('orgsuite', 'components/hierarchy-board');
script('orgsuite', 'components/organization-editor');
script('orgsuite', 'admin');
style('orgsuite', 'admin');
?>
<section id="orgsuite-admin" class="section orgs-admin" aria-labelledby="orgs-admin-heading">
    <h2 id="orgs-admin-heading">AD-/BR-Suite</h2>
    <p>Organisationsweite Einstellungen sind ausschließlich hier im Nextcloud-Adminbereich änderbar. Einstellungen in den Fachapps gelten nur für das jeweils eingeloggte Konto.</p>
    <div id="orgs-admin-notice" class="orgs-notice" role="status" aria-live="polite" aria-atomic="true" hidden></div>

    <section class="orgs-panel" aria-labelledby="orgs-organization-heading">
        <h3 id="orgs-organization-heading">AD-Organisation</h3>
        <p>Diese Konfiguration steuert Gruppen, sichtbare Namen, Bereiche, Hierarchie und Urlaubsansichten in den AD-Fachapps.</p>
        <p><strong>Wichtig:</strong> Änderungen an Gruppen-IDs verschieben keine bestehenden Nextcloud-Mitgliedschaften. Zielgruppen und Mitgliedschaften müssen vor der Umstellung vorbereitet werden.</p>
        <form id="orgs-organization-form">
            <div id="orgs-organization-editor"></div>
            <button type="submit" class="primary">Organisation speichern</button>
        </form>
    </section>

    <section class="orgs-panel" aria-labelledby="orgs-permissions-heading">
        <h3 id="orgs-permissions-heading">Zusätzliche Rechte innerhalb von Fachgruppen</h3>
        <form id="orgs-permissions-form">
            <fieldset>
                <legend>AD Kalender</legend>
                <p>Aktivierte Kolleg*innen dürfen innerhalb derselben Fachgruppe Kalenderdaten bearbeiten; Büro- und EB-Rechte bleiben auf gemeinsame Bürobereiche begrenzt.</p>
                <div id="orgs-calendar-peer-settings" class="orgs-checkbox-grid"></div>
            </fieldset>
            <fieldset>
                <legend>AD Urlaub</legend>
                <p>Aktivierte Kolleg*innen dürfen innerhalb derselben Fachgruppe geplante Urlaube genehmigen. Eigene Genehmigungen bleiben gesperrt.</p>
                <div id="orgs-vacation-peer-settings" class="orgs-checkbox-grid"></div>
            </fieldset>
            <button type="submit" class="primary">Rechte speichern</button>
        </form>
    </section>
</section>
