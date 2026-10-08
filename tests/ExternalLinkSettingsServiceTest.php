<?php

declare(strict_types=1);

namespace OCP {
    interface IAppConfig {
        public function getValueString(string $appId, string $key, string $default = ''): string;
        public function setValueString(string $appId, string $key, string $value): void;
    }
}

namespace OCA\OrgSuite\AppInfo {
    final class Application { public const APP_ID = 'orgsuite'; }
}

namespace {
    use OCA\OrgSuite\Service\ExternalLinkSettingsService;
    use OCP\IAppConfig;
    use function OCA\OrgSuite\Tests\Support\assertSameValue;

    $config = new class implements IAppConfig {
        public array $values = [];
        public function getValueString(string $appId, string $key, string $default = ''): string { return $this->values[$appId][$key] ?? $default; }
        public function setValueString(string $appId, string $key, string $value): void { $this->values[$appId][$key] = $value; }
    };
    $service = new ExternalLinkSettingsService($config);

    assertSameValue([], $service->all(), 'Eine leere Konfiguration muss eine leere Linkliste liefern.');
    $saved = $service->save([
        ['id' => 'flz-handbuch', 'suite' => 'flz', 'label' => 'FLZ-Handbuch', 'url' => 'https://docs.example.test/flz', 'active' => true],
        ['id' => 'br-portal', 'suite' => 'br', 'label' => ' BR-Portal ', 'url' => 'https://portal.example.test/br', 'active' => false],
    ]);
    assertSameValue('BR-Portal', $saved[1]['label'] ?? null, 'Bezeichnungen werden normalisiert.');
    assertSameValue(['flz-handbuch'], array_column($service->activeForSuite('flz'), 'id'), 'Aktive FLZ-Links fehlen.');
    assertSameValue([], $service->activeForSuite('br'), 'Inaktive BR-Links dürfen nicht im Menü erscheinen.');
    assertSameValue($saved, $service->save($saved), 'Dasselbe Payload muss idempotent bleiben.');

    $persisted = $config->values['orgsuite']['external_links'] ?? null;
    foreach ([
        array_fill(0, 51, ['id' => 'too-many', 'suite' => 'flz', 'label' => 'Zu viele', 'url' => 'https://example.test', 'active' => true]),
        [['id' => 'bad-http', 'suite' => 'flz', 'label' => 'Unsicher', 'url' => 'http://example.test', 'active' => true]],
        [['id' => 'bflz-full-suite', 'suite' => 'other', 'label' => 'Falsch', 'url' => 'https://example.test', 'active' => true]],
        [['id' => 'too-long', 'suite' => 'flz', 'label' => str_repeat('x', 81), 'url' => 'https://example.test', 'active' => true]],
        [['id' => 'credentials', 'suite' => 'br', 'label' => 'Zugang', 'url' => 'https://user:secret@example.test', 'active' => true]],
        [['id' => 'invalid-active', 'suite' => 'br', 'label' => 'Aktivstatus', 'url' => 'https://example.test', 'active' => 1]],
        [
            ['id' => 'duplicate', 'suite' => 'flz', 'label' => 'A', 'url' => 'https://a.example.test', 'active' => true],
            ['id' => 'duplicate', 'suite' => 'br', 'label' => 'B', 'url' => 'https://b.example.test', 'active' => true],
        ],
    ] as $invalid) {
        try {
            $service->save($invalid);
            throw new RuntimeException('Eine ungültige externe Linkliste wurde akzeptiert.');
        } catch (InvalidArgumentException) {
        }
        assertSameValue($persisted, $config->values['orgsuite']['external_links'] ?? null, 'Eine ungültige Liste hat den gespeicherten Stand verändert.');
    }

    $config->values['orgsuite']['external_links'] = '{kaputt';
    assertSameValue([], $service->all(), 'Beschädigte Persistenz muss ohne sichtbare Links fehlschlagen.');

    echo "OrgSuite external link settings service tests passed\n";
}
