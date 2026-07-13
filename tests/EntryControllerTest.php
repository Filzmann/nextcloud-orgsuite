<?php

declare(strict_types=1);

namespace OCP {
    interface IRequest {}
    interface IUser {}
    interface IUserSession { public function getUser(): ?IUser; }
    interface IURLGenerator { public function linkToRoute(string $routeName, array $arguments = []): string; }
}

namespace OCP\App {
    interface IAppManager { public function isEnabledForUser($appId, $user = null); }
}

namespace OCP\AppFramework {
    class Controller { public function __construct(string $appName, \OCP\IRequest $request) {} }
}

namespace OCP\AppFramework\Http {
    class Response {}
    class RedirectResponse extends Response {
        public function __construct(public string $redirectURL) {}
    }
    class NotFoundResponse extends Response {}
}

namespace OCP\AppFramework\Http\Attribute {
    #[\Attribute(\Attribute::TARGET_METHOD)] class NoAdminRequired {}
    #[\Attribute(\Attribute::TARGET_METHOD)] class NoCSRFRequired {}
}

namespace OCA\OrgSuite\AppInfo {
    final class Application { public const APP_ID = 'orgsuite'; }
}

namespace {
    require __DIR__ . '/../lib/Controller/EntryController.php';

    use OCA\OrgSuite\Controller\EntryController;
    use OCP\App\IAppManager;
    use OCP\AppFramework\Http\Attribute\NoAdminRequired;
    use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
    use OCP\AppFramework\Http\NotFoundResponse;
    use OCP\AppFramework\Http\RedirectResponse;
    use OCP\IRequest;
    use OCP\IURLGenerator;
    use OCP\IUser;
    use OCP\IUserSession;

    $user = new class implements IUser {};
    $request = new class implements IRequest {};
    $url = new class implements IURLGenerator {
        public function linkToRoute(string $routeName, array $arguments = []): string { return '/route/' . $routeName; }
    };
    $session = new class($user) implements IUserSession {
        public function __construct(private ?IUser $user) {}
        public function getUser(): ?IUser { return $this->user; }
    };
    $enabledApps = ['adplaner', 'brtop'];
    $apps = new class($enabledApps) implements IAppManager {
        public function __construct(private array $enabledApps) {}
        public function isEnabledForUser($appId, $user = null): bool { return in_array($appId, $this->enabledApps, true); }
    };

    $controller = new EntryController($request, $apps, $session, $url);
    $ad = $controller->ad();
    if (!$ad instanceof RedirectResponse || $ad->redirectURL !== '/route/adplaner.page.index') {
        throw new RuntimeException('AD muss auf die erste aktivierte Fachapp weiterleiten.');
    }
    $br = $controller->br();
    if (!$br instanceof RedirectResponse || $br->redirectURL !== '/route/brtop.page.index') {
        throw new RuntimeException('BR muss auf die erste aktivierte Fachapp weiterleiten.');
    }

    $noApps = new class implements IAppManager {
        public function isEnabledForUser($appId, $user = null): bool { return false; }
    };
    if (!(new EntryController($request, $noApps, $session, $url))->ad() instanceof NotFoundResponse) {
        throw new RuntimeException('Eine vollstaendig deaktivierte Suite muss abgewiesen werden.');
    }
    $loggedOut = new class implements IUserSession { public function getUser(): ?IUser { return null; } };
    if (!(new EntryController($request, $apps, $loggedOut, $url))->br() instanceof NotFoundResponse) {
        throw new RuntimeException('Ein anonymer Suite-Einstieg muss abgewiesen werden.');
    }

    foreach (['ad', 'br'] as $methodName) {
        $method = new ReflectionMethod(EntryController::class, $methodName);
        if ($method->getAttributes(NoAdminRequired::class) === [] || $method->getAttributes(NoCSRFRequired::class) === []) {
            throw new RuntimeException("Suite-Einstieg {$methodName} braucht die erwarteten Leseattribute.");
        }
    }

    echo "OrgSuite entry controller tests passed\n";
}

