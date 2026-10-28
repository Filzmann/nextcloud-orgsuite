<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Controller;

use InvalidArgumentException;
use OCA\OrgSuite\AppInfo\Application;
use OCA\OrgSuite\Service\ExternalLinkSettingsService;
use OCA\OrgSuite\Service\SuiteTargetCatalog;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

/** Geschützte Admin-API für zusätzliche FLZ-/BR-Menülinks. */
final class ExternalLinkAdminController extends Controller {
    public function __construct(
        IRequest $request,
        private IUserSession $session,
        private IGroupManager $groups,
        private ExternalLinkSettingsService $links,
        private SuiteTargetCatalog $targets,
    ) {
        parent::__construct(Application::APP_ID, $request);
    }

    public function settings(): JSONResponse {
        if (!$this->isAdmin()) return $this->denied();
        return new JSONResponse([
            ...$this->links->configuration($this->targets->keysBySuite()),
            'targets' => $this->targets->adminDefinitions(),
        ]);
    }

    public function save(mixed $links = null, mixed $order = null): JSONResponse {
        if (!$this->isAdmin()) return $this->denied();
        if (!is_array($links) || !is_array($order)) {
            return new JSONResponse(['error' => 'Links und Menüreihenfolge müssen als Listen übergeben werden.'], 400);
        }
        try {
            return new JSONResponse([
                ...$this->links->saveConfiguration($links, $order, $this->targets->keysBySuite()),
                'targets' => $this->targets->adminDefinitions(),
            ]);
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
