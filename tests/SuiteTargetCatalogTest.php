<?php

declare(strict_types=1);

namespace {
    use OCA\LocalBase\Catalog\FlzProductCatalog;
    use OCA\OrgSuite\Service\SuiteTargetCatalog;
    use function OCA\OrgSuite\Tests\Support\assertSameValue;

    $targets = new SuiteTargetCatalog(new FlzProductCatalog());

    assertSameValue(
        ['flzcalendar', 'flzplaner', 'flzurlaub', 'flzroom', 'flzrecruitment', 'flzbqplanung'],
        array_column($targets->forSuite('flz'), 'app'),
        'FLZ-Ziele müssen aus dem kanonischen Produktkatalog stammen.',
    );
    assertSameValue(
        ['brtop', 'brstunden'],
        array_column($targets->forSuite('br'), 'app'),
        'BR-Ziele müssen aus genau einem lokalen Zielkatalog stammen.',
    );
    assertSameValue([], $targets->forSuite('unknown'), 'Eine unbekannte Suite darf keine internen Ziele erhalten.');
    assertSameValue(
        ['flz' => ['app:flzcalendar', 'app:flzplaner', 'app:flzurlaub', 'app:flzroom', 'app:flzrecruitment', 'app:flzbqplanung'], 'br' => ['app:brtop', 'app:brstunden']],
        $targets->keysBySuite(),
        'Die administrierbaren Sortierschlüssel müssen stabil aus den Zieldefinitionen entstehen.',
    );
    assertSameValue(
        ['key' => 'app:flzcalendar', 'suite' => 'flz', 'app' => 'flzcalendar', 'label' => 'Kalender', 'kind' => 'internal'],
        $targets->adminDefinitions()[0] ?? null,
        'Der Adminbereich erhält sichere, abgeleitete interne Zielmetadaten.',
    );

    $missing = new SuiteTargetCatalog(new FlzProductCatalog(__DIR__ . '/missing-catalog.json'));
    assertSameValue([], $missing->forSuite('flz'), 'Ein fehlender FLZ-Katalog muss ohne erfundene Ziele fehlschlagen.');

    echo "OrgSuite suite target catalog tests passed\n";
}
