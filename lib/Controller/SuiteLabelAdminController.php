<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Controller;

use InvalidArgumentException;
use OCA\OrgSuite\AppInfo\Application;
use OCA\OrgSuite\Service\SuiteLabelSettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

/** Geschützte Admin-API für die sichtbare Bezeichnung der FLZ-Suite. */
final class SuiteLabelAdminController extends Controller {
    public function __construct(
        IRequest $request,
        private IUserSession $session,
        private IGroupManager $groups,
        private SuiteLabelSettingsService $labels,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    public function settings(): JSONResponse {
        if (!$this->isAdmin()) return $this->denied();
        return new JSONResponse(['flzLabel' => $this->labels->flzLabel()]);
    }

    public function save(string $flzLabel): JSONResponse {
        if (!$this->isAdmin()) return $this->denied();
        try {
            return new JSONResponse(['flzLabel' => $this->labels->saveFlzLabel($flzLabel)]);
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
