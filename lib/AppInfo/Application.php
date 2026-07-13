<?php

declare(strict_types=1);

namespace OCA\OrgSuite\AppInfo;

use OCA\OrgSuite\Listener\NavigationListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Navigation\Events\LoadAdditionalEntriesEvent;

/**
 * Zweck: Registriert die gemeinsamen AD- und BR-Einstiege im Nextcloud-Appmenue.
 * Zusammenspiel: Nextcloud -> NavigationListener; die Fachapps laden separat das gemeinsame Suite-Menue.
 */
final class Application extends App implements IBootstrap {
    public const APP_ID = 'orgsuite';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(LoadAdditionalEntriesEvent::class, NavigationListener::class);
    }

    public function boot(IBootContext $context): void {
    }
}

