<?php

declare(strict_types=1);

namespace AmarMayor\Middleware;

use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Logger;
use Closure;

/**
 * Attaches request correlation ID to context, logger, and response headers.
 */
class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->getRequestId();
        Logger::setRequestId($requestId);
        Response::setGlobalRequestId($requestId);

        /** @var Response $response */
        $response = $next($request);
        $response->setHeader('X-Request-ID', $requestId);

        return $response;
    }
}
