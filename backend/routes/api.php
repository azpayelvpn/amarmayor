<?php

declare(strict_types=1);

use AmarMayor\Controllers\Api\HealthController;
use AmarMayor\Http\Controllers\AuthController;
use AmarMayor\Middleware\AuthenticateMiddleware;
use AmarMayor\Http\Router;

/** @var Router $r */
$r->get('/health', [HealthController::class, 'check']);

// Public API Auth Routes (prefixed with /api/v1 by router group)
$r->post('/auth/otp/request', [AuthController::class, 'apiOtpRequest']);
$r->post('/auth/otp/verify', [AuthController::class, 'apiOtpVerify']);
$r->post('/auth/login', [AuthController::class, 'apiLogin']);

// Authenticated API Auth Routes
$r->get('/auth/me', [AuthController::class, 'apiMe'], [AuthenticateMiddleware::class]);
$r->post('/auth/logout', [AuthController::class, 'apiLogout'], [AuthenticateMiddleware::class]);
