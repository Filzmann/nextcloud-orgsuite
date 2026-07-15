<?php

declare(strict_types=1);

namespace OCA\OrgSuite\AppInfo;

use OCA\OrgSuite\Listener\NavigationListener;
use OCA\OrgSuite\Listener\SuiteAssetsListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\Navigation\Events\LoadAdditionalEntriesEvent;

/**
 * Zweck: Registriert die gemeinsamen AD- und BR-Einstiege im Nextcloud-Appmenue.
 * Zusammenspiel: Nextcloud -> NavigationListener und SuiteAssetsListener; Fachapps stellen nur optionale Menühosts bereit.
 */
final class Application extends App implements IBootstrap {
    public const APP_ID = 'orgsuite';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(LoadAdditionalEntriesEvent::class, NavigationListener::class);
        $context->registerEventListener(BeforeTemplateRenderedEvent::class, SuiteAssetsListener::class);
    }

    public function boot(IBootContext $context): void {
    }
}
