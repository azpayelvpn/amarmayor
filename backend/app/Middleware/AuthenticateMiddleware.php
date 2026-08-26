<?php

declare(strict_types=1);

namespace AmarMayor\Middleware;

use AmarMayor\Auth\Auth;
use AmarMayor\Auth\AuthService;
use AmarMayor\Auth\User;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use Closure;

class AuthenticateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = null;

        // 1. Check Bearer Token in Authorization header
        $authHeader = $request->getHeader('authorization');
        if ($authHeader && str_starts_with(strtolower($authHeader), 'bearer ')) {
            $token = trim(substr($authHeader, 7));
            $authService = new AuthService();
            $user = $authService->authenticateWithToken($token);
        } elseif (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            $user = User::findById((int)$_SESSION['user_id']);
        }

        Auth::setUser($user);

        // 2. Check if user is authenticated
        if (!$user) {
            if ($request->isJson() || str_starts_with($request->getPath(), '/api/')) {
                return Response::json([
                    'code' => 'UNAUTHORIZED',
                    'message' => __('auth.unauthorized'),
                ], 401);
            }

            return Response::redirect('/login');
        }

        return $next($request);
    }
}
