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
        public array $writes = [];
        public int $reads = 0;
        public function getValueString(string $appId, string $key, string $default = ''): string { $this->reads++; return $this->values[$appId][$key] ?? $default; }
        public function setValueString(string $appId, string $key, string $value): void { $this->writes[] = [$appId, $key, $value]; $this->values[$appId][$key] = $value; }
    };
    $service = new ExternalLinkSettingsService($config);

    assertSameValue([], $service->all(), 'Eine leere Konfiguration muss eine leere Linkliste liefern.');
    $internal = ['flz' => ['app:flzcalendar', 'app:flzplaner'], 'br' => ['app:brtop']];
    $savedConfiguration = $service->saveConfiguration([
        ['id' => 'flz-handbuch', 'suite' => 'flz', 'label' => 'FLZ-Handbuch', 'url' => 'https://docs.example.test/flz', 'active' => true, 'openMode' => 'same'],
        ['id' => 'br-portal', 'suite' => 'br', 'label' => ' BR-Portal ', 'url' => 'https://portal.example.test/br', 'active' => false, 'openMode' => 'new'],
    ], [
        'flz' => ['external:flz-handbuch', 'app:flzplaner', 'app:flzcalendar'],
        'br' => ['app:brtop', 'external:br-portal'],
    ], $internal);
    $saved = $savedConfiguration['links'];
    assertSameValue('BR-Portal', $saved[1]['label'] ?? null, 'Bezeichnungen werden normalisiert.');
    assertSameValue('same', $saved[0]['openMode'] ?? null, 'Der Öffnungsmodus wird persistiert.');
    $readsBeforeConfiguration = $config->reads;
    assertSameValue($savedConfiguration, $service->configuration($internal), 'Links und gemeinsame Reihenfolge werden zusammen gelesen.');
    assertSameValue(1, $config->reads - $readsBeforeConfiguration, 'Eine Konfiguration muss aus genau einem konsistenten AppConfig-Snapshot gelesen werden.');
    assertSameValue(['flz-handbuch'], array_column($service->activeForSuite('flz'), 'id'), 'Aktive FLZ-Links fehlen.');
    assertSameValue([], $service->activeForSuite('br'), 'Inaktive BR-Links dürfen nicht im Menü erscheinen.');
    assertSameValue($savedConfiguration, $service->saveConfiguration($saved, $savedConfiguration['order'], $internal), 'Dasselbe Payload muss idempotent bleiben.');

    $persisted = $config->values['orgsuite']['external_links'] ?? null;
    $persistedEnvelope = json_decode((string)$persisted, true, flags: JSON_THROW_ON_ERROR);
    assertSameValue(1, $persistedEnvelope['version'] ?? null, 'Die atomare Navigationskonfiguration besitzt keine eindeutige Version.');
    assertSameValue($savedConfiguration, ['links' => $persistedEnvelope['links'] ?? null, 'order' => $persistedEnvelope['order'] ?? null], 'Links und Reihenfolge liegen nicht in demselben AppConfig-Wert.');
    assertSameValue(false, isset($config->values['orgsuite']['navigation_order']), 'Eine zweite persistierte Reihenfolge würde einen Mischzustand ermöglichen.');
    foreach ($config->writes as [, $key]) assertSameValue('external_links', $key, 'Die Konfiguration wurde über mehr als einen AppConfig-Schlüssel geschrieben.');
    foreach ([
        array_fill(0, 51, ['id' => 'too-many', 'suite' => 'flz', 'label' => 'Zu viele', 'url' => 'https://example.test', 'active' => true]),
        [['id' => 'bad-http', 'suite' => 'flz', 'label' => 'Unsicher', 'url' => 'http://example.test', 'active' => true, 'openMode' => 'new']],
        [['id' => 'bflz-full-suite', 'suite' => 'other', 'label' => 'Falsch', 'url' => 'https://example.test', 'active' => true, 'openMode' => 'new']],
        [['id' => 'too-long', 'suite' => 'flz', 'label' => str_repeat('x', 81), 'url' => 'https://example.test', 'active' => true, 'openMode' => 'new']],
        [['id' => 'credentials', 'suite' => 'br', 'label' => 'Zugang', 'url' => 'https://user:secret@example.test', 'active' => true, 'openMode' => 'new']],
        [['id' => 'invalid-active', 'suite' => 'br', 'label' => 'Aktivstatus', 'url' => 'https://example.test', 'active' => 1, 'openMode' => 'new']],
        [['id' => 'invalid-mode', 'suite' => 'br', 'label' => 'Modus', 'url' => 'https://example.test', 'active' => true, 'openMode' => '_blank']],
        [
            ['id' => 'duplicate', 'suite' => 'flz', 'label' => 'A', 'url' => 'https://a.example.test', 'active' => true, 'openMode' => 'new'],
            ['id' => 'duplicate', 'suite' => 'br', 'label' => 'B', 'url' => 'https://b.example.test', 'active' => true, 'openMode' => 'new'],
        ],
    ] as $invalid) {
        try {
            $service->saveConfiguration($invalid, $savedConfiguration['order'], $internal);
            throw new RuntimeException('Eine ungültige externe Linkliste wurde akzeptiert.');
        } catch (InvalidArgumentException) {
        }
        assertSameValue($persisted, $config->values['orgsuite']['external_links'] ?? null, 'Eine ungültige Liste hat den gespeicherten Stand verändert.');
    }

    foreach ([
        ['flz' => ['app:flzcalendar', 'app:flzcalendar', 'app:flzplaner', 'external:flz-handbuch'], 'br' => ['app:brtop', 'external:br-portal']],
        ['flz' => ['app:flzcalendar', 'app:flzplaner', 'external:unknown'], 'br' => ['app:brtop', 'external:br-portal']],
        ['flz' => ['app:flzcalendar', 'external:flz-handbuch'], 'br' => ['app:brtop', 'external:br-portal']],
        ['flz' => ['app:flzcalendar', 'app:flzplaner', 'external:br-portal'], 'br' => ['app:brtop', 'external:flz-handbuch']],
    ] as $invalidOrder) {
        try {
            $service->saveConfiguration($saved, $invalidOrder, $internal);
            throw new RuntimeException('Eine manipulierte gemeinsame Reihenfolge wurde akzeptiert.');
        } catch (InvalidArgumentException) {
        }
        assertSameValue($persisted, $config->values['orgsuite']['external_links'] ?? null, 'Eine ungültige Reihenfolge hat die Linkpersistenz verändert.');
    }

    $legacyConfig = new class implements IAppConfig {
        public array $values = ['orgsuite' => ['external_links' => '[]']];
        public function getValueString(string $appId, string $key, string $default = ''): string { return $this->values[$appId][$key] ?? $default; }
        public function setValueString(string $appId, string $key, string $value): void { $this->values[$appId][$key] = $value; }
    };
    $legacyConfig->values['orgsuite']['external_links'] = json_encode([
        ['id' => 'legacy', 'suite' => 'flz', 'label' => 'Bestehender Link', 'url' => 'https://legacy.example.test', 'active' => true],
    ], JSON_THROW_ON_ERROR);
    $legacy = (new ExternalLinkSettingsService($legacyConfig))->configuration($internal);
    assertSameValue('new', $legacy['links'][0]['openMode'] ?? null, 'Die bisherige Linkliste erhält nicht den neuen sicheren Standardmodus.');
    assertSameValue(['app:flzcalendar', 'app:flzplaner', 'external:legacy'], $legacy['order']['flz'] ?? null, 'Die bisherige Linkliste wird nicht ohne zweite Quelle in die gemeinsame Standardreihenfolge projiziert.');

    $failingConfig = new class($persisted) implements IAppConfig {
        public function __construct(public string $value) {}
        public function getValueString(string $appId, string $key, string $default = ''): string { return $this->value; }
        public function setValueString(string $appId, string $key, string $value): void { throw new RuntimeException('synthetischer atomarer Schreibfehler'); }
    };
    $failingService = new ExternalLinkSettingsService($failingConfig);
    try {
        $failingService->saveConfiguration($saved, $savedConfiguration['order'], $internal);
        throw new RuntimeException('Der synthetische Schreibfehler wurde verschluckt.');
    } catch (RuntimeException $error) {
        assertSameValue('synthetischer atomarer Schreibfehler', $error->getMessage(), 'Unerwarteter Persistenzfehler.');
    }
    assertSameValue($persisted, $failingConfig->value, 'Ein fehlgeschlagener atomarer Schreibvorgang hat den vorherigen Snapshot verändert.');

    $config->values['orgsuite']['external_links'] = '{kaputt';
    assertSameValue([], $service->all(), 'Beschädigte Persistenz muss ohne sichtbare Links fehlschlagen.');

    echo "OrgSuite external link settings service tests passed\n";
}
