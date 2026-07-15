<?php

declare(strict_types=1);

namespace OCP\EventDispatcher { class Event {} interface IEventListener { public function handle(Event $event): void; } }
namespace OCP\AppFramework\Http\Events { class BeforeTemplateRenderedEvent extends \OCP\EventDispatcher\Event {} }
namespace OCP {
    final class Util {
        public static array $scripts = [];
        public static array $styles = [];
        public static function addScript(string $appId, string $script): void { self::$scripts[] = [$appId, $script]; }
        public static function addStyle(string $appId, string $style): void { self::$styles[] = [$appId, $style]; }
    }
}

namespace {
    require_once __DIR__ . '/../lib/Listener/SuiteAssetsListener.php';

    use OCA\OrgSuite\Listener\SuiteAssetsListener;
    use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
    use OCP\EventDispatcher\Event;
    use OCP\Util;

    $listener = new SuiteAssetsListener();
    $listener->handle(new Event());
    if (Util::$scripts !== [] || Util::$styles !== []) throw new RuntimeException('Fremdes Event lädt Suite-Assets.');
    $listener->handle(new BeforeTemplateRenderedEvent());
    if (Util::$scripts !== [['orgsuite', 'suite-navigation']] || Util::$styles !== [['orgsuite', 'suite-navigation']]) {
        throw new RuntimeException('Suite-Assets werden nicht zentral registriert.');
    }

    echo "OrgSuite assets listener execution tests passed\n";
}
