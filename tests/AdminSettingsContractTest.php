<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$info = file_get_contents($root . '/appinfo/info.xml');
$routes = file_get_contents($root . '/appinfo/routes.php');
$controller = file_get_contents($root . '/lib/Controller/AdminApiController.php');
$template = file_get_contents($root . '/templates/admin.php');
foreach ([$info, $routes, $controller, $template] as $source) if ($source === false) throw new RuntimeException('Admin-Vertragsdatei konnte nicht gelesen werden.');

foreach (['<app>localbase</app>', '<admin>OCA\OrgSuite\Settings\Admin</admin>', '<admin-section>OCA\OrgSuite\Settings\AdminSection</admin-section>'] as $contract) {
    if (!str_contains($info, $contract)) throw new RuntimeException("Admin-Registrierung fehlt: {$contract}");
}
foreach (['/api/admin/settings', '/api/admin/organization', '/api/admin/permissions'] as $contract) {
    if (!str_contains($routes, $contract)) throw new RuntimeException("Admin-Route fehlt: {$contract}");
}
if (preg_match('/#\[[^\]]*NoAdminRequired/', $controller)) throw new RuntimeException('Admin-Controller ist für normale Nutzer*innen freigegeben.');
if (preg_match('/#\[[^\]]*NoCSRFRequired[^\]]*\]\s+public function save/', $controller)) throw new RuntimeException('Schreibender Admin-Endpunkt umgeht den CSRF-Schutz.');
foreach (['private function isAdmin()', '$this->groups->isAdmin(', 'Http::STATUS_FORBIDDEN', 'saveOrganization', 'savePermissions'] as $contract) {
    if (!str_contains($controller, $contract)) throw new RuntimeException("Serverseitiger Admin-Vertrag fehlt: {$contract}");
}
foreach (['id="orgsuite-admin"', 'id="orgs-organization-form"', 'id="orgs-permissions-form"', 'ausschließlich hier im Nextcloud-Adminbereich'] as $contract) {
    if (!str_contains($template, $contract)) throw new RuntimeException("Admin-UI-Vertrag fehlt: {$contract}");
}
foreach (["\\OCP\\Util::addScript('orgsuite', 'components/hierarchy-board')", "\\OCP\\Util::addScript('orgsuite', 'components/organization-editor')", "\\OCP\\Util::addStyle('orgsuite', 'admin')"] as $contract) {
    if (!str_contains($template, $contract)) throw new RuntimeException("Admin-Assetvertrag fehlt: {$contract}");
}
if (preg_match('/^\\s*(?:script|style)\\s*\\(/m', $template) === 1) throw new RuntimeException('Veralteter globaler Templatehelfer gefunden.');
echo "AdminSettingsContractTest: OK\n";
