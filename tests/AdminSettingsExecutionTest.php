<?php

declare(strict_types=1);

namespace OCP {
    interface IURLGenerator { public function imagePath(string $appName, string $file): string; }
    final class Util {
        public static array $scripts = [];
        public static array $styles = [];
        public static function addScript(string $appId, string $script): void { self::$scripts[] = [$appId, $script]; }
        public static function addStyle(string $appId, string $style): void { self::$styles[] = [$appId, $style]; }
    }
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
    use OCA\OrgSuite\Settings\Admin;
    use OCA\OrgSuite\Settings\AdminSection;
    use OCP\IURLGenerator;
    use OCP\Util;

    $form = (new Admin())->getForm();
    if ($form->appName !== 'localbase' || $form->templateName !== 'organization-admin' || $form->params !== ['standalone' => false]) {
        throw new RuntimeException('OrgSuite bindet nicht das LocalBase-Organisationsformular ein.');
    }
    if (Util::$scripts !== [['orgsuite', 'external-links-admin']] || Util::$styles !== [['orgsuite', 'external-links-admin']]) {
        throw new RuntimeException('OrgSuite lädt die Adminassets für externe Links nicht.');
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
