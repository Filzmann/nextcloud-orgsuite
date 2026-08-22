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
    use OCA\OrgSuite\Controller\ExternalLinkAdminController;
    use OCA\OrgSuite\Service\ExternalLinkSettingsService;
    use OCP\IAppConfig;
    use OCP\IGroupManager;
    use OCP\IRequest;
    use OCP\IUser;
    use OCP\IUserSession;
    use function OCA\LocalBase\Tests\Support\assertSameValue;

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
    $payload = [['id' => 'ad-docs', 'suite' => 'ad', 'label' => 'Dokumentation', 'url' => 'https://docs.example.test', 'active' => true]];

    $denied = new ExternalLinkAdminController($request, $session($member), $groups, $service);
    assertSameValue(403, $denied->settings()->status, 'Nichtadmins dürfen externe Links nicht lesen.');
    assertSameValue(403, $denied->save($payload)->status, 'Nichtadmins dürfen externe Links nicht speichern.');
    assertSameValue([], $service->all(), 'Ein verweigerter Schreibzugriff hat die Linkliste verändert.');

    $anonymous = new ExternalLinkAdminController($request, $session(null), $groups, $service);
    assertSameValue(403, $anonymous->save($payload)->status, 'Anonyme Zugriffe müssen abgewiesen werden.');

    $allowed = new ExternalLinkAdminController($request, $session($admin), $groups, $service);
    assertSameValue(200, $allowed->save($payload)->status, 'Admins können gültige externe Links speichern.');
    assertSameValue($payload, $allowed->settings()->data['links'] ?? null, 'Admins lesen den gespeicherten Stand.');
    $before = $service->all();
    $invalid = $allowed->save([['id' => 'bad', 'suite' => 'br', 'label' => 'Unsicher', 'url' => 'javascript:alert(1)', 'active' => true]]);
    assertSameValue(400, $invalid->status, 'Unsichere URLs müssen als Validierungsfehler beantwortet werden.');
    assertSameValue($before, $service->all(), 'Ein ungültiger Admin-Request hat den gespeicherten Stand verändert.');

    echo "OrgSuite external link admin controller tests passed\n";
}
