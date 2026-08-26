<?php

declare(strict_types=1);

use AmarMayor\Support\Env;

return [
    'driver' => Env::get('SESSION_DRIVER', 'file'),
    'lifetime' => (int)Env::get('SESSION_LIFETIME', 120), // minutes
    'cookie_name' => Env::get('SESSION_COOKIE_NAME', 'amarmayor_session'),
    'save_path' => dirname(__DIR__) . '/storage/sessions',
    'path' => '/',
    'domain' => null,
    'secure' => (bool)Env::get('SESSION_SECURE_COOKIE', false),
    'http_only' => (bool)Env::get('SESSION_HTTP_ONLY', true),
    'same_site' => Env::get('SESSION_SAME_SITE', 'lax'),
];
