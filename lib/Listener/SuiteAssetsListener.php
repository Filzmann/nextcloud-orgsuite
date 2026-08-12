<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Listener;

use OCA\LocalBase\Catalog\AdProductCatalog;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Util;
use RuntimeException;

/**
 * Lädt das gemeinsame Quermenü und liefert ausschließlich aktivierte Ziele aus.
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
final class SuiteAssetsListener implements IEventListener {
    private const BR_TARGETS = [
        ['app' => 'brtop', 'route' => 'brtop.page.index', 'label' => 'Sitzungen'],
        ['app' => 'brstunden', 'route' => 'brstunden.page.index', 'label' => 'Stunden'],
        ['app' => 'br_permission_matrix', 'route' => 'br_permission_matrix.page.index', 'label' => 'Berechtigungsmatrix'],
    ];

    public function __construct(
        private AdProductCatalog $catalog,
        private IAppManager $appManager,
        private IUserSession $userSession,
        private IURLGenerator $url,
        private IInitialState $initialState,
    ) {
    }

    public function handle(Event $event): void {
        if (!$event instanceof BeforeTemplateRenderedEvent) {
            return;
        }

        $this->initialState->provideInitialState('suite-navigation', $this->navigation());
        Util::addScript('orgsuite', 'suite-navigation');
        Util::addStyle('orgsuite', 'suite-navigation');
    }

    /** @return array<string, array{label: string, items: list<array{app: string, label: string, href: string}>}> */
    private function navigation(): array {
        $user = $this->userSession->getUser();
        if ($user === null) {
            return [];
        }

        $adItems = [];
        try {
            foreach ($this->catalog->menuProducts('ad') as $product) {
                if (!$this->appManager->isEnabledForUser($product['id'], $user)) {
                    continue;
                }
                $adItems[] = [
                    'app' => $product['id'],
                    'label' => $product['navigationLabel'],
                    'href' => $this->url->linkToRoute($product['route']),
                ];
            }
        } catch (RuntimeException) {
            $adItems = [];
        }

        return [
            'ad' => ['label' => 'AD-Anwendungen', 'items' => $adItems],
            'br' => ['label' => 'BR-Anwendungen', 'items' => $this->enabledBrItems($user)],
        ];
    }

    /** @return list<array{app: string, label: string, href: string}> */
    private function enabledBrItems(IUser $user): array {
        $items = [];
        foreach (self::BR_TARGETS as $target) {
            if (!$this->appManager->isEnabledForUser($target['app'], $user)) {
                continue;
            }
            $items[] = [
                'app' => $target['app'],
                'label' => $target['label'],
                'href' => $this->url->linkToRoute($target['route']),
            ];
        }
        return $items;
    }
}
