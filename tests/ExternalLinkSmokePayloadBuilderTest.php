<?php

declare(strict_types=1);

namespace {
    use OCA\OrgSuite\Tests\Support\ExternalLinkSmokePayloadBuilder;
    use function OCA\OrgSuite\Tests\Support\assertSameValue;

    $settings = [
        'links' => [
            ['id' => 'old-flz', 'suite' => 'flz'],
            ['id' => 'old-br', 'suite' => 'br'],
        ],
        'order' => [
            'flz' => ['external:old-flz', 'app:flzcalendar', 'external:stale-flz', 'app:flzplaner'],
            'br' => ['app:brtop', 'external:old-br', 'external:stale-br'],
        ],
        'targets' => [['key' => 'app:flzcalendar']],
    ];
    $links = [
        ['id' => 'smoke-flz-docs', 'suite' => 'flz', 'label' => 'Smoke FLZ Docs', 'url' => 'https://docs.example.test/flz', 'active' => true, 'openMode' => 'new'],
        ['id' => 'smoke-br-portal', 'suite' => 'br', 'label' => 'Smoke BR Portal', 'url' => 'https://portal.example.test/br', 'active' => true, 'openMode' => 'same'],
    ];

    $payload = ExternalLinkSmokePayloadBuilder::replaceLinks($settings, $links);
    assertSameValue($links, $payload['links'] ?? null, 'Der Smoke-Payload enthält nicht genau die synthetischen Ersatzlinks.');
    assertSameValue(
        ['external:smoke-flz-docs', 'app:flzcalendar', 'app:flzplaner'],
        $payload['order']['flz'] ?? null,
        'Veraltete externe FLZ-Sortierschlüssel wurden nicht vollständig entfernt.',
    );
    assertSameValue(
        ['external:smoke-br-portal', 'app:brtop'],
        $payload['order']['br'] ?? null,
        'Veraltete externe BR-Sortierschlüssel wurden nicht vollständig entfernt.',
    );
    assertSameValue(false, isset($payload['targets']), 'Nicht speicherbare Zielmetadaten gelangten in den Smoke-Payload.');

    echo "OrgSuite external link smoke payload builder tests passed\n";
}
