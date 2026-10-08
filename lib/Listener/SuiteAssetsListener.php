<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Listener;

use OCA\LocalBase\Catalog\FlzProductCatalog;
use OCA\OrgSuite\Service\ExternalLinkSettingsService;
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
    ];

    public function __construct(
        private FlzProductCatalog $catalog,
        private IAppManager $appManager,
        private IUserSession $userSession,
        private IURLGenerator $url,
        private IInitialState $initialState,
        private ExternalLinkSettingsService $externalLinks,
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
            foreach ($this->catalog->menuProducts('flz') as $product) {
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
            'flz' => ['label' => 'Filzmann-Anwendungen', 'items' => array_merge($adItems, $this->externalItems('flz'))],
            'br' => ['label' => 'BR-Anwendungen', 'items' => array_merge($this->enabledBrItems($user), $this->externalItems('br'))],
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

    /** @return list<array{app:string,label:string,href:string,external:true}> */
    private function externalItems(string $suite): array {
        return array_map(static fn(array $link): array => [
            'app' => 'orgsuite-external-' . $link['id'],
            'label' => $link['label'],
            'href' => $link['url'],
            'external' => true,
        ], $this->externalLinks->activeForSuite($suite));
    }
}
