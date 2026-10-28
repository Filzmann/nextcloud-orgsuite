<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Service;

use OCA\LocalBase\Catalog\FlzProductCatalog;
use RuntimeException;

/** Kanonische OrgSuite-Projektion der internen FLZ-/BR-Navigationsziele. */
final class SuiteTargetCatalog {
    /** @var list<array{app:string,route:string,label:string}> */
    private const BR_TARGETS = [
        ['app' => 'brtop', 'route' => 'brtop.page.index', 'label' => 'Sitzungen'],
        ['app' => 'brstunden', 'route' => 'brstunden.page.index', 'label' => 'Stunden'],
    ];

    public function __construct(private FlzProductCatalog $flzProducts) {}

    /** @return list<array{app:string,route:string,label:string}> */
    public function forSuite(string $suite): array {
        if ($suite === 'br') {
            return self::BR_TARGETS;
        }
        if ($suite !== 'flz') {
            return [];
        }

        try {
            return array_map(static fn(array $product): array => [
                'app' => $product['id'],
                'route' => $product['route'],
                'label' => $product['navigationLabel'],
            ], $this->flzProducts->menuProducts('flz'));
        } catch (RuntimeException) {
            return [];
        }
    }

    /** @return array{flz:list<string>,br:list<string>} */
    public function keysBySuite(): array {
        return [
            'flz' => array_map(self::key(...), $this->forSuite('flz')),
            'br' => array_map(self::key(...), $this->forSuite('br')),
        ];
    }

    /** @return list<array{key:string,suite:string,app:string,label:string,kind:string}> */
    public function adminDefinitions(): array {
        $definitions = [];
        foreach (['flz', 'br'] as $suite) {
            foreach ($this->forSuite($suite) as $target) {
                $definitions[] = [
                    'key' => self::key($target),
                    'suite' => $suite,
                    'app' => $target['app'],
                    'label' => $target['label'],
                    'kind' => 'internal',
                ];
            }
        }
        return $definitions;
    }

    /** @param array{app:string} $target */
    private static function key(array $target): string {
        return 'app:' . $target['app'];
    }
}
