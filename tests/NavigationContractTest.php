<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$info = file_get_contents($root . '/appinfo/info.xml');
$routes = file_get_contents($root . '/appinfo/routes.php');
$listener = file_get_contents($root . '/lib/Listener/NavigationListener.php');
$controller = file_get_contents($root . '/lib/Controller/EntryController.php');
$targetCatalog = file_get_contents($root . '/lib/Service/SuiteTargetCatalog.php');

foreach ([$info, $routes, $listener, $controller, $targetCatalog] as $source) {
    if ($source === false) {
        throw new RuntimeException('OrgSuite-Vertragsdatei konnte nicht gelesen werden.');
    }
}
if (str_contains($info, '<navigations>')) {
    throw new RuntimeException('Suite-Einstiege muessen benutzerbezogen registriert werden.');
}
foreach (["'entry#flz'", "'entry#br'"] as $contract) {
    if (!str_contains($routes, $contract)) {
        throw new RuntimeException("Suite-Route fehlt: {$contract}");
    }
}
foreach (['isEnabledForUser', "'id' => Application::APP_ID . '-' . \$suite", "'name' => \$name"] as $contract) {
    if (!str_contains($listener, $contract)) {
        throw new RuntimeException("Navigationsvertrag fehlt: {$contract}");
    }
}
foreach (['NoAdminRequired', 'NoCSRFRequired', 'RedirectResponse', 'NotFoundResponse', 'isEnabledForUser'] as $contract) {
    if (!str_contains($controller, $contract)) {
        throw new RuntimeException("Weiterleitungsvertrag fehlt: {$contract}");
    }
}
foreach ([$listener, $controller] as $consumer) {
    if (!str_contains($consumer, 'SuiteTargetCatalog') || !str_contains($consumer, 'forSuite')) {
        throw new RuntimeException('Interne Suite-Ziele werden nicht durchgängig aus dem gemeinsamen Zielkatalog konsumiert.');
    }
}
foreach (['FlzProductCatalog', "menuProducts('flz')"] as $catalogContract) {
    if (!str_contains($targetCatalog, $catalogContract)) throw new RuntimeException("FLZ-Produktkatalog fehlt in der gemeinsamen Zielprojektion: {$catalogContract}");
}
foreach (['flzcalendar', 'flzplaner', 'flzurlaub', 'flzroom', 'flzrecruitment'] as $appId) {
    if (str_contains($listener, "'{$appId}'") || str_contains($controller, "'app' => '{$appId}'")) {
        throw new RuntimeException("FLZ-Produkt bleibt außerhalb des Katalogs festgeschrieben: {$appId}");
    }
}

echo "OrgSuite navigation contract passed\n";
