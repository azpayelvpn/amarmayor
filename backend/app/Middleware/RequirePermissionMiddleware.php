<?php

declare(strict_types=1);

namespace AmarMayor\Middleware;

use AmarMayor\Auth\Auth;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use Closure;

class RequirePermissionMiddleware
{
    private string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::can($this->permission)) {
            if ($request->isJson() || str_starts_with($request->getPath(), '/api/')) {
                return Response::json([
                    'success' => false,
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => __('auth.forbidden'),
                        'required_permission' => $this->permission,
                    ],
                ], 403);
            }

            return Response::html(view('errors/403', [
                'message' => __('auth.forbidden'),
            ], 'layouts/app')->getBody(), 403);
        }

        return $next($request);
    }
}
