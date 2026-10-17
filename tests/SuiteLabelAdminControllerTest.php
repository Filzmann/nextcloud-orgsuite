<?php

declare(strict_types=1);

namespace OCP {
    interface IAppConfig { public function getValueString(string $appId, string $key, string $default = ''): string; public function setValueString(string $appId, string $key, string $value): void; }
    interface IRequest {}
    interface IUser { public function getUID(): string; }
    interface IUserSession { public function getUser(): ?IUser; }
    interface IGroupManager { public function isAdmin(string $uid): bool; }
}
namespace OCP\AppFramework { class Controller { public function __construct(string $appName, \OCP\IRequest $request) {} } }
namespace OCP\AppFramework\Http { final class JSONResponse { public function __construct(public mixed $data = [], public int $status = 200) {} } }
namespace OCA\OrgSuite\AppInfo { final class Application { public const APP_ID = 'orgsuite'; } }

namespace {
    use OCA\OrgSuite\Controller\SuiteLabelAdminController;
    use OCA\OrgSuite\Service\SuiteLabelSettingsService;
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
    $service = new SuiteLabelSettingsService($config);

    $denied = new SuiteLabelAdminController($request, $session($member), $groups, $service);
    assertSameValue(403, $denied->settings()->status, 'Nichtadmins dürfen die Suite-Bezeichnung nicht lesen.');
    assertSameValue(403, $denied->save('Verboten')->status, 'Nichtadmins dürfen die Suite-Bezeichnung nicht speichern.');
    assertSameValue('Filzmann', $service->flzLabel(), 'Ein verweigerter Schreibzugriff hat die Bezeichnung verändert.');

    $anonymous = new SuiteLabelAdminController($request, $session(null), $groups, $service);
    assertSameValue(403, $anonymous->save('Verboten')->status, 'Anonyme Schreibzugriffe müssen abgewiesen werden.');

    $allowed = new SuiteLabelAdminController($request, $session($admin), $groups, $service);
    assertSameValue(200, $allowed->save('FLZ Demo')->status, 'Admins können eine gültige Suite-Bezeichnung speichern.');
    assertSameValue('FLZ Demo', $allowed->settings()->data['flzLabel'] ?? null, 'Admins lesen nicht den gespeicherten Stand.');
    $invalid = $allowed->save('');
    assertSameValue(400, $invalid->status, 'Eine leere Bezeichnung muss als Validierungsfehler beantwortet werden.');
    assertSameValue('FLZ Demo', $service->flzLabel(), 'Ein ungültiger Request hat den gespeicherten Stand verändert.');

    echo "OrgSuite suite label admin controller tests passed\n";
}
