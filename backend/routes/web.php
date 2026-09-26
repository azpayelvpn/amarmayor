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

// Authenticated Citizen Portfolio & Profile
$r->get('/my-complaints', [ComplaintWebController::class, 'myComplaints']);
$r->get('/profile', [\AmarMayor\Controllers\Web\ProfileWebController::class, 'show']);
$r->post('/profile', [\AmarMayor\Controllers\Web\ProfileWebController::class, 'update']);

// Civic Governance, Wards & Public Notices
$r->get('/who-is-responsible', [CivicDirectoryWebController::class, 'whoIsResponsible']);
$r->get('/wards', [CivicDirectoryWebController::class, 'wards']);
$r->get('/my-area', [CivicDirectoryWebController::class, 'wards']);
$r->get('/notices', [CivicDirectoryWebController::class, 'notices']);
$r->get('/schedules', [CivicDirectoryWebController::class, 'schedules']);

// Community Upvote & Anti-Duplicate
$r->post('/complaints/{id}/support', [ComplaintWebController::class, 'support']);

// Authentication Routes
$r->get('/login', [AuthController::class, 'showLogin']);
$r->post('/login/password', [AuthController::class, 'loginPassword']);
$r->post('/auth/otp/request', [AuthController::class, 'requestOtp']);
$r->post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
$r->post('/logout', [AuthController::class, 'logout']);

// Notification Center & In-App Alerts
$r->get('/notifications', [\AmarMayor\Controllers\Web\NotificationWebController::class, 'index']);
$r->post('/notifications/{id}/read', [\AmarMayor\Controllers\Web\NotificationWebController::class, 'markRead']);
$r->post('/notifications/read-all', [\AmarMayor\Controllers\Web\NotificationWebController::class, 'markAllRead']);
$r->get('/notifications/unread-count', [\AmarMayor\Controllers\Web\NotificationWebController::class, 'unreadCount']);

// Internal Role Working Dashboards & Staff Operations
$r->get('/dashboard', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'index']);
$r->get('/dashboard/complaints/{number}', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'complaintDetail']);
$r->post('/dashboard/tasks/{id}/start', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'startTask']);
$r->post('/dashboard/tasks/{id}/complete', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'completeTask']);
$r->post('/dashboard/tasks/{id}/verify', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'verifyTask']);
$r->post('/dashboard/tasks/dispatch-squad', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'dispatchSquad']);
$r->post('/dashboard/workforce/request', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'requestWorkforce']);
$r->post('/dashboard/workforce/allocate', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'allocateWorkforce']);
$r->post('/dashboard/executive/directive', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'issueDirective']);
$r->post('/dashboard/directives/{id}/respond', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'respondDirective']);
$r->post('/dashboard/support-requests/create', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'createSupportRequest']);
$r->post('/dashboard/support-requests/{id}/respond', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'respondSupportRequest']);
$r->post('/dashboard/support-requests/{id}/verify-receipt', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'verifySupportReceipt']);
$r->post('/dashboard/complaints/{id}/internal-notes', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'addInternalNote']);
$r->post('/dashboard/call-center/submit', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'submitCallCenterIntake']);
$r->post('/dashboard/ceo/explanation-requests', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'requestExplanation']);
$r->post('/dashboard/councillor/referral', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'councillorReferral']);
$r->post('/dashboard/inspection-notes', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'addInspectionNote']);
$r->post('/dashboard/control-room/re-route', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'reRouteComplaint']);
$r->post('/dashboard/notices/create', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'createNotice']);
$r->post('/dashboard/tech/run-jobs', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'runPendingJobs']);
$r->post('/dashboard/tech/clear-cache', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'clearCache']);
$r->post('/dashboard/team-leader/report-progress', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'reportTeamProgress']);

// Executive Reporting & Analytics Center
$r->get('/dashboard/reports', [\AmarMayor\Controllers\Web\ReportWebController::class, 'index']);
$r->get('/dashboard/reports/print', [\AmarMayor\Controllers\Web\ReportWebController::class, 'printView']);
$r->get('/dashboard/reports/export', [\AmarMayor\Controllers\Web\ReportWebController::class, 'exportCsv']);

// Field Operations Printable Route-Sheet for Non-Smartphone Sanitation Crews
$r->get('/dashboard/tasks/print-sheet', [\AmarMayor\Controllers\Web\DashboardWebController::class, 'printTaskSheet']);

// Local Development Testing Tools (Strictly 404 in Production)
$r->get('/dev/otp-inbox', [\AmarMayor\Controllers\Web\DevTestingController::class, 'otpInbox']);
$r->get('/dev/testing-access', [\AmarMayor\Controllers\Web\DevTestingController::class, 'testingAccess']);


