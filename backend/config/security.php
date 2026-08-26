<?php

declare(strict_types=1);

use AmarMayor\Support\Env;

return [
    'rate_limits' => [
        'global' => (int)Env::get('RATE_LIMIT_GLOBAL', 60),
        'auth' => (int)Env::get('RATE_LIMIT_AUTH', 10),
        'otp' => (int)Env::get('RATE_LIMIT_OTP', 3),
    ],

    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token', 'X-Idempotency-Key', 'Accept-Language'],
        'max_age' => 86400,
    ],
];
