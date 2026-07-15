<?php

declare(strict_types=1);

namespace OCP {
    interface IURLGenerator { public function imagePath(string $appName, string $file): string; }
}
namespace OCP\Settings {
    interface ISettings { public function getForm(); public function getSection(): string; public function getPriority(): int; }
    interface IIconSection { public function getIcon(): string; public function getID(): string; public function getName(): string; public function getPriority(): int; }
}
namespace OCP\AppFramework\Http {
    class TemplateResponse {
        public function __construct(public string $appName, public string $templateName, public array $params = []) {}
    }
}
namespace OCA\OrgSuite\AppInfo { final class Application { public const APP_ID = 'orgsuite'; } }

namespace {
    require_once __DIR__ . '/../lib/Settings/Admin.php';
    require_once __DIR__ . '/../lib/Settings/AdminSection.php';

    use OCA\OrgSuite\Settings\Admin;
    use OCA\OrgSuite\Settings\AdminSection;
    use OCP\IURLGenerator;

    $form = (new Admin())->getForm();
    if ($form->appName !== 'localbase' || $form->templateName !== 'organization-admin' || $form->params !== ['standalone' => false]) {
        throw new RuntimeException('OrgSuite bindet nicht das LocalBase-Organisationsformular ein.');
    }
    if ((new Admin())->getSection() !== 'orgsuite' || (new Admin())->getPriority() !== 20) {
        throw new RuntimeException('OrgSuite-Adminsetting besitzt falsche Metadaten.');
    }

    $url = new class implements IURLGenerator {
        public function imagePath(string $appName, string $file): string { return "$appName/$file"; }
    };
    $section = new AdminSection($url);
    if ($section->getID() !== 'orgsuite' || $section->getName() !== 'AD-/BR-Suite' || $section->getPriority() !== 60 || $section->getIcon() !== 'orgsuite/ad.svg') {
        throw new RuntimeException('OrgSuite-Adminabschnitt besitzt falsche Metadaten.');
    }

    echo "OrgSuite admin settings execution test passed\n";
}
