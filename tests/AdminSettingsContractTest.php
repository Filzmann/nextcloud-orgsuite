<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$info = file_get_contents($root . '/appinfo/info.xml');
$routes = file_get_contents($root . '/appinfo/routes.php');
$setting = file_get_contents($root . '/lib/Settings/Admin.php');
$controller = file_get_contents($root . '/lib/Controller/ExternalLinkAdminController.php');
$service = file_get_contents($root . '/lib/Service/ExternalLinkSettingsService.php');
$labelController = file_get_contents($root . '/lib/Controller/SuiteLabelAdminController.php');
$labelService = file_get_contents($root . '/lib/Service/SuiteLabelSettingsService.php');
$httpSmoke = file_get_contents($root . '/tests/http-smoke.sh');
foreach ([$info, $routes, $setting, $controller, $service, $labelController, $labelService, $httpSmoke] as $source) if ($source === false) throw new RuntimeException('Admin-Vertragsdatei konnte nicht gelesen werden.');

foreach (['<admin>OCA\OrgSuite\Settings\Admin</admin>', '<admin-section>OCA\OrgSuite\Settings\AdminSection</admin-section>'] as $contract) if (!str_contains($info, $contract)) throw new RuntimeException("Admin-Registrierung fehlt: {$contract}");
if (!str_contains($info, '<name>Filzmann Hub</name>')) throw new RuntimeException('Die installierbare Infrastruktur-App ist öffentlich nicht eindeutig als Filzmann Hub benannt.');
if (str_contains($info, '<app>')) throw new RuntimeException('Nicht unterstützte App-Abhängigkeit im OrgSuite-Manifest.');
foreach (['external_link_admin#settings', 'external_link_admin#save', '/api/admin/external-links'] as $contract) if (!str_contains($routes, $contract)) throw new RuntimeException("Externe-Link-Adminroute fehlt: {$contract}");
foreach (['suite_label_admin#settings', 'suite_label_admin#save', '/api/admin/suite-label'] as $contract) if (!str_contains($routes, $contract)) throw new RuntimeException("Suite-Bezeichnungs-Adminroute fehlt: {$contract}");
foreach (["new TemplateResponse('localbase', 'organization-admin'", "'standalone' => false"] as $contract) if (!str_contains($setting, $contract)) throw new RuntimeException("LocalBase-Adminadapter fehlt: {$contract}");
foreach (["addScript('orgsuite', 'external-links-admin')", "addStyle('orgsuite', 'external-links-admin')"] as $contract) if (!str_contains($setting, $contract)) throw new RuntimeException("Externe-Link-Adminasset fehlt: {$contract}");
if (!str_contains($setting, "addScript('orgsuite', 'suite-label-admin')")) throw new RuntimeException('Suite-Bezeichnungs-Adminasset fehlt.');
if (str_contains($controller, 'NoAdminRequired') || str_contains($controller, 'NoCSRFRequired')) throw new RuntimeException('Externe-Link-Adminendpunkte schwächen Nextclouds Schutzattribute ab.');
foreach (['IUserSession', 'IGroupManager', 'isAdmin', 'ExternalLinkSettingsService'] as $contract) if (!str_contains($controller, $contract)) throw new RuntimeException("Expliziter Adminschutz fehlt: {$contract}");
foreach (['IAppConfig', 'external_links', 'VERSION', "['flz', 'br']", 'https', 'openMode', "'links' =>", "'order' =>"] as $contract) if (!str_contains($service, $contract)) throw new RuntimeException("Persistenz- oder Validierungsvertrag fehlt: {$contract}");
if (str_contains($service, 'navigation_order')) throw new RuntimeException('Links und Reihenfolge dürfen nicht über getrennte AppConfig-Schlüssel persistiert werden.');
if (str_contains($labelController, 'NoAdminRequired') || str_contains($labelController, 'NoCSRFRequired')) throw new RuntimeException('Suite-Bezeichnungs-Endpunkte schwächen Nextclouds Schutzattribute ab.');
foreach (['IUserSession', 'IGroupManager', 'isAdmin', 'SuiteLabelSettingsService'] as $contract) if (!str_contains($labelController, $contract)) throw new RuntimeException("Expliziter Schutz der Suite-Bezeichnung fehlt: {$contract}");
foreach (['IAppConfig', 'flz_menu_label', 'MAX_LENGTH'] as $contract) if (!str_contains($labelService, $contract)) throw new RuntimeException("Persistenz- oder Validierungsvertrag der Suite-Bezeichnung fehlt: {$contract}");
if (str_contains($httpSmoke, 'random_bytes') || !str_contains($httpSmoke, 'OC_PASS="$nonadmin"')) throw new RuntimeException('Das lokale OrgSuite-Testkonto verwendet nicht Benutzername = Passwort.');

echo "AdminSettingsContractTest: OK\n";
