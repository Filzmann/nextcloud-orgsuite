<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Service;

use InvalidArgumentException;
use OCA\OrgSuite\AppInfo\Application;
use OCP\IAppConfig;

/** Führende OrgSuite-Konfiguration für die sichtbare FLZ-Menübezeichnung. */
final class SuiteLabelSettingsService {
    private const KEY = 'flz_menu_label';
    private const DEFAULT_LABEL = 'Filzmann';
    private const MAX_LENGTH = 40;

    public function __construct(private IAppConfig $config) {}

    public function flzLabel(): string {
        $stored = $this->config->getValueString(Application::APP_ID, self::KEY, '');
        if ($stored === '') return self::DEFAULT_LABEL;

        try {
            return $this->normalize($stored);
        } catch (InvalidArgumentException) {
            return self::DEFAULT_LABEL;
        }
    }

    public function flzNavigationLabel(): string {
        return $this->flzLabel() . '-Anwendungen';
    }

    public function saveFlzLabel(string $label): string {
        $normalized = $this->normalize($label);
        $this->config->setValueString(Application::APP_ID, self::KEY, $normalized);
        return $normalized;
    }

    private function normalize(string $label): string {
        if (preg_match('/[\x00-\x1F\x7F]/u', $label) === 1) {
            throw new InvalidArgumentException('Die FLZ-Menübezeichnung muss aus höchstens 40 sichtbaren Zeichen bestehen.');
        }
        $label = preg_replace('/\s+/u', ' ', trim($label)) ?? '';
        $length = preg_match_all('/./us', $label);
        if ($label === '' || $length === false || $length > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Die FLZ-Menübezeichnung muss aus höchstens 40 sichtbaren Zeichen bestehen.');
        }
        return $label;
    }
}
