<?php

declare(strict_types=1);

namespace OCP {
    interface IAppConfig { public function getValueString(string $appId, string $key, string $default = ''): string; public function setValueString(string $appId, string $key, string $value): void; }
    interface IRequest {}
    interface IUser { public function getUID(): string; }
    interface IUserSession { public function getUser(): ?IUser; }
    interface IGroupManager { public function isAdmin(string $uid): bool; }
}
namespace OCP\AppFramework {
    class Controller { public function __construct(string $appName, \OCP\IRequest $request) {} }
}
namespace OCP\AppFramework\Http {
    final class JSONResponse {
        public function __construct(public mixed $data = [], public int $status = 200) {}
    }
}
namespace OCA\OrgSuite\AppInfo {
    final class Application { public const APP_ID = 'orgsuite'; }
}

namespace {
    use OCA\LocalBase\Catalog\FlzProductCatalog;
    use OCA\OrgSuite\Controller\ExternalLinkAdminController;
    use OCA\OrgSuite\Service\ExternalLinkSettingsService;
    use OCA\OrgSuite\Service\SuiteTargetCatalog;
    use OCA\OrgSuite\Tests\Support\ExternalLinkSmokePayloadBuilder;
    use OCP\IAppConfig;
    use OCP\IGroupManager;
    use OCP\IRequest;
    use OCP\IUser;
    use OCP\IUserSession;
    use function OCA\OrgSuite\Tests\Support\assertSameValue;

    $config = new class implements IAppConfig {
        public array $values = [];
        public function getValueString(string $appId, string $key, string $default = ''): string { return $this->values[$appId][$key] ?? $default; }
        public function setValueString(string $appId, string $key, string $value): void { $this->values[$appId][$key] = $value; }
    };
    $request = new class implements IRequest {};
    $admin = new class implements IUser { public function getUID(): string { return 'admin-user'; } };
    $member = new class implements IUser { public function getUID(): string { return 'normal-user'; } };
    $session = static fn(?IUser $user): IUserSession => new class($user) implements IUserSession {
        public function __construct(private ?IUser $user) {}
        public function getUser(): ?IUser { return $this->user; }
    };
    $groups = new class implements IGroupManager { public function isAdmin(string $uid): bool { return $uid === 'admin-user'; } };
    $service = new ExternalLinkSettingsService($config);
    $targets = new SuiteTargetCatalog(new FlzProductCatalog());
    $payload = [['id' => 'flz-docs', 'suite' => 'flz', 'label' => 'Dokumentation', 'url' => 'https://docs.example.test', 'active' => true, 'openMode' => 'new']];
    $order = $targets->keysBySuite();
    array_unshift($order['flz'], 'external:flz-docs');

    $denied = new ExternalLinkAdminController($request, $session($member), $groups, $service, $targets);
    assertSameValue(403, $denied->settings()->status, 'Nichtadmins dürfen externe Links nicht lesen.');
    assertSameValue(403, $denied->save($payload, $order)->status, 'Nichtadmins dürfen externe Links nicht speichern.');
    assertSameValue([], $service->all(), 'Ein verweigerter Schreibzugriff hat die Linkliste verändert.');

    $anonymous = new ExternalLinkAdminController($request, $session(null), $groups, $service, $targets);
    assertSameValue(403, $anonymous->save($payload, $order)->status, 'Anonyme Zugriffe müssen abgewiesen werden.');

    $allowed = new ExternalLinkAdminController($request, $session($admin), $groups, $service, $targets);
    assertSameValue(200, $allowed->save($payload, $order)->status, 'Admins können gültige externe Links speichern.');
    assertSameValue($payload, $allowed->settings()->data['links'] ?? null, 'Admins lesen den gespeicherten Stand.');
    assertSameValue($order, $allowed->settings()->data['order'] ?? null, 'Admins lesen die gemeinsame Reihenfolge.');
    assertSameValue('internal', $allowed->settings()->data['targets'][0]['kind'] ?? null, 'Interne Ziele fehlen in der Administration.');
    $before = $service->all();
    $invalid = $allowed->save([['id' => 'bad', 'suite' => 'br', 'label' => 'Unsicher', 'url' => 'javascript:alert(1)', 'active' => true, 'openMode' => 'new']], $order);
    assertSameValue(400, $invalid->status, 'Unsichere URLs müssen als Validierungsfehler beantwortet werden.');
    assertSameValue($before, $service->all(), 'Ein ungültiger Admin-Request hat den gespeicherten Stand verändert.');
    $manipulatedOrder = $order;
    $manipulatedOrder['flz'][0] = 'app:foreign';
    assertSameValue(400, $allowed->save($payload, $manipulatedOrder)->status, 'Manipulierte Sortierziele müssen abgewiesen werden.');
    assertSameValue($before, $service->all(), 'Eine manipulierte Reihenfolge hat die Linkliste verändert.');
    assertSameValue(400, $allowed->save('keine-liste', $order)->status, 'Falsch typisierte Linkdaten müssen kontrolliert abgewiesen werden.');
    assertSameValue(400, $allowed->save($payload, 'keine-reihenfolge')->status, 'Falsch typisierte Sortierdaten müssen kontrolliert abgewiesen werden.');
    assertSameValue($before, $service->all(), 'Falsch typisierte Daten haben die Linkliste verändert.');

    $replacementLinks = [
        ['id' => 'smoke-flz', 'suite' => 'flz', 'label' => 'Smoke FLZ', 'url' => 'https://smoke-flz.example.test', 'active' => true, 'openMode' => 'new'],
        ['id' => 'smoke-br', 'suite' => 'br', 'label' => 'Smoke BR', 'url' => 'https://smoke-br.example.test', 'active' => true, 'openMode' => 'same'],
    ];
    $replacement = ExternalLinkSmokePayloadBuilder::replaceLinks($allowed->settings()->data, $replacementLinks);
    $replaced = $allowed->save($replacement['links'], $replacement['order']);
    assertSameValue(200, $replaced->status, 'Der aus einem Stand mit vorhandenen Links erzeugte Smoke-Payload lässt sich nicht speichern.');
    assertSameValue(['external:smoke-flz', ...$targets->keysBySuite()['flz']], $replaced->data['order']['flz'] ?? null, 'Der gespeicherte Smoke-Payload enthält veraltete externe Sortierschlüssel.');

    echo "OrgSuite external link admin controller tests passed\n";
}
