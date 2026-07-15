<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$info = file_get_contents($root . '/appinfo/info.xml');
$routes = file_get_contents($root . '/appinfo/routes.php');
$setting = file_get_contents($root . '/lib/Settings/Admin.php');
foreach ([$info, $routes, $setting] as $source) if ($source === false) throw new RuntimeException('Admin-Vertragsdatei konnte nicht gelesen werden.');

foreach (['<admin>OCA\OrgSuite\Settings\Admin</admin>', '<admin-section>OCA\OrgSuite\Settings\AdminSection</admin-section>'] as $contract) if (!str_contains($info, $contract)) throw new RuntimeException("Admin-Registrierung fehlt: {$contract}");
if (str_contains($info, '<app>')) throw new RuntimeException('Nicht unterstützte App-Abhängigkeit im OrgSuite-Manifest.');
if (str_contains($routes, '/api/admin/')) throw new RuntimeException('OrgSuite besitzt noch einen parallelen Admin-Datenpfad.');
foreach (["new TemplateResponse('localbase', 'organization-admin'", "'standalone' => false"] as $contract) if (!str_contains($setting, $contract)) throw new RuntimeException("LocalBase-Adminadapter fehlt: {$contract}");

echo "AdminSettingsContractTest: OK\n";
