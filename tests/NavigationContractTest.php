<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$info = file_get_contents($root . '/appinfo/info.xml');
$routes = file_get_contents($root . '/appinfo/routes.php');
$listener = file_get_contents($root . '/lib/Listener/NavigationListener.php');
$controller = file_get_contents($root . '/lib/Controller/EntryController.php');

foreach ([$info, $routes, $listener, $controller] as $source) {
    if ($source === false) {
        throw new RuntimeException('OrgSuite-Vertragsdatei konnte nicht gelesen werden.');
    }
}
if (str_contains($info, '<navigations>')) {
    throw new RuntimeException('Suite-Einstiege muessen benutzerbezogen registriert werden.');
}
foreach (["'entry#ad'", "'entry#br'"] as $contract) {
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

echo "OrgSuite navigation contract passed\n";

