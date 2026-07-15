<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Listener;

use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Util;

/**
 * Zweck: Lädt das gemeinsame Quermenü zentral, sodass Fachapps keine harte Asset-Abhängigkeit zur OrgSuite besitzen.
 * Vertrag: Ohne aktivierte OrgSuite wird der Listener nicht geladen; Fachapp-Templates bleiben eigenständig auslieferbar.
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
final class SuiteAssetsListener implements IEventListener {
    public function handle(Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent) return;
        Util::addScript('orgsuite', 'suite-navigation');
        Util::addStyle('orgsuite', 'suite-navigation');
    }
}
