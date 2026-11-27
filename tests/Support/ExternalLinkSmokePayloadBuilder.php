<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Tests\Support;

use InvalidArgumentException;

/** Baut einen reproduzierbaren HTTP-Smoke-Payload ohne veraltete externe Sortierschlüssel. */
final class ExternalLinkSmokePayloadBuilder {
    /**
     * @param array<mixed> $settings
     * @param list<array<string,mixed>> $links
     * @return array{links:list<array<string,mixed>>,order:array{flz:list<string>,br:list<string>}}
     */
    public static function replaceLinks(array $settings, array $links): array {
        $order = $settings['order'] ?? null;
        if (!is_array($order) || !is_array($order['flz'] ?? null) || !is_array($order['br'] ?? null)) {
            throw new InvalidArgumentException('Der gelesene Smoke-Ausgangsstand besitzt keine Menüreihenfolge.');
        }

        $replacementKeys = ['flz' => [], 'br' => []];
        foreach ($links as $link) {
            $suite = $link['suite'] ?? null;
            $id = $link['id'] ?? null;
            if (!is_string($suite) || !isset($replacementKeys[$suite]) || !is_string($id) || $id === '') {
                throw new InvalidArgumentException('Ein synthetischer Smoke-Link besitzt keinen gültigen Sortierschlüssel.');
            }
            $replacementKeys[$suite][] = 'external:' . $id;
        }

        $resultOrder = ['flz' => [], 'br' => []];
        foreach (['flz', 'br'] as $suite) {
            $internalKeys = array_values(array_filter(
                $order[$suite],
                static fn(mixed $key): bool => is_string($key) && !str_starts_with($key, 'external:'),
            ));
            $resultOrder[$suite] = [...$replacementKeys[$suite], ...$internalKeys];
        }

        return ['links' => $links, 'order' => $resultOrder];
    }
}
