<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Controller;

use InvalidArgumentException;
use OCA\OrgSuite\AppInfo\Application;
use OCA\OrgSuite\Service\ExternalLinkSettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

/** Geschützte Admin-API für zusätzliche AD-/BR-Menülinks. */
final class ExternalLinkAdminController extends Controller {
    public function __construct(
        IRequest $request,
        private IUserSession $session,
        private IGroupManager $groups,
        private ExternalLinkSettingsService $links,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    public function settings(): JSONResponse {
        if (!$this->isAdmin()) return $this->denied();
        return new JSONResponse(['links' => $this->links->all()]);
    }

    public function save(array $links): JSONResponse {
        if (!$this->isAdmin()) return $this->denied();
        try {
            return new JSONResponse(['links' => $this->links->save($links)]);
        } catch (InvalidArgumentException $error) {
            return new JSONResponse(['error' => $error->getMessage()], 400);
        }
    }

    private function isAdmin(): bool {
        $user = $this->session->getUser();
        return $user !== null && $this->groups->isAdmin($user->getUID());
    }

    private function denied(): JSONResponse {
        return new JSONResponse(['error' => 'Keine Berechtigung.'], 403);
    }
}
