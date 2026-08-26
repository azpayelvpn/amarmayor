<?php

declare(strict_types=1);

use AmarMayor\Controllers\Api\HealthController;
use AmarMayor\Http\Router;

/** @var Router $r */
$r->get('/health', [HealthController::class, 'check']);
