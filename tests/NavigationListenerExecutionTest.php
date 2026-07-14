<?php

declare(strict_types=1);

namespace OCP\EventDispatcher { class Event {} interface IEventListener { public function handle(Event $event): void; } }
namespace OCP\Navigation\Events { class LoadAdditionalEntriesEvent extends \OCP\EventDispatcher\Event {} }
namespace OCP {
    interface IUser {}
    interface IUserSession { public function getUser(): ?IUser; }
    interface IURLGenerator { public function linkToRoute(string $routeName, array $arguments = []): string; public function imagePath(string $appName, string $file): string; }
    interface INavigationManager { public const TYPE_APPS = 'link'; public function add(callable $entry): void; }
}
namespace OCP\App { interface IAppManager { public function isEnabledForUser($appId, $user = null); } }
namespace OCA\OrgSuite\AppInfo { final class Application { public const APP_ID = 'orgsuite'; } }

namespace {
    require_once __DIR__ . '/../lib/Listener/NavigationListener.php';

    use OCA\OrgSuite\Listener\NavigationListener;
    use OCP\App\IAppManager;
    use OCP\EventDispatcher\Event;
    use OCP\INavigationManager;
    use OCP\IURLGenerator;
    use OCP\IUser;
    use OCP\IUserSession;
    use OCP\Navigation\Events\LoadAdditionalEntriesEvent;

    $user = new class implements IUser {};
    $session = new class($user) implements IUserSession { public function __construct(private ?IUser $user) {} public function getUser(): ?IUser { return $this->user; } };
    $apps = new class implements IAppManager {
        public array $enabled = ['adurlaub', 'brstunden'];
        public function isEnabledForUser($appId, $user = null): bool { return in_array($appId, $this->enabled, true); }
    };
    $navigation = new class implements INavigationManager {
        public array $entries = [];
        public function add(callable $entry): void { $this->entries[] = $entry; }
    };
    $url = new class implements IURLGenerator {
        public function linkToRoute(string $routeName, array $arguments = []): string { return '/route/' . $routeName; }
        public function imagePath(string $appName, string $file): string { return '/image/' . $appName . '/' . $file; }
    };
    $listener = new NavigationListener($session, $apps, $navigation, $url);
    $listener->handle(new Event());
    if ($navigation->entries !== []) throw new RuntimeException('Fremdes Event erzeugt Navigation.');
    $listener->handle(new LoadAdditionalEntriesEvent());
    $entries = array_map(static fn(callable $entry): array => $entry(), $navigation->entries);
    if (array_column($entries, 'id') !== ['orgsuite-ad', 'orgsuite-br'] || $entries[0]['href'] !== '/route/orgsuite.entry.ad' || $entries[1]['icon'] !== '/image/orgsuite/br.svg') {
        throw new RuntimeException('Suite-Navigation wird nicht korrekt registriert.');
    }
    $apps->enabled = [];
    $emptyNavigation = new class implements INavigationManager { public array $entries = []; public function add(callable $entry): void { $this->entries[] = $entry; } };
    (new NavigationListener($session, $apps, $emptyNavigation, $url))->handle(new LoadAdditionalEntriesEvent());
    if ($emptyNavigation->entries !== []) throw new RuntimeException('Leere Suite wird angezeigt.');
    $loggedOut = new class implements IUserSession { public function getUser(): ?IUser { return null; } };
    (new NavigationListener($loggedOut, $apps, $emptyNavigation, $url))->handle(new LoadAdditionalEntriesEvent());
    if ($emptyNavigation->entries !== []) throw new RuntimeException('Anonyme Navigation wurde registriert.');

    echo "OrgSuite navigation listener execution tests passed\n";
}
