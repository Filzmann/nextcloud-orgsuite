<?php
/** @var OCP\IL10N $l */
/** @var array $_ */

use OCP\IURLGenerator;
use OCP\Server;

$organizationAdminUrl = Server::get(IURLGenerator::class)->linkToRoute('flz_organization.admin.index');
?>
<section id="orgsuite-admin" class="section" aria-labelledby="orgsuite-admin-heading">
    <h2 id="orgsuite-admin-heading"><?php p($l->t('Filzmann Hub')); ?></h2>
    <p><?php p($l->t('Organisationsweite Einstellungen (Organisation, Kalenderkontext, Rechte innerhalb von Fachgruppen) werden in der flz_organization-Verwaltung gepflegt.')); ?></p>
    <p><a class="button" href="<?php print_unescaped($organizationAdminUrl); ?>"><?php p($l->t('Zur Organisationsverwaltung (flz_organization)')); ?></a></p>
    <div id="orgsuite-external-links-admin"></div>
    <div id="orgsuite-suite-label-admin"></div>
</section>
