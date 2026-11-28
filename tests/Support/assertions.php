<?php

declare(strict_types=1);

namespace OCA\OrgSuite\Tests\Support;

function assertSameValue(mixed $expected, mixed $actual, string $message): void {
    if ($expected !== $actual) {
        throw new \RuntimeException($message . '\nExpected: ' . var_export($expected, true) . '\nActual: ' . var_export($actual, true));
    }
}

function assertThrows(callable $callback, string $exceptionClass, string $message): \Throwable {
    try {
        $callback();
    } catch (\Throwable $exception) {
        if ($exception instanceof $exceptionClass) {
            return $exception;
        }
        throw new \RuntimeException($message . '\nFalsche Ausnahme: ' . $exception::class, previous: $exception);
    }
    throw new \RuntimeException($message . '\nKeine Ausnahme vom Typ ' . $exceptionClass . ' wurde geworfen.');
}
