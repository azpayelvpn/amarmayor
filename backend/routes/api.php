<?php

declare(strict_types=1);

use AmarMayor\Controllers\Api\HealthController;
use AmarMayor\Http\Controllers\AuthController;
use AmarMayor\Http\Controllers\CityController;
use AmarMayor\Http\Controllers\AdminOpsController;
use AmarMayor\Http\Controllers\CommandCenterController;
use AmarMayor\Http\Controllers\CommunicationController;
use AmarMayor\Http\Controllers\ComplaintConfigController;
use AmarMayor\Http\Controllers\ComplaintController;
use AmarMayor\Http\Controllers\ExecutiveController;
use AmarMayor\Http\Controllers\GovernanceController;
use AmarMayor\Http\Controllers\PublicAccountabilityController;
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

// Complaint Configuration & Taxonomy
$r->get('/complaints/categories', [ComplaintConfigController::class, 'getCategories']);
$r->get('/complaints/subcategories/{id}', [ComplaintConfigController::class, 'getSubcategory']);
$r->get('/complaints/config/routing-gaps', [ComplaintConfigController::class, 'getRoutingGaps']);
$r->get('/complaints/config/deadlines', [ComplaintConfigController::class, 'getDeadlines']);

// Citizen Complaint Core & Tracking
$r->post('/complaints', [ComplaintController::class, 'submit'], [AuthenticateMiddleware::class]);
$r->get('/complaints/my', [ComplaintController::class, 'getMyComplaints'], [AuthenticateMiddleware::class]);
$r->get('/complaints/track/{trackingNumber}', [ComplaintController::class, 'track']);
$r->get('/complaints/{id}', [ComplaintController::class, 'getComplaint']);

// Field Operations & Tasks
$r->post('/complaints/{id}/tasks', [ComplaintController::class, 'createTask'], [AuthenticateMiddleware::class]);
$r->post('/tasks/{taskId}/start', [ComplaintController::class, 'startTask'], [AuthenticateMiddleware::class]);
$r->post('/tasks/{taskId}/complete', [ComplaintController::class, 'completeTask'], [AuthenticateMiddleware::class]);

// Resolution Quality & Confirmation
$r->post('/complaints/{id}/verify', [ComplaintController::class, 'verify'], [AuthenticateMiddleware::class]);
$r->post('/complaints/{id}/confirm', [ComplaintController::class, 'confirm'], [AuthenticateMiddleware::class]);

// Executive Attention & Command Center
$r->get('/executive/attention-queue', [ExecutiveController::class, 'getAttentionQueue'], [AuthenticateMiddleware::class]);
$r->post('/executive/directives', [ExecutiveController::class, 'issueDirective'], [AuthenticateMiddleware::class]);
$r->post('/executive/explanation-requests', [ExecutiveController::class, 'requestExplanation'], [AuthenticateMiddleware::class]);
$r->get('/command-center/kpis', [CommandCenterController::class, 'getKpis'], [AuthenticateMiddleware::class]);
$r->get('/command-center/daily-brief', [CommandCenterController::class, 'getDailyBrief'], [AuthenticateMiddleware::class]);
$r->get('/command-center/dashboard', [CommandCenterController::class, 'getDashboard'], [AuthenticateMiddleware::class]);

// Structured Communication
$r->post('/complaints/{id}/messages', [CommunicationController::class, 'sendMessage'], [AuthenticateMiddleware::class]);
$r->get('/complaints/{id}/messages', [CommunicationController::class, 'getMessages'], [AuthenticateMiddleware::class]);
$r->post('/complaints/{id}/notes', [CommunicationController::class, 'addNote'], [AuthenticateMiddleware::class]);
$r->post('/office-messages', [CommunicationController::class, 'sendOfficeMessage'], [AuthenticateMiddleware::class]);

// Public Accountability & Directory
$r->get('/public/metrics', [PublicAccountabilityController::class, 'getMetrics']);
$r->get('/public/who-is-responsible', [PublicAccountabilityController::class, 'getWhoIsResponsible']);
$r->get('/public/notices', [PublicAccountabilityController::class, 'getNotices']);

// Admin Operations & Health
$r->get('/admin/platform/overview', [AdminOpsController::class, 'getPlatformOverview'], [AuthenticateMiddleware::class]);
$r->get('/admin/system/health', [AdminOpsController::class, 'getSystemHealth'], [AuthenticateMiddleware::class]);
$r->post('/admin/system/run-scheduler', [AdminOpsController::class, 'runScheduler'], [AuthenticateMiddleware::class]);

// Operational Intelligence
$r->get('/intelligence/recurring', [\AmarMayor\Http\Controllers\IntelligenceController::class, 'getRecurring'], [AuthenticateMiddleware::class]);
$r->get('/intelligence/hotspots', [\AmarMayor\Http\Controllers\IntelligenceController::class, 'getHotspots'], [AuthenticateMiddleware::class]);
$r->get('/intelligence/evaluate-project', [\AmarMayor\Http\Controllers\IntelligenceController::class, 'evaluateProject'], [AuthenticateMiddleware::class]);




