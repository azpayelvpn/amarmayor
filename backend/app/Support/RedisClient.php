<?php

declare(strict_types=1);

namespace AmarMayor\Support;

use Throwable;

/**
 * Redis Abstraction & Client Adapter.
 * Provides caching, locking, and rate limiting with graceful local offline fallback.
 * Critical Rule: MySQL is the permanent source of truth; Redis is non-durable.
 */
class RedisClient
{
    private static mixed $client = null;
    private static bool $connected = false;
    private static array $memoryFallback = [];
    private static ?string $fallbackDir = null;

    public static function getClient(): mixed
    {
        if (self::$client === null) {
            self::connect();
        }
        return self::$client;
    }

    public static function set(string $key, mixed $value, int $ttlSeconds = 0): bool
    {
        $prefixedKey = self::prefix($key);
        $encoded = is_scalar($value) ? (string)$value : json_encode($value);

        if (self::isConnected()) {
            try {
                if ($ttlSeconds > 0) {
                    return (bool)self::$client->setex($prefixedKey, $ttlSeconds, $encoded);
                }
                return (bool)self::$client->set($prefixedKey, $encoded);
            } catch (Throwable $e) {
                Logger::warning("Redis set failed, falling back to memory: " . $e->getMessage());
            }
        }

        $expiresAt = $ttlSeconds > 0 ? time() + $ttlSeconds : 0;
        self::$memoryFallback[$prefixedKey] = [
            'value' => $encoded,
            'expires_at' => $expiresAt,
        ];
        self::writeFallbackFile($prefixedKey, $encoded, $expiresAt);

        return true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $prefixedKey = self::prefix($key);

        if (self::isConnected()) {
            try {
                $val = self::$client->get($prefixedKey);
                if ($val !== false && $val !== null) {
                    $decoded = json_decode((string)$val, true);
                    return (json_last_error() === JSON_ERROR_NONE && !is_numeric($val)) ? $decoded : $val;
                }
                return $default;
            } catch (Throwable $e) {
                Logger::warning("Redis get failed, falling back to memory: " . $e->getMessage());
            }
        }

        // 1. Check in-process static memory cache
        if (isset(self::$memoryFallback[$prefixedKey])) {
            $item = self::$memoryFallback[$prefixedKey];
            if ($item['expires_at'] === 0 || $item['expires_at'] >= time()) {
                $val = $item['value'];
                $decoded = json_decode((string)$val, true);
                return (json_last_error() === JSON_ERROR_NONE && !is_numeric($val)) ? $decoded : $val;
            }
            unset(self::$memoryFallback[$prefixedKey]);
            self::deleteFallbackFile($prefixedKey);
            return $default;
        }

        // 2. Check persistent disk fallback (cross-request survival)
        $diskItem = self::readFallbackFile($prefixedKey);
        if ($diskItem !== null) {
            if ($diskItem['expires_at'] === 0 || $diskItem['expires_at'] >= time()) {
                self::$memoryFallback[$prefixedKey] = $diskItem;
                $val = $diskItem['value'];
                $decoded = json_decode((string)$val, true);
                return (json_last_error() === JSON_ERROR_NONE && !is_numeric($val)) ? $decoded : $val;
            }
            self::deleteFallbackFile($prefixedKey);
        }

        return $default;
    }

    public static function delete(string $key): bool
    {
        $prefixedKey = self::prefix($key);
        unset(self::$memoryFallback[$prefixedKey]);
        self::deleteFallbackFile($prefixedKey);

        if (self::isConnected()) {
            try {
                return (bool)self::$client->del($prefixedKey);
            } catch (Throwable $e) {
                Logger::warning("Redis del failed: " . $e->getMessage());
            }
        }
        return true;
    }

    public static function increment(string $key, int $by = 1, int $ttlSeconds = 60): int
    {
        $prefixedKey = self::prefix($key);

        if (self::isConnected()) {
            try {
                $newVal = (int)self::$client->incrBy($prefixedKey, $by);
                if ($newVal === $by && $ttlSeconds > 0) {
                    self::$client->expire($prefixedKey, $ttlSeconds);
                }
                return $newVal;
            } catch (Throwable $e) {
                Logger::warning("Redis incr failed: " . $e->getMessage());
            }
        }

        $current = (int)(self::get($key, 0));
        $newVal = $current + $by;
        self::set($key, $newVal, $ttlSeconds);
        return $newVal;
    }

    public static function checkHealth(): array
    {
        if (self::isConnected()) {
            try {
                $ping = self::$client->ping();
                return [
                    'status' => 'healthy',
                    'connected' => true,
                    'driver' => 'phpredis',
                    'ping' => $ping === true || $ping === '+PONG' ? 'PONG' : (string)$ping,
                ];
            } catch (Throwable $e) {
                return [
                    'status' => 'degraded',
                    'connected' => false,
                    'driver' => 'memory_fallback',
                    'message' => 'Redis connection failed: ' . $e->getMessage(),
                ];
            }
        }

        return [
            'status' => 'offline_fallback',
            'connected' => false,
            'driver' => 'memory_fallback',
            'message' => 'Redis service is not running or phpredis extension is missing. Operating with safe local memory fallback.',
        ];
    }

    public static function isConnected(): bool
    {
        if (self::$client === null) {
            self::connect();
        }
        return self::$connected;
    }

    public static function flushFallback(): void
    {
        self::$memoryFallback = [];
        $dir = self::getFallbackDir();
        if (is_dir($dir)) {
            $files = glob($dir . '/*.cache');
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
        }
    }

    private static function getFallbackDir(): string
    {
        if (self::$fallbackDir === null) {
            self::$fallbackDir = dirname(__DIR__, 2) . '/storage/cache/redis_fallback';
        }
        if (!is_dir(self::$fallbackDir)) {
            @mkdir(self::$fallbackDir, 0777, true);
        }
        return self::$fallbackDir;
    }

    private static function getFallbackFilePath(string $prefixedKey): string
    {
        return self::getFallbackDir() . '/' . sha1($prefixedKey) . '.cache';
    }

    private static function writeFallbackFile(string $prefixedKey, string $encoded, int $expiresAt): void
    {
        $file = self::getFallbackFilePath($prefixedKey);
        $payload = json_encode([
            'key' => $prefixedKey,
            'value' => $encoded,
            'expires_at' => $expiresAt,
        ]);
        if ($payload !== false) {
            @file_put_contents($file, $payload, LOCK_EX);
        }
    }

    private static function readFallbackFile(string $prefixedKey): ?array
    {
        $file = self::getFallbackFilePath($prefixedKey);
        if (!file_exists($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if (!$raw) {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) && isset($decoded['value'], $decoded['expires_at']) ? $decoded : null;
    }

    private static function deleteFallbackFile(string $prefixedKey): void
    {
        $file = self::getFallbackFilePath($prefixedKey);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    private static function connect(): void
    {
        $host = (string)Config::get('redis.host', '127.0.0.1');
        $port = (int)Config::get('redis.port', 6379);
        $password = Config::get('redis.password', null);
        $timeout = (float)Config::get('redis.timeout', 1.0);

        if (!class_exists('Redis')) {
            self::$connected = false;
            self::$client = false;
            return;
        }

        try {
            $redis = new \Redis();
            $success = @$redis->connect($host, $port, $timeout);
            if ($success) {
                if (!empty($password)) {
                    $redis->auth($password);
                }
                $database = (int)Config::get('redis.database', 0);
                if ($database > 0) {
                    $redis->select($database);
                }
                self::$client = $redis;
                self::$connected = true;
                return;
            }
        } catch (Throwable $e) {
            Logger::debug("Redis connection skipped or unavailable: " . $e->getMessage());
        }

        self::$connected = false;
        self::$client = false;
    }

    private static function prefix(string $key): string
    {
        $prefix = (string)Config::get('redis.prefix', 'amarmayor:');
        return str_starts_with($key, $prefix) ? $key : $prefix . $key;
    }
}
