<?php

declare(strict_types=1);

use AmarMayor\Controllers\Web\HomeController;
use AmarMayor\Http\Controllers\AuthController;
use AmarMayor\Http\Router;

/** @var Router $r */
$r->get('/', [HomeController::class, 'index']);
$r->get('/htmx/status-check', [HomeController::class, 'htmxStatusCheck']);

// Authentication Routes
$r->get('/login', [AuthController::class, 'showLogin']);
$r->post('/login/password', [AuthController::class, 'loginPassword']);
$r->post('/auth/otp/request', [AuthController::class, 'requestOtp']);
$r->post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
$r->post('/logout', [AuthController::class, 'logout']);

