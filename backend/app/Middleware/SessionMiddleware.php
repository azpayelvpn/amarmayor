<?php

declare(strict_types=1);

namespace AmarMayor\Middleware;

use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Config;
use Closure;

/**
 * Initializes and manages session cookie parameters.
 */
class SessionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            $cookieName = (string)Config::get('session.cookie_name', 'amarmayor_session');
            $lifetime = (int)Config::get('session.lifetime', 120) * 60;
            $path = (string)Config::get('session.path', '/');
            $domain = Config::get('session.domain', '');
            $secure = (bool)Config::get('session.secure', false);
            $httpOnly = (bool)Config::get('session.http_only', true);
            $sameSite = (string)Config::get('session.same_site', 'Lax');

            if (!headers_sent()) {
                session_name($cookieName);
                session_set_cookie_params([
                    'lifetime' => $lifetime,
                    'path' => $path,
                    'domain' => $domain,
                    'secure' => $secure,
                    'httponly' => $httpOnly,
                    'samesite' => $sameSite,
                ]);
            }

            @session_start();
        }

        return $next($request);
    }
}
