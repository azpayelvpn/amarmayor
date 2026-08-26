<?php

declare(strict_types=1);

namespace AmarMayor\Middleware;

use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Config;
use AmarMayor\Support\Translator;
use Closure;

/**
 * Resolves and sets active application language (Bangla default / English fallback).
 */
class LocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = (string)Config::get('app.locale', 'bn');

        // 1. Query parameter (?lang=en or ?lang=bn)
        $queryLang = $request->query('lang');
        if (in_array($queryLang, ['bn', 'en'], true)) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['app_locale'] = $queryLang;
            }
            Translator::setLocale($queryLang);
        } elseif (isset($_SESSION['app_locale']) && in_array($_SESSION['app_locale'], ['bn', 'en'], true)) {
            // 2. Stored session preference
            Translator::setLocale($_SESSION['app_locale']);
        } else {
            // 3. Accept-Language header or default
            $acceptLang = (string)$request->header('accept-language', '');
            if (str_starts_with(strtolower($acceptLang), 'en')) {
                Translator::setLocale('en');
            } else {
                Translator::setLocale($defaultLocale);
            }
        }

        return $next($request);
    }
}
