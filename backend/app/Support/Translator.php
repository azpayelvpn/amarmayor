<?php

declare(strict_types=1);

namespace AmarMayor\Support;

/**
 * Bilingual Translation & Localization Engine.
 * Default: Bangla (বাংলা), Secondary: English.
 */
class Translator
{
    private static string $langPath = '';
    private static string $currentLocale = 'bn';
    private static string $fallbackLocale = 'en';
    private static array $loaded = [];

    public static function setLangPath(string $path): void
    {
        self::$langPath = rtrim($path, '/\\');
    }

    public static function setLocale(string $locale): void
    {
        self::$currentLocale = in_array($locale, ['bn', 'en'], true) ? $locale : 'bn';
    }

    public static function getLocale(): string
    {
        return self::$currentLocale;
    }

    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $targetLocale = $locale ?? self::$currentLocale;
        $parts = explode('.', $key);
        $file = array_shift($parts);

        $line = self::resolveLine($targetLocale, $file, $parts);

        // Fallback if line is missing
        if ($line === null && $targetLocale !== self::$fallbackLocale) {
            $line = self::resolveLine(self::$fallbackLocale, $file, $parts);
        }

        if ($line === null) {
            return $key;
        }

        if (!is_string($line)) {
            return $key;
        }

        foreach ($replace as $placeholder => $value) {
            $line = str_replace(
                [':' . $placeholder, '{' . $placeholder . '}'],
                (string)$value,
                $line
            );
        }

        return $line;
    }

    private static function resolveLine(string $locale, string $file, array $parts): mixed
    {
        if (!isset(self::$loaded[$locale][$file])) {
            self::loadFile($locale, $file);
        }

        $current = self::$loaded[$locale][$file] ?? null;
        if ($current === null) {
            return null;
        }

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return null;
            }
            $current = $current[$part];
        }

        return $current;
    }

    private static function loadFile(string $locale, string $file): void
    {
        $filePath = self::$langPath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $file . '.php';
        if (file_exists($filePath)) {
            $data = require $filePath;
            self::$loaded[$locale][$file] = is_array($data) ? $data : [];
        } else {
            self::$loaded[$locale][$file] = [];
        }
    }

    public static function flush(): void
    {
        self::$loaded = [];
    }
}
