<?php

declare(strict_types=1);

namespace {
    use OCA\OrgSuite\Catalog\FlzProductCatalog;
    use OCA\OrgSuite\Service\SuiteTargetCatalog;
    use function OCA\OrgSuite\Tests\Support\assertSameValue;

    $targets = new SuiteTargetCatalog(new FlzProductCatalog(), new \OCA\OrgSuite\Tests\Support\FakeL10N());

    assertSameValue(
        ['flzcalendar', 'flzplaner', 'flzurlaub', 'flzroom', 'flzrecruitment', 'flzbqplanung', 'flz_akut', 'flz_kapazitaet'],
        array_column($targets->forSuite('flz'), 'app'),
        'FLZ-Ziele müssen aus dem kanonischen Produktkatalog plus expliziten LocalBase-freien Extras stammen.',
    );
    assertSameValue(
        ['brtop', 'brstunden'],
        array_column($targets->forSuite('br'), 'app'),
        'BR-Ziele müssen aus genau einem lokalen Zielkatalog stammen.',
    );
    assertSameValue([], $targets->forSuite('unknown'), 'Eine unbekannte Suite darf keine internen Ziele erhalten.');
    assertSameValue(
        ['flz' => ['app:flzcalendar', 'app:flzplaner', 'app:flzurlaub', 'app:flzroom', 'app:flzrecruitment', 'app:flzbqplanung', 'app:flz_akut', 'app:flz_kapazitaet'], 'br' => ['app:brtop', 'app:brstunden']],
        $targets->keysBySuite(),
        'Die administrierbaren Sortierschlüssel müssen stabil aus den Zieldefinitionen entstehen.',
    );
    assertSameValue(
        ['key' => 'app:flzcalendar', 'suite' => 'flz', 'app' => 'flzcalendar', 'label' => 'Kalender', 'kind' => 'internal'],
        $targets->adminDefinitions()[0] ?? null,
        'Der Adminbereich erhält sichere, abgeleitete interne Zielmetadaten.',
    );

    $missing = new SuiteTargetCatalog(new FlzProductCatalog(__DIR__ . '/missing-catalog.json'), new \OCA\OrgSuite\Tests\Support\FakeL10N());
    assertSameValue(
        [['app' => 'flz_kapazitaet', 'route' => 'flz_kapazitaet.page.index', 'label' => 'Kapazität']],
        $missing->forSuite('flz'),
        'Ein fehlender FLZ-Katalog darf nur die explizit deklarierten Extras liefern, keine erfundenen Ziele.',
    );

    echo "OrgSuite suite target catalog tests passed\n";
}
