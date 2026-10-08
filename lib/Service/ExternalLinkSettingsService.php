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
    private const MAX_LINKS = 50;

    public function __construct(private IAppConfig $config, private ?LoggerInterface $logger = null) {}

    /** @return list<array{id:string,suite:string,label:string,url:string,active:bool}> */
    public function all(): array {
        $raw = $this->config->getValueString(Application::APP_ID, self::KEY, '');
        if ($raw === '') return [];

        try {
            $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
            return $this->normalize(is_array($decoded) ? $decoded : []);
        } catch (\Throwable $error) {
            $this->logger?->error('Die externe OrgSuite-Linkkonfiguration ist ungültig.', ['exception' => $error]);
            return [];
        }
    }

    /** @return list<array{id:string,suite:string,label:string,url:string,active:bool}> */
    public function activeForSuite(string $suite): array {
        if (!in_array($suite, ['flz', 'br'], true)) return [];
        return array_values(array_filter(
            $this->all(),
            static fn(array $link): bool => $link['suite'] === $suite && $link['active'],
        ));
    }

    /**
     * @param array<mixed> $links
     * @return list<array{id:string,suite:string,label:string,url:string,active:bool}>
     */
    public function save(array $links): array {
        $normalized = $this->normalize($links);
        $this->config->setValueString(
            Application::APP_ID,
            self::KEY,
            json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
        return $normalized;
    }

    /** @return list<array{id:string,suite:string,label:string,url:string,active:bool}> */
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

            $ids[$id] = true;
            $result[] = compact('id', 'suite', 'label', 'url', 'active');
        }
        return $result;
    }
}
