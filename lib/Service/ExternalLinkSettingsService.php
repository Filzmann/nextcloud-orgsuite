<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Service;

use InvalidArgumentException;
use OCA\OrgSuite\AppInfo\Application;
use OCP\IAppConfig;
use Psr\Log\LoggerInterface;

/** Persistiert die vollständig validierte, geordnete Liste zusätzlicher FLZ-/BR-Menülinks. */
final class ExternalLinkSettingsService {
    private const KEY = 'external_links';
    private const VERSION = 1;
    private const MAX_LINKS = 50;

    public function __construct(private IAppConfig $config, private ?LoggerInterface $logger = null) {}

    /** @return list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}> */
    public function all(): array {
        return $this->readSnapshot()['links'];
    }

    /** @return list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}> */
    public function activeForSuite(string $suite): array {
        if (!in_array($suite, ['flz', 'br'], true)) return [];
        return array_values(array_filter(
            $this->all(),
            static fn(array $link): bool => $link['suite'] === $suite && $link['active'],
        ));
    }

    /**
     * @param array<mixed> $links
     * @param array<mixed> $order
     * @param array{flz:list<string>,br:list<string>} $internalKeys
     * @return array{links:list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}>,order:array{flz:list<string>,br:list<string>}}
     */
    public function saveConfiguration(array $links, array $order, array $internalKeys): array {
        $normalizedLinks = $this->normalize($links);
        $normalizedOrder = $this->normalizeOrder($order, $internalKeys, $normalizedLinks);

        $this->persist(['version' => self::VERSION, 'links' => $normalizedLinks, 'order' => $normalizedOrder]);

        return ['links' => $normalizedLinks, 'order' => $normalizedOrder];
    }

    /**
     * @param array{flz:list<string>,br:list<string>} $internalKeys
     * @return array{links:list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}>,order:array{flz:list<string>,br:list<string>}}
     */
    public function configuration(array $internalKeys): array {
        $snapshot = $this->readSnapshot();
        $links = $snapshot['links'];
        $available = $this->availableKeys($internalKeys, $links);
        return [
            'links' => $links,
            'order' => [
                'flz' => $this->mergeOrder($snapshot['order']['flz'], $available['flz']),
                'br' => $this->mergeOrder($snapshot['order']['br'], $available['br']),
            ],
        ];
    }

    /** @param list<string> $stored @param list<string> $availableKeys @return list<string> */
    private function mergeOrder(array $stored, array $availableKeys): array {
        $availableSet = array_fill_keys($availableKeys, true);
        $ordered = array_values(array_filter($stored, static fn(string $key): bool => isset($availableSet[$key])));
        $seen = array_fill_keys($ordered, true);
        foreach ($availableKeys as $key) {
            if (!isset($seen[$key])) $ordered[] = $key;
        }
        return $ordered;
    }

    /** @return list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}> */
    private function normalize(array $links): array {
        if (count($links) > self::MAX_LINKS) {
            throw new InvalidArgumentException('Es sind höchstens 50 externe Suite-Links zulässig.');
        }

        $result = [];
        $ids = [];
        foreach ($links as $link) {
            if (!is_array($link)) throw new InvalidArgumentException('Ein externer Suite-Link ist ungültig.');
            $id = trim((string)($link['id'] ?? ''));
            $suite = trim((string)($link['suite'] ?? ''));
            $label = trim((string)($link['label'] ?? ''));
            $url = trim((string)($link['url'] ?? ''));
            $active = $link['active'] ?? null;
            $openMode = $link['openMode'] ?? 'new';

            if (preg_match('/^[a-z0-9][a-z0-9-]{0,63}$/D', $id) !== 1 || isset($ids[$id])) {
                throw new InvalidArgumentException('Die ID eines externen Suite-Links ist ungültig oder doppelt.');
            }
            if (!in_array($suite, ['flz', 'br'], true)) {
                throw new InvalidArgumentException('Die Suite eines externen Links ist ungültig.');
            }
            $labelLength = preg_match_all('/./us', $label, $characters);
            if ($label === '' || $labelLength === false || $labelLength > 80) {
                throw new InvalidArgumentException('Die Bezeichnung eines externen Suite-Links ist ungültig.');
            }
            $parts = parse_url($url);
            if (strlen($url) > 2048 || filter_var($url, FILTER_VALIDATE_URL) === false
                || !is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https'
                || trim((string)($parts['host'] ?? '')) === '' || isset($parts['user']) || isset($parts['pass'])) {
                throw new InvalidArgumentException('Externe Suite-Links benötigen eine gültige HTTPS-URL ohne Zugangsdaten.');
            }
            if (!is_bool($active)) {
                throw new InvalidArgumentException('Der Aktivstatus eines externen Suite-Links ist ungültig.');
            }
            if (!is_string($openMode) || !in_array($openMode, ['new', 'same'], true)) {
                throw new InvalidArgumentException('Der Öffnungsmodus eines externen Suite-Links ist ungültig.');
            }

            $ids[$id] = true;
            $result[] = compact('id', 'suite', 'label', 'url', 'active', 'openMode');
        }
        return $result;
    }

    /**
     * @param array<mixed> $order
     * @param array{flz:list<string>,br:list<string>} $internalKeys
     * @param list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}> $links
     * @return array{flz:list<string>,br:list<string>}
     */
    private function normalizeOrder(array $order, array $internalKeys, array $links): array {
        $available = $this->availableKeys($internalKeys, $links);
        $result = ['flz' => [], 'br' => []];
        if (array_diff(array_keys($order), ['flz', 'br']) !== [] || !isset($order['flz'], $order['br'])) {
            throw new InvalidArgumentException('Die gemeinsame Menüreihenfolge ist unvollständig.');
        }
        foreach (['flz', 'br'] as $suite) {
            if (!is_array($order[$suite]) || !array_is_list($order[$suite])) {
                throw new InvalidArgumentException('Die gemeinsame Menüreihenfolge ist ungültig.');
            }
            $keys = [];
            foreach ($order[$suite] as $key) {
                if (!is_string($key) || isset($keys[$key])) {
                    throw new InvalidArgumentException('Die gemeinsame Menüreihenfolge enthält einen ungültigen oder doppelten Schlüssel.');
                }
                $keys[$key] = true;
                $result[$suite][] = $key;
            }
            if (array_keys($keys) !== $available[$suite]) {
                $expected = $available[$suite];
                sort($expected);
                $actual = array_keys($keys);
                sort($actual);
                if ($actual !== $expected) {
                    throw new InvalidArgumentException('Die gemeinsame Menüreihenfolge enthält unbekannte, fremde oder fehlende Ziele.');
                }
            }
        }
        return $result;
    }

    /**
     * @param array{flz:list<string>,br:list<string>} $internalKeys
     * @param list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}> $links
     * @return array{flz:list<string>,br:list<string>}
     */
    private function availableKeys(array $internalKeys, array $links): array {
        $available = ['flz' => array_values($internalKeys['flz'] ?? []), 'br' => array_values($internalKeys['br'] ?? [])];
        foreach ($links as $link) $available[$link['suite']][] = 'external:' . $link['id'];
        return $available;
    }

    /** @return array{links:list<array{id:string,suite:string,label:string,url:string,active:bool,openMode:string}>,order:array{flz:list<string>,br:list<string>}} */
    private function readSnapshot(): array {
        $raw = $this->config->getValueString(Application::APP_ID, self::KEY, '');
        if ($raw === '') return ['links' => [], 'order' => ['flz' => [], 'br' => []]];
        try {
            $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) throw new InvalidArgumentException('Ungültige Navigationskonfiguration.');
            if (array_is_list($decoded)) {
                return ['links' => $this->normalize($decoded), 'order' => ['flz' => [], 'br' => []]];
            }
            if (($decoded['version'] ?? null) !== self::VERSION || !is_array($decoded['links'] ?? null) || !is_array($decoded['order'] ?? null)) {
                throw new InvalidArgumentException('Unbekannte oder unvollständige Navigationskonfiguration.');
            }
            $order = ['flz' => [], 'br' => []];
            $seen = ['flz' => [], 'br' => []];
            foreach (['flz', 'br'] as $suite) {
                $keys = $decoded['order'][$suite] ?? null;
                if (!is_array($keys) || !array_is_list($keys)) throw new InvalidArgumentException('Ungültige Reihenfolge.');
                foreach ($keys as $key) {
                    if (!is_string($key) || isset($seen[$suite][$key])) throw new InvalidArgumentException('Ungültige Reihenfolge.');
                    $seen[$suite][$key] = true;
                    $order[$suite][] = $key;
                }
            }
            return ['links' => $this->normalize($decoded['links']), 'order' => $order];
        } catch (\Throwable $error) {
            $this->logger?->error('Die atomare OrgSuite-Navigationskonfiguration ist ungültig.', ['exception' => $error]);
            return ['links' => [], 'order' => ['flz' => [], 'br' => []]];
        }
    }

    private function persist(array $value): void {
        $this->config->setValueString(
            Application::APP_ID,
            self::KEY,
            json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
    }
}
