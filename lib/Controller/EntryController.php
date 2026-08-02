<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Controller;

use OCA\LocalBase\Catalog\AdProductCatalog;
use OCA\OrgSuite\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use RuntimeException;

/**
 * Zweck: Leitet einen Suite-Einstieg auf die erste fuer die aktuelle Person aktivierte Fachapp weiter.
 * Vertrag: Die Weiterleitung erteilt keine Rechte; der Zielcontroller prueft den fachlichen Zugriff erneut.
 */
final class EntryController extends Controller {
    /** @var array<string, list<array{app: string, route: string}>> */
    private const BR_TARGETS = [
        ['app' => 'brtop', 'route' => 'brtop.page.index'],
        ['app' => 'brstunden', 'route' => 'brstunden.page.index'],
        ['app' => 'br_permission_matrix', 'route' => 'br_permission_matrix.page.index'],
    ];

    public function __construct(
        IRequest $request,
        private IAppManager $appManager,
        private IUserSession $userSession,
        private IURLGenerator $url,
        private AdProductCatalog $catalog,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function ad(): RedirectResponse|NotFoundResponse {
        return $this->redirectToSuite('ad');
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function br(): RedirectResponse|NotFoundResponse {
        return $this->redirectToSuite('br');
    }

    private function redirectToSuite(string $suite): RedirectResponse|NotFoundResponse {
        $user = $this->userSession->getUser();
        if ($user === null) {
            return new NotFoundResponse();
        }

        foreach ($this->targets($suite) as $target) {
            if ($this->appManager->isEnabledForUser($target['app'], $user)) {
                return new RedirectResponse($this->url->linkToRoute($target['route']));
            }
        }

        return new NotFoundResponse();
    }

    /** @return list<array{app: string, route: string}> */
    private function targets(string $suite): array {
        if ($suite === 'br') {
            return self::BR_TARGETS;
        }
        if ($suite !== 'ad') {
            return [];
        }

        try {
            return array_map(
                static fn(array $product): array => ['app' => $product['id'], 'route' => $product['route']],
                $this->catalog->menuProducts('ad'),
            );
        } catch (RuntimeException) {
            return [];
        }
    }
}
