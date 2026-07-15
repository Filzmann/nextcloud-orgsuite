<?php

declare(strict_types=1);

namespace OCP\AppFramework {
    class App { public function __construct(string $appId, array $urlParams = []) {} }
}
namespace OCP\AppFramework\Bootstrap {
    interface IBootstrap {}
    interface IBootContext {}
    interface IRegistrationContext { public function registerEventListener(string $event, string $listener): void; }
}
namespace OCP\AppFramework\Http\Events { class BeforeTemplateRenderedEvent {} }
namespace OCP\Navigation\Events { class LoadAdditionalEntriesEvent {} }

namespace {
    require_once __DIR__ . '/../lib/AppInfo/Application.php';

    use OCA\OrgSuite\AppInfo\Application;
    use OCA\OrgSuite\Listener\NavigationListener;
    use OCA\OrgSuite\Listener\SuiteAssetsListener;
    use OCP\AppFramework\Bootstrap\IBootContext;
    use OCP\AppFramework\Bootstrap\IRegistrationContext;
    use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
    use OCP\Navigation\Events\LoadAdditionalEntriesEvent;

    $registration = new class implements IRegistrationContext {
        public array $listeners = [];
        public function registerEventListener(string $event, string $listener): void { $this->listeners[] = [$event, $listener]; }
    };
    $application = new Application();
    $application->register($registration);
    $application->boot(new class implements IBootContext {});

    if ($registration->listeners !== [
        [LoadAdditionalEntriesEvent::class, NavigationListener::class],
        [BeforeTemplateRenderedEvent::class, SuiteAssetsListener::class],
    ]) {
        throw new RuntimeException('OrgSuite-Bootstrap registriert nicht alle Listener.');
    }

    echo "OrgSuite application bootstrap execution test passed\n";
}
