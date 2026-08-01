<?php

declare(strict_types=1);

namespace OCP\EventDispatcher { class Event {} interface IEventListener { public function handle(Event $event): void; } }
namespace OCP\AppFramework\Http\Events { class BeforeTemplateRenderedEvent extends \OCP\EventDispatcher\Event {} }
namespace OCP\AppFramework\Services { interface IInitialState { public function provideInitialState(string $key, $data): void; } }
namespace OCP\App { interface IAppManager { public function isEnabledForUser($appId, $user = null); } }
namespace OCP {
    interface IUser {}
    interface IUserSession { public function getUser(): ?IUser; }
    interface IURLGenerator { public function linkToRoute(string $routeName, array $arguments = []): string; }
    final class Util {
        public static array $scripts = [];
        public static array $styles = [];
        public static function addScript(string $appId, string $script): void { self::$scripts[] = [$appId, $script]; }
        public static function addStyle(string $appId, string $style): void { self::$styles[] = [$appId, $style]; }
    }
}

namespace {
    require_once __DIR__ . '/../../localbase/lib/Catalog/AdProductCatalog.php';
    require_once __DIR__ . '/../lib/Listener/SuiteAssetsListener.php';

    use OCA\LocalBase\Catalog\AdProductCatalog;
    use OCA\OrgSuite\Listener\SuiteAssetsListener;
    use OCP\App\IAppManager;
    use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
    use OCP\EventDispatcher\Event;
    use OCP\AppFramework\Services\IInitialState;
    use OCP\IURLGenerator;
    use OCP\IUser;
    use OCP\IUserSession;
    use OCP\Util;

    $user = new class implements IUser {};
    $session = new class($user) implements IUserSession { public function __construct(private ?IUser $user) {} public function getUser(): ?IUser { return $this->user; } };
    $apps = new class implements IAppManager {
        public function isEnabledForUser($appId, $user = null): bool { return in_array($appId, ['adplaner', 'adrecruitment', 'brtop'], true); }
    };
    $url = new class implements IURLGenerator { public function linkToRoute(string $routeName, array $arguments = []): string { return '/route/' . $routeName; } };
    $initialState = new class implements IInitialState {
        public array $states = [];
        public function provideInitialState(string $key, $data): void { $this->states[$key] = $data; }
    };

    $listener = new SuiteAssetsListener(new AdProductCatalog(), $apps, $session, $url, $initialState);
    $listener->handle(new Event());
    if (Util::$scripts !== [] || Util::$styles !== [] || $initialState->states !== []) throw new RuntimeException('Fremdes Event lädt Suite-Assets.');
    $listener->handle(new BeforeTemplateRenderedEvent());
    if (Util::$scripts !== [['orgsuite', 'suite-navigation']] || Util::$styles !== [['orgsuite', 'suite-navigation']]) {
        throw new RuntimeException('Suite-Assets werden nicht zentral registriert.');
    }
    $navigation = $initialState->states['suite-navigation'] ?? [];
    if (array_column($navigation['ad']['items'] ?? [], 'app') !== ['adplaner', 'adrecruitment']) {
        throw new RuntimeException('AD-Menüdaten sind nicht katalogisiert oder nicht auf aktivierte Apps begrenzt.');
    }
    if (($navigation['ad']['items'][1]['href'] ?? null) !== '/route/adrecruitment.page.index') {
        throw new RuntimeException('Recruitment-Menüroute stammt nicht aus dem Katalog.');
    }

    $loggedOut = new class implements IUserSession { public function getUser(): ?IUser { return null; } };
    $anonymousState = new class implements IInitialState {
        public array $states = [];
        public function provideInitialState(string $key, $data): void { $this->states[$key] = $data; }
    };
    (new SuiteAssetsListener(new AdProductCatalog(), $apps, $loggedOut, $url, $anonymousState))->handle(new BeforeTemplateRenderedEvent());
    if (($anonymousState->states['suite-navigation'] ?? null) !== []) {
        throw new RuntimeException('Anonyme Sitzung erhält unerwartete Suite-Menüdaten.');
    }

    $missingState = new class implements IInitialState {
        public array $states = [];
        public function provideInitialState(string $key, $data): void { $this->states[$key] = $data; }
    };
    $missingCatalog = new AdProductCatalog(__DIR__ . '/missing-catalog.json');
    (new SuiteAssetsListener($missingCatalog, $apps, $session, $url, $missingState))->handle(new BeforeTemplateRenderedEvent());
    if (($missingState->states['suite-navigation']['ad']['items'] ?? null) !== []) {
        throw new RuntimeException('Fehlender Katalog erzeugt unsichere AD-Menüdaten.');
    }

    echo "OrgSuite assets listener execution tests passed\n";
}
