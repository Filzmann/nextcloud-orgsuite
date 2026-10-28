<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Listener;

use OCA\OrgSuite\Service\ExternalLinkSettingsService;
use OCA\OrgSuite\Service\SuiteLabelSettingsService;
use OCA\OrgSuite\Service\SuiteTargetCatalog;
use OCP\App\IAppManager;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Util;

/**
 * Lädt das gemeinsame Quermenü und liefert ausschließlich aktivierte Ziele aus.
 * @template-implements IEventListener<BeforeTemplateRenderedEvent>
 */
final class SuiteAssetsListener implements IEventListener {
    public function __construct(
        private SuiteTargetCatalog $targets,
        private IAppManager $appManager,
        private IUserSession $userSession,
        private IURLGenerator $url,
        private IInitialState $initialState,
        private ExternalLinkSettingsService $externalLinks,
        private SuiteLabelSettingsService $labels,
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
        $configuration = $this->externalLinks->configuration($this->targets->keysBySuite());

        return [
            'flz' => ['label' => $this->labels->flzNavigationLabel(), 'items' => $this->orderedItems('flz', $user, $configuration)],
            'br' => ['label' => 'BR-Anwendungen', 'items' => $this->orderedItems('br', $user, $configuration)],
        ];
    }

    /** @param array{links:list<array<string,mixed>>,order:array{flz:list<string>,br:list<string>}} $configuration
     * @return list<array<string,mixed>>
     */
    private function orderedItems(string $suite, IUser $user, array $configuration): array {
        $itemsByKey = [];
        foreach ($this->targets->forSuite($suite) as $target) {
            if (!$this->appManager->isEnabledForUser($target['app'], $user)) {
                continue;
            }
            $key = 'app:' . $target['app'];
            $itemsByKey[$key] = [
                'app' => $target['app'],
                'label' => $target['label'],
                'href' => $this->url->linkToRoute($target['route']),
            ];
        }
        foreach ($this->externalItems($suite, $configuration['links']) as $key => $item) $itemsByKey[$key] = $item;
        $visible = array_fill_keys(array_keys($itemsByKey), true);
        $orderedKeys = array_values(array_filter(
            $configuration['order'][$suite],
            static fn(string $key): bool => isset($visible[$key]),
        ));

        return array_map(
            static fn(string $key): array => $itemsByKey[$key],
            $orderedKeys,
        );
    }

    /** @param list<array<string,mixed>> $links
     * @return array<string,array{app:string,label:string,href:string,external:true,openMode:string}>
     */
    private function externalItems(string $suite, array $links): array {
        $items = [];
        foreach ($links as $link) {
            if ($link['suite'] !== $suite || !$link['active']) continue;
            $items['external:' . $link['id']] = [
                'app' => 'orgsuite-external-' . $link['id'],
                'label' => $link['label'],
                'href' => $link['url'],
                'external' => true,
                'openMode' => $link['openMode'],
            ];
        }
        return $items;
    }
}
