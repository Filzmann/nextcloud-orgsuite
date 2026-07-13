<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Listener;

use OCA\OrgSuite\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\INavigationManager;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Navigation\Events\LoadAdditionalEntriesEvent;

/**
 * Zweck: Zeigt pro Fachbereich genau einen App-Einstieg und vermeidet Links auf vollstaendig deaktivierte Suiten.
 * Vertrag: Die Sichtbarkeit prueft nur die Nextcloud-Appfreigabe. Fachliche Berechtigungen bleiben bei den Zielapps.
 * @template-extends IEventListener<LoadAdditionalEntriesEvent>
 */
final class NavigationListener implements IEventListener {
    /** @var array<string, list<string>> */
    private const TARGETS = [
        'ad' => ['adcalendar', 'adplaner', 'adurlaub'],
        'br' => ['brtop', 'brstunden', 'br_permission_matrix'],
    ];

    public function __construct(
        private IUserSession $userSession,
        private IAppManager $appManager,
        private INavigationManager $navigation,
        private IURLGenerator $url
    ) {
    }

    public function handle(Event $event): void {
        if (!$event instanceof LoadAdditionalEntriesEvent) {
            return;
        }

        $user = $this->userSession->getUser();
        if ($user === null) {
            return;
        }

        $this->addEntryWhenAvailable('ad', 'AD', 80, $user);
        $this->addEntryWhenAvailable('br', 'BR', 81, $user);
    }

    private function addEntryWhenAvailable(string $suite, string $name, int $order, IUser $user): void {
        if (!$this->hasEnabledTarget($suite, $user)) {
            return;
        }

        $this->navigation->add(fn(): array => [
            'id' => Application::APP_ID . '-' . $suite,
            'type' => INavigationManager::TYPE_APPS,
            'app' => Application::APP_ID,
            'href' => $this->url->linkToRoute(Application::APP_ID . '.entry.' . $suite),
            'icon' => $this->url->imagePath(Application::APP_ID, $suite . '.svg'),
            'name' => $name,
            'order' => $order,
        ]);
    }

    private function hasEnabledTarget(string $suite, IUser $user): bool {
        foreach (self::TARGETS[$suite] ?? [] as $appId) {
            if ($this->appManager->isEnabledForUser($appId, $user)) {
                return true;
            }
        }

        return false;
    }
}

