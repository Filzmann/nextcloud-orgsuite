<?php

declare(strict_types=1);

namespace {
    use OCA\OrgSuite\Catalog\FlzProductCatalog;
    use function OCA\OrgSuite\Tests\Support\assertSameValue;
    use function OCA\OrgSuite\Tests\Support\assertThrows;

$catalog = new FlzProductCatalog();
assertSameValue(1, $catalog->version(), 'Katalogversion muss 1 sein.');
assertSameValue(
    ['localbase', 'orgsuite', 'flzcalendar', 'flzplaner', 'flzurlaub', 'flzroom', 'flzrecruitment', 'flzbqplanung', 'flz_akut'],
    array_column($catalog->entries(), 'id'),
    'Katalogeinträge müssen vollständig geladen werden.'
);
assertSameValue(
    ['flzcalendar', 'flzplaner', 'flzurlaub', 'flzroom', 'flzrecruitment', 'flzbqplanung', 'flz_akut'],
    array_column($catalog->products(), 'id'),
    'Produktliste muss nach Katalogreihenfolge sortiert sein.'
);
assertSameValue(
    array_column($catalog->menuProducts('flz'), 'id'),
    array_column($catalog->products(), 'id'),
    'Alle FLZ-Produkte müssen menüfähig sein.'
);
assertSameValue(
    ['localbase', 'orgsuite', 'flzcalendar', 'flzplaner', 'flzurlaub', 'flzroom', 'flzrecruitment', 'flz_akut'],
    $catalog->fullSuiteAppIds(),
    'Full-Suite-Liste muss Infrastruktur zuerst und flzbqplanung ausgeschlossen führen.'
);
assertSameValue(['localbase', 'orgsuite', 'flzrecruitment'], $catalog->productBundleAppIds('flzrecruitment'), 'Produktbundle muss Infrastruktur plus Produkt enthalten.');
assertThrows(static fn() => $catalog->productBundleAppIds('flzbqplanung'), InvalidArgumentException::class, 'flzbqplanung darf vor der Releasefreigabe kein Produktbundle besitzen.');
assertSameValue('flzcalendar.page.index', $catalog->product('flzcalendar')['route'], 'Produktabfrage muss den Katalogeintrag liefern.');
assertThrows(static fn() => $catalog->product('unbekannt'), InvalidArgumentException::class, 'Unbekannte Produkte müssen abgelehnt werden.');

// definition(): Datei nicht verfügbar
assertThrows(static fn() => (new FlzProductCatalog('/nicht/vorhanden.json'))->entries(), UnexpectedValueException::class, 'Fehlende Katalogdatei muss eine Ausnahme werfen.');

// definition(): ungültiges JSON und fehlende Wurzel
$tmp = tempnam(sys_get_temp_dir(), 'catalog');
file_put_contents($tmp, '{ungültig');
assertThrows(static fn() => (new FlzProductCatalog($tmp))->entries(), UnexpectedValueException::class, 'Ungültiges JSON muss eine Ausnahme werfen.');
file_put_contents($tmp, '"nur ein string"');
assertThrows(static fn() => (new FlzProductCatalog($tmp))->entries(), UnexpectedValueException::class, 'Eine nicht-objektartige Wurzel muss abgelehnt werden.');
unlink($tmp);

// validate(): Strukturfehler
$baseEntry = ['id' => 'x', 'kind' => 'infrastructure', 'suite' => null, 'order' => null, 'route' => null, 'productLabel' => null, 'navigationLabel' => null, 'standalone' => false, 'menu' => false, 'fullSuiteBundle' => true, 'productBundle' => true];
$base = ['version' => 1, 'entries' => [
    ['id' => 'localbase', 'kind' => 'infrastructure', 'suite' => null, 'order' => null, 'route' => null, 'productLabel' => null, 'navigationLabel' => null, 'standalone' => false, 'menu' => false, 'fullSuiteBundle' => true, 'productBundle' => true],
    ['id' => 'orgsuite', 'kind' => 'infrastructure', 'suite' => null, 'order' => null, 'route' => null, 'productLabel' => null, 'navigationLabel' => null, 'standalone' => false, 'menu' => false, 'fullSuiteBundle' => true, 'productBundle' => true],
]];
assertSameValue(1, FlzProductCatalog::validate($base)['version'], 'Ein gültiger Minimalkatalog muss validieren.');
assertThrows(static fn() => FlzProductCatalog::validate(['version' => 2, 'entries' => []]), UnexpectedValueException::class, 'Falsche Version muss abgelehnt werden.');
assertThrows(static fn() => FlzProductCatalog::validate(['version' => 1]), UnexpectedValueException::class, 'Fehlende Einträge müssen abgelehnt werden.');
$incomplete = $base;
$incomplete['entries'][] = ['id' => 'x'];
assertThrows(static fn() => FlzProductCatalog::validate($incomplete), UnexpectedValueException::class, 'Unvollständige Einträge müssen abgelehnt werden.');
$dup = $base;
$dup['entries'][] = $base['entries'][0];
assertThrows(static fn() => FlzProductCatalog::validate($dup), UnexpectedValueException::class, 'Doppelte IDs müssen abgelehnt werden.');
$badFlag = $base;
$badFlag['entries'][] = array_merge($baseEntry, ['id' => 'x', 'fullSuiteBundle' => 'ja']);
assertThrows(static fn() => FlzProductCatalog::validate($badFlag), UnexpectedValueException::class, 'Nicht-boolesche Flags müssen abgelehnt werden.');
$infraNav = $base;
$infraNav['entries'][] = array_merge($baseEntry, ['id' => 'x', 'menu' => true]);
assertThrows(static fn() => FlzProductCatalog::validate($infraNav), UnexpectedValueException::class, 'Infrastruktur darf keine Fachnavigation definieren.');
$product = ['id' => 'flztest', 'kind' => 'product', 'suite' => 'flz', 'order' => 10, 'route' => 'flztest.page.index', 'productLabel' => 'Test', 'navigationLabel' => 'Test', 'standalone' => true, 'menu' => true, 'fullSuiteBundle' => true, 'productBundle' => true];
$badRoute = $base;
$badRoute['entries'][] = array_merge($product, ['route' => 'andere.page.index']);
assertThrows(static fn() => FlzProductCatalog::validate($badRoute), UnexpectedValueException::class, 'Produktrouten müssen mit der App-ID beginnen.');
$unknownKind = $base;
$unknownKind['entries'][] = array_merge($product, ['kind' => 'spezial']);
assertThrows(static fn() => FlzProductCatalog::validate($unknownKind), UnexpectedValueException::class, 'Unbekannte Produktarten müssen abgelehnt werden.');
$missingInfra = ['version' => 1, 'entries' => [$product]];
assertThrows(static fn() => FlzProductCatalog::validate($missingInfra), UnexpectedValueException::class, 'Verbindliche Infrastruktur darf nicht fehlen.');
$menuOrder = $base;
$menuOrder['entries'][] = $product;
$menuOrder['entries'][] = array_merge($product, ['id' => 'flztest2', 'route' => 'flztest2.page.index']);
assertThrows(static fn() => FlzProductCatalog::validate($menuOrder), UnexpectedValueException::class, 'Doppelte Menüreihenfolgen müssen abgelehnt werden.');

echo 'FlzProductCatalogContractTest: OK' . PHP_EOL;

}
