<?php

declare(strict_types=1);

namespace AmarMayor\Support;

/**
 * Structured Application Logger.
 * Writes sanitized JSON-structured log lines with Request ID correlation.
 */
class Logger
{
    private static string $logPath = '';
    private static string $requestId = 'sys-init';
    private static array $redactedKeys = [
        'password', 'password_confirmation', 'otp', 'token', 'auth_token',
        'mfa_secret', 'secret', 'access_token', 'refresh_token', 'card_number'
    ];

    public static function setLogPath(string $path): void
    {
        self::$logPath = $path;
    }

    public static function setRequestId(string $requestId): void
    {
        self::$requestId = $requestId;
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $minLevel = strtoupper((string)Config::get('logging.level', 'debug'));
        $levels = ['DEBUG' => 1, 'INFO' => 2, 'WARNING' => 3, 'ERROR' => 4];

        if (($levels[$level] ?? 1) < ($levels[$minLevel] ?? 1)) {
            return;
        }

        $record = [
            'timestamp' => date('Y-m-d\TH:i:sP'),
            'environment' => Config::get('app.env', 'local'),
            'level' => $level,
            'request_id' => self::$requestId,
            'message' => $message,
            'context' => self::sanitizeContext($context),
        ];

        $json = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        $targetFile = self::$logPath ?: dirname(__DIR__, 2) . '/storage/logs/app.log';
        $logDir = dirname($targetFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        @file_put_contents($targetFile, $json, FILE_APPEND | LOCK_EX);
    }

    private static function sanitizeContext(array $context): array
    {
        $sanitized = [];
        foreach ($context as $key => $value) {
            $lowerKey = strtolower((string)$key);
            if (in_array($lowerKey, self::$redactedKeys, true)) {
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeContext($value);
            } elseif ($value instanceof \Throwable) {
                $sanitized[$key] = [
                    'class' => get_class($value),
                    'message' => $value->getMessage(),
                    'code' => $value->getCode(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                ];
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
