<?php

declare(strict_types=1);

use AmarMayor\Controllers\Web\HomeController;
use AmarMayor\Http\Router;

/** @var Router $r */
$r->get('/', [HomeController::class, 'index']);
$r->get('/htmx/status-check', [HomeController::class, 'htmxStatusCheck']);
