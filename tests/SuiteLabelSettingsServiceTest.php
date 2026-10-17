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
    use OCA\OrgSuite\Service\SuiteLabelSettingsService;
    use OCP\IAppConfig;
    use function OCA\OrgSuite\Tests\Support\assertSameValue;

    $config = new class implements IAppConfig {
        public array $values = [];
        public function getValueString(string $appId, string $key, string $default = ''): string { return $this->values[$appId][$key] ?? $default; }
        public function setValueString(string $appId, string $key, string $value): void { $this->values[$appId][$key] = $value; }
    };
    $service = new SuiteLabelSettingsService($config);

    assertSameValue('Filzmann', $service->flzLabel(), 'Ohne Konfiguration muss der bisherige Menüname erhalten bleiben.');
    $config->values['orgsuite']['flz_menu_label'] = "Ungültig\nGespeichert";
    assertSameValue('Filzmann', $service->flzLabel(), 'Eine ungültige gespeicherte Bezeichnung muss sicher auf den Standard zurückfallen.');
    unset($config->values['orgsuite']['flz_menu_label']);
    assertSameValue('Meine Organisation', $service->saveFlzLabel('  Meine   Organisation  '), 'Die Menübezeichnung wird normalisiert.');
    assertSameValue('Meine Organisation', $service->flzLabel(), 'Die gespeicherte Menübezeichnung wird nicht gelesen.');
    assertSameValue('Meine Organisation-Anwendungen', $service->flzNavigationLabel(), 'Das Quermenü verwendet nicht dieselbe kanonische Bezeichnung.');
    assertSameValue('Meine Organisation', $service->saveFlzLabel('Meine Organisation'), 'Wiederholtes Speichern muss idempotent bleiben.');

    $persisted = $config->values['orgsuite']['flz_menu_label'] ?? null;
    foreach (['', "Ungültig\nName", str_repeat('x', 41)] as $invalid) {
        try {
            $service->saveFlzLabel($invalid);
            throw new RuntimeException('Eine ungültige Suite-Menübezeichnung wurde akzeptiert.');
        } catch (InvalidArgumentException) {
        }
        assertSameValue($persisted, $config->values['orgsuite']['flz_menu_label'] ?? null, 'Eine ungültige Bezeichnung hat den gespeicherten Stand verändert.');
    }

    echo "OrgSuite suite label settings service tests passed\n";
}
