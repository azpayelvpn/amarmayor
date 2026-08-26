<?php

declare(strict_types=1);

use AmarMayor\Controllers\Web\CivicDirectoryWebController;
use AmarMayor\Controllers\Web\ComplaintWebController;
use AmarMayor\Controllers\Web\HomeController;
use AmarMayor\Http\Controllers\AuthController;
use AmarMayor\Http\Router;

/** @var Router $r */

// Homepage & Public Live Updates
$r->get('/', [HomeController::class, 'index']);
$r->get('/htmx/status-check', [HomeController::class, 'htmxStatusCheck']);

// Citizen Complaint Lifecycle
$r->get('/complaints/create', [ComplaintWebController::class, 'create']);
$r->post('/complaints/create', [ComplaintWebController::class, 'store']);
$r->get('/submit', [ComplaintWebController::class, 'create']);

// Public Complaint Tracking & Citizen Confirmation / Reopen
$r->get('/track', [ComplaintWebController::class, 'track']);
$r->get('/track/{trackingNumber}', [ComplaintWebController::class, 'track']);
$r->post('/complaints/{id}/confirm-resolution', [ComplaintWebController::class, 'confirmResolution']);

// Authenticated Citizen Portfolio
$r->get('/my-complaints', [ComplaintWebController::class, 'myComplaints']);

// Civic Governance, Wards & Public Notices
$r->get('/who-is-responsible', [CivicDirectoryWebController::class, 'whoIsResponsible']);
$r->get('/wards', [CivicDirectoryWebController::class, 'wards']);
$r->get('/notices', [CivicDirectoryWebController::class, 'notices']);

// Authentication Routes
$r->get('/login', [AuthController::class, 'showLogin']);
$r->post('/login/password', [AuthController::class, 'loginPassword']);
$r->post('/auth/otp/request', [AuthController::class, 'requestOtp']);
$r->post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
$r->post('/logout', [AuthController::class, 'logout']);

// Internal Role Working Dashboards & Staff Operations
$r->get('/dashboard', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'index']);
$r->post('/dashboard/tasks/{id}/start', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'startTask']);
$r->post('/dashboard/tasks/{id}/complete', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'completeTask']);
$r->post('/dashboard/tasks/{id}/verify', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'verifyTask']);
$r->post('/dashboard/executive/directive', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'issueDirective']);

// Local Development Testing Tools (Strictly 404 in Production)
$r->get('/dev/otp-inbox', [\AmarMayor\Controllers\Web\DevTestingController::class, 'otpInbox']);
$r->get('/dev/testing-access', [\AmarMayor\Controllers\Web\DevTestingController::class, 'testingAccess']);


