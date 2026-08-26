<?php

declare(strict_types=1);

namespace AmarMayor\Support;

use DateTimeInterface;
use DateTime;
use Exception;

/**
 * Bilingual Translation & Localization Engine.
 * Default: Bangla (বাংলা), Secondary: English (ইংরেজি).
 */
class Translator
{
    private static string $langPath = '';
    private static string $currentLocale = 'bn';
    private static string $fallbackLocale = 'en';
    private static array $loaded = [];

    private static array $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    private static array $enDigits = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];

    private static array $bnMonths = [
        1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
        5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
        9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর'
    ];

    private static array $enMonths = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];

    private static array $bnDays = [
        'Sunday' => 'রবিবার', 'Monday' => 'সোমবার', 'Tuesday' => 'মঙ্গলবার',
        'Wednesday' => 'বুধবার', 'Thursday' => 'বৃহস্পতিবার', 'Friday' => 'শুক্রবার',
        'Saturday' => 'শনিবার'
    ];

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

    public static function getFallbackLocale(): string
    {
        return self::$fallbackLocale;
    }

    public static function getAvailableLocales(): array
    {
        return ['bn', 'en'];
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
            $valStr = (string)$value;
            if ($targetLocale === 'bn' && is_numeric($value)) {
                $valStr = self::toBanglaNumber($valStr);
            }
            $line = str_replace(
                [':' . $placeholder, '{' . $placeholder . '}'],
                $valStr,
                $line
            );
        }

        return $line;
    }

    /**
     * Converts ASCII/English numbers to Bangla numerals.
     */
    public static function toBanglaNumber(int|float|string $number): string
    {
        return strtr((string)$number, self::$bnDigits);
    }

    /**
     * Converts Bangla numerals to ASCII/English numbers.
     */
    public static function toEnglishNumber(string $number): string
    {
        return strtr($number, self::$enDigits);
    }

    /**
     * Localizes date/time with appropriate month names, AM/PM, and numerals.
     */
    public static function formatDate(string|DateTimeInterface|null $dateTime, string $format = 'medium', ?string $locale = null): string
    {
        if ($dateTime === null) {
            return '';
        }

        $loc = $locale ?? self::$currentLocale;

        if (is_string($dateTime)) {
            try {
                $dt = new DateTime($dateTime);
            } catch (Exception) {
                return $dateTime;
            }
        } else {
            $dt = $dateTime;
        }

        $day = (int)$dt->format('j');
        $month = (int)$dt->format('n');
        $year = (int)$dt->format('Y');
        $hour12 = (int)$dt->format('g');
        $minute = $dt->format('i');
        $ampm = $dt->format('a'); // am / pm

        if ($loc === 'bn') {
            $bnDay = self::toBanglaNumber($day);
            $bnYear = self::toBanglaNumber($year);
            $bnMonth = self::$bnMonths[$month] ?? '';
            $bnHour = self::toBanglaNumber($hour12);
            $bnMin = self::toBanglaNumber($minute);
            $bnAmpm = ($ampm === 'am') ? 'পূর্বাহ্ন' : 'অপরাহ্ন';

            return match ($format) {
                'short' => "{$bnDay}/" . self::toBanglaNumber(sprintf('%02d', $month)) . "/{$bnYear}",
                'date_only' => "{$bnDay} {$bnMonth}, {$bnYear}",
                'time_only' => "{$bnHour}:{$bnMin} {$bnAmpm}",
                'full' => (self::$bnDays[$dt->format('l')] ?? '') . ", {$bnDay} {$bnMonth} {$bnYear}, {$bnHour}:{$bnMin} {$bnAmpm}",
                default => "{$bnDay} {$bnMonth} {$bnYear}, {$bnHour}:{$bnMin} {$bnAmpm}",
            };
        }

        $enMonth = self::$enMonths[$month] ?? '';
        $enAmpm = strtoupper($ampm);

        return match ($format) {
            'short' => sprintf('%02d/%02d/%d', $day, $month, $year),
            'date_only' => "{$enMonth} {$day}, {$year}",
            'time_only' => "{$hour12}:{$minute} {$enAmpm}",
            'full' => $dt->format('l') . ", {$enMonth} {$day}, {$year} {$hour12}:{$minute} {$enAmpm}",
            default => "{$enMonth} {$day}, {$year} {$hour12}:{$minute} {$enAmpm}",
        };
    }

    /**
     * Formats currency/taka amounts.
     */
    public static function formatMoney(int|float $amount, ?string $locale = null): string
    {
        $loc = $locale ?? self::$currentLocale;
        $formatted = number_format($amount, 2);

        if ($loc === 'bn') {
            return '৳' . self::toBanglaNumber($formatted);
        }

        return 'BDT ' . $formatted;
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
