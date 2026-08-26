<?php

declare(strict_types=1);

use AmarMayor\Http\Request;

/**
 * Amar Mayor (আমার ময়মনসিংহ) — Front Controller Entry Point.
 */

// 1. Bootstrap Application
$app = require dirname(__DIR__) . '/bootstrap/app.php';

// 2. Capture HTTP Request
$request = Request::capture();

// 3. Handle Request & Dispatch Response
$response = $app['handle']($request);

// 4. Send Response to Client
$response->send();
