<?php

declare(strict_types=1);

namespace AmarMayor\Support;

use AmarMayor\Http\Response;
use Throwable;

/**
 * Centralized Application Error & Exception Handler.
 * Prevents stack trace / credential leaks in production environments.
 */
class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleException(Throwable $e): void
    {
        $isDebug = (bool)Config::get('app.debug', false);
        $requestId = Response::class; // or get from Logger

        Logger::error("Uncaught exception: " . $e->getMessage(), [
            'exception' => $e,
        ]);

        $isApi = isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/api/');

        if ($isApi) {
            $details = $isDebug ? [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ] : [];

            $response = Response::error(
                'INTERNAL_SERVER_ERROR',
                $isDebug ? $e->getMessage() : 'সার্ভারে সাময়িক সমস্যা হয়েছে। কারিগরি টিমকে জানানো হয়েছে।',
                500,
                $details
            );
            $response->send();
            exit(1);
        }

        if ($isDebug) {
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=UTF-8');
            }
            echo '<!DOCTYPE html><html lang="bn"><head><title>System Error (Debug Mode)</title>';
            echo '<style>body{font-family:monospace;background:#1a1a1a;color:#f8f9fa;padding:2rem;} .box{background:#2d2d2d;padding:1.5rem;border-radius:8px;border-left:4px solid #dc3545;} pre{overflow-x:auto;}</style></head><body>';
            echo '<div class="box"><h2>💥 Uncaught ' . htmlspecialchars(get_class($e)) . '</h2>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Location:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
            echo '<h3>Stack Trace:</h3><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div></body></html>';
            exit(1);
        }

        // Production Safe HTML View
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        echo '<!DOCTYPE html><html lang="bn"><head><meta charset="utf-8"><title>সাময়িক ত্রুটি - আমার ময়মনসিংহ</title>';
        echo '<style>body{font-family:sans-serif;background:#f8f9fa;color:#333;text-align:center;padding:5rem 1rem;} .card{max-width:500px;margin:0 auto;background:#fff;padding:2.5rem;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.08);}</style></head><body>';
        echo '<div class="card"><h1>⚠️ সাময়িক সমস্যা</h1><p>সার্ভারে একটি অপ্রত্যাশিত ত্রুটি ঘটেছে। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।</p>';
        echo '<a href="/" style="display:inline-block;margin-top:1.5rem;padding:0.6rem 1.2rem;background:#0d6efd;color:#fff;text-decoration:none;border-radius:6px;">প্রধান পৃষ্ঠায় ফিরে যান</a></div></body></html>';
        exit(1);
    }
}
