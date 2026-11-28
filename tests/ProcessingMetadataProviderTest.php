<?php

declare(strict_types=1);

namespace OCP\EventDispatcher {
    if (!class_exists(Event::class)) {
        class Event { public function __construct() {} }
        interface IEventListener { public function handle(Event $event): void; }
    }
}

namespace {
    require_once __DIR__ . '/bootstrap.php';

    use OCA\OrgSuite\Privacy\OrgSuiteProcessingMetadataProvider;
    use OCA\OrgSuite\Privacy\OrgSuiteProcessingMetadataProviderListener;
    use OCA\FlzDataProtection\PublicApi\V1\RegisterProcessingMetadataProvidersEvent;
    use OCP\EventDispatcher\Event;

    $provider = new OrgSuiteProcessingMetadataProvider();
    $catalog = $provider->catalog();
    $descriptor = $provider->descriptor();
    if ($descriptor->appId() !== 'orgsuite' || $descriptor->displayName() !== 'Filzmann Hub' || $descriptor->contractVersion() !== '1.0') {
        throw new RuntimeException('Der Processing-Metadata-Provider beschreibt Filzmann Hub nicht korrekt.');
    }
    if ($catalog->appId() !== 'orgsuite') {
        throw new RuntimeException('Processing-Metadata-Provider und Katalog verwenden nicht die kanonische App-ID.');
    }
    if ($catalog->processingIds() !== ["suite_administration"]) {
        throw new RuntimeException('Der app-lokale Processing-Katalog ist unvollständig.');
    }
    if (array_key_exists('personal_runtime_data', $catalog->toArray())) {
        throw new RuntimeException('Der Processing-Katalog enthält personenbezogene Laufzeitdaten.');
    }

    $registration = new RegisterProcessingMetadataProvidersEvent();
    $listener = new OrgSuiteProcessingMetadataProviderListener($provider);
    $listener->handle(new Event());
    if ($registration->providers() !== []) {
        throw new RuntimeException('Ein fremdes Event registriert den Processing-Metadata-Provider.');
    }
    $listener->handle($registration);
    if (($registration->providers()['orgsuite'] ?? null) !== $provider) {
        throw new RuntimeException('Der Processing-Metadata-Provider wird nicht lazy registriert.');
    }

    $application = (string)file_get_contents(dirname(__DIR__) . '/lib/AppInfo/Application.php');
    if (!str_contains($application, 'RegisterProcessingMetadataProvidersEvent::class')) {
        throw new RuntimeException('Der Processing-Metadata-Provider ist nicht im App-Bootstrap registriert.');
    }

    echo "OrgSuiteProcessingMetadataProviderTest: OK\n";
}
