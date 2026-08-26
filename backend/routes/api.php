<?php

declare(strict_types=1);

use AmarMayor\Controllers\Api\HealthController;
use AmarMayor\Http\Controllers\AuthController;
use AmarMayor\Http\Controllers\CityController;
use AmarMayor\Http\Controllers\GovernanceController;
use AmarMayor\Http\Controllers\WorkforceController;
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

// City Structure Directory
$r->get('/city/profile', [CityController::class, 'getProfile']);
$r->get('/city/zones', [CityController::class, 'getZones']);
$r->get('/city/wards/{id}', [CityController::class, 'getWard']);
$r->get('/city/reserved-seats', [CityController::class, 'getReservedSeats']);

// Governance & Civic Leadership Directory
$r->get('/governance/leadership', [GovernanceController::class, 'getLeadership']);
$r->get('/governance/wards/{wardId}/representatives', [GovernanceController::class, 'getWardRepresentatives']);
$r->get('/governance/wards/{wardId}/history', [GovernanceController::class, 'getWardHistory']);

// Workforce & Operational Directory
$r->get('/workforce/departments', [WorkforceController::class, 'getDepartments']);
$r->get('/workforce/teams', [WorkforceController::class, 'getTeams']);

