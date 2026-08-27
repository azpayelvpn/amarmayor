<?php

declare(strict_types=1);

namespace AmarMayor\Auth\Otp;

use AmarMayor\Support\Config;
use AmarMayor\Support\Security;

/**
 * Transient Mock OTP Inbox Service for Local Development & Automated Testing.
 * Strictly blocked in production.
 */
class DevOtpInboxService
{
    private static string $storagePath = '';

    private static function getStorageFile(): string
    {
        if (empty(self::$storagePath)) {
            self::$storagePath = dirname(__DIR__, 4) . '/storage/cache/dev_otp_inbox.json';
        }
        return self::$storagePath;
    }

    /**
     * Record a new mock OTP entry during local development.
     */
    public static function recordOtp(string $phone, string $otp, int $ttlSeconds = 600): void
    {
        if (Config::get('app.env') === 'production') {
            return;
        }

        $entries = self::loadEntries();
        $now = time();

        // Remove old expired entries (> 30 mins)
        $filtered = array_values(array_filter($entries, function ($e) use ($now) {
            return ($e['created_timestamp'] ?? 0) > ($now - 1800);
        }));

        $newEntry = [
            'id' => uniqid('otp_', true),
            'phone' => $phone,
            'otp' => $otp,
            'created_at' => date('Y-m-d H:i:s', $now),
            'created_timestamp' => $now,
            'expires_at' => date('Y-m-d H:i:s', $now + $ttlSeconds),
            'expires_timestamp' => $now + $ttlSeconds,
            'used' => false,
        ];

        array_unshift($filtered, $newEntry);

        // Keep maximum 50 recent items
        $filtered = array_slice($filtered, 0, 50);

        self::saveEntries($filtered);
    }

    /**
     * Get recent OTP records for Developer Inbox.
     */
    public static function getRecentOtps(): array
    {
        if (Config::get('app.env') === 'production') {
            return [];
        }

        $entries = self::loadEntries();
        $now = time();

        // Purge expired entries older than 30 mins
        $valid = array_values(array_filter($entries, function ($e) use ($now) {
            return ($e['created_timestamp'] ?? 0) > ($now - 1800);
        }));

        self::saveEntries($valid);

        return $valid;
    }

    /**
     * Mark OTP as used when successfully verified.
     */
    public static function markUsed(string $phone, string $otp): void
    {
        if (Config::get('app.env') === 'production') {
            return;
        }

        $entries = self::loadEntries();
        $updated = false;
        $normTarget = Security::normalizePhone($phone) ?: $phone;

        foreach ($entries as &$entry) {
            $normEntry = Security::normalizePhone($entry['phone']) ?: $entry['phone'];
            if ($normEntry === $normTarget && $entry['otp'] === $otp && !$entry['used']) {
                $entry['used'] = true;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            self::saveEntries($entries);
        }
    }

    private static function loadEntries(): array
    {
        $file = self::getStorageFile();
        if (!file_exists($file)) {
            return [];
        }

        $data = @file_get_contents($file);
        if (!$data) {
            return [];
        }

        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function saveEntries(array $entries): void
    {
        $file = self::getStorageFile();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @file_put_contents($file, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}
