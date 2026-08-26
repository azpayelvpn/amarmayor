<?php

declare(strict_types=1);

namespace AmarMayor\Middleware;

use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Config;
use Closure;

/**
 * Handles CORS headers for REST API endpoints.
 */
class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = (array)Config::get('security.cors.allowed_origins', ['*']);
        $allowedMethods = implode(', ', (array)Config::get('security.cors.allowed_methods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']));
        $allowedHeaders = implode(', ', (array)Config::get('security.cors.allowed_headers', ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token', 'X-Idempotency-Key']));
        $maxAge = (string)Config::get('security.cors.max_age', 86400);

        $origin = $request->header('origin', '*');
        $resolvedOrigin = in_array('*', $allowedOrigins, true) ? '*' : (in_array($origin, $allowedOrigins, true) ? $origin : '');

        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response('', 204);
            if ($resolvedOrigin) {
                $response->setHeader('Access-Control-Allow-Origin', $resolvedOrigin);
            }
            $response->setHeader('Access-Control-Allow-Methods', $allowedMethods);
            $response->setHeader('Access-Control-Allow-Headers', $allowedHeaders);
            $response->setHeader('Access-Control-Max-Age', $maxAge);
            return $response;
        }

        /** @var Response $response */
        $response = $next($request);
        if ($resolvedOrigin) {
            $response->setHeader('Access-Control-Allow-Origin', $resolvedOrigin);
        }
        $response->setHeader('Access-Control-Allow-Methods', $allowedMethods);
        $response->setHeader('Access-Control-Allow-Headers', $allowedHeaders);

        return $response;
    }
}
