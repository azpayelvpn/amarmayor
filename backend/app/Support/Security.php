<?php

declare(strict_types=1);

namespace AmarMayor\Support;

/**
 * Security Utilities & Cryptographic Helpers.
 */
class Security
{
    /**
     * Contextual HTML escaping.
     */
    public static function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Generate cryptographically secure random hexadecimal string.
     */
    public static function randomToken(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }

    /**
     * Generate secure UUIDv4.
     */
    public static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Secure password hashing using Argon2id (or bcrypt fallback if Argon2id unavailable).
     */
    public static function hashPassword(string $password): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $options = (defined('PASSWORD_ARGON2ID') && $algo === PASSWORD_ARGON2ID)
            ? ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 2]
            : ['cost' => 12];

        $hash = password_hash($password, $algo, $options);
        if ($hash === false) {
            throw new \RuntimeException("Failed to generate secure password hash.");
        }
        return $hash;
    }

    /**
     * Verify password against hash.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Timing attack-resistant string comparison.
     */
    public static function timingSafeEquals(string $knownString, string $userString): bool
    {
        return hash_equals($knownString, $userString);
    }

    /**
     * Generate and store a CSRF token in session.
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = self::randomToken(32);
        }
        return (string)$_SESSION['_csrf_token'];
    }

    /**
     * Validate an incoming CSRF token.
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $sessionToken = $_SESSION['_csrf_token'] ?? '';
        return is_string($sessionToken) && $sessionToken !== '' && self::timingSafeEquals($sessionToken, $token);
    }

    /**
     * Normalizes Bangladeshi mobile numbers to standard E.164 (+8801XXXXXXXXX).
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Convert Bengali numerals if present
        $phone = Translator::toEnglishNumber(trim($phone));

        // Remove non-numeric characters except leading plus
        $phone = preg_replace('/[^\d+]/', '', $phone);

        if (preg_match('/^(\+?88)?(01[3-9]\d{8})$/', $phone, $matches)) {
            return '+88' . $matches[2];
        }

        return null;
    }

    /**
     * Generates HMAC-SHA256 keyed lookup hash for privacy-safe phone lookups.
     */
    public static function phoneLookupHash(string $phone): string
    {
        $normalized = self::normalizePhone($phone) ?: $phone;
        $key = Config::get('app.key', 'amar_mayor_default_secret_key');
        return hash_hmac('sha256', $normalized, $key);
    }
}
