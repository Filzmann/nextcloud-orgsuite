<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$info = file_get_contents($root . '/appinfo/info.xml');
$routes = file_get_contents($root . '/appinfo/routes.php');
$setting = file_get_contents($root . '/lib/Settings/Admin.php');
$controller = file_get_contents($root . '/lib/Controller/ExternalLinkAdminController.php');
$service = file_get_contents($root . '/lib/Service/ExternalLinkSettingsService.php');
$httpSmoke = file_get_contents($root . '/tests/http-smoke.sh');
foreach ([$info, $routes, $setting, $controller, $service, $httpSmoke] as $source) if ($source === false) throw new RuntimeException('Admin-Vertragsdatei konnte nicht gelesen werden.');

foreach (['<admin>OCA\OrgSuite\Settings\Admin</admin>', '<admin-section>OCA\OrgSuite\Settings\AdminSection</admin-section>'] as $contract) if (!str_contains($info, $contract)) throw new RuntimeException("Admin-Registrierung fehlt: {$contract}");
if (str_contains($info, '<app>')) throw new RuntimeException('Nicht unterstützte App-Abhängigkeit im OrgSuite-Manifest.');
foreach (['external_link_admin#settings', 'external_link_admin#save', '/api/admin/external-links'] as $contract) if (!str_contains($routes, $contract)) throw new RuntimeException("Externe-Link-Adminroute fehlt: {$contract}");
foreach (["new TemplateResponse('localbase', 'organization-admin'", "'standalone' => false"] as $contract) if (!str_contains($setting, $contract)) throw new RuntimeException("LocalBase-Adminadapter fehlt: {$contract}");
foreach (["addScript('orgsuite', 'external-links-admin')", "addStyle('orgsuite', 'external-links-admin')"] as $contract) if (!str_contains($setting, $contract)) throw new RuntimeException("Externe-Link-Adminasset fehlt: {$contract}");
if (str_contains($controller, 'NoAdminRequired') || str_contains($controller, 'NoCSRFRequired')) throw new RuntimeException('Externe-Link-Adminendpunkte schwächen Nextclouds Schutzattribute ab.');
foreach (['IUserSession', 'IGroupManager', 'isAdmin', 'ExternalLinkSettingsService'] as $contract) if (!str_contains($controller, $contract)) throw new RuntimeException("Expliziter Adminschutz fehlt: {$contract}");
foreach (['IAppConfig', 'external_links', "['ad', 'br']", 'https'] as $contract) if (!str_contains($service, $contract)) throw new RuntimeException("Persistenz- oder Validierungsvertrag fehlt: {$contract}");
if (str_contains($httpSmoke, 'random_bytes') || !str_contains($httpSmoke, 'OC_PASS="$nonadmin"')) throw new RuntimeException('Das lokale OrgSuite-Testkonto verwendet nicht Benutzername = Passwort.');

echo "AdminSettingsContractTest: OK\n";
