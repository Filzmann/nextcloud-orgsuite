<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Settings;

use OCA\OrgSuite\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Util;

/** Zweck: Bindet die organisationsweiten Suite-Einstellungen in den Nextcloud-Adminbereich ein. */
final class Admin implements ISettings {
    public function getForm(): TemplateResponse {
        Util::addScript('orgsuite', 'external-links-admin');
        Util::addStyle('orgsuite', 'external-links-admin');
        return new TemplateResponse('localbase', 'organization-admin', ['standalone' => false]);
    }

    public function getSection(): string {
        return Application::APP_ID;
    }

    public function getPriority(): int {
        return 20;
    }
}
