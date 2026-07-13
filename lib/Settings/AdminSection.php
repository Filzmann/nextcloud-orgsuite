<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Settings;

use OCA\OrgSuite\AppInfo\Application;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

/** Zweck: Registriert einen eigenen, nur für Nextcloud-Admins sichtbaren Suite-Abschnitt. */
final class AdminSection implements IIconSection {
    public function __construct(private IURLGenerator $url) {}

    public function getIcon(): string {
        return $this->url->imagePath(Application::APP_ID, 'ad.svg');
    }

    public function getID(): string {
        return Application::APP_ID;
    }

    public function getName(): string {
        return 'AD-/BR-Suite';
    }

    public function getPriority(): int {
        return 60;
    }
}
