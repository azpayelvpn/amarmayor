<?php

declare(strict_types=1);

namespace AmarMayor\Tests;

use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Http\Router;
use AmarMayor\Support\Config;
use AmarMayor\Support\Container;
use AmarMayor\Support\Translator;

/**
 * Base Testing Harness for Core Backend Foundation.
 */
abstract class TestCase
{
    protected Router $router;
    protected Container $container;

    public function setUp(): void
    {
        $baseDir = dirname(__DIR__);
        require_once $baseDir . '/bootstrap/app.php';

        \AmarMayor\Auth\Auth::setUser(null);
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
        }

        $this->container = Container::getInstance();
        $this->router = $this->container->get(Router::class);
    }

    protected function get(string $uri, array $headers = []): Response
    {
        $request = new Request('GET', $uri, [], [], $headers);
        return $this->router->dispatch($request);
    }

    protected function post(string $uri, array $data = [], array $headers = []): Response
    {
        $request = new Request('POST', $uri, [], $data, $headers);
        return $this->router->dispatch($request);
    }

    protected function postJson(string $uri, array $data = [], array $headers = []): Response
    {
        $headers['content-type'] = 'application/json';
        $rawBody = json_encode($data);
        $request = new Request('POST', $uri, [], [], $headers, [], [], [], $rawBody);
        return $this->router->dispatch($request);
    }

    protected function assert(bool $condition, string $message = 'Assertion failed'): void
    {
        if (!$condition) {
            throw new \AssertionError($message);
        }
    }

    protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $expectedStr = is_scalar($expected) ? (string)$expected : json_encode($expected);
            $actualStr = is_scalar($actual) ? (string)$actual : json_encode($actual);
            throw new \AssertionError($message ?: "Expected [{$expectedStr}], got [{$actualStr}]");
        }
    }

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        $this->assert($condition === true, $message ?: 'Expected true, got false');
    }

    protected function assertFalse(bool $condition, string $message = ''): void
    {
        $this->assert($condition === false, $message ?: 'Expected false, got true');
    }

    protected function assertNull(mixed $actual, string $message = ''): void
    {
        $this->assert($actual === null, $message ?: 'Expected null, got non-null');
    }

    protected function assertNotNull(mixed $actual, string $message = ''): void
    {
        $this->assert($actual !== null, $message ?: 'Expected non-null, got null');
    }

    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        if (!str_contains($haystack, $needle)) {
            throw new \AssertionError($message ?: "String [{$haystack}] does not contain [{$needle}]");
        }
    }

    protected function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertStringContains($needle, $haystack, $message);
    }

    protected function assertCount(int $expectedCount, array|\Countable $array, string $message = ''): void
    {
        $actualCount = count($array);
        if ($expectedCount !== $actualCount) {
            throw new \AssertionError($message ?: "Expected count {$expectedCount}, got {$actualCount}");
        }
    }

    protected function assertArrayHasKey(string|int $key, array $array, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            throw new \AssertionError($message ?: "Array does not contain key [{$key}]");
        }
    }

    protected function assertNotEmpty(mixed $actual, string $message = ''): void
    {
        $this->assert(!empty($actual), $message ?: 'Expected non-empty value, got empty');
    }

    protected function assertGreaterThanOrEqual(int|float $expected, int|float $actual, string $message = ''): void
    {
        $this->assert($actual >= $expected, $message ?: "Expected [{$actual}] to be >= [{$expected}]");
    }

    protected function assertStringNotContainsString(string $needle, string $haystack, string $message = ''): void
    {
        if (str_contains($haystack, $needle)) {
            throw new \AssertionError($message ?: "String [{$haystack}] unexpectedly contains [{$needle}]");
        }
    }

    protected function assertIsArray(mixed $actual, string $message = ''): void
    {
        if (!is_array($actual)) {
            throw new \AssertionError($message ?: "Expected array, got " . gettype($actual));
        }
    }
}
