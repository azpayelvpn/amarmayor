<?php

declare(strict_types=1);

namespace AmarMayor\Support;

/**
 * Lightweight Environment File Loader.
 */
class Env
{
    private static array $cache = [];

    /**
     * Load environment variables from a file into $_ENV and putenv().
     */
    public static function load(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip enclosing quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $parsedValue = self::parseValue($value);
            $_ENV[$key] = $parsedValue;
            self::$cache[$key] = $parsedValue;
            putenv("{$key}={$value}");
        }
    }

    /**
     * Retrieve an environment variable value with default fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return self::parseValue($_ENV[$key]);
        }

        $env = getenv($key);
        if ($env !== false) {
            return self::parseValue($env);
        }

        return $default;
    }

    private static function parseValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $lower = strtolower($value);
        if ($lower === 'true' || $lower === '(true)') {
            return true;
        }
        if ($lower === 'false' || $lower === '(false)') {
            return false;
        }
        if ($lower === 'null' || $lower === '(null)') {
            return null;
        }
        if ($lower === 'empty' || $lower === '(empty)') {
            return '';
        }

        return $value;
    }
}
