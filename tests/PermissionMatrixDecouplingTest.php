<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$runtimeFiles = [
    $root . '/lib/Controller/EntryController.php',
    $root . '/lib/Listener/NavigationListener.php',
    $root . '/lib/Listener/SuiteAssetsListener.php',
];

foreach ($runtimeFiles as $file) {
    $source = file_get_contents($file);
    if ($source === false) {
        throw new RuntimeException('OrgSuite-Laufzeitdatei ist nicht lesbar: ' . $file);
    }
    foreach (['br_permission_matrix', 'flz_permission_matrix'] as $matrixAppId) {
        if (str_contains($source, $matrixAppId)) {
            throw new RuntimeException('OrgSuite führt die eigenständige Berechtigungsmatrix weiterhin als BR-Ziel: ' . $matrixAppId);
        }
    }
}

echo "OrgSuite permission matrix decoupling test passed\n";

