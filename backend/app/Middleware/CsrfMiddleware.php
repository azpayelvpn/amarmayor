<?php

declare(strict_types=1);

namespace AmarMayor\Middleware;

use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Support\Security;
use Closure;

/**
 * Validates CSRF tokens for browser state-changing requests (POST, PUT, PATCH, DELETE).
 */
class CsrfMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input('_csrf_token')
                ?: $request->header('x-csrf-token')
                ?: $request->header('x-xsrf-token');

            if (!Security::validateCsrfToken(is_string($token) ? $token : null)) {
                if ($request->isJson() || $request->isHtmx()) {
                    return Response::error('CSRF_TOKEN_INVALID', 'নিরাপত্তা টোকেনটি সঠিক নয়। অনুগ্রহ করে পৃষ্ঠাটি রিফ্রেশ করুন।', 419);
                }
                return Response::html('<h1>419 Page Expired</h1><p>নিরাপত্তা টোকেনের মেয়াদ উত্তীর্ণ হয়েছে। অনুগ্রহ করে পূর্বের পৃষ্ঠায় ফিরে রিফ্রেশ করুন।</p>', 419);
            }
        }

        return $next($request);
    }
}
