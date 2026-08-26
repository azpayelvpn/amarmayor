<?php

declare(strict_types=1);

namespace AmarMayor\Support;

/**
 * Centralized Configuration Manager.
 */
class Config
{
    private static array $items = [];
    private static string $configPath = '';

    public static function setConfigPath(string $path): void
    {
        self::$configPath = rtrim($path, '/\\');
    }

    /**
     * Get a configuration item using dot notation (e.g. 'app.locale', 'database.mysql.host').
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);

        if (!isset(self::$items[$file])) {
            self::loadFile($file);
        }

        $current = self::$items[$file] ?? null;
        if ($current === null) {
            return $default;
        }

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return $default;
            }
            $current = $current[$part];
        }

        return $current;
    }

    /**
     * Set a configuration item dynamically (primarily for testing).
     */
    public static function set(string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);

        if (!isset(self::$items[$file])) {
            self::loadFile($file);
        }

        $current = &self::$items[$file];
        while (count($parts) > 1) {
            $part = array_shift($parts);
            if (!isset($current[$part]) || !is_array($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }

        $finalKey = array_shift($parts);
        if ($finalKey !== null) {
            $current[$finalKey] = $value;
        } else {
            self::$items[$file] = is_array($value) ? $value : [];
        }
    }

    /**
     * Clear loaded configuration cache.
     */
    public static function flush(): void
    {
        self::$items = [];
    }

    private static function loadFile(string $file): void
    {
        $filePath = self::$configPath . DIRECTORY_SEPARATOR . $file . '.php';
        if (file_exists($filePath)) {
            $data = require $filePath;
            self::$items[$file] = is_array($data) ? $data : [];
        } else {
            self::$items[$file] = [];
        }
    }
}
